<?php
echo "Testing SQLite connection...\n";
$pdo = new PDO('sqlite:database/database.sqlite');
echo "Connection OK!\n";
$stmt = $pdo->query('SELECT name FROM sqlite_master WHERE type="table";');
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Tables: " . implode(', ', $tables) . "\n";
