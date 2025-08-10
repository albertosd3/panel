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
        // compute clicks from events to be safe
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
            // Normalize destination: add https:// if user omits scheme
            if ($request->filled('destination') && !preg_match('/^https?:\/\//i', $request->input('destination'))) {
                $request->merge(['destination' => 'https://' . ltrim($request->input('destination'))]);
            }

            $data = $request->validate([
                'destination' => ['required','url','max:2048'],
                'slug' => ['nullable','alpha_dash','min:3','max:64','unique:shortlinks,slug'],
                'random' => ['nullable','boolean']
            ]);

            $slug = $data['slug'] ?? null;
            if (!$slug) {
                $length = 6;
                do {
                    $slug = Str::lower(Str::random($length));
                    $length = min($length + 1, 12);
                } while (Shortlink::where('slug', $slug)->exists());
            }

            $link = Shortlink::create([
                'slug' => $slug,
                'destination' => $data['destination'],
                'meta' => [
                    'created_ip' => $request->ip(),
                    'created_by' => 'panel',
                ],
            ]);

            return response()->json([
                'ok' => true,
                'data' => $link,
                'short_url' => url($slug)
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menyimpan shortlink: '.$e->getMessage(),
            ], 422);
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
                // If you also have ASN DB, you can read it similarly (requires GeoLite2-ASN.mmdb)
                $reader->close();
            }
        } catch (\Throwable $e) {
            // ignore failures
        }

        return [$country, $city, $asn, $org];
    }

    protected function recordHit(int $shortlinkId, array $payload): void
    {
        // Simpan event dan increment clicks (abaikan bot bila diatur)
        DB::transaction(function () use ($shortlinkId, $payload) {
            ShortlinkEvent::create(array_merge($payload, [
                'shortlink_id' => $shortlinkId,
                'clicked_at' => now(),
            ]));

            $countBots = (bool) config('panel.count_bots', false);
            if ($countBots || empty($payload['is_bot'])) {
                Shortlink::where('id', $shortlinkId)->update([
                    'clicks' => DB::raw('clicks + 1')
                ]);
            }
        });
    }

    public function redirect(Request $request, string $slug)
    {
        $link = Shortlink::where('slug', $slug)->where('active', true)->first();
        if (!$link) abort(404);

        $ip = $request->headers->get('CF-Connecting-IP') ?: $request->ip();
        if (BlockedIp::where('ip', $ip)->exists()) abort(403);

        // Initialize crawler detector with headers and proper User-Agent string
        $crawler = new CrawlerDetect($request->headers->all(), (string) $request->userAgent());
        $isBot = $crawler->isCrawler();

        $agent = new Agent();
        $agent->setUserAgent($request->userAgent());

        // Prefer CF country, else geo lookup including ASN/org
        $country = $request->headers->get('CF-IPCountry');
        $city = $asn = $org = null;
        if (!$country) {
            [$country, $city, $asn, $org] = $this->geoLookup($ip);
        }

        $payload = [
            'ip' => $ip,
            'country' => $country,
            'city' => $city,
            'asn' => $asn,
            'org' => $org,
            'device' => $agent->device(),
            'platform' => $agent->platform(),
            'browser' => $agent->browser(),
            'referrer' => $request->headers->get('referer'),
            'is_bot' => $isBot,
        ];

        // Record synchronously for reliability
        try {
            $this->recordHit($link->id, $payload);
        } catch (\Throwable $e) {
            // swallow to not break redirect; optionally log
            \Log::warning('recordHit failed: '.$e->getMessage());
        }

        if ($isBot && config('panel.block_bots')) {
            if (config('panel.auto_block_bot_ips')) {
                BlockedIp::firstOrCreate(['ip' => $ip], ['reason' => 'auto-bot']);
            }
            abort(403);
        }

        return redirect()->away($link->destination, 302);
    }

    public function stats(Request $request, string $slug)
    {
        $link = Shortlink::where('slug', $slug)->firstOrFail();

        $countBots = (bool) config('panel.count_bots', false);
        $eventsQuery = ShortlinkEvent::where('shortlink_id', $link->id);
        if (!$countBots) { $eventsQuery->where('is_bot', false); }

        $events = (clone $eventsQuery)->latest('clicked_at')->limit(200)
            ->get(['clicked_at','ip','country','city','asn','org','device','platform','browser','referrer','is_bot']);

        $totalClicks = (clone $eventsQuery)->count();

        $agg = [
            'by_country' => ShortlinkEvent::select('country', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)->when(!$countBots, fn($q)=>$q->where('is_bot', false))
                ->whereNotNull('country')->groupBy('country')->orderByDesc('c')->limit(20)->get(),
            'by_org' => ShortlinkEvent::select('org', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)->when(!$countBots, fn($q)=>$q->where('is_bot', false))
                ->whereNotNull('org')->groupBy('org')->orderByDesc('c')->limit(20)->get(),
            'by_device' => ShortlinkEvent::select('device', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)->when(!$countBots, fn($q)=>$q->where('is_bot', false))
                ->whereNotNull('device')->groupBy('device')->orderByDesc('c')->limit(10)->get(),
            'by_browser' => ShortlinkEvent::select('browser', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)->when(!$countBots, fn($q)=>$q->where('is_bot', false))
                ->whereNotNull('browser')->groupBy('browser')->orderByDesc('c')->limit(10)->get(),
        ];

        return response()->json(['ok' => true, 'summary' => [
            'clicks' => $totalClicks,
            'last_200' => $events,
            'aggregate' => $agg,
        ]]);
    }
}
