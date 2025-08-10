<?php

// Quick script to add sample domains
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->boot();

use App\Models\Domain;

echo "Creating sample domains...\n";

// Create default domain
$defaultDomain = Domain::create([
    'domain' => 'localhost',
    'description' => 'Default development domain',
    'is_default' => true,
    'is_active' => true,
]);
echo "Created default domain: {$defaultDomain->domain}\n";

// Create additional domain
$customDomain = Domain::create([
    'domain' => 'short.example.com',
    'description' => 'Custom branded domain',
    'is_default' => false,
    'is_active' => true,
]);
echo "Created custom domain: {$customDomain->domain}\n";

echo "Sample domains created successfully!\n";
