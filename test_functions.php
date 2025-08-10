<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test creating a shortlink
echo "Testing shortlink creation...\n";
$shortlink = \App\Models\Shortlink::create([
    'slug' => 'test123',
    'destination' => 'https://google.com',
    'meta' => ['test' => true]
]);
echo "Created shortlink: {$shortlink->slug} -> {$shortlink->destination}\n";

// Test creating an event
echo "Testing event creation...\n";
$event = \App\Models\ShortlinkEvent::create([
    'shortlink_id' => $shortlink->id,
    'ip' => '127.0.0.1',
    'country' => 'US',
    'device' => 'Desktop',
    'browser' => 'Chrome',
    'is_bot' => false,
    'clicked_at' => now()
]);
echo "Created event for shortlink ID: {$event->shortlink_id}\n";

// Update click count
\App\Models\Shortlink::where('id', $shortlink->id)->update(['clicks' => \Illuminate\Support\Facades\DB::raw('clicks + 1')]);
$shortlink->refresh();
echo "Updated clicks: {$shortlink->clicks}\n";

// Clean up
$event->delete();
$shortlink->delete();
echo "Cleaned up test data\n";
echo "All tests passed!\n";
