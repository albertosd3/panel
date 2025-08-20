<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shortlink_visitors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shortlink_id')->index();
            $table->string('ip', 45)->index();
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('first_seen')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->boolean('is_bot')->default(false);
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('asn')->nullable();
            $table->string('org')->nullable();
            $table->timestamps();

            $table->unique(['shortlink_id','ip']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shortlink_visitors');
    }
};
