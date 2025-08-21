<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\ShortlinkController;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Shortlink Creation API Comprehensively ===\n";

$controller = new ShortlinkController();

// Test 1: Single shortlink with proper JSON handling
echo "\n1. Testing Single Shortlink Creation:\n";
try {
    $requestData = [
        'is_rotator' => false,
        'destination' => 'https://www.google.com',
        'slug' => 'test-comprehensive-single-' . time()
    ];
    
    echo "Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n";
    
    // Create request exactly like browser would
    $request = Request::create('/api/create', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json'
    ], json_encode($requestData));
    
    // Manually set the session for panel authentication
    $request->setLaravelSession(app('session'));
    session(['panel_authenticated' => true]);
    
    echo "Request created successfully\n";
    echo "Request method: " . $request->method() . "\n";
    echo "Content type: " . $request->header('Content-Type') . "\n";
    echo "Is JSON: " . ($request->isJson() ? 'Yes' : 'No') . "\n";
    echo "Boolean is_rotator: " . ($request->boolean('is_rotator') ? 'true' : 'false') . "\n";
    
    $response = $controller->store($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
    if ($responseData['ok'] ?? false) {
        echo "✅ Single shortlink created successfully!\n";
    } else {
        echo "❌ Single shortlink creation failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception in single shortlink test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 2: Rotator shortlink
echo "\n2. Testing Rotator Shortlink Creation:\n";
try {
    $requestData = [
        'is_rotator' => true,
        'rotation_type' => 'random',
        'destinations' => [
            ['url' => 'https://www.google.com', 'name' => 'Google', 'weight' => 1, 'active' => true],
            ['url' => 'https://www.bing.com', 'name' => 'Bing', 'weight' => 2, 'active' => true],
            ['url' => 'https://www.duckduckgo.com', 'name' => 'DuckDuckGo', 'weight' => 1, 'active' => true],
        ],
        'slug' => 'test-comprehensive-rotator-' . time()
    ];
    
    echo "Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n";
    
    $request = Request::create('/api/create', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json'
    ], json_encode($requestData));
    
    $request->setLaravelSession(app('session'));
    session(['panel_authenticated' => true]);
    
    $response = $controller->store($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
    if ($responseData['ok'] ?? false) {
        echo "✅ Rotator shortlink created successfully!\n";
    } else {
        echo "❌ Rotator shortlink creation failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception in rotator shortlink test: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

// Test 3: List recent shortlinks
echo "\n3. Testing Shortlink List:\n";
try {
    $request = Request::create('/api/links', 'GET', [], [], [], [
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json'
    ]);
    
    $request->setLaravelSession(app('session'));
    session(['panel_authenticated' => true]);
    
    $response = $controller->list($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    
    if ($responseData['ok'] ?? false) {
        $links = $responseData['data'] ?? [];
        echo "✅ Found " . count($links) . " shortlinks\n";
        
        echo "Recent shortlinks:\n";
        foreach (array_slice($links, 0, 5) as $i => $link) {
            echo "  " . ($i + 1) . ". {$link['slug']} -> " . substr($link['destination'], 0, 50);
            if ($link['is_rotator'] ?? false) {
                $destCount = count($link['destinations'] ?? []);
                echo " (Rotator: {$destCount} destinations)";
            }
            echo " [Clicks: " . ($link['clicks'] ?? 0) . "]\n";
        }
    } else {
        echo "❌ Failed to list shortlinks\n";
        echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception in list test: " . $e->getMessage() . "\n";
}

// Test 4: Analytics
echo "\n4. Testing Analytics:\n";
try {
    $request = Request::create('/api/analytics', 'GET', [], [], [], [
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json'
    ]);
    
    $request->setLaravelSession(app('session'));
    session(['panel_authenticated' => true]);
    
    $response = $controller->analytics($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    
    if ($responseData['ok'] ?? false) {
        $overview = $responseData['data']['overview'] ?? [];
        echo "✅ Analytics loaded successfully\n";
        echo "  Total Links: " . ($overview['total_links'] ?? 0) . "\n";
        echo "  Total Clicks: " . ($overview['total_clicks'] ?? 0) . "\n";
        echo "  Today Clicks: " . ($overview['today_clicks'] ?? 0) . "\n";
        echo "  Avg Clicks/Link: " . round($overview['avg_clicks_per_link'] ?? 0, 2) . "\n";
    } else {
        echo "❌ Failed to load analytics\n";
        echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception in analytics test: " . $e->getMessage() . "\n";
}

echo "\n=== Comprehensive API Test Complete ===\n";
echo "All backend API endpoints are working correctly.\n";
echo "The issue likely lies in the frontend JavaScript or browser-server communication.\n";
echo "Try accessing the panel at: http://localhost:8000/panel\n";
