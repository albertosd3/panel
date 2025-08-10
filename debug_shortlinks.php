<?php
echo "Testing shortlink creation and persistence...\n";

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    // Create a shortlink
    $shortlink = \App\Models\Shortlink::create([
        'slug' => 'test' . time(),
        'destination' => 'https://google.com',
        'meta' => ['test' => true]
    ]);
    
    echo "Created shortlink ID: {$shortlink->id} with slug: {$shortlink->slug}\n";
    
    // Check if it persists
    $found = \App\Models\Shortlink::find($shortlink->id);
    echo "Found shortlink after creation: " . ($found ? 'YES' : 'NO') . "\n";
    
    // Count total
    $count = \App\Models\Shortlink::count();
    echo "Total shortlinks in DB: {$count}\n";
    
    // List all shortlinks
    $all = \App\Models\Shortlink::all(['id', 'slug', 'destination', 'created_at']);
    echo "All shortlinks:\n";
    foreach ($all as $link) {
        echo "- ID: {$link->id}, Slug: {$link->slug}, Created: {$link->created_at}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
