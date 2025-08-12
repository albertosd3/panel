<?php

// Create sample shortlinks for testing reset features
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Manually set up the app without booting
use App\Models\Shortlink;
use App\Models\ShortlinkEvent;

echo "Creating sample shortlinks for testing...\n";

try {
    // Create shortlinks directly with PDO
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    
    // Create sample shortlinks
    $shortlinks = [
        ['slug' => 'test1', 'destination' => 'https://google.com', 'clicks' => 0],
        ['slug' => 'test2', 'destination' => 'https://github.com', 'clicks' => 0],
        ['slug' => 'sample', 'destination' => 'https://example.com', 'clicks' => 0]
    ];
    
    foreach ($shortlinks as $link) {
        $stmt = $pdo->prepare("INSERT OR IGNORE INTO shortlinks (slug, destination, clicks, active, created_at, updated_at) VALUES (?, ?, ?, 1, datetime('now'), datetime('now'))");
        $stmt->execute([$link['slug'], $link['destination'], $link['clicks']]);
        echo "Created shortlink: {$link['slug']} -> {$link['destination']}\n";
    }
    
    // Create some sample events
    $stmt = $pdo->query("SELECT id, slug FROM shortlinks LIMIT 3");
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($links as $link) {
        // Create 3-5 sample events per shortlink
        $eventCount = rand(3, 8);
        for ($i = 0; $i < $eventCount; $i++) {
            $stmt = $pdo->prepare("INSERT INTO shortlink_events (shortlink_id, ip, country, city, device, platform, browser, referrer, clicked_at, is_bot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, datetime('now', '-' || ? || ' hours'), 0)");
            $stmt->execute([
                $link['id'],
                '192.168.1.' . rand(1, 100),
                'Indonesia',
                'Jakarta',
                'Desktop',
                'Windows',
                'Chrome',
                'https://google.com',
                rand(1, 48)
            ]);
        }
        
        // Update clicks count
        $stmt = $pdo->prepare("UPDATE shortlinks SET clicks = ? WHERE id = ?");
        $stmt->execute([$eventCount, $link['id']]);
        
        echo "Added {$eventCount} sample events for {$link['slug']}\n";
    }
    
    echo "\n✅ Sample data created successfully!\n";
    echo "Now you can test the reset visitor features.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
