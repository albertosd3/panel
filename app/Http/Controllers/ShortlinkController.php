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

    public function store(Request $request)
    {
        $data = $request->validate([
            'destination' => ['required','url'],
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

    public function redirect(Request $request, string $slug)
    {
        $link = Shortlink::where('slug', $slug)->where('active', true)->first();
        if (!$link) abort(404);

        $ip = $request->headers->get('CF-Connecting-IP') ?: $request->ip();
        if (BlockedIp::where('ip', $ip)->exists()) abort(403);

        // Initialize crawler detector with headers and proper User-Agent string
        $crawler = new CrawlerDetect($request->headers->all(), (string) $request->userAgent());
        $isBot = $crawler->isCrawler();
        if ($isBot && config('panel.block_bots')) {
            if (config('panel.auto_block_bot_ips')) {
                BlockedIp::firstOrCreate(['ip' => $ip], ['reason' => 'auto-bot']);
            }
            abort(403);
        }

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

        RecordShortlinkHit::dispatch($link->id, $payload)->afterResponse();
        return redirect()->away($link->destination, 302);
    }

    public function stats(Request $request, string $slug)
    {
        $link = Shortlink::where('slug', $slug)->firstOrFail();

        $events = ShortlinkEvent::where('shortlink_id', $link->id)
            ->latest('clicked_at')->limit(200)
            ->get(['clicked_at','ip','country','city','asn','org','device','platform','browser','referrer','is_bot']);

        $agg = [
            'by_country' => ShortlinkEvent::select('country', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)->whereNotNull('country')
                ->groupBy('country')->orderByDesc('c')->limit(20)->get(),
            'by_org' => ShortlinkEvent::select('org', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)->whereNotNull('org')
                ->groupBy('org')->orderByDesc('c')->limit(20)->get(),
            'by_device' => ShortlinkEvent::select('device', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)->whereNotNull('device')
                ->groupBy('device')->orderByDesc('c')->limit(10)->get(),
            'by_browser' => ShortlinkEvent::select('browser', DB::raw('count(*) as c'))
                ->where('shortlink_id', $link->id)->whereNotNull('browser')
                ->groupBy('browser')->orderByDesc('c')->limit(10)->get(),
        ];

        return response()->json(['ok' => true, 'summary' => [
            'clicks' => $link->clicks,
            'last_200' => $events,
            'aggregate' => $agg,
        ]]);
    }
}
