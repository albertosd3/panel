<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('shortlink_events', 'asn')) {
            Schema::table('shortlink_events', function (Blueprint $table) {
                $table->string('asn', 32)->nullable()->after('country')->index();
                $table->string('org', 191)->nullable()->after('asn')->index();
            });
        }
    }

    public function down(): void
    {
        Schema::table('shortlink_events', function (Blueprint $table) {
            if (Schema::hasColumn('shortlink_events', 'asn')) {
                $table->dropColumn('asn');
            }
            if (Schema::hasColumn('shortlink_events', 'org')) {
                $table->dropColumn('org');
            }
        });
    }
};
