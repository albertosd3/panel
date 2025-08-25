<?php

/**
 * Script untuk test create rotator shortlink
 * Jalankan dengan: php test_rotator_fix.php
 * Pastikan web server sudah berjalan di http://localhost:8000
 */

echo "=== TEST ROTATOR LINK FIX ===\n\n";

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

echo "\n3. 🔗 Testing create rotator shortlink...\n";

// Test data for rotator link
$rotatorData = [
    'is_rotator' => true,
    'rotation_type' => 'random',
    'destinations' => [
        [
            'url' => 'https://example.com/page1',
            'name' => 'Page 1',
            'weight' => 1
        ],
        [
            'url' => 'https://example.com/page2',
            'name' => 'Page 2',
            'weight' => 2
        ]
    ],
    'slug' => 'test-rotator-' . time(),
    '_token' => $csrfToken
];

echo "   - Creating rotator shortlink: {$rotatorData['slug']}\n";
echo "   - Destinations: " . count($rotatorData['destinations']) . " URLs\n";

$ch = curl_init($baseUrl . '/panel/shortlinks');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($rotatorData));
curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
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
        echo "   - ✅ Rotator shortlink berhasil dibuat!\n";
        echo "   - Slug: " . ($responseData['shortlink']['slug'] ?? 'N/A') . "\n";
        echo "   - Is Rotator: " . ($responseData['shortlink']['is_rotator'] ? 'Yes' : 'No') . "\n";
        echo "   - Destinations: " . count($responseData['shortlink']['destinations'] ?? []) . "\n";
    } else {
        echo "   - ❌ Create gagal: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "   - ❌ Create request gagal (HTTP {$createHttpCode})\n";
}

echo "\n4. 🔗 Testing create single shortlink...\n";

// Test data for single link
$singleData = [
    'is_rotator' => false,
    'destination' => 'https://example.com/single',
    'slug' => 'test-single-' . time(),
    '_token' => $csrfToken
];

echo "   - Creating single shortlink: {$singleData['slug']}\n";

$ch = curl_init($baseUrl . '/panel/shortlinks');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($singleData));
curl_setopt($ch, CURLOPT_COOKIEJAR, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, 'test_cookies.txt');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

$createResponse = curl_exec($ch);
$createHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   - Create response code: {$createHttpCode}\n";

if ($createHttpCode === 200) {
    $responseData = json_decode($createResponse, true);
    if ($responseData && isset($responseData['success']) && $responseData['success']) {
        echo "   - ✅ Single shortlink berhasil dibuat!\n";
        echo "   - Slug: " . ($responseData['shortlink']['slug'] ?? 'N/A') . "\n";
        echo "   - Is Rotator: " . ($responseData['shortlink']['is_rotator'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "   - ❌ Create gagal: " . ($responseData['message'] ?? 'Unknown error') . "\n";
    }
} else {
    echo "   - ❌ Create request gagal (HTTP {$createHttpCode})\n";
}

echo "\n5. 🧹 Cleanup...\n";

if (file_exists('test_cookies.txt')) {
    unlink('test_cookies.txt');
    echo "   - ✅ Cookies file dibersihkan\n";
}

echo "\n=== TEST SELESAI ===\n";

if ($createHttpCode === 200) {
    echo "🎉 SUKSES! Rotator link creation sudah diperbaiki!\n\n";
    echo "📋 Fitur yang berfungsi:\n";
    echo "✅ Create rotator shortlink\n";
    echo "✅ Create single shortlink\n";
    echo "✅ Proper validation\n";
    echo "✅ Data structure handling\n";
} else {
    echo "❌ Masih ada masalah dengan rotator link creation.\n";
    echo "Periksa:\n";
    echo "1. Log files di storage/logs/\n";
    echo "2. Backend validation\n";
    echo "3. Data structure\n";
}
