<?php
/**
 * Shortlink Creation Test Script
 * Direct test of the shortlink creation API without frontend
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

try {
    echo json_encode([
        'message' => 'Testing shortlink creation API...',
        'timestamp' => now()->toISOString()
    ]);
    echo "\n\n";
    
    // Test 1: Single Link Creation
    echo "=== Test 1: Single Link Creation ===\n";
    
    $singleLinkData = [
        'is_rotator' => false,
        'destination' => 'https://www.google.com',
        'slug' => 'test-single-' . time()
    ];
    
    try {
        $controller = new \App\Http\Controllers\ShortlinkController();
        $request = \Illuminate\Http\Request::create('/api/create', 'POST', $singleLinkData);
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');
        
        $response = $controller->create($request);
        $responseData = json_decode($response->getContent(), true);
        
        echo "Response Status: " . $response->getStatusCode() . "\n";
        echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        
        if ($response->getStatusCode() === 200 && $responseData['ok'] ?? false) {
            echo "✅ Single link creation test PASSED\n";
        } else {
            echo "❌ Single link creation test FAILED\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Single link creation test ERROR: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\n";
    
    // Test 2: Rotator Link Creation
    echo "=== Test 2: Rotator Link Creation ===\n";
    
    $rotatorLinkData = [
        'is_rotator' => true,
        'rotation_type' => 'random',
        'destinations' => [
            [
                'url' => 'https://www.google.com',
                'name' => 'Google',
                'weight' => 1,
                'active' => true
            ],
            [
                'url' => 'https://www.youtube.com',
                'name' => 'YouTube',
                'weight' => 2,
                'active' => true
            ]
        ],
        'slug' => 'test-rotator-' . time()
    ];
    
    try {
        $controller = new \App\Http\Controllers\ShortlinkController();
        $request = \Illuminate\Http\Request::create('/api/create', 'POST', $rotatorLinkData);
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');
        
        $response = $controller->create($request);
        $responseData = json_decode($response->getContent(), true);
        
        echo "Response Status: " . $response->getStatusCode() . "\n";
        echo "Response Data: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        
        if ($response->getStatusCode() === 200 && $responseData['ok'] ?? false) {
            echo "✅ Rotator link creation test PASSED\n";
        } else {
            echo "❌ Rotator link creation test FAILED\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Rotator link creation test ERROR: " . $e->getMessage() . "\n";
        echo "Stack trace: " . $e->getTraceAsString() . "\n";
    }
    
    echo "\n";
    
    // Test 3: API List Endpoint
    echo "=== Test 3: API List Endpoint ===\n";
    
    try {
        $controller = new \App\Http\Controllers\ShortlinkController();
        $request = \Illuminate\Http\Request::create('/api/list', 'GET');
        $request->headers->set('Accept', 'application/json');
        
        $response = $controller->list($request);
        $responseData = json_decode($response->getContent(), true);
        
        echo "Response Status: " . $response->getStatusCode() . "\n";
        echo "Links Count: " . count($responseData['data'] ?? []) . "\n";
        
        if ($response->getStatusCode() === 200 && $responseData['ok'] ?? false) {
            echo "✅ API list test PASSED\n";
        } else {
            echo "❌ API list test FAILED\n";
        }
        
    } catch (Exception $e) {
        echo "❌ API list test ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
    
    // Test 4: Analytics Endpoint
    echo "=== Test 4: Analytics Endpoint ===\n";
    
    try {
        $controller = new \App\Http\Controllers\ShortlinkController();
        $request = \Illuminate\Http\Request::create('/api/analytics', 'GET');
        $request->headers->set('Accept', 'application/json');
        
        $response = $controller->analytics($request);
        $responseData = json_decode($response->getContent(), true);
        
        echo "Response Status: " . $response->getStatusCode() . "\n";
        echo "Total Links: " . ($responseData['data']['overview']['total_links'] ?? 'N/A') . "\n";
        echo "Total Clicks: " . ($responseData['data']['overview']['total_clicks'] ?? 'N/A') . "\n";
        
        if ($response->getStatusCode() === 200 && $responseData['ok'] ?? false) {
            echo "✅ Analytics test PASSED\n";
        } else {
            echo "❌ Analytics test FAILED\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Analytics test ERROR: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== All Tests Complete ===\n";
    echo "If all tests passed, the backend API is working correctly.\n";
    echo "If shortlink creation still fails in the frontend, check:\n";
    echo "1. Browser console for JavaScript errors\n";
    echo "2. Network tab for failed AJAX requests\n";
    echo "3. CSRF token issues\n";
    echo "4. Mixed content (HTTP/HTTPS) issues\n";
    
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
