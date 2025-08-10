<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Complete Panel Debugging ===\n\n";

// Test 1: Database connection
echo "1. Testing Database Connection...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "   ✓ Database connected successfully\n";
    
    // Check tables exist
    $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table'");
    echo "   ✓ Found " . count($tables) . " tables\n";
    
    foreach ($tables as $table) {
        if (in_array($table->name, ['shortlinks', 'shortlink_events', 'blocked_ips'])) {
            echo "   ✓ Table {$table->name} exists\n";
        }
    }
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}

// Test 2: Check models
echo "\n2. Testing Models...\n";
try {
    $linkCount = \App\Models\Shortlink::count();
    $eventCount = \App\Models\ShortlinkEvent::count();
    echo "   ✓ Shortlink model works - {$linkCount} links\n";
    echo "   ✓ ShortlinkEvent model works - {$eventCount} events\n";
} catch (Exception $e) {
    echo "   ✗ Model error: " . $e->getMessage() . "\n";
}

// Test 3: Check configuration
echo "\n3. Testing Configuration...\n";
$pin = config('panel.pin');
$blockBots = config('panel.block_bots') ? 'enabled' : 'disabled';
$aggressiveDetection = config('panel.aggressive_bot_detection') ? 'enabled' : 'disabled';
echo "   ✓ PIN: {$pin}\n";
echo "   ✓ Bot blocking: {$blockBots}\n";
echo "   ✓ Aggressive detection: {$aggressiveDetection}\n";

// Test 4: Check routes
echo "\n4. Testing Routes...\n";
$routes = app('router')->getRoutes();
$apiRoutes = [];
foreach ($routes->getRoutesByMethod()['POST'] as $route) {
    if (str_contains($route->uri(), 'api')) {
        $apiRoutes[] = $route->uri();
    }
}
foreach ($routes->getRoutesByMethod()['GET'] as $route) {
    if (str_contains($route->uri(), 'api')) {
        $apiRoutes[] = $route->uri();
    }
}

if (empty($apiRoutes)) {
    echo "   ✗ No API routes found\n";
} else {
    echo "   ✓ Found API routes:\n";
    foreach ($apiRoutes as $route) {
        echo "     - {$route}\n";
    }
}

// Test 5: Test controller directly
echo "\n5. Testing Controller...\n";
try {
    $controller = new \App\Http\Controllers\ShortlinkController();
    
    // Test analytics
    $request = \Illuminate\Http\Request::create('/api/analytics', 'GET');
    $response = $controller->analytics($request);
    $analyticsData = json_decode($response->getContent(), true);
    
    if ($analyticsData && isset($analyticsData['ok']) && $analyticsData['ok']) {
        echo "   ✓ Analytics method works\n";
    } else {
        echo "   ✗ Analytics method failed\n";
    }
    
    // Test list
    $request = \Illuminate\Http\Request::create('/api/links', 'GET');
    $response = $controller->list($request);
    $listData = json_decode($response->getContent(), true);
    
    if ($listData && isset($listData['ok']) && $listData['ok']) {
        echo "   ✓ List method works\n";
    } else {
        echo "   ✗ List method failed\n";
    }
    
    // Test store
    $request = \Illuminate\Http\Request::create('/api/create', 'POST', [
        'destination' => 'https://example.com/debug-test',
        'slug' => 'debug-' . time()
    ]);
    
    $response = $controller->store($request);
    $storeData = json_decode($response->getContent(), true);
    
    if ($storeData && isset($storeData['ok']) && $storeData['ok']) {
        echo "   ✓ Store method works - created slug: {$storeData['data']['slug']}\n";
    } else {
        echo "   ✗ Store method failed: " . ($storeData['message'] ?? 'Unknown error') . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Controller error: " . $e->getMessage() . "\n";
}

// Test 6: Create sample data for frontend testing
echo "\n6. Creating Sample Data...\n";
try {
    // Create some sample shortlinks
    for ($i = 1; $i <= 3; $i++) {
        $slug = 'sample-' . $i . '-' . time();
        $link = \App\Models\Shortlink::create([
            'slug' => $slug,
            'destination' => "https://example.com/page-{$i}",
            'clicks' => rand(5, 50),
            'active' => true
        ]);
        
        // Create some sample events
        for ($j = 1; $j <= rand(2, 8); $j++) {
            \App\Models\ShortlinkEvent::create([
                'shortlink_id' => $link->id,
                'ip' => '192.168.1.' . rand(1, 254),
                'country' => ['ID', 'US', 'SG', 'MY'][rand(0, 3)],
                'city' => ['Jakarta', 'New York', 'Singapore', 'Kuala Lumpur'][rand(0, 3)],
                'device' => ['Desktop', 'Mobile', 'Tablet'][rand(0, 2)],
                'platform' => ['Windows', 'Android', 'iOS', 'Linux'][rand(0, 3)],
                'browser' => ['Chrome', 'Firefox', 'Safari', 'Edge'][rand(0, 3)],
                'is_bot' => rand(0, 10) > 8, // 20% chance of bot
                'clicked_at' => now()->subHours(rand(1, 72))
            ]);
        }
        echo "   ✓ Created sample link: {$slug}\n";
    }
} catch (Exception $e) {
    echo "   ✗ Sample data creation failed: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";
echo "\nTo test the frontend:\n";
echo "1. Start server: php artisan serve\n";
echo "2. Visit: http://localhost:8000/panel/login\n";
echo "3. Use PIN: {$pin}\n";
echo "4. Try creating a shortlink\n";
echo "\nTo test API directly:\n";
echo "curl -X GET http://localhost:8000/api/debug\n";
echo "curl -X GET http://localhost:8000/api/analytics\n";
echo "\n";
