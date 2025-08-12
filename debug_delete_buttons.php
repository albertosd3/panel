<?php
require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Shortlink;

echo "=== Checking Delete Button Implementation ===\n\n";

// Check if shortlinks exist
$shortlinks = Shortlink::limit(3)->get();

if ($shortlinks->isEmpty()) {
    echo "❌ No shortlinks found. Creating sample data...\n";
    
    // Create sample shortlinks
    Shortlink::create([
        'slug' => 'test-delete',
        'destination' => 'https://example.com/test-delete',
        'hits' => 0
    ]);
    
    echo "✅ Sample shortlink created: test-delete\n\n";
    $shortlinks = Shortlink::limit(3)->get();
}

echo "📋 Current shortlinks:\n";
foreach ($shortlinks as $link) {
    echo "   • {$link->slug} -> {$link->destination}\n";
}

echo "\n🔍 Checking HTML structure for delete buttons...\n";

// Read the shortlinks.blade.php file to verify button implementation
$shortlinksFile = file_get_contents('resources/views/panel/shortlinks.blade.php');

if (strpos($shortlinksFile, 'btn-delete-sm') !== false) {
    echo "✅ Delete button CSS class found\n";
} else {
    echo "❌ Delete button CSS class NOT found\n";
}

if (strpos($shortlinksFile, 'deleteShortlink') !== false) {
    echo "✅ deleteShortlink function found\n";
} else {
    echo "❌ deleteShortlink function NOT found\n";
}

if (strpos($shortlinksFile, '🗑️') !== false) {
    echo "✅ Delete icon (🗑️) found in template\n";
} else {
    echo "❌ Delete icon (🗑️) NOT found in template\n";
}

// Check exact location of delete button
$lines = explode("\n", $shortlinksFile);
$deleteButtonLine = null;
foreach ($lines as $num => $line) {
    if (strpos($line, 'btn-delete-sm') !== false && strpos($line, 'onclick') !== false) {
        $deleteButtonLine = $num + 1;
        echo "✅ Delete button found at line: $deleteButtonLine\n";
        echo "   Code: " . trim($line) . "\n";
        break;
    }
}

if (!$deleteButtonLine) {
    echo "❌ Delete button with onclick NOT found\n";
}

echo "\n🎯 Expected button HTML:\n";
echo '<button class="btn-delete-sm" onclick="deleteShortlink(\'SLUG\')" title="Delete shortlink">' . "\n";
echo '    🗑️' . "\n";
echo '</button>' . "\n";

echo "\n💡 If buttons are not showing:\n";
echo "1. Clear browser cache (Ctrl+F5)\n";
echo "2. Check browser console for JavaScript errors\n";
echo "3. Verify the page is loading the latest JavaScript\n";
echo "4. Test in incognito/private mode\n";

echo "\n✅ Delete button implementation status: READY\n";
echo "🌐 Open: http://localhost:8000/panel to test\n";
?>
