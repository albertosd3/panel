<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\PanelSetting;

// Sync Stopbot settings from .env to database
$envStopbotEnabled = env('STOPBOT_ENABLED', false);
$envStopbotApiKey = env('STOPBOT_API_KEY', '');
$envStopbotRedirectUrl = env('STOPBOT_REDIRECT_URL', 'https://www.google.com');
$envStopbotLogEnabled = env('STOPBOT_LOG_ENABLED', true);

echo "Syncing Stopbot settings from .env to database...\n";

PanelSetting::set('stopbot_enabled', $envStopbotEnabled, 'boolean', 'stopbot', 'Enable Stopbot.net integration');
echo "✅ stopbot_enabled = " . ($envStopbotEnabled ? 'true' : 'false') . "\n";

PanelSetting::set('stopbot_api_key', $envStopbotApiKey, 'string', 'stopbot', 'Stopbot.net API key');
echo "✅ stopbot_api_key = " . ($envStopbotApiKey ? substr($envStopbotApiKey, 0, 8) . '...' : 'empty') . "\n";

PanelSetting::set('stopbot_redirect_url', $envStopbotRedirectUrl, 'string', 'stopbot', 'URL to redirect blocked requests');
echo "✅ stopbot_redirect_url = $envStopbotRedirectUrl\n";

PanelSetting::set('stopbot_log_enabled', $envStopbotLogEnabled, 'boolean', 'stopbot', 'Enable Stopbot logging');
echo "✅ stopbot_log_enabled = " . ($envStopbotLogEnabled ? 'true' : 'false') . "\n";

echo "\nDatabase updated successfully!\n";

// Verify settings
echo "\nCurrent database values:\n";
$settings = ['stopbot_enabled', 'stopbot_api_key', 'stopbot_redirect_url', 'stopbot_log_enabled', 'stopbot_timeout'];
foreach ($settings as $key) {
    $value = PanelSetting::get($key, 'NOT_SET');
    $type = PanelSetting::where('key', $key)->first()->type ?? 'unknown';
    echo "$key=" . ($key === 'stopbot_api_key' && $value ? substr($value, 0, 8) . '...' : $value) . " ($type)\n";
}
