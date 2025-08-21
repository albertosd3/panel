<?php

require_once __DIR__ . '/vendor/autoload.php';

try {
    echo "=== Laravel Application Health Check ===\n";
    
    // Bootstrap Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    echo "✅ Laravel application bootstrapped successfully\n";
    
    // Test database connection
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connection established\n";
    
    // Test shortlinks table
    $shortlinkCount = DB::table('shortlinks')->count();
    echo "✅ Shortlinks table accessible, found {$shortlinkCount} records\n";
    
    // Test panel settings table
    $settingsCount = DB::table('panel_settings')->count();
    echo "✅ Panel settings table accessible, found {$settingsCount} records\n";
    
    // Test creating a shortlink directly
    try {
        $link = new App\Models\Shortlink([
            'slug' => 'health-check-' . time(),
            'destination' => 'https://www.google.com',
            'clicks' => 0,
            'active' => true,
            'is_rotator' => false,
            'rotation_type' => 'random',
            'destinations' => null,
            'current_index' => 0
        ]);
        
        $link->save();
        echo "✅ Shortlink model save test successful (ID: {$link->id})\n";
        
        // Clean up test data
        $link->delete();
        echo "✅ Test cleanup completed\n";
        
    } catch (Exception $e) {
        echo "❌ Shortlink model test failed: " . $e->getMessage() . "\n";
    }
    
    // Test Stopbot settings
    try {
        $stopbotEnabled = App\Models\PanelSetting::get('stopbot_enabled', false);
        $stopbotApiKey = App\Models\PanelSetting::get('stopbot_api_key', '');
        echo "✅ Stopbot settings accessible (enabled: " . ($stopbotEnabled ? 'true' : 'false') . ", has API key: " . (!empty($stopbotApiKey) ? 'yes' : 'no') . ")\n";
    } catch (Exception $e) {
        echo "❌ Stopbot settings test failed: " . $e->getMessage() . "\n";
    }
    
    // Test routes
    try {
        $router = app('router');
        $routes = $router->getRoutes();
        $apiCreateRoute = null;
        
        foreach ($routes as $route) {
            if ($route->uri() === 'api/create' && in_array('POST', $route->methods())) {
                $apiCreateRoute = $route;
                break;
            }
        }
        
        if ($apiCreateRoute) {
            echo "✅ API create route found: " . implode('|', $apiCreateRoute->methods()) . " " . $apiCreateRoute->uri() . "\n";
        } else {
            echo "❌ API create route not found\n";
        }
    } catch (Exception $e) {
        echo "❌ Route test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Health Check Complete ===\n";
    echo "System appears to be healthy. You can now test the panel at http://localhost:8000/panel\n";
    
} catch (Exception $e) {
    echo "❌ Application bootstrap failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
