<?php

/**
 * Script lengkap untuk test login dengan PIN 666666
 * Jalankan dengan: php test_login_complete.php
 */

echo "=== TEST LOGIN LENGKAP DENGAN PIN 666666 ===\n\n";

// 1. Verifikasi konfigurasi
echo "1. 🔧 Verifikasi Konfigurasi...\n";

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\PanelSetting;

try {
    $dbPin = PanelSetting::get('panel_pin', null);
    $configPin = config('panel.pin');
    
    if ($dbPin === '666666' && $configPin === '666666') {
        echo "   - ✅ PIN 666666 sudah tersimpan dengan benar\n";
    } else {
        echo "   - 🔧 Setting PIN 666666...\n";
        PanelSetting::set('panel_pin', '666666', 'string', 'general', 'Panel access PIN');
        config(['panel.pin' => '666666']);
        echo "   - ✅ PIN 666666 berhasil diset\n";
    }
} catch (Exception $e) {
    echo "   - ❌ Error setting PIN: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Start web server
echo "\n2. 🌐 Memulai Web Server...\n";

$serverPort = 8000;
$serverUrl = "http://localhost:{$serverPort}";

echo "   - Server akan berjalan di: {$serverUrl}\n";
echo "   - Login URL: {$serverUrl}/panel/login\n";
echo "   - PIN: 666666\n\n";

echo "   - Memulai server di background...\n";

// Start server in background (Windows PowerShell)
$serverCommand = "Start-Process -NoNewWindow php -ArgumentList '-S', 'localhost:{$serverPort}', '-t', 'public'";
exec("powershell -Command \"{$serverCommand}\"", $output, $returnCode);

if ($returnCode === 0) {
    echo "   - ✅ Server berhasil dimulai\n";
} else {
    echo "   - ⚠️ Server mungkin sudah berjalan atau ada error\n";
}

// Wait a bit for server to start
echo "   - Menunggu server siap...\n";
sleep(3);

// 3. Test server availability
echo "\n3. 🧪 Testing Server Availability...\n";

$maxRetries = 5;
$retryCount = 0;
$serverReady = false;

while ($retryCount < $maxRetries && !$serverReady) {
    $retryCount++;
    echo "   - Attempt {$retryCount}/{$maxRetries}: Testing server...\n";
    
    $ch = curl_init($serverUrl . '/health-check');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   - ✅ Server siap! (HTTP {$httpCode})\n";
        $serverReady = true;
    } else {
        echo "   - ⏳ Server belum siap (HTTP {$httpCode}), menunggu...\n";
        sleep(2);
    }
}

if (!$serverReady) {
    echo "   - ❌ Server tidak bisa diakses setelah {$maxRetries} attempts\n";
    echo "   - Coba jalankan manual: php -S localhost:{$serverPort} -t public\n";
    exit(1);
}

// 4. Test login functionality
echo "\n4. 🔐 Testing Login Functionality...\n";

try {
    // Get login page
    $ch = curl_init($serverUrl . '/panel/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
    $loginHtml = curl_exec($ch);
    $loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($loginHttpCode === 200) {
        echo "   - ✅ Login page berhasil diakses\n";
        
        // Extract CSRF token
        if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $loginHtml, $matches)) {
            $csrfToken = $matches[1];
            echo "   - ✅ CSRF token: " . substr($csrfToken, 0, 10) . "...\n";
        } else {
            echo "   - ⚠️ CSRF token tidak ditemukan\n";
            $csrfToken = '';
        }
        
        // Test login
        $loginData = [
            'pin' => '666666',
            '_token' => $csrfToken
        ];
        
        $ch = curl_init($serverUrl . '/panel/verify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
        curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
        curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        
        $loginResponse = curl_exec($ch);
        $loginResponseHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        
        echo "   - Login response: HTTP {$loginResponseHttpCode}\n";
        echo "   - Final URL: {$finalUrl}\n";
        
        if (strpos($finalUrl, '/panel/shortlinks') !== false) {
            echo "   - ✅ Login berhasil! Redirect ke dashboard\n";
            
            // Test dashboard access
            $ch = curl_init($serverUrl . '/panel/shortlinks');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
            curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
            $dashboardResponse = curl_exec($ch);
            $dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($dashboardHttpCode === 200) {
                echo "   - ✅ Dashboard berhasil diakses\n";
                echo "   - 🎉 LOGIN BERHASIL! Sistem berfungsi dengan baik\n";
            } else {
                echo "   - ⚠️ Dashboard tidak bisa diakses (HTTP {$dashboardHttpCode})\n";
            }
        } else {
            echo "   - ❌ Login gagal atau redirect tidak sesuai\n";
            echo "   - Response preview: " . substr($loginResponse, 0, 200) . "...\n";
        }
        
    } else {
        echo "   - ❌ Login page tidak bisa diakses (HTTP {$loginHttpCode})\n";
    }
    
} catch (Exception $e) {
    echo "   - ❌ Error testing login: " . $e->getMessage() . "\n";
}

// 5. Cleanup
echo "\n5. 🧹 Cleanup...\n";

if (file_exists('test_cookies.txt')) {
    unlink('test_cookies.txt');
    echo "   - ✅ Test cookies dibersihkan\n";
}

// 6. Summary
echo "\n=== TEST SELESAI ===\n";
echo "Jika semua test berhasil, login dengan PIN 666666 sudah berfungsi!\n\n";

echo "📋 Cara menggunakan:\n";
echo "1. Buka browser dan akses: {$serverUrl}/panel/login\n";
echo "2. Masukkan PIN: 666666\n";
echo "3. Form akan auto-submit setelah 6 digit\n";
echo "4. Jika berhasil, akan redirect ke dashboard\n\n";

echo "🔍 Jika masih ada masalah:\n";
echo "1. Pastikan web server berjalan: php -S localhost:{$serverPort} -t public\n";
echo "2. Cek log files di storage/logs/\n";
echo "3. Pastikan database bisa diakses\n";
echo "4. Cek browser console untuk error JavaScript\n";

echo "\n🚀 Server sedang berjalan di: {$serverUrl}\n";
echo "Tekan Ctrl+C untuk stop script ini (server akan tetap berjalan)\n";
