<?php

namespace App\Http\Controllers;

use App\Jobs\RecordShortlinkHit;
use App\Models\BlockedIp;
use App\Models\PanelSetting;
use App\Models\Shortlink;
use App\Models\ShortlinkEvent;
use App\Models\ShortlinkVisitor;
use GeoIp2\Database\Reader as GeoIP2Reader;
use GeoIp2\WebService\Client as GeoIP2Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Jenssegers\Agent\Agent;

class ShortlinkController extends Controller
{
    public function index()
    {
        return view('panel.shortlinks');
    }

    public function list(Request $request)
    {
        $links = Shortlink::orderByDesc('id')
            ->limit(200)
            ->get(['id','slug','destination','clicks','active','created_at','is_rotator','rotation_type','destinations']);
        // Compute real clicks from events table based on bot counting setting
        $countBots = (bool) config('panel.count_bots', false);
        $eventCounts = ShortlinkEvent::select('shortlink_id', DB::raw('COUNT(*) as c'))
            ->when(!$countBots, fn($q) => $q->where('is_bot', false))
            ->whereIn('shortlink_id', $links->pluck('id'))
            ->groupBy('shortlink_id')->pluck('c','shortlink_id');
        
        $links->each(function ($l) use ($eventCounts) {
            $l->clicks = (int) ($eventCounts[$l->id] ?? 0);
        });
        
        return response()->json(['ok' => true, 'data' => $links]);
    }

    public function analytics(Request $request)
    {
        $countBots = (bool) config('panel.count_bots', false);
        $period = $request->get('period', 'week'); // day, week, month, year
        
        // General statistics
        $totalLinks = Shortlink::count();
        $totalClicks = ShortlinkEvent::when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
        
        // Period-based calculations
        $periodData = $this->getPeriodData($period, $countBots);
        
        // Today's clicks
        $todayClicks = ShortlinkEvent::whereDate('clicked_at', today())
            ->when(!$countBots, fn($q) => $q->where('is_bot', false))
            ->count();
        
        // Top countries
        $topCountries = ShortlinkEvent::select('country', DB::raw('COUNT(*) as count'))
            ->when(!$countBots, fn($q) => $q->where('is_bot', false))
            ->whereNotNull('country')
            ->groupBy('country')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
        
        // Device stats
        $deviceStats = ShortlinkEvent::select('device', DB::raw('COUNT(*) as count'))
            ->when(!$countBots, fn($q) => $q->where('is_bot', false))
            ->whereNotNull('device')
            ->groupBy('device')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
        
        // Browser stats
        $browserStats = ShortlinkEvent::select('browser', DB::raw('COUNT(*) as count'))
            ->when(!$countBots, fn($q) => $q->where('is_bot', false))
            ->whereNotNull('browser')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(10)
            ->get();
        
        // Top shortlinks by clicks
        $topLinks = Shortlink::select('shortlinks.slug', 'shortlinks.destination', DB::raw('COUNT(shortlink_events.id) as clicks'))
            ->leftJoin('shortlink_events', 'shortlinks.id', '=', 'shortlink_events.shortlink_id')
            ->when(!$countBots, fn($q) => $q->where('shortlink_events.is_bot', false))
            ->groupBy('shortlinks.id', 'shortlinks.slug', 'shortlinks.destination')
            ->orderByDesc('clicks')
            ->limit(10)
            ->get();

        // Calculate unique visitors
        $uniqueVisitors = ShortlinkVisitor::when(!$countBots, fn($q) => $q->where('is_bot', false))->distinct('ip')->count();
        
        // Calculate bot percentage
        $totalEvents = ShortlinkEvent::count();
        $botEvents = ShortlinkEvent::where('is_bot', true)->count();
        $botPercentage = $totalEvents > 0 ? round(($botEvents / $totalEvents) * 100, 1) : 0;
        
        // Calculate average clicks per link
        $avgClicksPerLink = $totalLinks > 0 ? round($totalClicks / $totalLinks, 1) : 0;
        
        return response()->json([
            'ok' => true,
            'data' => [
                'overview' => [
                    'total_links' => $totalLinks,
                    'total_clicks' => $totalClicks,
                    'unique_visitors' => $uniqueVisitors,
                    'bot_percentage' => $botPercentage . '%',
                    'today_clicks' => $todayClicks,
                    'avg_clicks_per_link' => $avgClicksPerLink
                ],
                'chart_data' => $periodData['timeline'],
                'current_period' => [
                    'total_clicks' => $periodData['comparison']['current'],
                    'unique_visitors' => $uniqueVisitors,
                    'bot_percentage' => $botPercentage . '%'
                ],
                'previous_period' => [
                    'total_clicks' => $periodData['comparison']['previous'],
                    'unique_visitors' => $uniqueVisitors, // This should be calculated separately
                    'bot_percentage' => $botPercentage . '%' // This should be calculated separately
                ],
                'top_countries' => $topCountries,
                'device_types' => $deviceStats,
                'top_browsers' => $browserStats,
                'popular_links' => $topLinks
            ]
        ]);
    }

    private function getPeriodData(string $period, bool $countBots): array
    {
        $timeline = [];
        $comparison = [];
        
        switch ($period) {
            case 'day':
                // Last 24 hours by hour
                for ($i = 23; $i >= 0; $i--) {
                    $hour = now()->subHours($i);
                    $clicks = ShortlinkEvent::whereBetween('clicked_at', [
                        $hour->copy()->startOfHour(),
                        $hour->copy()->endOfHour()
                    ])->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                    
                    $timeline[] = [
                        'date' => $hour->format('H:i'),
                        'clicks' => $clicks
                    ];
                }
                
                // Compare with yesterday
                $todayClicks = ShortlinkEvent::whereDate('clicked_at', today())
                    ->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                $yesterdayClicks = ShortlinkEvent::whereDate('clicked_at', today()->subDay())
                    ->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                
                $comparison = [
                    'current' => $todayClicks,
                    'previous' => $yesterdayClicks,
                    'change' => $yesterdayClicks > 0 ? (($todayClicks - $yesterdayClicks) / $yesterdayClicks) * 100 : 0,
                    'label' => 'vs Yesterday'
                ];
                break;
                
            case 'week':
                // Last 7 days
                for ($i = 6; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $clicks = ShortlinkEvent::whereDate('clicked_at', $date)
                        ->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                    
                    $timeline[] = [
                        'date' => $date->format('M d'),
                        'clicks' => $clicks
                    ];
                }
                
                // Compare with last week
                $thisWeekClicks = ShortlinkEvent::whereBetween('clicked_at', [
                    now()->startOfWeek(),
                    now()->endOfWeek()
                ])->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                
                $lastWeekClicks = ShortlinkEvent::whereBetween('clicked_at', [
                    now()->subWeek()->startOfWeek(),
                    now()->subWeek()->endOfWeek()
                ])->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                
                $comparison = [
                    'current' => $thisWeekClicks,
                    'previous' => $lastWeekClicks,
                    'change' => $lastWeekClicks > 0 ? (($thisWeekClicks - $lastWeekClicks) / $lastWeekClicks) * 100 : 0,
                    'label' => 'vs Last Week'
                ];
                break;
                
            case 'month':
                // Last 30 days
                for ($i = 29; $i >= 0; $i--) {
                    $date = now()->subDays($i);
                    $clicks = ShortlinkEvent::whereDate('clicked_at', $date)
                        ->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                    
                    $timeline[] = [
                        'date' => $date->format('M d'),
                        'clicks' => $clicks
                    ];
                }
                
                // Compare with last month
                $thisMonthClicks = ShortlinkEvent::whereBetween('clicked_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth()
                ])->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                
                $lastMonthClicks = ShortlinkEvent::whereBetween('clicked_at', [
                    now()->subMonth()->startOfMonth(),
                    now()->subMonth()->endOfMonth()
                ])->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                
                $comparison = [
                    'current' => $thisMonthClicks,
                    'previous' => $lastMonthClicks,
                    'change' => $lastMonthClicks > 0 ? (($thisMonthClicks - $lastMonthClicks) / $lastMonthClicks) * 100 : 0,
                    'label' => 'vs Last Month'
                ];
                break;
                
            case 'year':
                // Last 12 months
                for ($i = 11; $i >= 0; $i--) {
                    $month = now()->subMonths($i);
                    $clicks = ShortlinkEvent::whereBetween('clicked_at', [
                        $month->copy()->startOfMonth(),
                        $month->copy()->endOfMonth()
                    ])->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                    
                    $timeline[] = [
                        'date' => $month->format('M Y'),
                        'clicks' => $clicks
                    ];
                }
                
                // Compare with last year
                $thisYearClicks = ShortlinkEvent::whereBetween('clicked_at', [
                    now()->startOfYear(),
                    now()->endOfYear()
                ])->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                
                $lastYearClicks = ShortlinkEvent::whereBetween('clicked_at', [
                    now()->subYear()->startOfYear(),
                    now()->subYear()->endOfYear()
                ])->when(!$countBots, fn($q) => $q->where('is_bot', false))->count();
                
                $comparison = [
                    'current' => $thisYearClicks,
                    'previous' => $lastYearClicks,
                    'change' => $lastYearClicks > 0 ? (($thisYearClicks - $lastYearClicks) / $lastYearClicks) * 100 : 0,
                    'label' => 'vs Last Year'
                ];
                break;
        }
        
        return compact('timeline', 'comparison');
    }

    public function store(Request $request)
    {
        try {
            // Properly handle JSON input by replacing request data
            if ($request->isJson()) {
                $jsonData = $request->json()->all();
                $request->replace($jsonData);
            }

            // Debug incoming request
            \Log::info('Shortlink creation request', [
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'is_json' => $request->isJson(),
                'is_rotator' => $request->boolean('is_rotator'),
                'has_destination' => $request->has('destination'),
                'has_destinations' => $request->has('destinations'),
                'destinations_count' => is_array($request->get('destinations')) ? count($request->get('destinations')) : 0,
                'all_data' => $request->all()
            ]);

            $isRotator = $request->boolean('is_rotator');

            // Pre-normalize URLs so validation accepts inputs without scheme
            $normalized = $request->all();
            if ($isRotator && isset($normalized['destinations']) && is_array($normalized['destinations'])) {
                foreach ($normalized['destinations'] as $idx => $dest) {
                    $rawUrl = (string) ($dest['url'] ?? '');
                    if ($rawUrl !== '' && !preg_match('/^https?:\/\//i', $rawUrl)) {
                        $normalized['destinations'][$idx]['url'] = 'https://' . ltrim($rawUrl, '/');
                    }
                }
            } elseif (!$isRotator && isset($normalized['destination'])) {
                $rawUrl = (string) $normalized['destination'];
                if ($rawUrl !== '' && !preg_match('/^https?:\/\//i', $rawUrl)) {
                    $normalized['destination'] = 'https://' . ltrim($rawUrl, '/');
                }
            }
            // Replace request inputs with normalized values before validation
            $request->replace($normalized);

            $validationRules = [
                'slug' => ['nullable','alpha_dash','min:3','max:64','unique:shortlinks,slug'],
                'is_rotator' => ['boolean'],
            ];

            if ($isRotator) {
                // Rotator validation
                $validationRules['rotation_type'] = ['nullable','in:random,sequential,weighted'];
                $validationRules['destinations'] = ['required','array','min:1'];
                $validationRules['destinations.*.url'] = ['required','url','max:2048'];
                $validationRules['destinations.*.weight'] = ['nullable','integer','min:1','max:100'];
                $validationRules['destinations.*.active'] = ['nullable','boolean'];
                $validationRules['destinations.*.name'] = ['nullable','string','max:255'];
            } else {
                // Single destination validation
                $validationRules['destination'] = ['required','url','max:2048'];
            }

            $data = $request->validate($validationRules);

            // Ensure defaults for rotator metadata after validation
            if ($isRotator && !empty($data['destinations'])) {
                foreach ($data['destinations'] as $key => $dest) {
                    $data['destinations'][$key]['active'] = $dest['active'] ?? true;
                    $data['destinations'][$key]['weight'] = $dest['weight'] ?? 1;
                    $data['destinations'][$key]['name'] = $dest['name'] ?? '';
                }
            }

            $slug = $data['slug'] ?? null;
            if (!$slug || trim($slug) === '') {
                // Generate random slug
                $length = 6;
                do {
                    $slug = Str::lower(Str::random($length));
                    $length = min($length + 1, 12);
                } while (Shortlink::where('slug', $slug)->exists());
            }

            $linkData = [
                'slug' => $slug,
                'clicks' => 0,
                'active' => true,
                'meta' => [
                    'created_ip' => $request->ip(),
                    'created_by' => 'panel',
                    'created_at_formatted' => now()->format('Y-m-d H:i:s'),
                ],
                'is_rotator' => $isRotator,
                'rotation_type' => $data['rotation_type'] ?? 'random',
                'current_index' => 0,
            ];

            if ($isRotator) {
                $linkData['destination'] = $data['destinations'][0]['url'] ?? ''; // fallback destination
                $linkData['destinations'] = $data['destinations'];
            } else {
                $linkData['destination'] = $data['destination'];
                $linkData['destinations'] = null;
            }

            $link = Shortlink::create($linkData);

            return response()->json([
                'ok' => true,
                'data' => $link->fresh(),
                'short_url' => $link->full_url
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validasi gagal: ' . collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menyimpan shortlink: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function geoLookup(string $ip): array
    {
        $conf = config('panel.geoip');
        if (!($conf['enabled'] ?? true)) return [null, null, null, null];

        $country = $city = $asn = $org = null;
        try {
            if (!empty($conf['account_id']) && !empty($conf['license_key'])) {
                $client = new GeoIP2Client((int) $conf['account_id'], (string) $conf['license_key']);
                try {
                    $cityRec = $client->city($ip);
                    $country = $cityRec->country?->isoCode; $city = $cityRec->city?->name;
                } catch (\Throwable $e) {}
                try {
                    $asnRec = $client->asn($ip);
                    $asn = $asnRec->autonomousSystemNumber ? 'AS'.$asnRec->autonomousSystemNumber : null;
                    $org = $asnRec->autonomousSystemOrganization;
                } catch (\Throwable $e) {}
                return [$country, $city, $asn, $org];
            }

            $dbPath = $conf['database_path'] ?? null;
            if ($dbPath && file_exists($dbPath)) {
                $reader = new GeoIP2Reader($dbPath);
                $rec = $reader->city($ip);
                $country = $rec->country?->isoCode; $city = $rec->city?->name;
                $reader->close();
            }
        } catch (\Throwable $e) {
            // Ignore GeoIP failures
        }

        return [$country, $city, $asn, $org];
    }

    protected function recordHit(int $shortlinkId, array $payload): void
    {
        try {
            \Log::info('=== RECORD HIT START ===', [
                'shortlink_id' => $shortlinkId, 
                'payload' => $payload,
                'timestamp' => now()->toISOString()
            ]);
            
            // Validate required fields
            if (empty($payload['ip'])) {
                \Log::error('Missing IP address in payload', ['payload' => $payload]);
                throw new \Exception('IP address is required');
            }

            DB::transaction(function () use ($shortlinkId, $payload) {
                // Always create event record
                $eventData = array_merge($payload, [
                    'shortlink_id' => $shortlinkId,
                    'clicked_at' => now(),
                ]);
                
                \Log::info('Creating ShortlinkEvent with data', ['event_data' => $eventData]);
                
                $event = ShortlinkEvent::create($eventData);
                \Log::info('ShortlinkEvent created successfully', [
                    'event_id' => $event->id,
                    'shortlink_id' => $shortlinkId,
                    'ip' => $payload['ip']
                ]);

                // Increment clicks only if counting bots OR this is not a bot
                $countBots = (bool) config('panel.count_bots', false);
                $isBot = (bool) ($payload['is_bot'] ?? false);
                
                if ($countBots || !$isBot) {
                    $updated = Shortlink::where('id', $shortlinkId)->update([
                        'clicks' => DB::raw('clicks + 1')
                    ]);
                    \Log::info('Clicks incremented for shortlink', [
                        'shortlink_id' => $shortlinkId,
                        'rows_updated' => $updated
                    ]);
                } else {
                    \Log::info('Skipping click increment for bot', [
                        'shortlink_id' => $shortlinkId,
                        'is_bot' => $isBot,
                        'count_bots' => $countBots
                    ]);
                }

                // Record visitor summary (upsert per IP)
                try {
                    $ip = $payload['ip'];
                    \Log::info('Processing visitor record for IP', ['ip' => $ip]);
                    
                    $visitor = ShortlinkVisitor::firstOrNew([
                        'shortlink_id' => $shortlinkId,
                        'ip' => $ip,
                    ]);

                    if (!$visitor->exists) {
                        \Log::info('Creating new visitor record', ['ip' => $ip, 'shortlink_id' => $shortlinkId]);
                        $visitor->first_seen = now();
                        $visitor->hits = 0;
                        $visitor->country = $payload['country'] ?? null;
                        $visitor->city = $payload['city'] ?? null;
                        $visitor->asn = $payload['asn'] ?? null;
                        $visitor->org = $payload['org'] ?? null;
                    } else {
                        \Log::info('Updating existing visitor record', [
                            'visitor_id' => $visitor->id,
                            'ip' => $ip,
                            'current_hits' => $visitor->hits
                        ]);
                    }

                    $visitor->hits = ($visitor->hits ?? 0) + 1;
                    $visitor->last_seen = now();
                    $visitor->is_bot = $isBot;
                    
                    // Update geo info if not already set
                    if (empty($visitor->country) && !empty($payload['country'])) {
                        $visitor->country = $payload['country'];
                    }
                    if (empty($visitor->city) && !empty($payload['city'])) {
                        $visitor->city = $payload['city'];
                    }
                    if (empty($visitor->asn) && !empty($payload['asn'])) {
                        $visitor->asn = $payload['asn'];
                    }
                    if (empty($visitor->org) && !empty($payload['org'])) {
                        $visitor->org = $payload['org'];
                    }
                    
                    $saved = $visitor->save();
                    \Log::info('ShortlinkVisitor saved successfully', [
                        'visitor_id' => $visitor->id,
                        'ip' => $ip,
                        'hits' => $visitor->hits,
                        'saved' => $saved,
                        'is_bot' => $visitor->is_bot
                    ]);
                    
                } catch (\Throwable $e) {
                    \Log::error('Failed to upsert shortlink visitor', [
                        'error' => $e->getMessage(),
                        'shortlink_id' => $shortlinkId,
                        'ip' => $payload['ip'],
                        'trace' => $e->getTraceAsString()
                    ]);
                    // Don't re-throw - continue with the process
                }
            });
            
            \Log::info('=== RECORD HIT COMPLETED SUCCESSFULLY ===', [
                'shortlink_id' => $shortlinkId,
                'ip' => $payload['ip'],
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Throwable $e) {
            \Log::error('=== RECORD HIT FAILED ===', [
                'error' => $e->getMessage(),
                'shortlink_id' => $shortlinkId,
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString()
            ]);
            throw $e; // Re-throw for debugging
        }
    }

    public function redirect(Request $request, string $slug)
    {
        $link = Shortlink::where('slug', $slug)->where('active', true)->first();
        if (!$link) {
            abort(404, 'Shortlink not found');
        }

        // Get real IP address with proper fallback
        $ip = $this->getRealIp($request);
        
        \Log::info('=== SHORTLINK REDIRECT START ===', [
            'slug' => $slug,
            'shortlink_id' => $link->id,
            'ip' => $ip,
            'user_agent' => $request->userAgent(),
            'headers' => $request->headers->all(),
            'timestamp' => now()->toISOString()
        ]);
        
        // Check if IP is blocked
        if (BlockedIp::where('ip', $ip)->exists()) {
            \Log::warning('IP blocked from accessing shortlink', ['ip' => $ip, 'slug' => $slug]);
            abort(403, 'IP blocked');
        }

        // Basic UA and crawler detection
        $userAgent = (string) $request->userAgent();
        $isLegitimateBot = $this->isLegitimateBot($userAgent);
        $crawler = new CrawlerDetect($request->headers->all(), $userAgent);
        $isBot = $crawler->isCrawler();

        // Geo lookup
        $country = $request->headers->get('CF-IPCountry');
        $city = $asn = $org = null;
        if (!$country) {
            [$country, $city, $asn, $org] = $this->geoLookup($ip);
        }

        // Aggressive heuristics
        if (config('panel.aggressive_bot_detection', true)) {
            $isBot = $isBot || $this->isAggressiveBot($ip, $userAgent, $country, $asn, $org);
        }

        // Integrate with Stopbot.net if enabled
        $stopbotService = app(\App\Services\StopbotService::class);
        if ($stopbotService->isEnabled()) {
            $stopbotBlocked = $stopbotService->shouldBlock($ip, $userAgent, $request->getRequestUri());
            if ($stopbotBlocked) {
                // Log the block but continue with local processing
                \Log::info("Stopbot would block IP {$ip}, but proceeding with local bot detection");
                $isBot = true; // Mark as bot for our local analytics
            }
        }

        // Don't block configured legitimate crawlers
        if ($isLegitimateBot) {
            $isBot = false;
        }

        // Require JS-based human verification cookie for all public redirects.
        // Note: human verification interstitial removed — proceed without forcing the JS challenge.
        $humanToken = $request->cookie('human_verified');
        // $isHuman = $this->validateHumanToken($humanToken, $ip, $userAgent);

        // Proceed to record hit and redirect regardless of human_verified cookie.
        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        // Prepare event payload
        $payload = [
            'ip' => $ip,
            'country' => $country,
            'city' => $city,
            'asn' => $asn,
            'org' => $org,
            'device' => $agent->device() ?: 'Unknown',
            'platform' => $agent->platform() ?: 'Unknown',
            'browser' => $agent->browser() ?: 'Unknown',
            'referrer' => $request->headers->get('referer'),
            'is_bot' => $isBot,
        ];

        \Log::info('Prepared payload for recording hit', [
            'payload' => $payload,
            'shortlink_id' => $link->id
        ]);

        try {
            \Log::info('About to call recordHit', ['shortlink_id' => $link->id, 'payload' => $payload]);
            $this->recordHit($link->id, $payload);
            \Log::info('recordHit completed in redirect method');
        } catch (\Throwable $e) {
            \Log::error('Recording hit failed, but continuing redirect', [
                'error' => $e->getMessage(),
                'shortlink_id' => $link->id,
                'ip' => $ip,
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Redirect to destination
        $destination = $link->getNextDestination();
        
        \Log::info('=== SHORTLINK REDIRECT COMPLETED ===', [
            'slug' => $slug,
            'shortlink_id' => $link->id,
            'ip' => $ip,
            'destination' => $destination,
            'is_bot' => $isBot,
            'timestamp' => now()->toISOString()
        ]);
        
        return redirect()->away($destination, 302);
    }

    /**
     * Get real IP address considering CloudFlare and other proxies
     */
    protected function getRealIp(Request $request): string
    {
        // Check CloudFlare connecting IP
        if ($cfIp = $request->header('CF-Connecting-IP')) {
            \Log::info('Using CloudFlare IP', ['cf_ip' => $cfIp]);
            return $cfIp;
        }

        // Check other proxy headers
        if ($clientIp = $request->header('HTTP_CLIENT_IP')) {
            if (filter_var($clientIp, FILTER_VALIDATE_IP)) {
                \Log::info('Using HTTP_CLIENT_IP', ['client_ip' => $clientIp]);
                return $clientIp;
            }
        }

        if ($forwardedIp = $request->header('HTTP_X_FORWARDED_FOR')) {
            if (filter_var($forwardedIp, FILTER_VALIDATE_IP)) {
                \Log::info('Using HTTP_X_FORWARDED_FOR', ['forwarded_ip' => $forwardedIp]);
                return $forwardedIp;
            }
        }

        // Fallback to default IP
        $defaultIp = $request->ip();
        \Log::info('Using default IP', ['default_ip' => $defaultIp]);
        return $defaultIp;
    }

    /**
     * Endpoint to accept JS challenge verification and set human_verified cookie.
     * Expects JSON { slug, ua, languages, width, height, timestamp }
     */
    public function verifyHuman(Request $request)
    {
        $ip = $request->headers->get('CF-Connecting-IP') ?: $request->ip();
        $headerUa = (string) $request->userAgent();

        $data = $request->validate([
            'slug' => ['required','string'],
            'ua' => ['required','string'],
            'languages' => ['array'],
            'width' => ['nullable','integer'],
            'height' => ['nullable','integer'],
            'timestamp' => ['required','numeric']
        ]);

        // Basic consistency checks: posted UA should match header UA
        if (isset($data['ua']) && $data['ua'] !== $headerUa) {
            return response()->json(['ok' => false, 'message' => 'User agent mismatch'], 400);
        }

        // Basic JS evidence: languages array and non-zero screen dims
        $languages = $data['languages'] ?? [];
        $width = (int) ($data['width'] ?? 0);
        $height = (int) ($data['height'] ?? 0);

        if (empty($languages) || ($width <= 0 && $height <= 0)) {
            return response()->json(['ok' => false, 'message' => 'Missing browser evidence'], 400);
        }

        // Passed checks: create token and set cookie
        $token = $this->createHumanToken($ip, $headerUa);
        $resp = response()->json(['ok' => true, 'message' => 'Human verified']);
        // Cookie for 60 minutes
        $resp->headers->setCookie(cookie('human_verified', $token, 60, '/'));
        return $resp;
    }

    protected function createHumanToken(string $ip, string $ua): string
    {
        $expiry = time() + 3600; // 1 hour
        $payload = $ip . '|' . $ua . '|' . $expiry;
        $sig = hash_hmac('sha256', $payload, config('app.key'));
        return base64_encode($expiry . '|' . $sig);
    }

    protected function validateHumanToken(?string $token, string $ip, string $ua): bool
    {
        if (empty($token)) return false;
        try {
            $decoded = base64_decode($token);
            if (!str_contains($decoded, '|')) return false;
            [$expiry, $sig] = explode('|', $decoded, 2);
            if ((int)$expiry < time()) return false;
            $payload = $ip . '|' . $ua . '|' . $expiry;
            $expected = hash_hmac('sha256', $payload, config('app.key'));
            return hash_equals($expected, $sig);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function stats(Request $request, string $slug)
    {
        $link = Shortlink::where('slug', $slug)->firstOrFail();

        $countBots = (bool) config('panel.count_bots', false);
        $eventsQuery = ShortlinkEvent::where('shortlink_id', $link->id);
        if (!$countBots) { 
            $eventsQuery->where('is_bot', false); 
        }

        // Get recent events
        $events = (clone $eventsQuery)->latest('clicked_at')->limit(200)
            ->get(['clicked_at','ip','country','city','asn','org','device','platform','browser','referrer','is_bot']);

        // Get total clicks
        $totalClicks = (clone $eventsQuery)->count();

        // Get aggregations
        $agg = [
            'by_country' => ShortlinkEvent::select('country', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)
                ->when(!$countBots, fn($q) => $q->where('is_bot', false))
                ->whereNotNull('country')
                ->groupBy('country')->orderByDesc('c')->limit(20)->get(),
            'by_org' => ShortlinkEvent::select('org', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)
                ->when(!$countBots, fn($q) => $q->where('is_bot', false))
                ->whereNotNull('org')
                ->groupBy('org')->orderByDesc('c')->limit(20)->get(),
            'by_device' => ShortlinkEvent::select('device', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)
                ->when(!$countBots, fn($q) => $q->where('is_bot', false))
                ->whereNotNull('device')
                ->groupBy('device')->orderByDesc('c')->limit(10)->get(),
            'by_browser' => ShortlinkEvent::select('browser', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)
                ->when(!$countBots, fn($q) => $q->where('is_bot', false))
                ->whereNotNull('browser')
                ->groupBy('browser')->orderByDesc('c')->limit(10)->get(),
        ];

        return response()->json(['ok' => true, 'summary' => [
            'clicks' => $totalClicks,
            'last_200' => $events,
            'aggregate' => $agg,
        ]]);
    }

    /**
     * Show visitors page for a shortlink
     */
    public function visitors(Request $request, $slug)
    {
        $link = Shortlink::where('slug', $slug)->firstOrFail();
        return view('panel.visitors', ['link' => $link]);
    }

    /**
     * Return JSON list of visitors for a shortlink
     */
    public function visitorsList(Request $request, $slug)
    {
        $link = Shortlink::where('slug', $slug)->firstOrFail();
        $q = $request->get('q');

        $query = ShortlinkVisitor::where('shortlink_id', $link->id)
            ->when($q, fn($qq) => $qq->where('ip', 'like', '%' . $q . '%'))
            ->orderByDesc('last_seen')
            ->limit(200)
            ->get(['ip','hits','first_seen','last_seen','is_bot','country','city','asn','org']);

        return response()->json(['ok' => true, 'data' => $query]);
    }

    protected function isLegitimateBot(string $userAgent): bool
    {
        $legitimateBots = config('panel.legitimate_bots', []);
        $userAgentLower = strtolower($userAgent);
        
        foreach ($legitimateBots as $bot) {
            if (str_contains($userAgentLower, strtolower($bot))) {
                return true;
            }
        }
        
        return false;
    }

    protected function ipInRange(string $ip, string $range): bool
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }
        
        [$subnet, $mask] = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $mask);
        
        return ($ip & $mask) === ($subnet & $mask);
    }

    protected function isAggressiveBot(string $ip, string $userAgent, ?string $country, ?string $asn, ?string $org): bool
    {
        // Early header and rate-based heuristics to catch non-browser clients that
        // typically do not send browser headers or perform very high-rate requests.
        try {
            $request = request();
            $headers = $request->headers;

            $accept = (string) ($headers->get('accept') ?? '');
            $acceptLanguage = (string) ($headers->get('accept-language') ?? '');
            $secFetchSite = $headers->get('sec-fetch-site');
            $secChUa = $headers->get('sec-ch-ua');

            // Missing or odd Accept header (non-HTML clients) is suspicious
            if (empty($accept) || (!str_contains($accept, 'text/html') && !str_contains($accept, '*/*'))) {
                \Log::info("Blocking bot due to missing/odd Accept header", ['ip' => $ip, 'accept' => $accept]);
                return true;
            }

            // Accept-Language is commonly sent by browsers; missing or tiny values indicate non-browser
            if (empty($acceptLanguage) || strlen($acceptLanguage) < 2) {
                \Log::info("Blocking bot due to missing Accept-Language header", ['ip' => $ip, 'accept_language' => $acceptLanguage]);
                return true;
            }

            // Modern browsers send Sec-* or Sec-CH-* headers. If both are missing, be suspicious
            if (empty($secFetchSite) && empty($secChUa)) {
                \Log::info("Suspicious request missing Sec-* headers", ['ip' => $ip]);

                // If we also see a burst of requests from the same IP within a short window, treat as bot
                $recentHits = ShortlinkEvent::where('ip', $ip)
                    ->where('clicked_at', '>=', now()->subSeconds(10))
                    ->count();

                if ($recentHits > 8) {
                    \Log::info("Blocking bot due to high request rate combined with missing Sec headers", ['ip' => $ip, 'recent_hits' => $recentHits]);
                    return true;
                }
            }
        } catch (\Throwable $ex) {
            // Non-fatal: continue to other checks if header inspection fails
            \Log::debug('Header-based bot heuristics failed: ' . $ex->getMessage());
        }

        // Check blocked countries
        if (config('panel.block_bot_countries', false) && $country) {
            $blockedCountries = config('panel.blocked_countries', []);
            if (in_array($country, $blockedCountries)) {
                \Log::info("Blocking bot from blocked country", ['ip' => $ip, 'country' => $country]);
                return true;
            }
        }

        // Check hosting ASNs
        if (config('panel.block_hosting_asns', true) && $asn) {
            $hostingAsns = config('panel.hosting_asns', []);
            if (in_array($asn, $hostingAsns)) {
                \Log::info("Blocking bot from hosting ASN", ['ip' => $ip, 'asn' => $asn]);
                return true;
            }
        }

        // Check ISP/org keywords
        if ($org) {
            $keywords = config('panel.isp_bot_keywords', []);
            foreach ($keywords as $keyword) {
                if (stripos($org, $keyword) !== false) {
                    \Log::info("Blocking bot from suspicious org", ['ip' => $ip, 'org' => $org, 'keyword' => $keyword]);
                    return true;
                }
            }
        }

        // Check suspicious user agents
        $suspiciousPatterns = [
            '/curl/i',
            '/wget/i',
            '/python/i',
            '/php/i',
            '/ruby/i',
            '/node/i',
            '/requests/i',
            '/httpclient/i',
            '/postman/i',
            '/insomnia/i',
            '/java/i',
            '/scanner/i',
            '/checker/i',
            '/monitor/i',
            '/test/i',
            '/bot\\b/i',
            '/spider/i',
            '/crawler/i',
            '/scraper/i',
            '/headless/i',
            '/phantom/i',
            '/selenium/i',
            '/webdriver/i',
            '/automation/i',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                \Log::info("Blocking bot with suspicious user agent", ['ip' => $ip, 'user_agent' => $userAgent]);
                return true;
            }
        }

        // Check for empty or very short user agents
        if (empty($userAgent) || strlen($userAgent) < 10) {
            \Log::info("Blocking bot with suspicious short user agent", ['ip' => $ip, 'user_agent' => $userAgent]);
            return true;
        }

        return false;
    }

    /**
     * Reset visitor count for a specific shortlink
     */
    public function resetVisitors(Request $request, $slug)
    {
        try {
            $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
            
            // Delete all events for this shortlink
            ShortlinkEvent::where('shortlink_id', $shortlink->id)->delete();
            
            // Reset clicks count
            $shortlink->update(['clicks' => 0]);
            
            return response()->json([
                'ok' => true,
                'message' => "Visitor count reset for shortlink '{$slug}'"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to reset visitor count: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset visitor count for all shortlinks
     */
    public function resetAllVisitors(Request $request)
    {
        try {
            // Delete all shortlink events
            ShortlinkEvent::truncate();
            
            // Reset all shortlinks clicks to 0
            Shortlink::query()->update(['clicks' => 0]);
            
            return response()->json([
                'ok' => true,
                'message' => 'All visitor counts have been reset'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to reset all visitor counts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a shortlink
     */
    public function destroy(Request $request, $slug)
    {
        try {
            $shortlink = Shortlink::where('slug', $slug)->firstOrFail();
            
            // Delete all associated events first
            ShortlinkEvent::where('shortlink_id', $shortlink->id)->delete();
            
            // Delete the shortlink
            $shortlink->delete();
            
            return response()->json([
                'ok' => true,
                'message' => "Shortlink '{$slug}' deleted successfully"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to delete shortlink: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rotator details
     */
    public function getRotator(Request $request, $slug)
    {
        try {
            $link = Shortlink::where('slug', $slug)->firstOrFail();
            
            return response()->json([
                'ok' => true,
                'data' => [
                    'slug' => $link->slug,
                    'is_rotator' => $link->is_rotator,
                    'rotation_type' => $link->rotation_type,
                    'destinations' => $link->destinations ?: [],
                    'current_index' => $link->current_index,
                    'destinations_count' => $link->destinations_count,
                    'rotation_summary' => $link->rotation_summary,
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to get rotator data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update rotator settings
     */
    public function updateRotator(Request $request, $slug)
    {
        try {
            $link = Shortlink::where('slug', $slug)->firstOrFail();
            
            $data = $request->validate([
                'is_rotator' => ['boolean'],
                'rotation_type' => ['nullable','in:random,sequential,weighted'],
                'destinations' => ['nullable','array'],
                'destinations.*.url' => ['required','url','max:2048'],
                'destinations.*.weight' => ['nullable','integer','min:1','max:100'],
                'destinations.*.active' => ['boolean'],
                'destinations.*.name' => ['nullable','string','max:255'],
            ]);

            // Auto-add https:// to destinations
            if (!empty($data['destinations'])) {
                foreach ($data['destinations'] as $key => $dest) {
                    $url = $dest['url'] ?? '';
                    if ($url && !preg_match('/^https?:\/\//i', $url)) {
                        $data['destinations'][$key]['url'] = 'https://' . ltrim($url, '/');
                    }
                    // Set defaults
                    $data['destinations'][$key]['active'] = $dest['active'] ?? true;
                    $data['destinations'][$key]['weight'] = $dest['weight'] ?? 1;
                    $data['destinations'][$key]['name'] = $dest['name'] ?? '';
                }
            }

            $updateData = [
                'is_rotator' => $request->boolean('is_rotator'),
                'rotation_type' => $data['rotation_type'] ?? 'random',
                'destinations' => $data['destinations'] ?? null,
                'current_index' => 0, // Reset index on update
            ];

            // Update fallback destination for non-rotator or first destination
            if (!$request->boolean('is_rotator')) {
                // Keep current destination for single links
            } else if (!empty($data['destinations'])) {
                $updateData['destination'] = $data['destinations'][0]['url'];
            }

            $link->update($updateData);

            return response()->json([
                'ok' => true,
                'data' => $link->fresh(),
                'message' => 'Rotator updated successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed: ' . collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Failed to update rotator: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update destinations for an existing shortlink.
     * Supports converting single -> rotator and vice-versa, or editing destinations in place.
     */
    public function updateDestinations(Request $request, $slug)
    {
        try {
            $link = Shortlink::where('slug', $slug)->firstOrFail();

            $data = $request->validate([
                'is_rotator' => ['boolean'],
                'rotation_type' => ['nullable','in:random,sequential,weighted'],
                'destination' => ['nullable','url','max:2048'],
                'destinations' => ['nullable','array'],
                'destinations.*.url' => ['required_with:destinations','url','max:2048'],
                'destinations.*.weight' => ['nullable','integer','min:1','max:100'],
                'destinations.*.active' => ['boolean'],
                'destinations.*.name' => ['nullable','string','max:255'],
            ]);

            // Normalize URLs: add https:// if missing
            if (!empty($data['destinations'])) {
                foreach ($data['destinations'] as $key => $dest) {
                    $url = $dest['url'] ?? '';
                    if ($url && !preg_match('/^https?:\/\//i', $url)) {
                        $data['destinations'][$key]['url'] = 'https://' . ltrim($url, '/');
                    }
                    $data['destinations'][$key]['active'] = $dest['active'] ?? true;
                    $data['destinations'][$key]['weight'] = $dest['weight'] ?? 1;
                    $data['destinations'][$key]['name'] = $dest['name'] ?? '';
                }
            }

            $update = [];
            $isRotator = $request->boolean('is_rotator', $link->is_rotator);

            if ($isRotator) {
                // Ensure destinations provided
                if (empty($data['destinations'])) {
                    return response()->json(['ok' => false, 'message' => 'Destinations are required for rotator links'], 422);
                }

                $update['is_rotator'] = true;
                $update['rotation_type'] = $data['rotation_type'] ?? $link->rotation_type ?? 'random';
                $update['destinations'] = $data['destinations'];
                // Set fallback destination to first active destination
                $first = current(array_values(array_filter($data['destinations'], fn($d) => $d['active'] ?? true)));
                $update['destination'] = $first['url'] ?? ($data['destinations'][0]['url'] ?? $link->destination);
                $update['current_index'] = 0;
            } else {
                // Single destination link
                $newDest = $data['destination'] ?? null;
                if (empty($newDest)) {
                    return response()->json(['ok' => false, 'message' => 'Destination URL is required for single links'], 422);
                }

                $update['is_rotator'] = false;
                $update['destinations'] = null;
                $update['destination'] = $newDest;
                $update['current_index'] = 0;
            }

            $link->update($update);

            return response()->json(['ok' => true, 'data' => $link->fresh(), 'message' => 'Destinations updated successfully']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['ok' => false, 'message' => collect($e->errors())->flatten()->first()], 422);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'Failed to update destinations: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show page listing detected IPs that accessed shortlinks
     */
    public function ips()
    {
        return view('panel.ips');
    }

    /**
     * Return JSON list of IPs that accessed shortlinks with aggregated data
     */
    public function ipsList(Request $request)
    {
        $q = $request->get('q');

        $query = ShortlinkEvent::select(
            'shortlink_events.ip',
            DB::raw('COUNT(*) as hits'),
            DB::raw('MAX(shortlink_events.clicked_at) as last_seen'),
            DB::raw("GROUP_CONCAT(DISTINCT shortlinks.slug) as slugs")
        )
        ->leftJoin('shortlinks', 'shortlink_events.shortlink_id', '=', 'shortlinks.id')
        ->when($q, function ($qry) use ($q) {
            $qry->where('shortlink_events.ip', 'like', '%' . $q . '%');
        })
        ->groupBy('shortlink_events.ip')
        ->orderByDesc('hits')
        ->limit(1000)
        ->get();

        return response()->json(['ok' => true, 'data' => $query]);
    }

    /**
     * Show Stopbot configuration page
     */
    public function stopbotConfig()
    {
        return view('panel.stopbot');
    }

    /**
     * Save Stopbot configuration
     */
    public function saveStopbotConfig(Request $request)
    {
        try {
            \Log::info('Stopbot save config request raw', ['json' => $request->getContent(), 'all' => $request->all(), 'session_authenticated' => session('panel_authenticated')]);

            $data = $request->validate([
                'enabled' => 'required|boolean',
                'api_key' => 'nullable|string|max:255',
                'redirect_url' => 'nullable|string|max:255',
                'log_enabled' => 'required|boolean',
                'timeout' => 'nullable|integer|min:1|max:30'
            ]);

            // If enabling but api_key empty -> error
            if ($data['enabled'] && empty($data['api_key'])) {
                return response()->json(['ok' => false, 'message' => 'API key wajib diisi saat mengaktifkan Stopbot'], 422);
            }

            // Normalize
            $data['api_key'] = trim($data['api_key'] ?? '');
            $data['redirect_url'] = trim($data['redirect_url'] ?? '');

            PanelSetting::set('stopbot_enabled', $data['enabled'], 'boolean', 'stopbot', 'Enable Stopbot.net integration');
            PanelSetting::set('stopbot_api_key', $data['api_key'], 'string', 'stopbot', 'Stopbot.net API key');
            PanelSetting::set('stopbot_redirect_url', $data['redirect_url'], 'string', 'stopbot', 'URL to redirect blocked requests');
            PanelSetting::set('stopbot_log_enabled', $data['log_enabled'], 'boolean', 'stopbot', 'Enable Stopbot logging');
            PanelSetting::set('stopbot_timeout', $data['timeout'] ?? 5, 'integer', 'stopbot', 'API timeout in seconds');

            // Read back
            $saved = [
                'enabled' => PanelSetting::get('stopbot_enabled', false),
                'api_key' => PanelSetting::get('stopbot_api_key', ''),
                'redirect_url' => PanelSetting::get('stopbot_redirect_url', ''),
                'log_enabled' => PanelSetting::get('stopbot_log_enabled', true),
                'timeout' => PanelSetting::get('stopbot_timeout', 5),
            ];
            \Log::info('Stopbot config saved', $saved);

            return response()->json(['ok' => true, 'message' => 'Configuration saved', 'data' => $saved]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => collect($e->errors())->flatten()->first()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Stopbot save config exception', ['err' => $e->getMessage()]);
            return response()->json(['ok' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get Stopbot usage statistics
     */
    public function getStopbotStats(Request $request)
    {
        try {
            // Get statistics from logs or database
            $totalChecks = ShortlinkEvent::count();
            $blockedRequests = ShortlinkEvent::where('is_bot', true)->count();
            $successRate = $totalChecks > 0 ? round((($totalChecks - $blockedRequests) / $totalChecks) * 100, 1) . '%' : '0%';

            return response()->json([
                'ok' => true, 
                'data' => [
                    'total_checks' => $totalChecks,
                    'blocked_requests' => $blockedRequests,
                    'success_rate' => $successRate
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Test Stopbot API connection
     */
    public function testStopbotApi(Request $request)
    {
        try {
            \Log::info('Stopbot API test started', ['request_data' => $request->all()]);
            
            $data = $request->validate(['api_key' => 'required|string']);
            $apiKey = $data['api_key'];

            \Log::info('Making Stopbot API request', ['api_key' => substr($apiKey, 0, 8) . '...']);

            $response = Http::timeout(10)->get('https://stopbot.net/api/blocker', [
                'apikey' => $apiKey,
                'ip' => '8.8.8.8', // Google DNS for testing
                'ua' => 'Mozilla/5.0 (Test)',
                'url' => '/test',
                'rand' => rand(1, 1000000)
            ]);

            \Log::info('Stopbot API response received', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body_length' => strlen($response->body()),
                'body_preview' => substr($response->body(), 0, 100)
            ]);

            if (!$response->successful()) {
                $errorMessage = 'HTTP Error ' . $response->status() . ': ' . $response->body();
                \Log::error('Stopbot API error', ['error' => $errorMessage]);
                return response()->json([
                    'ok' => false, 
                    'message' => $errorMessage
                ]);
            }

            $responseBody = $response->body();
            
            // Check if response is JSON
            $data = json_decode($responseBody, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $errorMessage = 'Invalid JSON response from Stopbot API: ' . substr($responseBody, 0, 200);
                \Log::error('Stopbot API JSON error', ['error' => $errorMessage, 'json_error' => json_last_error_msg()]);
                return response()->json([
                    'ok' => false, 
                    'message' => $errorMessage
                ]);
            }

            \Log::info('Stopbot API test successful', ['response_data' => $data]);
            return response()->json(['ok' => true, 'data' => $data]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessage = 'Validation error: ' . collect($e->errors())->flatten()->first();
            \Log::error('Stopbot API validation error', ['error' => $errorMessage]);
            return response()->json([
                'ok' => false, 
                'message' => $errorMessage
            ], 422);
        } catch (\Exception $e) {
            $errorMessage = 'Connection error: ' . $e->getMessage();
            \Log::error('Stopbot API exception', ['error' => $errorMessage, 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'ok' => false, 
                'message' => $errorMessage
            ]);
        }
    }
}
