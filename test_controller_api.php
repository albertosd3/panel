<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\ShortlinkController;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Shortlink Controller API ===\n";

$controller = new ShortlinkController();

// Test 1: Single shortlink via controller
echo "\n1. Testing Single Shortlink via Controller:\n";
try {
    $requestData = [
        'is_rotator' => false,
        'destination' => 'https://www.google.com',
        'slug' => 'test-api-single-' . rand(1000, 9999)
    ];
    
    echo "Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n";
    
    $request = Request::create('/api/create', 'POST', $requestData);
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $response = $controller->store($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
    if ($responseData['ok'] ?? false) {
        echo "✅ Single shortlink created successfully via controller!\n";
    } else {
        echo "❌ Failed to create single shortlink via controller\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error in single shortlink controller test: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test 2: Rotator shortlink via controller
echo "\n2. Testing Rotator Shortlink via Controller:\n";
try {
    $requestData = [
        'is_rotator' => true,
        'rotation_type' => 'random',
        'destinations' => [
            ['url' => 'https://www.google.com', 'name' => 'Google', 'weight' => 1, 'active' => true],
            ['url' => 'https://www.bing.com', 'name' => 'Bing', 'weight' => 2, 'active' => true],
            ['url' => 'https://www.duckduckgo.com', 'name' => 'DuckDuckGo', 'weight' => 1, 'active' => true],
        ],
        'slug' => 'test-api-rotator-' . rand(1000, 9999)
    ];
    
    echo "Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n";
    
    $request = Request::create('/api/create', 'POST', $requestData);
    $request->headers->set('Content-Type', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $response = $controller->store($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
    if ($responseData['ok'] ?? false) {
        echo "✅ Rotator shortlink created successfully via controller!\n";
    } else {
        echo "❌ Failed to create rotator shortlink via controller\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error in rotator shortlink controller test: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: List shortlinks via controller
echo "\n3. Testing Shortlink List via Controller:\n";
try {
    $request = Request::create('/api/links', 'GET');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $response = $controller->list($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    
    if ($responseData['ok'] ?? false) {
        $links = $responseData['data'] ?? [];
        echo "✅ Found " . count($links) . " shortlinks\n";
        
        foreach (array_slice($links, 0, 3) as $link) {
            echo "- {$link['slug']} -> " . substr($link['destination'], 0, 50);
            if ($link['is_rotator'] ?? false) {
                $destCount = count($link['destinations'] ?? []);
                echo " (Rotator: {$destCount} destinations)";
            }
            echo "\n";
        }
    } else {
        echo "❌ Failed to list shortlinks\n";
        echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error in list shortlinks test: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

// Test 4: Analytics via controller
echo "\n4. Testing Analytics via Controller:\n";
try {
    $request = Request::create('/api/analytics', 'GET');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    
    $response = $controller->analytics($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    
    if ($responseData['ok'] ?? false) {
        $overview = $responseData['data']['overview'] ?? [];
        echo "✅ Analytics loaded successfully\n";
        echo "Total Links: " . ($overview['total_links'] ?? 0) . "\n";
        echo "Total Clicks: " . ($overview['total_clicks'] ?? 0) . "\n";
        echo "Today Clicks: " . ($overview['today_clicks'] ?? 0) . "\n";
        echo "Avg Clicks/Link: " . ($overview['avg_clicks_per_link'] ?? 0) . "\n";
    } else {
        echo "❌ Failed to load analytics\n";
        echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error in analytics test: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Controller API Test Complete ===\n";
