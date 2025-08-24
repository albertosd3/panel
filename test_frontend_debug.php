<?php

/**
 * Script untuk test debug frontend create shortlink
 * Jalankan dengan: php test_frontend_debug.php
 * Pastikan web server sudah berjalan di http://localhost:8000
 */

echo "=== TEST FRONTEND DEBUG ===\n\n";

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
    exit(1);
}

echo "\n2. 🔐 Testing login untuk mendapatkan session...\n";

// Get login page
$ch = curl_init($baseUrl . '/panel/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'debug_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'debug_cookies.txt');
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
        echo "   - ❌ CSRF token tidak ditemukan\n";
        exit(1);
    }
} else {
    echo "   - ❌ Login page tidak bisa diakses (HTTP {$loginHttpCode})\n";
    exit(1);
}

// Perform login
$loginData = [
    'pin' => '666666',
    '_token' => $csrfToken
];

$ch = curl_init($baseUrl . '/panel/verify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($loginData));
curl_setopt($ch, CURLOPT_COOKIEJAR, 'debug_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'debug_cookies.txt');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$loginResponse = curl_exec($ch);
$loginHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($loginHttpCode === 200 || $loginHttpCode === 302) {
    echo "   - ✅ Login berhasil\n";
} else {
    echo "   - ❌ Login gagal (HTTP {$loginHttpCode})\n";
    exit(1);
}

echo "\n3. 🔍 Testing frontend page access...\n";

// Get shortlinks page
$ch = curl_init($baseUrl . '/panel/shortlinks');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'debug_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'debug_cookies.txt');
$shortlinksHtml = curl_exec($ch);
$shortlinksHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($shortlinksHttpCode === 200) {
    echo "   - ✅ Shortlinks page berhasil diakses\n";
    
    // Check if form exists
    if (strpos($shortlinksHtml, 'id="create-form"') !== false) {
        echo "   - ✅ Create form ditemukan\n";
    } else {
        echo "   - ❌ Create form TIDAK ditemukan\n";
    }
    
    // Check if submit button exists
    if (strpos($shortlinksHtml, 'type="submit"') !== false) {
        echo "   - ✅ Submit button ditemukan\n";
    } else {
        echo "   - ❌ Submit button TIDAK ditemukan\n";
    }
    
    // Check if JavaScript functions exist
    if (strpos($shortlinksHtml, 'function createShortlink') !== false) {
        echo "   - ✅ createShortlink function ditemukan\n";
    } else {
        echo "   - ❌ createShortlink function TIDAK ditemukan\n";
    }
    
    if (strpos($shortlinksHtml, 'function showNotification') !== false) {
        echo "   - ✅ showNotification function ditemukan\n";
    } else {
        echo "   - ❌ showNotification function TIDAK ditemukan\n";
    }
    
    // Check if event listener is set up
    if (strpos($shortlinksHtml, 'addEventListener(\'submit\'') !== false) {
        echo "   - ✅ Form event listener ditemukan\n";
    } else {
        echo "   - ❌ Form event listener TIDAK ditemukan\n";
    }
    
} else {
    echo "   - ❌ Shortlinks page tidak bisa diakses (HTTP {$shortlinksHttpCode})\n";
    exit(1);
}

echo "\n4. 🧹 Cleanup...\n";

if (file_exists('debug_cookies.txt')) {
    unlink('debug_cookies.txt');
    echo "   - ✅ Cookies file dibersihkan\n";
}

echo "\n=== TEST SELESAI ===\n";

echo "\n📋 INSTRUKSI DEBUG FRONTEND:\n";
echo "1. Buka browser dan akses: {$baseUrl}/panel/shortlinks\n";
echo "2. Login dengan PIN: 666666\n";
echo "3. Buka Developer Tools (F12)\n";
echo "4. Buka Console tab\n";
echo "5. Coba create shortlink dan lihat console output\n";
echo "6. Jika ada error, copy dan paste error message\n";
echo "\n🔧 TROUBLESHOOTING:\n";
echo "- Pastikan JavaScript enabled di browser\n";
echo "- Pastikan tidak ada ad blocker yang memblokir script\n";
echo "- Cek apakah ada error di Console\n";
echo "- Cek apakah form dan button ada di Elements tab\n";
