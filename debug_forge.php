<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Laravel Forge Deployment Debug ===\n";

// Check if we're in production mode
echo "APP_ENV: " . config('app.env') . "\n";
echo "APP_DEBUG: " . (config('app.debug') ? 'true' : 'false') . "\n";
echo "APP_URL: " . config('app.url') . "\n";

// Check database connection
try {
    DB::connection()->getPdo();
    echo "✅ Database connection: OK\n";
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Check shortlinks table
try {
    $count = DB::table('shortlinks')->count();
    echo "✅ Shortlinks table accessible, found {$count} records\n";
} catch (\Exception $e) {
    echo "❌ Shortlinks table error: " . $e->getMessage() . "\n";
}

// Check panel settings
try {
    $settings = DB::table('panel_settings')->get();
    echo "✅ Panel settings table accessible, found " . $settings->count() . " settings\n";
    
    foreach ($settings as $setting) {
        if (in_array($setting->key, ['stopbot_enabled', 'stopbot_api_key'])) {
            echo "  - {$setting->key}: " . (strlen($setting->value) > 20 ? substr($setting->value, 0, 20) . '...' : $setting->value) . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Panel settings error: " . $e->getMessage() . "\n";
}

// Test API routes
echo "\n=== Testing API Routes ===\n";

use Illuminate\Http\Request;
use App\Http\Controllers\ShortlinkController;

$controller = new ShortlinkController();

// Test analytics endpoint
try {
    $request = Request::create('/api/analytics', 'GET');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $response = $controller->analytics($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['ok'] ?? false) {
        echo "✅ Analytics API: Working\n";
        $overview = $data['data']['overview'] ?? [];
        echo "  - Total Links: " . ($overview['total_links'] ?? 0) . "\n";
        echo "  - Total Clicks: " . ($overview['total_clicks'] ?? 0) . "\n";
    } else {
        echo "❌ Analytics API failed: " . json_encode($data) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Analytics API error: " . $e->getMessage() . "\n";
}

// Test links list endpoint
try {
    $request = Request::create('/api/links', 'GET');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $response = $controller->list($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['ok'] ?? false) {
        echo "✅ Links List API: Working\n";
        echo "  - Found " . count($data['data'] ?? []) . " shortlinks\n";
    } else {
        echo "❌ Links List API failed: " . json_encode($data) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Links List API error: " . $e->getMessage() . "\n";
}

// Test shortlink creation
try {
    $requestData = [
        'is_rotator' => false,
        'destination' => 'https://www.google.com',
        'slug' => 'forge-test-' . time()
    ];
    
    $request = Request::create('/api/create', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
    ], json_encode($requestData));
    
    $response = $controller->store($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['ok'] ?? false) {
        echo "✅ Shortlink Creation API: Working\n";
        echo "  - Created: " . ($data['data']['slug'] ?? 'unknown') . "\n";
        echo "  - URL: " . ($data['data']['full_url'] ?? 'unknown') . "\n";
    } else {
        echo "❌ Shortlink Creation failed: " . json_encode($data) . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Shortlink Creation error: " . $e->getMessage() . "\n";
}

// Check web server configuration
echo "\n=== Server Configuration ===\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "Laravel Version: " . app()->version() . "\n";

if (isset($_SERVER['HTTP_HOST'])) {
    echo "HTTP Host: " . $_SERVER['HTTP_HOST'] . "\n";
}

if (isset($_SERVER['HTTPS'])) {
    echo "HTTPS: " . ($_SERVER['HTTPS'] ? 'enabled' : 'disabled') . "\n";
}

// Check storage permissions
$storagePath = storage_path();
echo "Storage path: {$storagePath}\n";
echo "Storage writable: " . (is_writable($storagePath) ? 'yes' : 'no') . "\n";

// Check logs
$logPath = storage_path('logs/laravel.log');
if (file_exists($logPath)) {
    $logSize = filesize($logPath);
    echo "Log file exists: {$logPath} ({$logSize} bytes)\n";
    
    if ($logSize > 0) {
        $lastLines = file($logPath);
        $lastLines = array_slice($lastLines, -5);
        echo "Recent log entries:\n";
        foreach ($lastLines as $line) {
            echo "  " . trim($line) . "\n";
        }
    }
} else {
    echo "No log file found\n";
}

echo "\n=== Debug Complete ===\n";
echo "If APIs are working but frontend shows 'Loading...', check:\n";
echo "1. Browser console for JavaScript errors\n";
echo "2. Network tab for failed AJAX requests\n";
echo "3. CSRF token issues\n";
echo "4. HTTPS/HTTP mixed content issues\n";
echo "5. Cloudflare or CDN caching issues\n";
