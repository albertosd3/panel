<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\Shortlink;
use App\Models\ShortlinkEvent;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Shortlink Panel Test ===\n";

// Test 1: Check database connection
try {
    $linkCount = Shortlink::count();
    $eventCount = ShortlinkEvent::count();
    echo "✓ Database connected - {$linkCount} links, {$eventCount} events\n";
} catch (Exception $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check config
$pin = config('panel.pin');
$blockBots = config('panel.block_bots') ? 'true' : 'false';
$aggressiveDetection = config('panel.aggressive_bot_detection') ? 'true' : 'false';
echo "✓ Config loaded - PIN: {$pin}, Block bots: {$blockBots}, Aggressive: {$aggressiveDetection}\n";

// Test 3: Test shortlink creation
try {
    $testSlug = 'test-' . time();
    $link = Shortlink::create([
        'slug' => $testSlug,
        'destination' => 'https://example.com/test',
        'active' => true,
        'clicks' => 0
    ]);
    echo "✓ Shortlink created: /{$testSlug}\n";
} catch (Exception $e) {
    echo "✗ Shortlink creation failed: " . $e->getMessage() . "\n";
}

// Test 4: Test event creation
try {
    $event = ShortlinkEvent::create([
        'shortlink_id' => $link->id,
        'ip' => '127.0.0.1',
        'country' => 'ID',
        'city' => 'Jakarta',
        'device' => 'Desktop',
        'platform' => 'Windows',
        'browser' => 'Chrome',
        'is_bot' => false,
        'clicked_at' => now()
    ]);
    echo "✓ Event created for shortlink\n";
} catch (Exception $e) {
    echo "✗ Event creation failed: " . $e->getMessage() . "\n";
}

// Test 5: Test analytics data
try {
    $totalLinks = Shortlink::count();
    $totalClicks = ShortlinkEvent::count();
    $todayClicks = ShortlinkEvent::whereDate('clicked_at', today())->count();
    
    echo "✓ Analytics: {$totalLinks} total links, {$totalClicks} total clicks, {$todayClicks} today\n";
} catch (Exception $e) {
    echo "✗ Analytics query failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
echo "Visit http://localhost:8000 to access the panel\n";
echo "Use PIN: {$pin} to login\n";
