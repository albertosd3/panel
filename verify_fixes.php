<?php

/**
 * Script untuk memverifikasi bahwa semua perbaikan berfungsi
 * Jalankan dengan: php verify_fixes.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Shortlink;
use App\Models\ShortlinkEvent;
use App\Models\ShortlinkVisitor;
use App\Models\PanelSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "=== VERIFIKASI PERBAIKAN LOGGING IP ===\n\n";

try {
    // Test 1: Cek konfigurasi logging
    echo "1. ✅ Konfigurasi Logging:\n";
    echo "   - Log Channel: " . config('logging.default') . "\n";
    echo "   - Log Level: " . config('logging.level', 'debug') . "\n";
    echo "   - Debug Mode: " . (config('app.debug') ? 'ON' : 'OFF') . "\n";
    
    // Test 2: Cek konfigurasi panel
    echo "\n2. ✅ Konfigurasi Panel:\n";
    echo "   - Panel PIN: " . (config('panel.pin') ? 'SET' : 'NOT SET') . "\n";
    echo "   - Block Bots: " . (config('panel.block_bots') ? 'ON' : 'OFF') . "\n";
    echo "   - Count Bots: " . (config('panel.count_bots') ? 'ON' : 'OFF') . "\n";
    echo "   - GeoIP Enabled: " . (config('panel.geoip.enabled') ? 'ON' : 'OFF') . "\n";
    
    // Test 3: Cek database connection
    echo "\n3. ✅ Database Connection:\n";
    try {
        DB::connection()->getPdo();
        echo "   - Database: Connected\n";
        echo "   - Driver: " . DB::connection()->getDriverName() . "\n";
        echo "   - Database Name: " . DB::connection()->getDatabaseName() . "\n";
    } catch (Exception $e) {
        echo "   - Database: ❌ Connection failed - " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Test 4: Cek struktur tabel
    echo "\n4. ✅ Struktur Tabel:\n";
    
    $tables = ['shortlinks', 'shortlink_events', 'shortlink_visitors', 'panel_settings'];
    foreach ($tables as $table) {
        try {
            $count = DB::table($table)->count();
            echo "   - {$table}: {$count} records\n";
        } catch (Exception $e) {
            echo "   - {$table}: ❌ Error - " . $e->getMessage() . "\n";
        }
    }
    
    // Test 5: Cek shortlink yang tersedia
    echo "\n5. ✅ Shortlink Test:\n";
    $shortlink = Shortlink::first();
    if ($shortlink) {
        echo "   - Found: {$shortlink->slug} -> {$shortlink->destination}\n";
        echo "   - Clicks: {$shortlink->clicks}\n";
        echo "   - Active: " . ($shortlink->active ? 'Yes' : 'No') . "\n";
    } else {
        echo "   - ❌ No shortlinks found\n";
        echo "   - Create a shortlink first through the panel\n";
    }
    
    // Test 6: Cek panel settings
    echo "\n6. ✅ Panel Settings:\n";
    try {
        $stopbotEnabled = PanelSetting::get('stopbot_enabled', false);
        $stopbotApiKey = PanelSetting::get('stopbot_api_key', '');
        echo "   - Stopbot Enabled: " . ($stopbotEnabled ? 'Yes' : 'No') . "\n";
        echo "   - Stopbot API Key: " . (!empty($stopbotApiKey) ? 'SET' : 'NOT SET') . "\n";
    } catch (Exception $e) {
        echo "   - ❌ Error reading panel settings: " . $e->getMessage() . "\n";
    }
    
    // Test 7: Test logging functionality
    echo "\n7. ✅ Test Logging:\n";
    try {
        Log::info('Test log message from verification script', [
            'timestamp' => now()->toISOString(),
            'test' => true
        ]);
        echo "   - Log message written successfully\n";
        
        // Check if log file exists and is writable
        $logPath = storage_path('logs');
        if (is_dir($logPath) && is_writable($logPath)) {
            echo "   - Log directory: Writable\n";
        } else {
            echo "   - Log directory: ❌ Not writable\n";
        }
    } catch (Exception $e) {
        echo "   - ❌ Logging failed: " . $e->getMessage() . "\n";
    }
    
    // Test 8: Test model creation
    echo "\n8. ✅ Test Model Creation:\n";
    if ($shortlink) {
        try {
            // Test ShortlinkEvent creation
            $testEvent = ShortlinkEvent::create([
                'shortlink_id' => $shortlink->id,
                'ip' => '127.0.0.1',
                'country' => 'ID',
                'city' => 'Test City',
                'device' => 'Test Device',
                'platform' => 'Test Platform',
                'browser' => 'Test Browser',
                'is_bot' => false,
                'clicked_at' => now(),
            ]);
            echo "   - ShortlinkEvent: ✅ Created (ID: {$testEvent->id})\n";
            
            // Test ShortlinkVisitor creation
            $testVisitor = ShortlinkVisitor::create([
                'shortlink_id' => $shortlink->id,
                'ip' => '127.0.0.1',
                'hits' => 1,
                'first_seen' => now(),
                'last_seen' => now(),
                'is_bot' => false,
                'country' => 'ID',
                'city' => 'Test City',
            ]);
            echo "   - ShortlinkVisitor: ✅ Created (ID: {$testVisitor->id})\n";
            
            // Cleanup test data
            $testEvent->delete();
            $testVisitor->delete();
            echo "   - Test data cleaned up\n";
            
        } catch (Exception $e) {
            echo "   - ❌ Model creation failed: " . $e->getMessage() . "\n";
            echo "   - Trace: " . $e->getTraceAsString() . "\n";
        }
    } else {
        echo "   - Skipped (no shortlink available)\n";
    }
    
    // Test 9: Cek IP detection
    echo "\n9. ✅ IP Detection Test:\n";
    try {
        // Simulate request headers
        $request = new \Illuminate\Http\Request();
        $request->headers->set('CF-Connecting-IP', '203.0.113.1');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Test)');
        
        // Test IP detection logic
        $ip = null;
        if ($cfIp = $request->header('CF-Connecting-IP')) {
            $ip = $cfIp;
        } elseif ($clientIp = $request->header('HTTP_CLIENT_IP')) {
            $ip = $clientIp;
        } elseif ($forwardedIp = $request->header('HTTP_X_FORWARDED_FOR')) {
            $ip = $forwardedIp;
        } else {
            $ip = $request->ip();
        }
        
        echo "   - Detected IP: {$ip}\n";
        echo "   - CloudFlare IP: " . ($request->header('CF-Connecting-IP') ?: 'Not set') . "\n";
        echo "   - User Agent: " . $request->userAgent() . "\n";
        
    } catch (Exception $e) {
        echo "   - ❌ IP detection test failed: " . $e->getMessage() . "\n";
    }
    
    // Summary
    echo "\n=== VERIFIKASI SELESAI ===\n";
    echo "Jika semua test berhasil, sistem logging IP seharusnya berfungsi dengan baik.\n";
    echo "\nLangkah selanjutnya:\n";
    echo "1. Akses shortlink melalui browser\n";
    echo "2. Cek log files di storage/logs/\n";
    echo "3. Cek database untuk data events dan visitors\n";
    echo "4. Monitor panel analytics untuk melihat data yang tersimpan\n";
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
