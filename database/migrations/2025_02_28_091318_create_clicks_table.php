<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shortlink_id');
            $table->foreign('shortlink_id')->references('id')->on('shortlinks')->onDelete('cascade');
            $table->string('ip');
            $table->text('user_agent');
            $table->string('referrer')->nullable();
            $table->string('country')->nullable();
            $table->string('device')->nullable();
            $table->timestamps(); // created_at servira de date/heure du clic
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clicks');
    }
};
