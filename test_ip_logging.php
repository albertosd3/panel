<?php

/**
 * Test script untuk memverifikasi logging IP
 * Jalankan dengan: php test_ip_logging.php
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Shortlink;
use App\Models\ShortlinkEvent;
use App\Models\ShortlinkVisitor;
use Illuminate\Support\Facades\DB;

echo "=== TEST IP LOGGING ===\n";

try {
    // Test 1: Cek apakah ada shortlink
    $shortlink = Shortlink::first();
    if (!$shortlink) {
        echo "❌ Tidak ada shortlink di database\n";
        echo "Buat shortlink terlebih dahulu melalui panel\n";
        exit(1);
    }
    
    echo "✅ Shortlink ditemukan: {$shortlink->slug} (ID: {$shortlink->id})\n";
    
    // Test 2: Cek struktur tabel
    echo "\n--- Struktur Tabel ---\n";
    
    $eventColumns = DB::select("PRAGMA table_info(shortlink_events)");
    echo "Tabel shortlink_events:\n";
    foreach ($eventColumns as $col) {
        echo "  - {$col->name} ({$col->type})\n";
    }
    
    $visitorColumns = DB::select("PRAGMA table_info(shortlink_visitors)");
    echo "\nTabel shortlink_visitors:\n";
    foreach ($visitorColumns as $col) {
        echo "  - {$col->name} ({$col->type})\n";
    }
    
    // Test 3: Cek data yang ada
    echo "\n--- Data yang Ada ---\n";
    
    $eventCount = ShortlinkEvent::count();
    echo "Total events: {$eventCount}\n";
    
    $visitorCount = ShortlinkVisitor::count();
    echo "Total visitors: {$visitorCount}\n";
    
    if ($eventCount > 0) {
        $latestEvent = ShortlinkEvent::latest('clicked_at')->first();
        echo "Latest event: IP {$latestEvent->ip} at {$latestEvent->clicked_at}\n";
    }
    
    if ($visitorCount > 0) {
        $latestVisitor = ShortlinkVisitor::latest('last_seen')->first();
        echo "Latest visitor: IP {$latestVisitor->ip} with {$latestVisitor->hits} hits\n";
    }
    
    // Test 4: Coba buat test event
    echo "\n--- Test Membuat Event ---\n";
    
    $testPayload = [
        'ip' => '192.168.1.100',
        'country' => 'ID',
        'city' => 'Jakarta',
        'asn' => 'AS1234',
        'org' => 'Test ISP',
        'device' => 'Desktop',
        'platform' => 'Windows',
        'browser' => 'Chrome',
        'referrer' => 'https://example.com',
        'is_bot' => false,
    ];
    
    echo "Membuat test event dengan IP: {$testPayload['ip']}\n";
    
    $event = ShortlinkEvent::create(array_merge($testPayload, [
        'shortlink_id' => $shortlink->id,
        'clicked_at' => now(),
    ]));
    
    echo "✅ Event berhasil dibuat dengan ID: {$event->id}\n";
    
    // Test 5: Cek visitor record
    echo "\n--- Test Visitor Record ---\n";
    
    $visitor = ShortlinkVisitor::firstOrNew([
        'shortlink_id' => $shortlink->id,
        'ip' => $testPayload['ip'],
    ]);
    
    if (!$visitor->exists) {
        echo "Membuat visitor record baru\n";
        $visitor->first_seen = now();
        $visitor->hits = 0;
        $visitor->country = $testPayload['country'];
        $visitor->city = $testPayload['city'];
        $visitor->asn = $testPayload['asn'];
        $visitor->org = $testPayload['org'];
    } else {
        echo "Mengupdate visitor record yang ada\n";
    }
    
    $visitor->hits = ($visitor->hits ?? 0) + 1;
    $visitor->last_seen = now();
    $visitor->is_bot = $testPayload['is_bot'];
    $visitor->save();
    
    echo "✅ Visitor record berhasil disimpan dengan ID: {$visitor->id}\n";
    echo "IP: {$visitor->ip}, Hits: {$visitor->hits}\n";
    
    // Test 6: Verifikasi data tersimpan
    echo "\n--- Verifikasi Data ---\n";
    
    $savedEvent = ShortlinkEvent::find($event->id);
    echo "Event tersimpan: " . ($savedEvent ? "✅" : "❌") . "\n";
    if ($savedEvent) {
        echo "  - IP: {$savedEvent->ip}\n";
        echo "  - Country: {$savedEvent->country}\n";
        echo "  - Is Bot: " . ($savedEvent->is_bot ? "Yes" : "No") . "\n";
    }
    
    $savedVisitor = ShortlinkVisitor::find($visitor->id);
    echo "Visitor tersimpan: " . ($savedVisitor ? "✅" : "❌") . "\n";
    if ($savedVisitor) {
        echo "  - IP: {$savedVisitor->ip}\n";
        echo "  - Hits: {$savedVisitor->hits}\n";
        echo "  - Country: {$savedVisitor->country}\n";
    }
    
    // Test 7: Cleanup test data
    echo "\n--- Cleanup ---\n";
    
    $event->delete();
    $visitor->delete();
    
    echo "✅ Test data berhasil dihapus\n";
    
    echo "\n=== TEST SELESAI ===\n";
    echo "Jika semua test berhasil, logging IP seharusnya berfungsi dengan baik.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
