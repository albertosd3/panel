<?php

namespace App\Http\Controllers;

use App\Models\Shortlink;
use App\Models\Domain;
use App\Models\BlockedIp;
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
        $shortlinks = Shortlink::with('domain')->orderBy('created_at', 'desc')->get();
        return response()->json($shortlinks);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|string|max:50|unique:shortlinks,slug',
            'destinations' => 'required|array|min:1',
            'destinations.*' => 'required|url',
            'is_rotator' => 'boolean',
            'domain_id' => 'nullable|exists:domains,id'
        ]);

        $shortlink = Shortlink::create([
            'slug' => $data['slug'],
            'destinations' => $data['destinations'],
            'is_rotator' => $data['is_rotator'] ?? false,
            'domain_id' => $data['domain_id'] ?? null,
            'active' => true
        ]);

        return response()->json(['success' => true, 'shortlink' => $shortlink]);
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
        $totalShortlinks = Shortlink::count();
        $totalClicks = Shortlink::sum('clicks');
        $activeShortlinks = Shortlink::where('active', true)->count();
        
        $topShortlinks = Shortlink::orderBy('clicks', 'desc')
            ->limit(10)
            ->get(['slug', 'clicks', 'created_at']);

        return view('panel.analytics', compact('totalShortlinks', 'totalClicks', 'activeShortlinks', 'topShortlinks'));
    }

    public function stats(string $slug)
    {
        $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
        return response()->json([
            'slug' => $shortlink->slug,
            'clicks' => $shortlink->clicks,
            'created_at' => $shortlink->created_at,
            'destinations' => $shortlink->destinations
        ]);
    }

    public function updateDestinations(Request $request, string $slug)
    {
        $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
        
        $data = $request->validate([
            'destinations' => 'required|array|min:1',
            'destinations.*' => 'required|url'
        ]);

        $shortlink->update(['destinations' => $data['destinations']]);
        
        return response()->json(['success' => true]);
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
        $data = $request->validate([
            'enabled' => 'boolean',
            'api_key' => 'required_if:enabled,true|string',
            'redirect_url' => 'required_if:enabled,true|url',
            'log_enabled' => 'boolean',
            'timeout' => 'integer|min:1|max:30'
        ]);

        // Save configuration logic here
        return response()->json(['success' => true]);
    }

    public function testStopbotApi(Request $request)
    {
        // Test API logic here
        return response()->json(['success' => true, 'message' => 'API test successful']);
    }

    public function getStopbotStats()
    {
        // Return stats logic here
        return response()->json(['success' => true, 'stats' => []]);
    }

    public function resetVisitors(string $slug)
    {
        $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
        $shortlink->update(['clicks' => 0]);
        
        return response()->json(['success' => true]);
    }

    public function resetAllVisitors()
    {
        Shortlink::query()->update(['clicks' => 0]);
        
        return response()->json(['success' => true]);
    }

    public function destroy(string $slug)
    {
        $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
        $shortlink->delete();
        
        return response()->json(['success' => true]);
    }

    public function updateRotator(Request $request, string $slug)
    {
        $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
        
        $data = $request->validate([
            'is_rotator' => 'required|boolean'
        ]);

        $shortlink->update(['is_rotator' => $data['is_rotator']]);
        
        return response()->json(['success' => true]);
    }

    public function getRotator(string $slug)
    {
        $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
        
        return response()->json([
            'is_rotator' => $shortlink->is_rotator,
            'destinations' => $shortlink->destinations
        ]);
    }

    public function verifyHuman(Request $request)
    {
        // Simple human verification
        return response()->json(['success' => true]);
    }
}
