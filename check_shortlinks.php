<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Shortlink;
use App\Models\ShortlinkEvent;
use App\Models\ShortlinkVisitor;

echo "=== Shortlink IP Logging Test ===\n\n";

// Check available shortlinks
echo "Available shortlinks:\n";
$shortlinks = Shortlink::select('id', 'slug', 'destination', 'clicks')->get();
foreach ($shortlinks as $s) {
    echo "- {$s->slug} -> {$s->destination} (clicks: {$s->clicks})\n";
}

echo "\n=== Current Database State ===\n";
echo "ShortlinkEvent count: " . ShortlinkEvent::count() . "\n";
echo "ShortlinkVisitor count: " . ShortlinkVisitor::count() . "\n";

// Check recent events
echo "\nRecent events (last 5):\n";
$recentEvents = ShortlinkEvent::latest('clicked_at')->limit(5)->get();
foreach ($recentEvents as $event) {
    echo "- {$event->clicked_at}: IP {$event->ip}, Country: {$event->country}, Bot: " . ($event->is_bot ? 'Yes' : 'No') . "\n";
}

// Check recent visitors
echo "\nRecent visitors (last 5):\n";
$recentVisitors = ShortlinkVisitor::latest('last_seen')->limit(5)->get();
foreach ($recentVisitors as $visitor) {
    echo "- IP {$visitor->ip}, Hits: {$visitor->hits}, Last seen: {$visitor->last_seen}, Bot: " . ($visitor->is_bot ? 'Yes' : 'No') . "\n";
}

echo "\n=== Testing IP Logging ===\n";
echo "To test IP logging, visit one of these URLs:\n";
foreach ($shortlinks->take(3) as $s) {
    echo "http://127.0.0.1:8000/{$s->slug}\n";
}

echo "\nAfter visiting, run this script again to see if new records were created.\n";
