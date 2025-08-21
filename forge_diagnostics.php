<?php
/**
 * Laravel Forge Diagnostic Tool
 * Run this script to diagnose common issues with shortlink panel deployment
 */

// Security check - only allow in development or specific IPs
$allowedIps = ['127.0.0.1', '::1']; // Add your IP here
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedIps) && !isset($_GET['force'])) {
    die('Access denied. Add ?force=1 to bypass IP check.');
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laravel Forge Diagnostics</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #ddd; padding: 20px; }
        .test { margin: 10px 0; padding: 10px; border-left: 4px solid #666; }
        .pass { border-color: #28a745; background: rgba(40, 167, 69, 0.1); }
        .fail { border-color: #dc3545; background: rgba(220, 53, 69, 0.1); }
        .warn { border-color: #ffc107; background: rgba(255, 193, 7, 0.1); }
        .section { margin: 20px 0; padding: 15px; background: #2a2a2a; border-radius: 5px; }
        pre { background: #000; padding: 10px; overflow-x: auto; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; margin: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>🔧 Laravel Forge Diagnostics</h1>
    
    <div class="section">
        <h2>🌐 Environment Tests</h2>
        
        <?php
        // Test 1: PHP Version
        $phpVersion = PHP_VERSION;
        $minPhp = '8.1.0';
        $phpOk = version_compare($phpVersion, $minPhp, '>=');
        echo "<div class='test " . ($phpOk ? 'pass' : 'fail') . "'>";
        echo "PHP Version: $phpVersion " . ($phpOk ? '✅' : '❌ (Minimum: ' . $minPhp . ')');
        echo "</div>";
        
        // Test 2: Extensions
        $requiredExts = ['pdo', 'pdo_sqlite', 'json', 'curl', 'mbstring', 'openssl'];
        foreach ($requiredExts as $ext) {
            $loaded = extension_loaded($ext);
            echo "<div class='test " . ($loaded ? 'pass' : 'fail') . "'>";
            echo "Extension $ext: " . ($loaded ? '✅ Loaded' : '❌ Missing');
            echo "</div>";
        }
        
        // Test 3: File permissions
        $paths = [
            'storage/logs' => 'storage/logs',
            'storage/framework' => 'storage/framework',
            'bootstrap/cache' => 'bootstrap/cache'
        ];
        
        foreach ($paths as $label => $path) {
            if (file_exists($path)) {
                $writable = is_writable($path);
                echo "<div class='test " . ($writable ? 'pass' : 'fail') . "'>";
                echo "$label: " . ($writable ? '✅ Writable' : '❌ Not writable');
                echo "</div>";
            } else {
                echo "<div class='test fail'>$label: ❌ Directory not found</div>";
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>📁 Laravel Framework Tests</h2>
        
        <?php
        // Test Laravel bootstrap
        try {
            if (file_exists('vendor/autoload.php')) {
                require_once 'vendor/autoload.php';
                echo "<div class='test pass'>Composer autoload: ✅ Found</div>";
                
                if (file_exists('bootstrap/app.php')) {
                    $app = require_once 'bootstrap/app.php';
                    echo "<div class='test pass'>Laravel app: ✅ Bootstrapped</div>";
                    
                    // Test database connection
                    try {
                        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
                        $kernel->bootstrap();
                        
                        $db = $app->make('db');
                        $db->connection()->getPdo();
                        echo "<div class='test pass'>Database: ✅ Connected</div>";
                        
                        // Test specific tables
                        $tables = ['shortlinks', 'panel_settings', 'shortlink_events'];
                        foreach ($tables as $table) {
                            try {
                                $exists = $db->getSchemaBuilder()->hasTable($table);
                                echo "<div class='test " . ($exists ? 'pass' : 'fail') . "'>";
                                echo "Table $table: " . ($exists ? '✅ Exists' : '❌ Missing');
                                echo "</div>";
                            } catch (Exception $e) {
                                echo "<div class='test fail'>Table $table: ❌ Error checking</div>";
                            }
                        }
                        
                    } catch (Exception $e) {
                        echo "<div class='test fail'>Database: ❌ Connection failed - " . $e->getMessage() . "</div>";
                    }
                } else {
                    echo "<div class='test fail'>Laravel app: ❌ bootstrap/app.php not found</div>";
                }
            } else {
                echo "<div class='test fail'>Composer autoload: ❌ vendor/autoload.php not found</div>";
            }
        } catch (Exception $e) {
            echo "<div class='test fail'>Laravel Framework: ❌ " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🌐 Web Server Tests</h2>
        
        <?php
        // Server info
        echo "<div class='test pass'>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</div>";
        echo "<div class='test pass'>Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "</div>";
        echo "<div class='test pass'>Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Unknown') . "</div>";
        echo "<div class='test pass'>Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "</div>";
        echo "<div class='test pass'>HTTP Host: " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "</div>";
        
        // HTTPS check
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
        echo "<div class='test " . ($isHttps ? 'pass' : 'warn') . "'>";
        echo "HTTPS: " . ($isHttps ? '✅ Enabled' : '⚠️ Not detected (may cause mixed content issues)');
        echo "</div>";
        ?>
    </div>
    
    <div class="section">
        <h2>🔗 API Endpoint Tests</h2>
        <button onclick="testApiEndpoints()">Test API Endpoints</button>
        <div id="api-results"></div>
    </div>
    
    <div class="section">
        <h2>📋 Environment Variables</h2>
        <button onclick="showEnvVars()">Show Environment</button>
        <div id="env-results"></div>
    </div>
    
    <div class="section">
        <h2>📝 Log Files</h2>
        <button onclick="showLogs()">Show Recent Logs</button>
        <div id="log-results"></div>
    </div>

    <script>
        async function testApiEndpoints() {
            const results = document.getElementById('api-results');
            results.innerHTML = '<p>Testing API endpoints...</p>';
            
            const endpoints = [
                { name: 'List Shortlinks', url: '/api/list', method: 'GET' },
                { name: 'Analytics', url: '/api/analytics', method: 'GET' },
                { name: 'Health Check', url: '/health', method: 'GET' }
            ];
            
            let html = '';
            
            for (const endpoint of endpoints) {
                try {
                    const response = await fetch(endpoint.url, {
                        method: endpoint.method,
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const status = response.status;
                    const isOk = status >= 200 && status < 300;
                    
                    html += `<div class="test ${isOk ? 'pass' : 'fail'}">`;
                    html += `${endpoint.name} (${endpoint.method} ${endpoint.url}): `;
                    html += `${isOk ? '✅' : '❌'} HTTP ${status}`;
                    
                    if (response.headers.get('content-type')?.includes('application/json')) {
                        try {
                            const data = await response.json();
                            if (data.ok !== undefined) {
                                html += ` - API Response: ${data.ok ? 'OK' : 'Error'}`;
                            }
                        } catch (e) {
                            html += ` - JSON Parse Error`;
                        }
                    }
                    html += '</div>';
                    
                } catch (error) {
                    html += `<div class="test fail">${endpoint.name}: ❌ ${error.message}</div>`;
                }
            }
            
            results.innerHTML = html;
        }
        
        async function showEnvVars() {
            const results = document.getElementById('env-results');
            results.innerHTML = '<p>Loading environment variables...</p>';
            
            try {
                const response = await fetch('/forge_diagnostics.php?action=env');
                const text = await response.text();
                results.innerHTML = `<pre>${text}</pre>`;
            } catch (error) {
                results.innerHTML = `<div class="test fail">Error: ${error.message}</div>`;
            }
        }
        
        async function showLogs() {
            const results = document.getElementById('log-results');
            results.innerHTML = '<p>Loading log files...</p>';
            
            try {
                const response = await fetch('/forge_diagnostics.php?action=logs');
                const text = await response.text();
                results.innerHTML = `<pre>${text}</pre>`;
            } catch (error) {
                results.innerHTML = `<div class="test fail">Error: ${error.message}</div>`;
            }
        }
    </script>
    
    <?php
    // Handle AJAX requests
    if (isset($_GET['action'])) {
        header('Content-Type: text/plain');
        
        switch ($_GET['action']) {
            case 'env':
                echo "Environment Variables:\n";
                echo "APP_ENV: " . ($_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: 'Not set') . "\n";
                echo "APP_DEBUG: " . ($_ENV['APP_DEBUG'] ?? getenv('APP_DEBUG') ?: 'Not set') . "\n";
                echo "APP_URL: " . ($_ENV['APP_URL'] ?? getenv('APP_URL') ?: 'Not set') . "\n";
                echo "DB_CONNECTION: " . ($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION') ?: 'Not set') . "\n";
                echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'Not set') . "\n";
                echo "\nPHP Configuration:\n";
                echo "memory_limit: " . ini_get('memory_limit') . "\n";
                echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
                echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
                echo "post_max_size: " . ini_get('post_max_size') . "\n";
                break;
                
            case 'logs':
                $logPath = 'storage/logs/laravel.log';
                if (file_exists($logPath)) {
                    $logs = file_get_contents($logPath);
                    // Show last 50 lines
                    $lines = explode("\n", $logs);
                    $recentLines = array_slice($lines, -50);
                    echo implode("\n", $recentLines);
                } else {
                    echo "Log file not found: $logPath";
                }
                break;
        }
        exit;
    }
    ?>
    
    <div class="section">
        <h2>📖 Instructions</h2>
        <ol>
            <li>Upload this file to your Laravel Forge public directory</li>
            <li>Access it via https://yourdomain.com/forge_diagnostics.php</li>
            <li>Run all tests to identify issues</li>
            <li>Check the Laravel logs in storage/logs/laravel.log</li>
            <li>Verify API endpoints are working</li>
            <li>Test frontend JavaScript in browser console</li>
        </ol>
        
        <h3>Common Fixes for Forge:</h3>
        <ul>
            <li>Run <code>php artisan config:cache</code> and <code>php artisan route:cache</code></li>
            <li>Set correct file permissions: <code>chmod -R 755 storage bootstrap/cache</code></li>
            <li>Check .env file is properly configured</li>
            <li>Verify database migrations: <code>php artisan migrate</code></li>
            <li>Clear all caches: <code>php artisan optimize:clear</code></li>
        </ul>
        
        <p><strong>Delete this file after diagnostics are complete!</strong></p>
    </div>
</body>
</html>
