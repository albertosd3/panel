<?php

namespace App\Http\Controllers;

use App\Jobs\RecordShortlinkHit;
use App\Models\BlockedIp;
use App\Models\Shortlink;
use App\Models\ShortlinkEvent;
use GeoIp2\Database\Reader as GeoIP2Reader;
use GeoIp2\WebService\Client as GeoIP2Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $links = Shortlink::orderByDesc('id')->limit(200)->get(['id','slug','destination','clicks','active','created_at']);
        // Compute real clicks from events table based on bot counting setting
        $countBots = (bool) config('panel.count_bots', false);
        $eventCounts = ShortlinkEvent::select('shortlink_id', DB::raw('COUNT(*) as c'))
            ->when(!$countBots, fn($q) => $q->where('is_bot', false))
            ->whereIn('shortlink_id', $links->pluck('id'))
            ->groupBy('shortlink_id')->pluck('c','shortlink_id');
        
        $links = $links->map(function ($l) use ($eventCounts) {
            $l->clicks = (int) ($eventCounts[$l->id] ?? 0);
            return $l;
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

        return response()->json([
            'ok' => true,
            'data' => [
                'overview' => [
                    'total_links' => $totalLinks,
                    'total_clicks' => $totalClicks,
                    'today_clicks' => $todayClicks,
                    'avg_clicks_per_link' => $totalLinks > 0 ? round($totalClicks / $totalLinks, 1) : 0
                ],
                'timeline' => $periodData['timeline'],
                'comparison' => $periodData['comparison'],
                'period' => $period,
                'top_countries' => $topCountries,
                'device_stats' => $deviceStats,
                'browser_stats' => $browserStats,
                'top_links' => $topLinks
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
            // Auto-add https:// if missing
            $destination = $request->input('destination', '');
            if ($destination && !preg_match('/^https?:\/\//i', $destination)) {
                $destination = 'https://' . ltrim($destination, '/');
                $request->merge(['destination' => $destination]);
            }

            $data = $request->validate([
                'destination' => ['required','url','max:2048'],
                'slug' => ['nullable','alpha_dash','min:3','max:64','unique:shortlinks,slug'],
                'domain_id' => ['nullable','exists:domains,id']
            ]);

            $slug = $data['slug'] ?? null;
            if (!$slug || trim($slug) === '') {
                // Generate random slug
                $length = 6;
                do {
                    $slug = Str::lower(Str::random($length));
                    $length = min($length + 1, 12);
                } while (Shortlink::where('slug', $slug)->exists());
            }

            $link = Shortlink::create([
                'slug' => $slug,
                'destination' => $data['destination'],
                'domain_id' => $data['domain_id'] ?? null,
                'clicks' => 0,
                'active' => true,
                'meta' => [
                    'created_ip' => $request->ip(),
                    'created_by' => 'panel',
                    'created_at_formatted' => now()->format('Y-m-d H:i:s'),
                ],
            ]);

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
            DB::transaction(function () use ($shortlinkId, $payload) {
                // Always create event record
                ShortlinkEvent::create(array_merge($payload, [
                    'shortlink_id' => $shortlinkId,
                    'clicked_at' => now(),
                ]));

                // Increment clicks only if counting bots OR this is not a bot
                $countBots = (bool) config('panel.count_bots', false);
                $isBot = (bool) ($payload['is_bot'] ?? false);
                
                if ($countBots || !$isBot) {
                    Shortlink::where('id', $shortlinkId)->update([
                        'clicks' => DB::raw('clicks + 1')
                    ]);
                }
            });
        } catch (\Throwable $e) {
            \Log::error('Failed to record hit: ' . $e->getMessage(), [
                'shortlink_id' => $shortlinkId,
                'payload' => $payload
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

        $ip = $request->headers->get('CF-Connecting-IP') ?: $request->ip();
        
        // Check if IP is blocked
        if (BlockedIp::where('ip', $ip)->exists()) {
            abort(403, 'IP blocked');
        }

        // Bot detection
        $userAgent = (string) $request->userAgent();
        
        // Check if IP is in legitimate crawler whitelist
        $isLegitimateBot = $this->isLegitimateBot($userAgent);

        $crawler = new CrawlerDetect($request->headers->all(), $userAgent);
        $isBot = $crawler->isCrawler();

        // Get user agent details
        $agent = new Agent();
        $agent->setUserAgent($userAgent);

        // Geo lookup
        $country = $request->headers->get('CF-IPCountry');
        $city = $asn = $org = null;
        if (!$country) {
            [$country, $city, $asn, $org] = $this->geoLookup($ip);
        }

        // Aggressive bot detection
        if (config('panel.aggressive_bot_detection', true)) {
            $isBot = $isBot || $this->isAggressiveBot($ip, $userAgent, $country, $asn, $org);
        }

        // Don't block legitimate crawlers
        if ($isLegitimateBot) {
            $isBot = false;
        }

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

        // Record hit immediately before redirect
        try {
            $this->recordHit($link->id, $payload);
        } catch (\Throwable $e) {
            // Log error but don't break redirect
            \Log::error('Recording hit failed, but continuing redirect: ' . $e->getMessage());
        }

        // Block bots after recording (if enabled)
        if ($isBot && config('panel.block_bots', true)) {
            if (config('panel.auto_block_bot_ips', true)) {
                try {
                    $reason = 'auto-bot-' . now()->format('Y-m-d');
                    if ($asn) $reason .= '-' . $asn;
                    if ($org) $reason .= '-' . substr($org, 0, 20);
                    BlockedIp::firstOrCreate(['ip' => $ip], ['reason' => $reason]);
                } catch (\Throwable $e) {
                    \Log::error('Failed to block bot IP: ' . $e->getMessage());
                }
            }
            abort(403, 'Bot access blocked');
        }

        // Redirect to destination
        return redirect()->away($link->destination, 302);
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
            '/bot\b/i',
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

}
