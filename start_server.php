<?php

/**
 * Script untuk memulai web server sederhana untuk test login
 * Jalankan dengan: php start_server.php
 */

echo "=== STARTING WEB SERVER ===\n";
echo "Server akan berjalan di: http://localhost:8000\n";
echo "Login URL: http://localhost:8000/panel/login\n";
echo "PIN: 666666\n\n";

echo "Tekan Ctrl+C untuk stop server\n\n";

// Start PHP built-in server
$command = 'php -S localhost:8000 -t public';
echo "Executing: {$command}\n\n";

// Execute command
passthru($command);
