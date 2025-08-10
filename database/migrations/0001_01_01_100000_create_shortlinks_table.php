<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shortlinks', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->text('destination');
            $table->unsignedBigInteger('clicks')->default(0)->index();
            $table->boolean('active')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['active', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shortlinks');
    }
};
