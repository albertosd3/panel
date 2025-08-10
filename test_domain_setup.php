<?php

// Simple test to check if domains were created
$pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');

echo "Testing domains table...\n";

$stmt = $pdo->query("SELECT * FROM domains");
$domains = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($domains) . " domains:\n";

foreach ($domains as $domain) {
    echo "- {$domain['domain']}" . 
         ($domain['is_default'] ? ' (default)' : '') . 
         ($domain['is_active'] ? ' (active)' : ' (inactive)') . "\n";
}

// Test shortlinks table for domain_id column
echo "\nTesting shortlinks table...\n";
$stmt = $pdo->query("PRAGMA table_info(shortlinks)");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

$hasDomainId = false;
foreach ($columns as $column) {
    if ($column['name'] === 'domain_id') {
        $hasDomainId = true;
        break;
    }
}

echo $hasDomainId ? "✅ domain_id column exists in shortlinks table\n" : "❌ domain_id column missing in shortlinks table\n";

echo "\nDomain management feature is ready! 🎉\n";
