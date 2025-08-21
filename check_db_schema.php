<?php

$db = new PDO('sqlite:database/database.sqlite');

echo "=== DATABASE SCHEMA CHECK ===\n\n";

// Get all tables
$tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

echo "Tables in database:\n";
foreach ($tables as $table) {
    echo "✅ $table\n";
}

echo "\n--- Shortlinks Table Structure ---\n";
try {
    $columns = $db->query("PRAGMA table_info(shortlinks)")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo "{$col['name']} ({$col['type']}) " . ($col['notnull'] ? 'NOT NULL' : 'NULL') . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n--- Sample Data ---\n";
try {
    $count = $db->query("SELECT COUNT(*) FROM shortlinks")->fetchColumn();
    echo "Shortlinks count: $count\n";
    
    if ($count > 0) {
        $samples = $db->query("SELECT slug, destination, is_rotator, rotation_type FROM shortlinks LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($samples as $sample) {
            $type = $sample['is_rotator'] ? 'Rotator' : 'Single';
            echo "- /{$sample['slug']} → {$sample['destination']} ({$type})\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ Error reading data: " . $e->getMessage() . "\n";
}

echo "\n--- Test Insert ---\n";
try {
    $slug = 'test-' . date('His');
    $stmt = $db->prepare("
        INSERT INTO shortlinks (slug, destination, clicks, active, is_rotator, rotation_type, current_index, destinations, meta, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'), datetime('now'))
    ");
    
    $result = $stmt->execute([
        $slug,
        'https://www.example.com',
        0,
        1,
        0,
        'random',
        0,
        null,
        json_encode(['test' => true])
    ]);
    
    if ($result) {
        echo "✅ Test insert successful: /$slug\n";
    } else {
        echo "❌ Test insert failed\n";
    }
} catch (\Exception $e) {
    echo "❌ Insert error: " . $e->getMessage() . "\n";
}

echo "\n=== CHECK COMPLETED ===\n";
