<?php

/**
 * Script untuk test login secara otomatis menggunakan cURL
 * Jalankan dengan: php auto_test_login.php
 */

echo "=== AUTO TEST LOGIN DENGAN PIN 666666 ===\n\n";

// Base URL
$baseUrl = 'http://localhost:8000';

echo "1. 🌐 Testing server availability...\n";
$healthCheck = curl_init($baseUrl . '/health-check');
curl_setopt($healthCheck, CURLOPT_RETURNTRANSFER, true);
curl_setopt($healthCheck, CURLOPT_TIMEOUT, 10);
$response = curl_exec($healthCheck);
$httpCode = curl_getinfo($healthCheck, CURLINFO_HTTP_CODE);
curl_close($healthCheck);

if ($httpCode === 200) {
    echo "   - ✅ Server berjalan di {$baseUrl}\n";
} else {
    echo "   - ❌ Server tidak bisa diakses (HTTP {$httpCode})\n";
    echo "   - Pastikan web server berjalan dengan: php start_server.php\n";
    exit(1);
}

echo "\n2. 🔐 Testing login page...\n";

// Get login page to get CSRF token
$loginPage = curl_init($baseUrl . '/panel/login');
curl_setopt($loginPage, CURLOPT_RETURNTRANSFER, true);
curl_setopt($loginPage, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($loginPage, CURLOPT_COOKIEFILE, 'cookies.txt');
$loginHtml = curl_exec($loginPage);
$loginHttpCode = curl_getinfo($loginPage, CURLINFO_HTTP_CODE);
curl_close($loginPage);

if ($loginHttpCode === 200) {
    echo "   - ✅ Login page berhasil diakses\n";
    
    // Extract CSRF token
    if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $loginHtml, $matches)) {
        $csrfToken = $matches[1];
        echo "   - ✅ CSRF token ditemukan: " . substr($csrfToken, 0, 10) . "...\n";
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

// Perform login
$loginRequest = curl_init($baseUrl . '/panel/verify');
curl_setopt($loginRequest, CURLOPT_RETURNTRANSFER, true);
curl_setopt($loginRequest, CURLOPT_POST, true);
curl_setopt($loginRequest, CURLOPT_POSTFIELDS, http_build_query($loginData));
curl_setopt($loginRequest, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($loginRequest, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($loginRequest, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($loginRequest, CURLOPT_HEADER, true);
curl_setopt($loginRequest, CURLOPT_NOBODY, false);

$loginResponse = curl_exec($loginRequest);
$loginResponseHttpCode = curl_getinfo($loginRequest, CURLINFO_HTTP_CODE);
$finalUrl = curl_getinfo($loginRequest, CURLINFO_EFFECTIVE_URL);
curl_close($loginRequest);

echo "   - Login response code: {$loginResponseHttpCode}\n";
echo "   - Final URL: {$finalUrl}\n";

if ($loginResponseHttpCode === 200 || $loginResponseHttpCode === 302) {
    if (strpos($finalUrl, '/panel/shortlinks') !== false) {
        echo "   - ✅ Login berhasil! Redirect ke dashboard\n";
    } else {
        echo "   - ⚠️ Login response OK tapi redirect tidak sesuai\n";
        echo "   - Response content:\n";
        echo "     " . substr($loginResponse, 0, 200) . "...\n";
    }
} else {
    echo "   - ❌ Login gagal (HTTP {$loginResponseHttpCode})\n";
    echo "   - Response content:\n";
    echo "     " . substr($loginResponse, 0, 200) . "...\n";
}

echo "\n4. 🔍 Testing dashboard access...\n";

// Try to access dashboard
$dashboard = curl_init($baseUrl . '/panel/shortlinks');
curl_setopt($dashboard, CURLOPT_RETURNTRANSFER, true);
curl_setopt($dashboard, CURLOPT_COOKIEJAR, 'cookies.txt');
curl_setopt($dashboard, CURLOPT_COOKIEFILE, 'cookies.txt');
$dashboardResponse = curl_exec($dashboard);
$dashboardHttpCode = curl_getinfo($dashboard, CURLINFO_HTTP_CODE);
curl_close($dashboard);

if ($dashboardHttpCode === 200) {
    echo "   - ✅ Dashboard berhasil diakses\n";
    echo "   - Login berhasil dan session tersimpan\n";
} else {
    echo "   - ❌ Dashboard tidak bisa diakses (HTTP {$dashboardHttpCode})\n";
    echo "   - Kemungkinan login gagal atau session tidak tersimpan\n";
}

echo "\n5. 🧹 Cleanup...\n";

// Clean up cookies file
if (file_exists('cookies.txt')) {
    unlink('cookies.txt');
    echo "   - ✅ Cookies file dibersihkan\n";
}

echo "\n=== TEST SELESAI ===\n";

if (strpos($finalUrl, '/panel/shortlinks') !== false) {
    echo "🎉 SUKSES! Login dengan PIN 666666 berfungsi dengan baik!\n";
    echo "Sekarang Anda bisa:\n";
    echo "1. Buka browser dan akses: {$baseUrl}/panel/login\n";
    echo "2. Masukkan PIN: 666666\n";
    echo "3. Akan redirect ke dashboard\n";
} else {
    echo "❌ Login masih ada masalah. Periksa:\n";
    echo "1. Log files di storage/logs/\n";
    echo "2. Konfigurasi database\n";
    echo "3. Session configuration\n";
    echo "4. Web server configuration\n";
}
