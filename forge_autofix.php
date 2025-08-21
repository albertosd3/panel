<?php
/**
 * Laravel Forge Auto-Fix Script
 * This script attempts to automatically fix common deployment issues
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

try {
    $fixes = [];
    $errors = [];
    
    // Fix 1: Clear all caches
    try {
        \Artisan::call('optimize:clear');
        $fixes[] = "✅ Cleared all caches (config, route, view, compiled)";
    } catch (Exception $e) {
        $errors[] = "❌ Failed to clear caches: " . $e->getMessage();
    }
    
    // Fix 2: Recreate caches for production
    try {
        \Artisan::call('config:cache');
        \Artisan::call('route:cache');
        \Artisan::call('view:cache');
        $fixes[] = "✅ Recreated production caches";
    } catch (Exception $e) {
        $errors[] = "❌ Failed to recreate caches: " . $e->getMessage();
    }
    
    // Fix 3: Run pending migrations
    try {
        \Artisan::call('migrate', ['--force' => true]);
        $fixes[] = "✅ Ran database migrations";
    } catch (Exception $e) {
        $errors[] = "❌ Failed to run migrations: " . $e->getMessage();
    }
    
    // Fix 4: Ensure panel settings exist
    try {
        $settings = [
            'stopbot_enabled' => false,
            'stopbot_api_key' => '',
            'stopbot_redirect_url' => 'https://www.google.com',
            'stopbot_log_enabled' => true,
            'stopbot_timeout' => 5,
            'count_bots' => false
        ];
        
        foreach ($settings as $key => $value) {
            \App\Models\PanelSetting::firstOrCreate(
                ['key' => $key],
                ['value' => is_bool($value) ? ($value ? '1' : '0') : $value]
            );
        }
        $fixes[] = "✅ Ensured panel settings exist";
    } catch (Exception $e) {
        $errors[] = "❌ Failed to create panel settings: " . $e->getMessage();
    }
    
    // Fix 5: Test database connectivity
    try {
        $count = \App\Models\Shortlink::count();
        $fixes[] = "✅ Database connection working ($count shortlinks found)";
    } catch (Exception $e) {
        $errors[] = "❌ Database connection failed: " . $e->getMessage();
    }
    
    // Fix 6: Verify file permissions
    $paths = ['storage', 'bootstrap/cache'];
    foreach ($paths as $path) {
        if (is_writable($path)) {
            $fixes[] = "✅ $path is writable";
        } else {
            $errors[] = "❌ $path is not writable - run: chmod -R 755 $path";
        }
    }
    
    // Fix 7: Check for required environment variables
    $requiredEnv = ['APP_KEY', 'APP_URL'];
    foreach ($requiredEnv as $var) {
        if (env($var)) {
            $fixes[] = "✅ $var is set";
        } else {
            $errors[] = "❌ $var is missing from .env file";
        }
    }
    
    echo json_encode([
        'ok' => count($errors) === 0,
        'fixes' => $fixes,
        'errors' => $errors,
        'timestamp' => now()->toISOString()
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'ok' => false,
        'error' => 'Auto-fix script failed: ' . $e->getMessage(),
        'timestamp' => now()->toISOString()
    ]);
}
