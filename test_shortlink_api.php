<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Models\Shortlink;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Shortlink Creation API ===\n";

// Test single shortlink creation
echo "\n1. Testing Single Shortlink Creation:\n";
try {
    $data = [
        'is_rotator' => false,
        'destination' => 'https://www.google.com',
        'slug' => 'test-single-' . rand(1000, 9999)
    ];
    
    echo "Data to create: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    
    $link = Shortlink::create([
        'slug' => $data['slug'],
        'destination' => $data['destination'],
        'clicks' => 0,
        'active' => true,
        'is_rotator' => false,
        'rotation_type' => 'random',
        'destinations' => null,
        'current_index' => 0,
        'meta' => [
            'created_ip' => '127.0.0.1',
            'created_by' => 'test_script',
            'created_at_formatted' => now()->format('Y-m-d H:i:s'),
        ]
    ]);
    
    echo "✅ Single shortlink created successfully!\n";
    echo "ID: {$link->id}\n";
    echo "Slug: {$link->slug}\n";
    echo "Destination: {$link->destination}\n";
    echo "Full URL: {$link->full_url}\n";
    
} catch (\Exception $e) {
    echo "❌ Error creating single shortlink: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test rotator shortlink creation
echo "\n2. Testing Rotator Shortlink Creation:\n";
try {
    $data = [
        'is_rotator' => true,
        'rotation_type' => 'random',
        'destinations' => [
            ['url' => 'https://www.google.com', 'name' => 'Google', 'weight' => 1, 'active' => true],
            ['url' => 'https://www.bing.com', 'name' => 'Bing', 'weight' => 2, 'active' => true],
            ['url' => 'https://www.duckduckgo.com', 'name' => 'DuckDuckGo', 'weight' => 1, 'active' => true],
        ],
        'slug' => 'test-rotator-' . rand(1000, 9999)
    ];
    
    echo "Data to create: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
    
    $link = Shortlink::create([
        'slug' => $data['slug'],
        'destination' => $data['destinations'][0]['url'], // fallback destination
        'clicks' => 0,
        'active' => true,
        'is_rotator' => true,
        'rotation_type' => $data['rotation_type'],
        'destinations' => $data['destinations'],
        'current_index' => 0,
        'meta' => [
            'created_ip' => '127.0.0.1',
            'created_by' => 'test_script',
            'created_at_formatted' => now()->format('Y-m-d H:i:s'),
        ]
    ]);
    
    echo "✅ Rotator shortlink created successfully!\n";
    echo "ID: {$link->id}\n";
    echo "Slug: {$link->slug}\n";
    echo "Primary Destination: {$link->destination}\n";
    echo "Destinations Count: {$link->destinations_count}\n";
    echo "Rotation Summary: {$link->rotation_summary}\n";
    echo "Full URL: {$link->full_url}\n";
    
    // Test rotation
    echo "\nTesting rotation destinations:\n";
    for ($i = 1; $i <= 5; $i++) {
        $dest = $link->getNextDestination();
        echo "Rotation {$i}: {$dest}\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error creating rotator shortlink: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test listing
echo "\n3. Testing Shortlink Listing:\n";
try {
    $links = Shortlink::orderByDesc('id')->limit(5)->get();
    echo "Found {$links->count()} recent shortlinks:\n";
    
    foreach ($links as $link) {
        echo "- {$link->slug} -> {$link->destination}";
        if ($link->is_rotator) {
            echo " (Rotator: {$link->destinations_count} destinations)";
        }
        echo "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error listing shortlinks: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
