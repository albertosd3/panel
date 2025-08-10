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
                'short_url' => url($slug)
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
                    BlockedIp::firstOrCreate(['ip' => $ip], ['reason' => 'auto-bot-' . now()->format('Y-m-d')]);
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
}
