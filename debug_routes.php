<?php

/**
 * Script untuk debug routes dan mengidentifikasi masalah
 * Jalankan dengan: php debug_routes.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Route;

echo "=== DEBUG ROUTES ===\n\n";

try {
    // 1. Test basic Laravel functionality
    echo "1. ✅ Test Laravel Basic:\n";
    echo "   - App name: " . config('app.name') . "\n";
    echo "   - Environment: " . config('app.env') . "\n";
    echo "   - Debug mode: " . (config('app.debug') ? 'ON' : 'OFF') . "\n";
    
    // 2. Test route list
    echo "\n2. 🌐 Test Route List:\n";
    
    try {
        $routes = Route::getRoutes();
        echo "   - Total routes: " . count($routes) . "\n";
        
        // Show panel routes
        echo "   - Panel routes:\n";
        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = $route->methods();
            $name = $route->getName();
            
            if (strpos($uri, 'panel') !== false) {
                echo "     * " . implode('|', $methods) . " {$uri} -> {$name}\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   - ❌ Error getting routes: " . $e->getMessage() . "\n";
    }
    
    // 3. Test specific routes
    echo "\n3. 🧪 Test Specific Routes:\n";
    
    $testRoutes = [
        '/panel/login' => 'GET',
        '/panel/verify' => 'POST',
        '/panel/shortlinks' => 'GET',
        '/health-check' => 'GET'
    ];
    
    foreach ($testRoutes as $uri => $method) {
        try {
            $route = Route::getRoutes()->match(
                \Illuminate\Http\Request::create($uri, $method)
            );
            
            if ($route) {
                $controller = $route->getController();
                $action = $route->getActionMethod();
                echo "   - ✅ {$method} {$uri} -> " . get_class($controller) . "@{$action}\n";
            } else {
                echo "   - ❌ {$method} {$uri} -> Route not found\n";
            }
            
        } catch (Exception $e) {
            echo "   - ❌ {$method} {$uri} -> Error: " . $e->getMessage() . "\n";
        }
    }
    
    // 4. Test route caching
    echo "\n4. 🔧 Test Route Caching:\n";
    
    try {
        $routeCachePath = storage_path('framework/cache/routes.php');
        if (file_exists($routeCachePath)) {
            echo "   - ✅ Route cache exists\n";
            echo "   - Cache size: " . filesize($routeCachePath) . " bytes\n";
            echo "   - Cache modified: " . date('Y-m-d H:i:s', filemtime($routeCachePath)) . "\n";
        } else {
            echo "   - ℹ️ Route cache not found\n";
        }
        
        // Clear route cache
        echo "   - Clearing route cache...\n";
        \Artisan::call('route:clear');
        echo "   - ✅ Route cache cleared\n";
        
    } catch (Exception $e) {
        echo "   - ❌ Error with route cache: " . $e->getMessage() . "\n";
    }
    
    // 5. Test route registration
    echo "\n5. 🔍 Test Route Registration:\n";
    
    try {
        // Test if routes are properly loaded
        $webRoutesPath = base_path('routes/web.php');
        if (file_exists($webRoutesPath)) {
            echo "   - ✅ Web routes file exists\n";
            echo "   - File size: " . filesize($webRoutesPath) . " bytes\n";
            echo "   - File modified: " . date('Y-m-d H:i:s', filemtime($webRoutesPath)) . "\n";
        } else {
            echo "   - ❌ Web routes file not found\n";
        }
        
        // Test route loading
        echo "   - Testing route loading...\n";
        \Artisan::call('route:list', ['--path' => 'panel']);
        echo "   - ✅ Route list command executed\n";
        
    } catch (Exception $e) {
        echo "   - ❌ Error testing route registration: " . $e->getMessage() . "\n";
    }
    
    // 6. Test middleware
    echo "\n6. 🛡️ Test Middleware:\n";
    
    try {
        $middleware = app('router')->getMiddleware();
        echo "   - Total middleware: " . count($middleware) . "\n";
        
        // Check panel.auth middleware
        if (isset($middleware['panel.auth'])) {
            echo "   - ✅ panel.auth middleware: " . $middleware['panel.auth'] . "\n";
        } else {
            echo "   - ❌ panel.auth middleware not found\n";
        }
        
    } catch (Exception $e) {
        echo "   - ❌ Error testing middleware: " . $e->getMessage() . "\n";
    }
    
    // 7. Test database connection
    echo "\n7. 🗄️ Test Database:\n";
    
    try {
        $db = DB::connection();
        $db->getPdo();
        echo "   - ✅ Database connection OK\n";
        
        // Test panel settings
        $pin = DB::table('panel_settings')->where('key', 'panel_pin')->first();
        if ($pin) {
            echo "   - ✅ Panel PIN found: " . $pin->value . "\n";
        } else {
            echo "   - ❌ Panel PIN not found in database\n";
        }
        
    } catch (Exception $e) {
        echo "   - ❌ Database error: " . $e->getMessage() . "\n";
    }
    
    // 8. Summary
    echo "\n=== DEBUG SELESAI ===\n";
    echo "Jika ada masalah dengan routes, coba:\n";
    echo "1. Clear route cache: php artisan route:clear\n";
    echo "2. Clear config cache: php artisan config:clear\n";
    echo "3. Restart web server\n";
    echo "4. Check log files di storage/logs/\n";
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
