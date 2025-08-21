<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('panel_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, integer, json
            $table->string('group')->default('general'); // general, stopbot, geoip, etc
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Insert default Stopbot settings
        DB::table('panel_settings')->insert([
            [
                'key' => 'stopbot_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'stopbot',
                'description' => 'Enable Stopbot.net integration',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'stopbot_api_key',
                'value' => '',
                'type' => 'string',
                'group' => 'stopbot',
                'description' => 'Stopbot.net API key',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'stopbot_redirect_url',
                'value' => 'https://www.google.com',
                'type' => 'string',
                'group' => 'stopbot',
                'description' => 'URL to redirect blocked requests',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'stopbot_log_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'stopbot',
                'description' => 'Enable Stopbot logging',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'stopbot_timeout',
                'value' => '5',
                'type' => 'integer',
                'group' => 'stopbot',
                'description' => 'API timeout in seconds',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('panel_settings');
    }
};
