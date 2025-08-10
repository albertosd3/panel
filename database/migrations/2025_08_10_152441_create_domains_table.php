<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->string('display_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->boolean('force_https')->default(false);
            $table->json('settings')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->index(['is_active', 'is_default']);
            $table->index('domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
