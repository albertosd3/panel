<?php

/**
 * Script untuk test login dengan PIN 666666 menggunakan route yang benar
 * Jalankan dengan: php test_login_fixed.php
 * Pastikan web server sudah berjalan di http://localhost:8000
 */

echo "=== TEST LOGIN DENGAN ROUTE YANG DIPERBAIKI ===\n\n";

$baseUrl = 'http://localhost:8000';

echo "1. 🌐 Testing server availability...\n";

$ch = curl_init($baseUrl . '/health-check');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo "   - ✅ Server berjalan di {$baseUrl}\n";
} else {
    echo "   - ❌ Server tidak bisa diakses (HTTP {$httpCode})\n";
    echo "   - Pastikan web server berjalan dengan: php -S localhost:8000 -t public\n";
    exit(1);
}

echo "\n2. 🔐 Testing login page...\n";

// Get login page
$ch = curl_init($baseUrl . '/panel/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'login_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'login_cookies.txt');
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
        echo "   - ⚠️ CSRF token tidak ditemukan, akan coba tanpa token\n";
        $csrfToken = '';
    }
} else {
    echo "   - ❌ Login page tidak bisa diakses (HTTP {$loginHttpCode})\n";
    exit(1);
}

echo "\n3. 🧪 Testing login dengan PIN 666666...\n";

// Prepare login data
$loginData = [
    'pin' => '666666',
    '_token' => $csrfToken
];

// Perform login - menggunakan /panel/verify (route yang benar)
$ch = curl_init($baseUrl . '/panel/verify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
curl_setopt($ch, CURLOPT_COOKIEJAR, 'login_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'login_cookies.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);

$loginResponse = curl_exec($ch);
$loginResponseHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
curl_close($ch);

echo "   - Login response code: {$loginResponseHttpCode}\n";
echo "   - Final URL: {$finalUrl}\n";

if ($loginResponseHttpCode === 200 || $loginResponseHttpCode === 302) {
    if (strpos($finalUrl, '/panel/shortlinks') !== false) {
        echo "   - ✅ Login berhasil! Redirect ke dashboard\n";
    } else {
        echo "   - ⚠️ Login response OK tapi redirect tidak sesuai\n";
        echo "   - Response preview:\n";
        echo "     " . substr($loginResponse, 0, 300) . "...\n";
    }
} else {
    echo "   - ❌ Login gagal (HTTP {$loginResponseHttpCode})\n";
    echo "   - Response preview:\n";
    echo "     " . substr($loginResponse, 0, 300) . "...\n";
}

echo "\n4. 🔍 Testing dashboard access...\n";

// Try to access dashboard
$ch = curl_init($baseUrl . '/panel/shortlinks');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'login_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'login_cookies.txt');
$dashboardResponse = curl_exec($ch);
$dashboardHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($dashboardHttpCode === 200) {
    echo "   - ✅ Dashboard berhasil diakses\n";
    echo "   - 🎉 LOGIN BERHASIL! Session tersimpan dengan baik\n";
} else {
    echo "   - ❌ Dashboard tidak bisa diakses (HTTP {$dashboardHttpCode})\n";
    echo "   - Kemungkinan login gagal atau session tidak tersimpan\n";
}

echo "\n5. 🧹 Cleanup...\n";

if (file_exists('login_cookies.txt')) {
    unlink('login_cookies.txt');
    echo "   - ✅ Cookies file dibersihkan\n";
}

echo "\n=== TEST SELESAI ===\n";

if (strpos($finalUrl, '/panel/shortlinks') !== false && $dashboardHttpCode === 200) {
    echo "🎉 SUKSES! Login dengan PIN 666666 berfungsi dengan baik!\n\n";
    echo "📋 Sekarang Anda bisa:\n";
    echo "1. Buka browser dan akses: {$baseUrl}/panel/login\n";
    echo "2. Masukkan PIN: 666666\n";
    echo "3. Form akan auto-submit setelah 6 digit\n";
    echo "4. Akan redirect ke dashboard\n";
} else {
    echo "❌ Login masih ada masalah. Periksa:\n";
    echo "1. Log files di storage/logs/\n";
    echo "2. Konfigurasi database\n";
    echo "3. Session configuration\n";
    echo "4. Web server configuration\n";
}
