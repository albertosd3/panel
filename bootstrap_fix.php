<?php
/**
 * Laravel Bootstrap Diagnostic & Fix Tool
 * Run this to diagnose and fix bootstrap issues on Laravel Forge
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laravel Bootstrap Fix Tool</title>
    <style>
        body { font-family: monospace; background: #1e1e1e; color: #ddd; padding: 20px; }
        .test { margin: 10px 0; padding: 10px; border-left: 4px solid #666; }
        .pass { border-color: #28a745; background: rgba(40, 167, 69, 0.1); }
        .fail { border-color: #dc3545; background: rgba(220, 53, 69, 0.1); }
        .warn { border-color: #ffc107; background: rgba(255, 193, 7, 0.1); }
        .section { margin: 20px 0; padding: 15px; background: #2a2a2a; border-radius: 5px; }
        pre { background: #000; padding: 10px; overflow-x: auto; white-space: pre-wrap; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; margin: 5px; cursor: pointer; border-radius: 4px; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>🔧 Laravel Bootstrap Fix Tool</h1>
    
    <div class="section">
        <h2>📁 File Structure Check</h2>
        <?php
        $requiredFiles = [
            'vendor/autoload.php' => 'Composer Autoloader',
            'bootstrap/app.php' => 'Laravel Bootstrap',
            'bootstrap/providers.php' => 'Service Providers',
            '.env' => 'Environment File',
            'database/database.sqlite' => 'SQLite Database',
            'storage/logs' => 'Logs Directory',
            'storage/framework/cache' => 'Cache Directory',
            'storage/framework/sessions' => 'Sessions Directory',
            'storage/framework/views' => 'Views Directory'
        ];
        
        foreach ($requiredFiles as $file => $description) {
            $exists = file_exists($file);
            $class = $exists ? 'pass' : 'fail';
            $status = $exists ? '✅ Found' : '❌ Missing';
            
            echo "<div class='test $class'>$description ($file): $status</div>";
            
            if (!$exists && strpos($file, 'storage/') === 0) {
                // Try to create missing storage directories
                @mkdir(dirname($file), 0755, true);
                if (file_exists(dirname($file))) {
                    echo "<div class='test pass'>→ Created directory: " . dirname($file) . "</div>";
                }
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🔧 Environment Configuration</h2>
        <?php
        if (file_exists('.env')) {
            $envContent = file_get_contents('.env');
            $envLines = explode("\n", $envContent);
            $envVars = [];
            
            foreach ($envLines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $envVars[trim($key)] = trim($value);
                }
            }
            
            $criticalVars = [
                'APP_ENV' => 'Should be "production" for Forge',
                'APP_DEBUG' => 'Should be "false" for production',
                'APP_URL' => 'Should match your domain with https://',
                'APP_KEY' => 'Required for encryption',
                'DB_CONNECTION' => 'Database connection type'
            ];
            
            foreach ($criticalVars as $var => $description) {
                $value = $envVars[$var] ?? 'NOT SET';
                $class = 'warn';
                $status = "⚠️ $value";
                
                // Check specific conditions
                if ($var === 'APP_ENV' && $value === 'production') {
                    $class = 'pass';
                    $status = "✅ $value";
                } elseif ($var === 'APP_DEBUG' && $value === 'false') {
                    $class = 'pass';
                    $status = "✅ $value";
                } elseif ($var === 'APP_URL' && strpos($value, 'https://') === 0) {
                    $class = 'pass';
                    $status = "✅ $value";
                } elseif ($var === 'APP_KEY' && !empty($value) && $value !== 'NOT SET') {
                    $class = 'pass';
                    $status = "✅ Set";
                } elseif ($var === 'DB_CONNECTION' && !empty($value)) {
                    $class = 'pass';
                    $status = "✅ $value";
                }
                
                echo "<div class='test $class'>$var: $status<br><small>$description</small></div>";
            }
        } else {
            echo "<div class='test fail'>❌ .env file not found</div>";
        }
        ?>
    </div>
    
    <div class="section">
        <h2>🚀 Bootstrap Test</h2>
        <button onclick="testBootstrap()">Test Laravel Bootstrap</button>
        <div id="bootstrap-results"></div>
    </div>
    
    <div class="section">
        <h2>🔧 Auto-Fix Options</h2>
        <button onclick="fixPermissions()">Fix File Permissions</button>
        <button onclick="clearCaches()">Clear All Caches</button>
        <button onclick="runMigrations()">Run Migrations</button>
        <button onclick="generateKey()">Generate New APP_KEY</button>
        <div id="fix-results"></div>
    </div>
    
    <div class="section">
        <h2>📝 Manual Fixes for Forge</h2>
        <h3>1. SSH Commands to Run:</h3>
        <pre>
# SSH to your Forge server
ssh forge@your-server-ip

# Navigate to project directory
cd /home/forge/yourdomain.com

# Fix permissions
chmod -R 755 storage bootstrap/cache
chown -R forge:forge storage bootstrap/cache

# Clear and rebuild caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Test application
php artisan tinker --execute="echo 'Laravel Version: ' . app()->version()"
        </pre>
        
        <h3>2. Update .env for Production:</h3>
        <pre>
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
        </pre>
        
        <h3>3. Nginx Configuration Check:</h3>
        <pre>
# Check Nginx config
sudo nginx -t

# Restart services if needed
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm
        </pre>
    </div>

    <script>
        async function testBootstrap() {
            const results = document.getElementById('bootstrap-results');
            results.innerHTML = '<p>Testing Laravel bootstrap...</p>';
            
            try {
                const response = await fetch('bootstrap_test.php');
                const text = await response.text();
                results.innerHTML = `<pre>${text}</pre>`;
            } catch (error) {
                results.innerHTML = `<div class="test fail">❌ Bootstrap test failed: ${error.message}</div>`;
            }
        }
        
        async function fixPermissions() {
            showFixResult('Fixing file permissions...', 'info');
            // This would typically require server-side script
            showFixResult('⚠️ Please run SSH commands manually for permissions', 'warn');
        }
        
        async function clearCaches() {
            showFixResult('Clearing caches...', 'info');
            try {
                const response = await fetch('clear_cache.php');
                const result = await response.text();
                showFixResult(result, 'success');
            } catch (error) {
                showFixResult('❌ Failed to clear caches: ' + error.message, 'error');
            }
        }
        
        async function runMigrations() {
            showFixResult('Running migrations...', 'info');
            try {
                const response = await fetch('run_migrations.php');
                const result = await response.text();
                showFixResult(result, 'success');
            } catch (error) {
                showFixResult('❌ Failed to run migrations: ' + error.message, 'error');
            }
        }
        
        async function generateKey() {
            showFixResult('⚠️ Please run "php artisan key:generate" via SSH', 'warn');
        }
        
        function showFixResult(message, type) {
            const results = document.getElementById('fix-results');
            const className = type === 'error' ? 'fail' : type === 'success' ? 'pass' : 'warn';
            results.innerHTML += `<div class="test ${className}">${message}</div>`;
        }
    </script>
</body>
</html>
