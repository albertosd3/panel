<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\ShortlinkController;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Shortlink Controller JSON API ===\n";

$controller = new ShortlinkController();

// Test 1: Single shortlink via controller with proper JSON handling
echo "\n1. Testing Single Shortlink via Controller (JSON):\n";
try {
    $requestData = [
        'is_rotator' => false,
        'destination' => 'https://www.google.com',
        'slug' => 'test-json-single-' . rand(1000, 9999)
    ];
    
    echo "Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n";
    
    // Create request with JSON content
    $request = Request::create('/api/create', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
    ], json_encode($requestData));
    
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

// Test 2: Rotator shortlink via controller with proper JSON handling
echo "\n2. Testing Rotator Shortlink via Controller (JSON):\n";
try {
    $requestData = [
        'is_rotator' => true,
        'rotation_type' => 'random',
        'destinations' => [
            ['url' => 'https://www.google.com', 'name' => 'Google', 'weight' => 1, 'active' => true],
            ['url' => 'https://www.bing.com', 'name' => 'Bing', 'weight' => 2, 'active' => true],
            ['url' => 'https://www.duckduckgo.com', 'name' => 'DuckDuckGo', 'weight' => 1, 'active' => true],
        ],
        'slug' => 'test-json-rotator-' . rand(1000, 9999)
    ];
    
    echo "Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n";
    
    // Create request with JSON content
    $request = Request::create('/api/create', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
    ], json_encode($requestData));
    
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

// Test 3: Test what data is actually received by the controller
echo "\n3. Testing Data Reception:\n";
try {
    $requestData = [
        'is_rotator' => false,
        'destination' => 'https://www.google.com',
        'slug' => 'test-debug-' . rand(1000, 9999)
    ];
    
    $request = Request::create('/api/create', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'
    ], json_encode($requestData));
    
    // Debug what the request contains
    echo "Request method: " . $request->method() . "\n";
    echo "Content type: " . $request->header('Content-Type') . "\n";
    echo "Raw content: " . $request->getContent() . "\n";
    echo "Has destination (get): " . ($request->get('destination') ? 'Yes' : 'No') . "\n";
    echo "Has destination (input): " . ($request->input('destination') ? 'Yes' : 'No') . "\n";
    echo "Boolean is_rotator: " . ($request->boolean('is_rotator') ? 'true' : 'false') . "\n";
    echo "All input: " . json_encode($request->all()) . "\n";
    echo "All JSON: " . json_encode($request->json()->all()) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Error in debug test: " . $e->getMessage() . "\n";
}

echo "\n=== JSON Controller API Test Complete ===\n";
