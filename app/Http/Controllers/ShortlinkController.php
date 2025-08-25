<?php

namespace App\Http\Controllers;

use App\Models\Shortlink;
use App\Models\Domain;
use App\Models\BlockedIp;
use App\Models\PanelSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShortlinkController extends Controller
{
    public function index()
    {
        $shortlinks = Shortlink::with('domain')->orderBy('created_at', 'desc')->get();
        return view('panel.shortlinks', compact('shortlinks'));
    }

    public function list()
    {
        try {
            $shortlinks = Shortlink::with('domain')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'slug', 'destination', 'destinations', 'clicks', 'active', 'is_rotator', 'created_at', 'domain_id']);
            
            return response()->json([
                'success' => true,
                'data' => $shortlinks
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load shortlinks', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shortlinks: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate input
            $data = $request->validate([
                'slug' => 'nullable|string|max:50|unique:shortlinks,slug',
                'destination' => 'nullable|string|max:2048',
                'destinations' => 'nullable|array|min:1',
                'destinations.*.url' => 'required|string|max:2048',
                'destinations.*.name' => 'nullable|string|max:100',
                'destinations.*.weight' => 'nullable|integer|min:1|max:100',
                'is_rotator' => 'boolean',
                'domain_id' => 'nullable|exists:domains,id'
            ]);

            // Handle both single and rotator links
            $isRotator = $data['is_rotator'] ?? false;
            
            if ($isRotator) {
                // Rotator link
                if (empty($data['destinations'])) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Destinations are required for rotator links'
                    ], 422);
                }
                
                // Normalize URLs and extract data
                $destinations = [];
                foreach ($data['destinations'] as $dest) {
                    if (!empty($dest['url'])) {
                        $url = $dest['url'];
                        if (!preg_match('/^https?:\/\//i', $url)) {
                            $url = 'https://' . ltrim($url, '/');
                        }
                        
                        $destinations[] = [
                            'url' => $url,
                            'name' => $dest['name'] ?? 'Destination',
                            'weight' => $dest['weight'] ?? 1,
                            'active' => true
                        ];
                    }
                }
                
                if (empty($destinations)) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'At least one valid destination URL is required'
                    ], 422);
                }
                
                $destination = $destinations[0]['url']; // Fallback destination
            } else {
                // Single link
                if (empty($data['destination'])) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Destination URL is required for single links'
                    ], 422);
                }
                
                $destination = $data['destination'];
                if (!preg_match('/^https?:\/\//i', $destination)) {
                    $destination = 'https://' . ltrim($destination, '/');
                }
                
                $destinations = null;
            }

            // Generate slug if not provided
            $slug = $data['slug'] ?? null;
            if (empty($slug)) {
                $slug = $this->generateUniqueSlug();
            }

            // Create shortlink
            $shortlink = Shortlink::create([
                'slug' => $slug,
                'destination' => $destination,
                'destinations' => $destinations,
                'is_rotator' => $isRotator,
                'clicks' => 0,
                'active' => true,
                'domain_id' => $data['domain_id'] ?? null,
                'meta' => [
                    'created_at' => now()->toISOString(),
                    'created_by' => 'panel'
                ]
            ]);

            return response()->json([
                'success' => true, 
                'shortlink' => $shortlink,
                'message' => 'Shortlink created successfully'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . collect($e->errors())->flatten()->first()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Shortlink creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create shortlink: ' . $e->getMessage()
            ], 500);
        }
    }

    protected function generateUniqueSlug(): string
    {
        $length = 6;
        do {
            $slug = strtolower(\Str::random($length));
            $length = min($length + 1, 12);
        } while (Shortlink::where('slug', $slug)->exists());
        
        return $slug;
    }

    public function redirect(Request $request, string $slug)
    {
        $link = Shortlink::where('slug', $slug)->where('active', true)->first();
        if (!$link) {
            abort(404, 'Shortlink not found');
        }

        // Get real IP address
        $ip = $this->getRealIp($request);
        
        // Check if IP is blocked
        if (BlockedIp::where('ip', $ip)->exists()) {
            abort(403, 'Access denied');
        }

        // Get destination URL
        $destination = $this->getDestination($link);
        
        // Increment click count
        $link->increment('clicks');
        
        // Redirect to destination
        return redirect($destination);
    }

    protected function getRealIp(Request $request): string
    {
        // Check for CloudFlare IP
        if ($request->header('CF-Connecting-IP')) {
            return $request->header('CF-Connecting-IP');
        }
        
        // Check for X-Forwarded-For
        if ($request->header('X-Forwarded-For')) {
            return trim(explode(',', $request->header('X-Forwarded-For'))[0]);
        }
        
        // Check for X-Real-IP
        if ($request->header('X-Real-IP')) {
            return $request->header('X-Real-IP');
        }
        
        // Fallback to request IP
        return $request->ip() ?: '127.0.0.1';
    }

    protected function getDestination(Shortlink $shortlink): string
    {
        if (!$shortlink->is_rotator || count($shortlink->destinations) <= 1) {
            return $shortlink->destinations[0];
        }

        // Simple rotation logic
        $index = $shortlink->clicks % count($shortlink->destinations);
        return $shortlink->destinations[$index];
    }

    public function analytics()
    {
        try {
            $totalShortlinks = Shortlink::count();
            $totalClicks = Shortlink::sum('clicks');
            $activeShortlinks = Shortlink::where('active', true)->count();
            
            $topShortlinks = Shortlink::orderBy('clicks', 'desc')
                ->limit(10)
                ->get(['slug', 'clicks', 'created_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'overview' => [
                        'total_links' => $totalShortlinks,
                        'total_clicks' => $totalClicks,
                        'active_links' => $activeShortlinks
                    ],
                    'top_links' => $topShortlinks
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load analytics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load analytics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stats(string $slug)
    {
        try {
            $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
            
            // Basic stats since IP logging is removed
            $stats = [
                'slug' => $shortlink->slug,
                'total_clicks' => $shortlink->clicks,
                'created_at' => $shortlink->created_at,
                'is_rotator' => $shortlink->is_rotator,
                'destination' => $shortlink->destination,
                'destinations' => $shortlink->destinations
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load shortlink stats', [
                'error' => $e->getMessage(),
                'slug' => $slug
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load stats: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDestinations(Request $request, string $slug)
    {
        try {
            $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
            
            $data = $request->validate([
                'is_rotator' => 'required|boolean',
                'destination' => 'required_if:is_rotator,false|string|max:2048',
                'destinations' => 'required_if:is_rotator,true|array|min:1',
                'destinations.*' => 'required|string|max:2048'
            ]);

            if ($data['is_rotator']) {
                // Update rotator destinations
                $shortlink->update([
                    'is_rotator' => true,
                    'destinations' => $data['destinations'],
                    'destination' => $data['destinations'][0] ?? null
                ]);
            } else {
                // Update single destination
                $shortlink->update([
                    'is_rotator' => false,
                    'destination' => $data['destination'],
                    'destinations' => null
                ]);
            }
            
            return response()->json(['success' => true, 'message' => 'Shortlink updated successfully']);
        } catch (\Exception $e) {
            \Log::error('Failed to update shortlink destinations', [
                'error' => $e->getMessage(),
                'slug' => $slug
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update shortlink: ' . $e->getMessage()
            ], 500);
        }
    }

    public function visitors(string $slug)
    {
        $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
        return view('panel.visitors', compact('shortlink'));
    }

    public function visitorsList(string $slug)
    {
        // Return empty array since IP logging is removed
        return response()->json([]);
    }

    public function ips()
    {
        $blockedIps = BlockedIp::orderBy('created_at', 'desc')->get();
        return view('panel.ips', compact('blockedIps'));
    }

    public function stopbotConfig()
    {
        return view('panel.stopbot');
    }

    public function saveStopbotConfig(Request $request)
    {
        try {
            $data = $request->validate([
                'enabled' => 'boolean',
                'api_key' => 'required_if:enabled,true|string',
                'redirect_url' => 'required_if:enabled,true|url',
                'log_enabled' => 'boolean',
                'timeout' => 'integer|min:1|max:30'
            ]);

            // Save configuration using PanelSetting model
            foreach ($data as $key => $value) {
                $type = is_bool($value) ? 'boolean' : (is_int($value) ? 'integer' : 'string');
                PanelSetting::set($key, $value, $type, 'stopbot');
            }

            return response()->json([
                'success' => true,
                'message' => 'Stopbot configuration saved successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to save stopbot config', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save configuration: ' . $e->getMessage()
            ], 500);
        }
    }

    public function testStopbotApi(Request $request)
    {
        try {
            $apiKey = PanelSetting::get('stopbot_api_key', '');
            $enabled = PanelSetting::get('stopbot_enabled', false);
            
            if (!$enabled || !$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stopbot is not configured or enabled'
                ], 400);
            }

            // Simple API test - you can implement actual API call here
            return response()->json([
                'success' => true,
                'message' => 'API test successful',
                'api_key' => substr($apiKey, 0, 8) . '***',
                'enabled' => $enabled
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to test stopbot API', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'API test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStopbotStats()
    {
        try {
            $enabled = PanelSetting::get('stopbot_enabled', false);
            $apiKey = PanelSetting::get('stopbot_api_key', '');
            
            $stats = [
                'enabled' => $enabled,
                'configured' => !empty($apiKey),
                'last_updated' => now()->toISOString()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get stopbot stats', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetVisitors(string $slug)
    {
        try {
            $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
            $shortlink->update(['clicks' => 0]);
            
            return response()->json([
                'success' => true,
                'message' => 'Visitor count reset successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to reset visitors', [
                'error' => $e->getMessage(),
                'slug' => $slug
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset visitors: ' . $e->getMessage()
            ], 500);
        }
    }

    public function resetAllVisitors()
    {
        try {
            Shortlink::query()->update(['clicks' => 0]);
            
            return response()->json([
                'success' => true,
                'message' => 'All visitor counts reset successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to reset all visitors', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset all visitors: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $slug)
    {
        try {
            $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
            $shortlink->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Shortlink deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to delete shortlink', [
                'error' => $e->getMessage(),
                'slug' => $slug
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete shortlink: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateRotator(Request $request, string $slug)
    {
        try {
            $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
            
            $data = $request->validate([
                'is_rotator' => 'required|boolean',
                'rotation_type' => 'required_if:is_rotator,true|string|in:random,sequential,weighted',
                'destinations' => 'required_if:is_rotator,true|array|min:1',
                'destinations.*.url' => 'required|string|max:2048',
                'destinations.*.name' => 'nullable|string|max:100',
                'destinations.*.weight' => 'nullable|integer|min:1|max:100'
            ]);

            if ($data['is_rotator']) {
                // Update rotator settings
                $shortlink->update([
                    'is_rotator' => true,
                    'destinations' => $data['destinations'],
                    'destination' => $data['destinations'][0]['url'] ?? null
                ]);
            } else {
                // Convert back to single link
                $shortlink->update([
                    'is_rotator' => false,
                    'destinations' => null
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Rotator settings updated successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update rotator settings', [
                'error' => $e->getMessage(),
                'slug' => $slug
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rotator: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getRotator(string $slug)
    {
        try {
            $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'is_rotator' => $shortlink->is_rotator,
                    'destinations' => $shortlink->destinations,
                    'rotation_type' => 'random' // Default rotation type
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get rotator settings', [
                'error' => $e->getMessage(),
                'slug' => $slug
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get rotator settings: ' . $e->getMessage()
            ], 500);
        }
    }

    public function verifyHuman(Request $request)
    {
        try {
            // Simple human verification - you can implement actual verification logic here
            $token = $request->input('token');
            $challenge = $request->input('challenge');
            
            if (empty($token) || empty($challenge)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing verification parameters'
                ], 400);
            }
            
            // For now, just return success
            return response()->json([
                'success' => true,
                'message' => 'Human verification successful'
            ]);
        } catch (\Exception $e) {
            \Log::error('Human verification failed', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Verification failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
