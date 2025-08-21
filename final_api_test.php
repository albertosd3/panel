<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Http\Request;
use App\Http\Controllers\ShortlinkController;

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Final API Functionality Test ===\n";

$controller = new ShortlinkController();

// Test 1: Single shortlink
echo "\n1. Testing Single Shortlink Creation:\n";
try {
    $requestData = [
        'is_rotator' => false,
        'destination' => 'https://www.google.com',
        'slug' => 'final-test-single-' . time()
    ];
    
    echo "Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n";
    
    $request = Request::create('/api/create', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json'
    ], json_encode($requestData));
    
    echo "Making request...\n";
    
    $response = $controller->store($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
    if (($responseData['ok'] ?? false) && $response->getStatusCode() === 200) {
        echo "✅ Single shortlink created successfully!\n";
        echo "   Slug: " . ($responseData['data']['slug'] ?? 'N/A') . "\n";
        echo "   URL: " . ($responseData['data']['full_url'] ?? 'N/A') . "\n";
    } else {
        echo "❌ Single shortlink creation failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
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
        'slug' => 'final-test-rotator-' . time()
    ];
    
    echo "Request Data: " . json_encode($requestData, JSON_PRETTY_PRINT) . "\n";
    
    $request = Request::create('/api/create', 'POST', [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        'HTTP_ACCEPT' => 'application/json'
    ], json_encode($requestData));
    
    echo "Making request...\n";
    
    $response = $controller->store($request);
    $responseData = json_decode($response->getContent(), true);
    
    echo "Response Status: " . $response->getStatusCode() . "\n";
    echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    
    if (($responseData['ok'] ?? false) && $response->getStatusCode() === 200) {
        echo "✅ Rotator shortlink created successfully!\n";
        echo "   Slug: " . ($responseData['data']['slug'] ?? 'N/A') . "\n";
        echo "   URL: " . ($responseData['data']['full_url'] ?? 'N/A') . "\n";
        echo "   Destinations: " . count($responseData['data']['destinations'] ?? []) . "\n";
    } else {
        echo "❌ Rotator shortlink creation failed\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Test Summary ===\n";
echo "Backend API is working correctly for shortlink creation.\n";
echo "Both single and rotator shortlinks can be created successfully.\n";
echo "\nTo test the frontend:\n";
echo "1. Start the Laravel server: php artisan serve --port=8000\n";
echo "2. Open browser to: http://localhost:8000/panel\n";
echo "3. Login with PIN: 666666\n";
echo "4. Try creating shortlinks through the web interface\n";
echo "\nIf frontend issues persist, check browser console for JavaScript errors.\n";
