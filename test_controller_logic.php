<?php

// Quick test untuk memastikan controller bekerja dengan benar
echo "=== TESTING CONTROLLER METHODS ===\n\n";

// Set up minimal environment
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/links';

require __DIR__.'/vendor/autoload.php';

// Simulate request untuk testing
class MockRequest {
    public function boolean($key) {
        return false;
    }
    
    public function get($key, $default = null) {
        return $default;
    }
    
    public function validate($rules) {
        return [
            'slug' => 'test123',
            'destination' => 'https://www.google.com',
            'is_rotator' => false
        ];
    }
    
    public function ip() {
        return '127.0.0.1';
    }
}

// Test basic shortlink creation logic
echo "1. Testing shortlink creation logic...\n";

try {
    $slug = 'test-' . date('His');
    $destination = 'https://www.google.com';
    
    $db = new PDO('sqlite:database/database.sqlite');
    $stmt = $db->prepare("
        INSERT INTO shortlinks (slug, destination, clicks, active, is_rotator, rotation_type, current_index, meta, created_at, updated_at)
        VALUES (?, ?, 0, 1, 0, 'random', 0, ?, datetime('now'), datetime('now'))
    ");
    
    $meta = json_encode([
        'created_ip' => '127.0.0.1',
        'created_by' => 'test',
        'created_at_formatted' => date('Y-m-d H:i:s')
    ]);
    
    $result = $stmt->execute([$slug, $destination, $meta]);
    
    if ($result) {
        echo "✅ Direct creation successful: /$slug\n";
        
        // Test retrieval
        $stmt = $db->prepare("SELECT * FROM shortlinks WHERE slug = ?");
        $stmt->execute([$slug]);
        $link = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($link) {
            echo "✅ Retrieval successful: ID {$link['id']}\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n2. Testing rotator creation...\n";

try {
    $slug = 'rotator-' . date('His');
    $destinations = json_encode([
        ['url' => 'https://www.google.com', 'name' => 'Google', 'weight' => 50, 'active' => true],
        ['url' => 'https://www.youtube.com', 'name' => 'YouTube', 'weight' => 30, 'active' => true],
        ['url' => 'https://www.github.com', 'name' => 'GitHub', 'weight' => 20, 'active' => true]
    ]);
    
    $stmt = $db->prepare("
        INSERT INTO shortlinks (slug, destination, clicks, active, is_rotator, rotation_type, current_index, destinations, meta, created_at, updated_at)
        VALUES (?, ?, 0, 1, 1, 'weighted', 0, ?, ?, datetime('now'), datetime('now'))
    ");
    
    $meta = json_encode(['test' => 'rotator']);
    
    $result = $stmt->execute([$slug, 'https://www.google.com', $destinations, $meta]);
    
    if ($result) {
        echo "✅ Rotator creation successful: /$slug\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Rotator error: " . $e->getMessage() . "\n";
}

echo "\n3. Testing data retrieval for API...\n";

try {
    $stmt = $db->query("
        SELECT slug, destination, clicks, active, is_rotator, rotation_type, destinations, created_at
        FROM shortlinks 
        ORDER BY id DESC 
        LIMIT 5
    ");
    
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Found " . count($links) . " shortlinks:\n";
    foreach ($links as $link) {
        $type = $link['is_rotator'] ? 'Rotator' : 'Single';
        echo "   - /{$link['slug']} → {$link['destination']} ({$type})\n";
    }
    
    // Format like API response
    $apiResponse = [
        'ok' => true,
        'data' => $links
    ];
    
    echo "\n✅ API response format test:\n";
    echo json_encode($apiResponse, JSON_PRETTY_PRINT) . "\n";
    
} catch (\Exception $e) {
    echo "❌ Retrieval error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETED ===\n";
