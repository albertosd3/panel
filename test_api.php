<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\ShortlinkController;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== API Route Test ===\n";

// Test direct API call
try {
    $controller = new ShortlinkController();
    
    // Create a mock request
    $request = Request::create('/api/create', 'POST', [
        'destination' => 'https://google.com',
        'slug' => 'test-api-' . time()
    ]);
    
    $response = $controller->store($request);
    $data = json_decode($response->getContent(), true);
    
    if ($data['ok']) {
        echo "✓ API create test successful: {$data['data']['slug']}\n";
    } else {
        echo "✗ API create test failed: " . ($data['message'] ?? 'Unknown error') . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ API test error: " . $e->getMessage() . "\n";
}

echo "\n=== Route List ===\n";
$routes = app('router')->getRoutes();
foreach ($routes->getRoutesByMethod()['POST'] as $route) {
    if (str_contains($route->uri(), 'api') || str_contains($route->uri(), 'create')) {
        echo "POST: " . $route->uri() . " -> " . ($route->getActionName() ?? 'N/A') . "\n";
    }
}

echo "\n=== Test Complete ===\n";
