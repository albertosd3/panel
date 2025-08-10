<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shortlink_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shortlink_id')->constrained('shortlinks')->cascadeOnDelete();
            $table->string('ip', 45)->nullable()->index();
            $table->string('country', 2)->nullable()->index();
            $table->string('city', 120)->nullable();
            $table->string('device', 50)->nullable()->index();
            $table->string('platform', 50)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('referrer', 255)->nullable();
            $table->boolean('is_bot')->default(false)->index();
            $table->timestamp('clicked_at')->useCurrent()->index();
            $table->timestamps();
            $table->index(['shortlink_id', 'clicked_at']);
        });

        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip', 45)->unique();
            $table->string('reason', 191)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shortlink_events');
        Schema::dropIfExists('blocked_ips');
    }
};
