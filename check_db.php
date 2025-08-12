<?php

echo "Testing SQLite connection...\n";

try {
    $pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    echo "Connection OK!\n";
    
    // Check shortlink_events table structure
    echo "\nShortlink_events table structure:\n";
    $stmt = $pdo->query('PRAGMA table_info(shortlink_events)');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($columns as $col) {
        echo "- " . $col['name'] . ' (' . $col['type'] . ")\n";
    }
    
    // Check data counts
    echo "\nData counts:\n";
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM shortlinks');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Shortlinks: " . $result['count'] . "\n";
    
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM shortlink_events');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Events: " . $result['count'] . "\n";
    
    // Show sample shortlinks
    echo "\nSample shortlinks:\n";
    $stmt = $pdo->query('SELECT slug, destination, clicks FROM shortlinks LIMIT 5');
    $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($links as $link) {
        echo "- {$link['slug']}: {$link['clicks']} clicks -> {$link['destination']}\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
