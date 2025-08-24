<?php

/**
 * Script untuk memeriksa dan memperbaiki konfigurasi login
 * Jalankan dengan: php check_login_config.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PanelSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== PERIKSA KONFIGURASI LOGIN ===\n\n";

try {
    // 1. Cek konfigurasi panel
    echo "1. ✅ Konfigurasi Panel:\n";
    
    $panelPin = config('panel.pin');
    echo "   - Panel PIN dari config: " . ($panelPin ?: 'NOT SET') . "\n";
    
    // Cek di database
    $dbPin = PanelSetting::get('panel_pin', null);
    echo "   - Panel PIN dari database: " . ($dbPin ?: 'NOT SET') . "\n";
    
    // 2. Cek tabel users
    echo "\n2. ✅ Tabel Users:\n";
    
    try {
        $userCount = DB::table('users')->count();
        echo "   - Total users: {$userCount}\n";
        
        if ($userCount > 0) {
            $users = DB::table('users')->select('id', 'name', 'email', 'created_at')->limit(5)->get();
            echo "   - Sample users:\n";
            foreach ($users as $user) {
                echo "     * ID: {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
            }
        }
    } catch (Exception $e) {
        echo "   - ❌ Error accessing users table: " . $e->getMessage() . "\n";
    }
    
    // 3. Cek panel settings
    echo "\n3. ✅ Panel Settings:\n";
    
    try {
        $settings = DB::table('panel_settings')->get();
        echo "   - Total settings: " . $settings->count() . "\n";
        
        foreach ($settings as $setting) {
            echo "     * {$setting->key}: {$setting->value} ({$setting->type})\n";
        }
    } catch (Exception $e) {
        echo "   - ❌ Error accessing panel_settings: " . $e->getMessage() . "\n";
    }
    
    // 4. Set PIN 666666
    echo "\n4. 🔧 Setting PIN 666666:\n";
    
    try {
        // Set PIN di database
        PanelSetting::set('panel_pin', '666666', 'string', 'general', 'Panel access PIN');
        
        // Set PIN di config
        config(['panel.pin' => '666666']);
        
        echo "   - ✅ PIN 666666 berhasil diset di database\n";
        echo "   - ✅ PIN 666666 berhasil diset di config\n";
        
        // Verifikasi
        $newPin = PanelSetting::get('panel_pin', null);
        echo "   - Verifikasi PIN: " . ($newPin ?: 'NOT SET') . "\n";
        
    } catch (Exception $e) {
        echo "   - ❌ Error setting PIN: " . $e->getMessage() . "\n";
    }
    
    // 5. Test login dengan PIN 666666
    echo "\n5. 🧪 Test Login dengan PIN 666666:\n";
    
    try {
        $testPin = '666666';
        $storedPin = PanelSetting::get('panel_pin', null);
        
        if ($storedPin === $testPin) {
            echo "   - ✅ PIN match: {$testPin} == {$storedPin}\n";
            echo "   - ✅ Login seharusnya berhasil dengan PIN 666666\n";
        } else {
            echo "   - ❌ PIN mismatch: {$testPin} != {$storedPin}\n";
        }
        
    } catch (Exception $e) {
        echo "   - ❌ Error testing PIN: " . $e->getMessage() . "\n";
    }
    
    // 6. Cek route login
    echo "\n6. 🌐 Route Login:\n";
    
    try {
        $routes = DB::table('routes')->get();
        echo "   - Total routes: " . $routes->count() . "\n";
    } catch (Exception $e) {
        echo "   - Routes table not found (normal for Laravel)\n";
    }
    
    echo "   - Login route: /panel/login\n";
    echo "   - Verify route: /panel/verify\n";
    
    // 7. Cek session configuration
    echo "\n7. 🔧 Session Configuration:\n";
    
    echo "   - Session driver: " . config('session.driver') . "\n";
    echo "   - Session lifetime: " . config('session.lifetime') . " minutes\n";
    echo "   - Session domain: " . config('session.domain') . "\n";
    
    // 8. Clear cache
    echo "\n8. 🧹 Clear Cache:\n";
    
    try {
        // Clear config cache
        $configPath = storage_path('framework/cache/config.php');
        if (file_exists($configPath)) {
            unlink($configPath);
            echo "   - ✅ Config cache cleared\n";
        } else {
            echo "   - ℹ️ Config cache already cleared\n";
        }
        
        // Clear application cache
        $appPath = storage_path('framework/cache/app.php');
        if (file_exists($appPath)) {
            unlink($appPath);
            echo "   - ✅ Application cache cleared\n";
        } else {
            echo "   - ℹ️ Application cache already cleared\n";
        }
        
    } catch (Exception $e) {
        echo "   - ❌ Error clearing cache: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== KONFIGURASI LOGIN SELESAI ===\n";
    echo "Sekarang coba login dengan PIN: 666666\n";
    echo "URL: http://localhost/panel/login\n";
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
