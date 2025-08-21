<?php

// Test Stopbot API directly
$apiKey = 'a4b14a7c137b0f5f384206940fa11cee';
$testIp = '8.8.8.8';
$testUa = 'Mozilla/5.0 (Test)';
$testUrl = '/test';

echo "Testing Stopbot API...\n";
echo "API Key: " . substr($apiKey, 0, 8) . "...\n";
echo "Test IP: $testIp\n\n";

$url = 'https://stopbot.net/api/blocker?' . http_build_query([
    'apikey' => $apiKey,
    'ip' => $testIp,
    'ua' => $testUa,
    'url' => $testUrl,
    'rand' => rand(1, 1000000)
]);

$context = stream_context_create([
    'http' => [
        'timeout' => 10,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

echo "Making request to: https://stopbot.net/api/blocker\n";
$response = file_get_contents($url, false, $context);

if ($response === false) {
    echo "❌ Failed to connect to Stopbot API\n";
    exit(1);
}

echo "✅ Response received:\n";
echo $response . "\n\n";

$data = json_decode($response, true);
if (!$data) {
    echo "❌ Invalid JSON response\n";
    exit(1);
}

echo "Parsed JSON:\n";
print_r($data);

if (isset($data['status'])) {
    echo "\n--- Analysis ---\n";
    echo "Status: " . $data['status'] . "\n";
    
    if ($data['status'] === 'success') {
        $blockAccess = $data['IPStatus']['BlockAccess'] ?? 0;
        echo "Block Access: " . ($blockAccess ? 'YES' : 'NO') . "\n";
        echo "IP Score: " . ($data['IPStatus']['IPScore'] ?? 'N/A') . "\n";
        echo "Country: " . ($data['IPStatus']['Country'] ?? 'N/A') . "\n";
        
        if ($blockAccess) {
            echo "🚫 This IP would be BLOCKED\n";
        } else {
            echo "✅ This IP would be ALLOWED\n";
        }
    } else {
        echo "❌ API Error: " . ($data['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "❌ Unexpected response format\n";
}

echo "\n✅ Stopbot API test completed!\n";
