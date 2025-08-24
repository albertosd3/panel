<?php

/**
 * Script untuk test create shortlink
 * Jalankan dengan: php test_create_shortlink.php
 * Pastikan web server sudah berjalan di http://localhost:8000
 */

echo "=== TEST CREATE SHORTLINK ===\n\n";

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
curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
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

echo "\n3. 🔗 Testing create shortlink...\n";

// Test data
$shortlinkData = [
    'destination' => 'https://example.com',
    'slug' => 'test-' . time(),
    'is_rotator' => false,
    '_token' => $csrfToken
];

echo "   - Creating shortlink: {$shortlinkData['slug']} -> {$shortlinkData['destination']}\n";

$ch = curl_init($baseUrl . '/panel/shortlinks');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($shortlinkData));
curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'X-Requested-With: XMLHttpRequest'
]);

$createResponse = curl_exec($ch);
$createHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   - Create response code: {$createHttpCode}\n";
echo "   - Response: " . substr($createResponse, 0, 200) . "...\n";

if ($createHttpCode === 200) {
    $responseData = json_decode($createResponse, true);
    if ($responseData && isset($responseData['success']) && $responseData['success']) {
        echo "   - ✅ Shortlink berhasil dibuat!\n";
        echo "   - Slug: " . ($responseData['shortlink']['slug'] ?? 'N/A') . "\n";
        echo "   - Destination: " . ($responseData['shortlink']['destination'] ?? 'N/A') . "\n";
    } else {
        echo "   - ❌ Create gagal: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "   - ❌ Create request gagal (HTTP {$createHttpCode})\n";
}

echo "\n4. 📋 Testing list shortlinks...\n";

$ch = curl_init($baseUrl . '/panel/shortlinks/list');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
$listResponse = curl_exec($ch);
$listHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   - List response code: {$listHttpCode}\n";

if ($listHttpCode === 200) {
    $listData = json_decode($listResponse, true);
    if ($listData && isset($listData['success']) && $listData['success']) {
        $count = count($listData['data'] ?? []);
        echo "   - ✅ List berhasil, total shortlinks: {$count}\n";
        
        if ($count > 0) {
            echo "   - Sample shortlink:\n";
            $sample = $listData['data'][0];
            echo "     Slug: " . ($sample['slug'] ?? 'N/A') . "\n";
            echo "     Destination: " . ($sample['destination'] ?? 'N/A') . "\n";
            echo "     Clicks: " . ($sample['clicks'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   - ❌ List gagal: " . ($listData['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "   - ❌ List request gagal (HTTP {$listHttpCode})\n";
}

echo "\n5. 📊 Testing analytics...\n";

$ch = curl_init($baseUrl . '/panel/analytics');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
$analyticsResponse = curl_exec($ch);
$analyticsHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   - Analytics response code: {$analyticsHttpCode}\n";

if ($analyticsHttpCode === 200) {
    $analyticsData = json_decode($analyticsResponse, true);
    if ($analyticsData && isset($analyticsData['success']) && $analyticsData['success']) {
        $overview = $analyticsData['data']['overview'] ?? [];
        echo "   - ✅ Analytics berhasil\n";
        echo "     Total links: " . ($overview['total_links'] ?? 'N/A') . "\n";
        echo "     Total clicks: " . ($overview['total_clicks'] ?? 'N/A') . "\n";
        echo "     Active links: " . ($overview['active_links'] ?? 'N/A') . "\n";
    } else {
        echo "   - ❌ Analytics gagal: " . ($analyticsData['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "   - ❌ Analytics request gagal (HTTP {$analyticsHttpCode})\n";
}

echo "\n6. 🧹 Cleanup...\n";

if (file_exists('test_cookies.txt')) {
    unlink('test_cookies.txt');
    echo "   - ✅ Cookies file dibersihkan\n";
}

echo "\n=== TEST SELESAI ===\n";

if ($createHttpCode === 200 && $listHttpCode === 200 && $analyticsHttpCode === 200) {
    echo "🎉 SUKSES! Semua fitur shortlink berfungsi dengan baik!\n\n";
    echo "📋 Fitur yang berfungsi:\n";
    echo "✅ Create shortlink\n";
    echo "✅ List shortlinks\n";
    echo "✅ Analytics\n";
    echo "✅ Session management\n";
} else {
    echo "❌ Ada masalah dengan beberapa fitur. Periksa:\n";
    echo "1. Log files di storage/logs/\n";
    echo "2. Database connection\n";
    echo "3. Route configuration\n";
    echo "4. CSRF token\n";
}
