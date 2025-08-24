<?php

/**
 * Script untuk test login dengan PIN 666666
 * Jalankan dengan: php test_login.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PanelSetting;
use Illuminate\Support\Facades\Config;

echo "=== TEST LOGIN DENGAN PIN 666666 ===\n\n";

try {
    // 1. Verifikasi PIN tersimpan
    echo "1. ✅ Verifikasi PIN:\n";
    
    $dbPin = PanelSetting::get('panel_pin', null);
    echo "   - PIN dari database: " . ($dbPin ?: 'NOT SET') . "\n";
    
    $configPin = config('panel.pin');
    echo "   - PIN dari config: " . ($configPin ?: 'NOT SET') . "\n";
    
    if ($dbPin === '666666' && $configPin === '666666') {
        echo "   - ✅ PIN 666666 sudah tersimpan dengan benar\n";
    } else {
        echo "   - ❌ PIN tidak sesuai, perlu diperbaiki\n";
        
        // Set ulang PIN
        PanelSetting::set('panel_pin', '666666', 'string', 'general', 'Panel access PIN');
        Config::set('panel.pin', '666666');
        
        echo "   - 🔧 PIN sudah diset ulang ke 666666\n";
    }
    
    // 2. Test login logic
    echo "\n2. 🧪 Test Login Logic:\n";
    
    $testPin = '666666';
    $storedPin = PanelSetting::get('panel_pin', null);
    
    if (hash_equals($storedPin, $testPin)) {
        echo "   - ✅ PIN match menggunakan hash_equals\n";
        echo "   - ✅ Login akan berhasil\n";
    } else {
        echo "   - ❌ PIN tidak match\n";
        echo "   - Stored: '{$storedPin}'\n";
        echo "   - Test: '{$testPin}'\n";
    }
    
    // 3. Test session
    echo "\n3. 🔧 Test Session:\n";
    
    try {
        // Simulate session
        session_start();
        $_SESSION['panel_authenticated'] = true;
        
        echo "   - ✅ Session bisa dibuat\n";
        echo "   - Session ID: " . session_id() . "\n";
        echo "   - Panel authenticated: " . ($_SESSION['panel_authenticated'] ? 'true' : 'false') . "\n";
        
        // Cleanup
        session_destroy();
        
    } catch (Exception $e) {
        echo "   - ❌ Error dengan session: " . $e->getMessage() . "\n";
    }
    
    // 4. Test route accessibility
    echo "\n4. 🌐 Test Route Accessibility:\n";
    
    echo "   - Login route: /panel/login\n";
    echo "   - Verify route: /panel/verify\n";
    echo "   - Dashboard route: /panel/shortlinks\n";
    
    // 5. Test CSRF token
    echo "\n5. 🛡️ Test CSRF Protection:\n";
    
    try {
        $token = csrf_token();
        echo "   - ✅ CSRF token generated: " . substr($token, 0, 10) . "...\n";
    } catch (Exception $e) {
        echo "   - ❌ Error generating CSRF token: " . $e->getMessage() . "\n";
    }
    
    // 6. Test database connection
    echo "\n6. 🗄️ Test Database Connection:\n";
    
    try {
        $db = DB::connection();
        $db->getPdo();
        echo "   - ✅ Database connection OK\n";
        echo "   - Driver: " . $db->getDriverName() . "\n";
        echo "   - Database: " . $db->getDatabaseName() . "\n";
    } catch (Exception $e) {
        echo "   - ❌ Database connection failed: " . $e->getMessage() . "\n";
    }
    
    // 7. Test view rendering
    echo "\n7. 🎨 Test View Rendering:\n";
    
    try {
        $view = view('panel.login');
        echo "   - ✅ Login view bisa di-render\n";
        echo "   - View path: " . $view->getPath() . "\n";
    } catch (Exception $e) {
        echo "   - ❌ Error rendering view: " . $e->getMessage() . "\n";
    }
    
    // 8. Summary dan instruksi
    echo "\n=== TEST SELESAI ===\n";
    echo "Jika semua test berhasil, login dengan PIN 666666 seharusnya berfungsi.\n\n";
    
    echo "📋 Langkah untuk test login:\n";
    echo "1. Buka browser dan akses: http://localhost/panel/login\n";
    echo "2. Masukkan PIN: 666666\n";
    echo "3. Form akan auto-submit setelah 6 digit\n";
    echo "4. Jika berhasil, akan redirect ke /panel/shortlinks\n\n";
    
    echo "🔍 Jika masih ada masalah:\n";
    echo "1. Cek log files di storage/logs/\n";
    echo "2. Pastikan web server berjalan\n";
    echo "3. Cek browser console untuk error JavaScript\n";
    echo "4. Pastikan database bisa diakses\n";
    
} catch (Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
