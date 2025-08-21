<?php

return [
    'pin' => env('PANEL_PIN', null),

    // Fallback main domain when domains table is empty or not configured
    'default_domain' => env('PANEL_DEFAULT_DOMAIN'),
    'default_force_https' => env('PANEL_DEFAULT_FORCE_HTTPS', true),

    // Enhanced bot blocking settings
    'block_bots' => env('PANEL_BLOCK_BOTS', true),
    'count_bots' => env('PANEL_COUNT_BOTS', false),
    'auto_block_bot_ips' => env('PANEL_AUTO_BLOCK_BOT_IPS', true),
    'bot_hits_to_block' => env('PANEL_BOT_HITS_TO_BLOCK', 2),
    'bot_window_minutes' => env('PANEL_BOT_WINDOW_MINUTES', 5),

    // Aggressive bot detection
    'aggressive_bot_detection' => env('PANEL_AGGRESSIVE_BOT_DETECTION', true),
    'block_datacenter_ips' => env('PANEL_BLOCK_DATACENTER_IPS', true),
    'block_vpn_proxy' => env('PANEL_BLOCK_VPN_PROXY', true),
    'block_headless_browsers' => env('PANEL_BLOCK_HEADLESS_BROWSERS', true),

    'geoip' => [
        'database_path' => env('GEOIP_DB_PATH', storage_path('app/GeoLite2-City.mmdb')),
        'account_id' => env('MAXMIND_ACCOUNT_ID'),
        'license_key' => env('MAXMIND_LICENSE_KEY'),
        'enabled' => env('GEOIP_ENABLED', true),
    ],

    // Enhanced ISP/ASN bot detection keywords
    'datacenter_keywords' => [
        'amazon', 'aws', 'ec2', 'google cloud', 'gcp', 'microsoft azure', 'azure', 'digitalocean',
        'linode', 'vultr', 'ovh', 'hetzner', 'choopa', 'leaseweb', 'contabo', 'scaleway',
        'oracle cloud', 'alibaba cloud', 'tencent cloud', 'huawei cloud', 'qcloud',
        'datacamp', 'hostinger', 'godaddy', 'namecheap', 'bluehost', 'siteground',
        'cloudflare', 'fastly', 'akamai', 'maxcdn', 'keycdn', 'stackpath',
        'datacenter', 'hosting', 'server', 'colocation', 'colo', 'cloud'
    ],

    'vpn_proxy_keywords' => [
        'vpn', 'proxy', 'tor', 'anonymizer', 'private internet access', 'nordvpn',
        'expressvpn', 'surfshark', 'cyberghost', 'purevpn', 'windscribe', 'protonvpn',
        'tunnelbear', 'hotspot shield', 'hide.me', 'anonymouse', 'hidemyass'
    ],

    'bot_user_agents' => [
        'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python-requests',
        'postman', 'insomnia', 'httpclient', 'okhttp', 'apache-httpclient',
        'headless', 'phantomjs', 'selenium', 'chromedriver', 'geckodriver',
        'puppeteer', 'playwright', 'browserless', 'scrapy', 'mechanize'
    ],

    'legitimate_bots' => [
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider', 'yandexbot',
        'facebookexternalhit', 'twitterbot', 'linkedinbot', 'pinterest', 'whatsapp',
        'telegrambot', 'skype', 'applebot', 'ia_archiver'
    ],

    /*
    |--------------------------------------------------------------------------
    | Stopbot.net Bot Blocking Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for external bot blocking service stopbot.net
    | This provides additional bot detection beyond the built-in system.
    |
    */
    'stopbot' => [
        'enabled' => env('STOPBOT_ENABLED', false),
        'api_key' => env('STOPBOT_API_KEY'),
        'redirect_url' => env('STOPBOT_REDIRECT_URL', 'https://www.google.com'),
        'log_enabled' => env('STOPBOT_LOG_ENABLED', true),
        'timeout' => env('STOPBOT_TIMEOUT', 5), // seconds
    ],
];
