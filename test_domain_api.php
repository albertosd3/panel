<?php

// Test domain API endpoint
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->boot();

use Illuminate\Http\Request;
use App\Http\Controllers\DomainController;

echo "Testing Domain API endpoint...\n";

// Create a mock request
$request = Request::create('/api/domains', 'GET');

// Initialize controller
$controller = new DomainController();

try {
    $response = $controller->apiList();
    $data = json_decode($response->getContent(), true);
    
    echo "API Response:\n";
    echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
    
    if ($data['ok']) {
        echo "\n✅ API endpoint working correctly!\n";
        echo "Found " . count($data['data']) . " domains\n";
        
        foreach ($data['data'] as $domain) {
            echo "- {$domain['domain']}" . ($domain['is_default'] ? ' (default)' : '') . "\n";
        }
    } else {
        echo "\n❌ API endpoint returned error\n";
    }
} catch (Exception $e) {
    echo "\n❌ Error testing API: " . $e->getMessage() . "\n";
}
