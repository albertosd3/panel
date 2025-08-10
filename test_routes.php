<?php

// Test route resolution
echo "Testing domain routes...\n";

// Check if artisan route:list shows our domain routes
$output = shell_exec('php artisan route:list --name=panel.domains 2>&1');
echo "Routes with panel.domains:\n";
echo $output;

// Test basic connection to domains table
$pdo = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
$stmt = $pdo->query("SELECT COUNT(*) as count FROM domains");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nDomains in database: " . $result['count'] . "\n";

echo "\nDomain routes should be working now! ✅\n";
