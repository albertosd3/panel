<?php
/**
 * Laravel Bootstrap Test Script
 * Tests if Laravel can bootstrap properly
 */

echo "=== Laravel Bootstrap Test ===\n\n";

try {
    echo "1. Testing Composer Autoloader...\n";
    if (file_exists('vendor/autoload.php')) {
        require_once 'vendor/autoload.php';
        echo "   ✅ Composer autoloader loaded successfully\n\n";
    } else {
        throw new Exception("Composer autoloader not found at vendor/autoload.php");
    }
    
    echo "2. Testing Laravel Bootstrap File...\n";
    if (file_exists('bootstrap/app.php')) {
        echo "   ✅ Bootstrap file exists\n";
        
        // Try to load the bootstrap file
        $app = require_once 'bootstrap/app.php';
        echo "   ✅ Bootstrap file loaded successfully\n";
        echo "   ✅ Application instance created: " . get_class($app) . "\n\n";
        
    } else {
        throw new Exception("Laravel bootstrap file not found at bootstrap/app.php");
    }
    
    echo "3. Testing Application Bootstrap...\n";
    try {
        // Try to bootstrap the kernel
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        echo "   ✅ Laravel kernel bootstrapped successfully\n";
        echo "   ✅ Laravel version: " . $app->version() . "\n\n";
        
    } catch (Exception $e) {
        echo "   ❌ Kernel bootstrap failed: " . $e->getMessage() . "\n\n";
        throw $e;
    }
    
    echo "4. Testing Database Connection...\n";
    try {
        $db = $app->make('db');
        $pdo = $db->connection()->getPdo();
        echo "   ✅ Database connection established\n";
        echo "   ✅ Database driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n\n";
        
    } catch (Exception $e) {
        echo "   ❌ Database connection failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "5. Testing Configuration...\n";
    try {
        $config = $app->make('config');
        echo "   ✅ App Name: " . $config->get('app.name') . "\n";
        echo "   ✅ App Environment: " . $config->get('app.env') . "\n";
        echo "   ✅ App Debug: " . ($config->get('app.debug') ? 'true' : 'false') . "\n";
        echo "   ✅ App URL: " . $config->get('app.url') . "\n\n";
        
    } catch (Exception $e) {
        echo "   ❌ Configuration test failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "6. Testing Routes...\n";
    try {
        $router = $app->make('router');
        $routes = $router->getRoutes();
        $routeCount = count($routes);
        echo "   ✅ Routes loaded: $routeCount routes found\n";
        
        // Look for specific API routes
        $apiRoutes = [];
        foreach ($routes as $route) {
            if (strpos($route->uri(), 'api/') === 0) {
                $apiRoutes[] = $route->uri();
            }
        }
        
        if (!empty($apiRoutes)) {
            echo "   ✅ API routes found: " . implode(', ', array_slice($apiRoutes, 0, 5)) . "\n";
        } else {
            echo "   ⚠️ No API routes found\n";
        }
        echo "\n";
        
    } catch (Exception $e) {
        echo "   ❌ Routes test failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "7. Testing Models...\n";
    try {
        // Test if we can load and use models
        $shortlinkCount = \App\Models\Shortlink::count();
        echo "   ✅ Shortlink model accessible, found $shortlinkCount records\n";
        
        $settingsCount = \App\Models\PanelSetting::count();
        echo "   ✅ PanelSetting model accessible, found $settingsCount records\n\n";
        
    } catch (Exception $e) {
        echo "   ❌ Models test failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "=== BOOTSTRAP TEST COMPLETE ===\n";
    echo "✅ Laravel application can bootstrap successfully!\n";
    echo "If you're still seeing errors, the issue may be:\n";
    echo "1. Web server configuration (Nginx/Apache)\n";
    echo "2. PHP-FPM configuration\n";
    echo "3. File permissions\n";
    echo "4. Environment-specific issues\n\n";
    
} catch (Exception $e) {
    echo "\n=== BOOTSTRAP TEST FAILED ===\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "❌ File: " . $e->getFile() . "\n";
    echo "❌ Line: " . $e->getLine() . "\n\n";
    
    echo "Stack Trace:\n";
    echo $e->getTraceAsString() . "\n\n";
    
    echo "Common Solutions:\n";
    echo "1. Run 'composer install --optimize-autoloader --no-dev'\n";
    echo "2. Run 'php artisan key:generate'\n";
    echo "3. Check .env file configuration\n";
    echo "4. Verify file permissions: chmod -R 755 storage bootstrap/cache\n";
    echo "5. Clear caches: php artisan optimize:clear\n";
}
