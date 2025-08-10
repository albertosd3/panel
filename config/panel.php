<?php

return [
    'pin' => env('PANEL_PIN', null),

    // Block bots detected by user-agent/crawler signatures
    'block_bots' => env('PANEL_BLOCK_BOTS', true),

    // Do not increment clicks for bot hits
    'count_bots' => env('PANEL_COUNT_BOTS', false),

    // Auto-block IPs that repeatedly appear as bots
    'auto_block_bot_ips' => env('PANEL_AUTO_BLOCK_BOT_IPS', true),

    // Number of bot hits from an IP within window to block
    'bot_hits_to_block' => env('PANEL_BOT_HITS_TO_BLOCK', 3),

    // Bot hit counting window in minutes
    'bot_window_minutes' => env('PANEL_BOT_WINDOW_MINUTES', 10),

    'geoip' => [
        // Path file MaxMind GeoLite2-City.mmdb jika pakai database lokal
        'database_path' => env('GEOIP_DB_PATH', storage_path('app/GeoLite2-City.mmdb')),


        // Atau pakai web service MaxMind (butuh akun)
        'account_id' => env('MAXMIND_ACCOUNT_ID'),
        'license_key' => env('MAXMIND_LICENSE_KEY'),
        'enabled' => env('GEOIP_ENABLED', true),
    ],

    'isp_bot_keywords' => [
        'google', 'facebook', 'amazon', 'aws', 'microsoft', 'azure', 'digitalocean', 'linode', 'ovh', 'hetzner',
        'choopa', 'leaseweb', 'contabo', 'huawei cloud', 'tencent', 'alibaba', 'tata communications', 'cloudflare',
        'fastly', 'akamai', 'vultr', 'scaleway', 'oracle', 'gcore', 'qcloud', 'colo', 'hosting', 'datacenter',
        'vpn', 'proxy', 'crawler', 'bot', 'apnic', 'arin', 'ripe', 'lacnic', 'afrinic'
    ],
];
