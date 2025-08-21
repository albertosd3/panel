<?php

$db = new PDO('sqlite:database/database.sqlite');

// Update stopbot settings from .env values
$updates = [
    ['stopbot_enabled', 'true', 'boolean'],
    ['stopbot_api_key', 'a4b14a7c137b0f5f384206940fa11cee', 'string'],
    ['stopbot_redirect_url', 'https://www.google.com', 'string'],
    ['stopbot_log_enabled', 'true', 'boolean'],
    ['stopbot_timeout', '5', 'integer']
];

foreach ($updates as [$key, $value, $type]) {
    $stmt = $db->prepare("UPDATE panel_settings SET value = ?, type = ?, updated_at = datetime('now') WHERE key = ?");
    $result = $stmt->execute([$value, $type, $key]);
    
    if ($result) {
        echo "✅ Updated $key = $value ($type)\n";
    } else {
        echo "❌ Failed to update $key\n";
    }
}

echo "\n--- Current Database Values ---\n";
$stmt = $db->query("SELECT key, value, type FROM panel_settings WHERE key LIKE 'stopbot_%' ORDER BY key");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $displayValue = $row['key'] === 'stopbot_api_key' ? substr($row['value'], 0, 8) . '...' : $row['value'];
    echo $row['key'] . '=' . $displayValue . ' (' . $row['type'] . ")\n";
}

echo "\nStopbot sync completed!\n";
