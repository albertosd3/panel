<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('create:sample-domains', function () {
    $this->info('Creating sample domains...');
    
    // Create default domain
    $defaultDomain = \App\Models\Domain::create([
        'domain' => 'localhost',
        'description' => 'Default development domain',
        'is_default' => true,
        'is_active' => true,
    ]);
    $this->info("Created default domain: {$defaultDomain->domain}");
    
    // Create additional domain
    $customDomain = \App\Models\Domain::create([
        'domain' => 'short.example.com',
        'description' => 'Custom branded domain',
        'is_default' => false,
        'is_active' => true,
    ]);
    $this->info("Created custom domain: {$customDomain->domain}");
    
    $this->info('Sample domains created successfully!');
})->purpose('Create sample domains for testing');
