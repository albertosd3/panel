<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shortlinks', function (Blueprint $table) {
            $table->boolean('is_rotator')->default(false)->after('active');
            $table->string('rotation_type')->default('random')->after('is_rotator'); // random, sequential, weighted
            $table->json('destinations')->nullable()->after('rotation_type'); // array of destination objects
            $table->integer('current_index')->default(0)->after('destinations'); // for sequential rotation
        });
    }

    public function down(): void
    {
        Schema::table('shortlinks', function (Blueprint $table) {
            $table->dropColumn(['is_rotator', 'rotation_type', 'destinations', 'current_index']);
        });
    }
};
