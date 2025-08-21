<?php
/**
 * Cache Clear Script for Laravel Forge
 * Clears all Laravel caches and rebuilds them for production
 */

try {
    // Load Laravel
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "🧹 Clearing Laravel Caches...\n\n";
    
    $commands = [
        'optimize:clear' => 'Clear all cached files',
        'config:clear' => 'Clear configuration cache',
        'route:clear' => 'Clear route cache',
        'view:clear' => 'Clear compiled views',
        'cache:clear' => 'Clear application cache'
    ];
    
    foreach ($commands as $command => $description) {
        try {
            echo "Running: $description...\n";
            \Artisan::call($command);
            echo "✅ $description completed\n\n";
        } catch (Exception $e) {
            echo "❌ $description failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo "🏗️ Rebuilding Production Caches...\n\n";
    
    $buildCommands = [
        'config:cache' => 'Cache configuration files',
        'route:cache' => 'Cache routes for faster lookup',
        'view:cache' => 'Compile and cache views'
    ];
    
    foreach ($buildCommands as $command => $description) {
        try {
            echo "Running: $description...\n";
            \Artisan::call($command);
            echo "✅ $description completed\n\n";
        } catch (Exception $e) {
            echo "❌ $description failed: " . $e->getMessage() . "\n\n";
        }
    }
    
    echo "✅ Cache operations completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Cache clear failed: " . $e->getMessage() . "\n";
    echo "Please run these commands manually via SSH:\n";
    echo "php artisan optimize:clear\n";
    echo "php artisan config:cache\n";
    echo "php artisan route:cache\n";
    echo "php artisan view:cache\n";
}
