<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Models\Shortlink;
use Illuminate\Support\Str;

echo "=== TESTING SHORTLINK CREATION ===\n\n";

// Test 1: Create single shortlink
echo "1. Testing Single Shortlink Creation...\n";
try {
    $slug = 'test-' . Str::random(6);
    $shortlink = Shortlink::create([
        'slug' => $slug,
        'destination' => 'https://www.google.com',
        'clicks' => 0,
        'active' => true,
        'is_rotator' => false,
        'rotation_type' => 'random',
        'current_index' => 0,
        'destinations' => null,
        'meta' => [
            'created_ip' => '127.0.0.1',
            'created_by' => 'test',
            'created_at_formatted' => now()->format('Y-m-d H:i:s'),
        ]
    ]);
    
    echo "✅ Single shortlink created: /{$shortlink->slug}\n";
    echo "   ID: {$shortlink->id}\n";
    echo "   Destination: {$shortlink->destination}\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating single shortlink: " . $e->getMessage() . "\n\n";
}

// Test 2: Create rotator shortlink  
echo "2. Testing Rotator Shortlink Creation...\n";
try {
    $slug = 'rotator-' . Str::random(6);
    $destinations = [
        [
            'url' => 'https://www.google.com',
            'name' => 'Google',
            'weight' => 50,
            'active' => true
        ],
        [
            'url' => 'https://www.youtube.com', 
            'name' => 'YouTube',
            'weight' => 30,
            'active' => true
        ],
        [
            'url' => 'https://www.github.com',
            'name' => 'GitHub', 
            'weight' => 20,
            'active' => true
        ]
    ];
    
    $rotator = Shortlink::create([
        'slug' => $slug,
        'destination' => $destinations[0]['url'], // fallback destination
        'clicks' => 0,
        'active' => true,
        'is_rotator' => true,
        'rotation_type' => 'weighted',
        'current_index' => 0,
        'destinations' => $destinations,
        'meta' => [
            'created_ip' => '127.0.0.1',
            'created_by' => 'test',
            'created_at_formatted' => now()->format('Y-m-d H:i:s'),
        ]
    ]);
    
    echo "✅ Rotator shortlink created: /{$rotator->slug}\n";
    echo "   ID: {$rotator->id}\n";
    echo "   Type: {$rotator->rotation_type}\n";
    echo "   Destinations: " . count($rotator->destinations) . "\n\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating rotator shortlink: " . $e->getMessage() . "\n\n";
}

// Test 3: List all shortlinks
echo "3. Testing Shortlink Listing...\n";
try {
    $links = Shortlink::orderByDesc('id')->limit(10)->get();
    echo "✅ Found {$links->count()} shortlinks in database:\n";
    
    foreach ($links as $link) {
        $type = $link->is_rotator ? 'Rotator' : 'Single';
        echo "   - /{$link->slug} → {$link->destination} ({$type})\n";
    }
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ Error listing shortlinks: " . $e->getMessage() . "\n\n";
}

// Test 4: Check database tables
echo "4. Checking Database Tables...\n";
try {
    $shortlinksCount = Shortlink::count();
    echo "✅ Shortlinks table: $shortlinksCount records\n";
    
    // Check if shortlink_events table exists
    try {
        $eventsCount = \DB::table('shortlink_events')->count();
        echo "✅ Shortlink events table: $eventsCount records\n";
    } catch (\Exception $e) {
        echo "⚠️  Shortlink events table: Not found or empty\n";
    }
    
    // Check panel_settings table
    try {
        $settingsCount = \DB::table('panel_settings')->count();
        echo "✅ Panel settings table: $settingsCount records\n";
    } catch (\Exception $e) {
        echo "⚠️  Panel settings table: Not found or empty\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error checking database: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETED ===\n";
