<?php

// Debug script untuk mengecek semua API endpoints
$endpoints = [
    '/api/links' => 'GET',
    '/api/analytics' => 'GET', 
    '/api/create' => 'POST'
];

$baseUrl = 'http://localhost:8000'; // Adjust sesuai server Anda

echo "=== API ENDPOINTS DEBUG ===\n\n";

foreach ($endpoints as $endpoint => $method) {
    echo "Testing: $method $endpoint\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'destination' => 'https://google.com',
            'slug' => 'test123',
            'is_rotator' => false
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-CSRF-TOKEN: test-token'
        ]);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ CURL Error: $error\n";
    } else {
        echo "✅ HTTP Status: $httpCode\n";
        
        // Split headers and body
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        if ($headerSize > 0) {
            $headers = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
        } else {
            $body = $response;
        }
        
        echo "Response preview: " . substr($body, 0, 200) . "...\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}
