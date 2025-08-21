<?php
/**
 * Migration Runner for Laravel Forge
 * Runs pending migrations safely
 */

try {
    // Load Laravel
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "🗄️ Running Laravel Migrations...\n\n";
    
    // Check migration status first
    echo "Checking migration status...\n";
    \Artisan::call('migrate:status');
    echo \Artisan::output() . "\n";
    
    // Run migrations
    echo "Running pending migrations...\n";
    \Artisan::call('migrate', ['--force' => true]);
    echo \Artisan::output() . "\n";
    
    // Verify tables exist
    echo "Verifying database tables...\n";
    $tables = ['shortlinks', 'panel_settings', 'shortlink_events', 'shortlink_visitors'];
    
    foreach ($tables as $table) {
        try {
            $exists = DB::getSchemaBuilder()->hasTable($table);
            echo ($exists ? "✅" : "❌") . " Table '$table': " . ($exists ? "exists" : "missing") . "\n";
        } catch (Exception $e) {
            echo "❌ Error checking table '$table': " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Migration operations completed!\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    echo "\nPlease run manually via SSH:\n";
    echo "php artisan migrate --force\n";
    echo "php artisan migrate:status\n";
}
