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
        Schema::create('shortlinks', function (Blueprint $table) {
            $table->id();
            $table->string('destination');  // Champ obligatoire
            $table->string('titre')->nullable();  // Facultatif
            $table->string('domaine')->default('tnbresa');  // Valeur par défaut "tnbresa"
            $table->string('chemin_personnalise')->nullable()->unique();  // Facultatif et unique
            $table->string('utm_term')->nullable();  // Facultatif
            $table->string('utm_content')->nullable();  // Facultatif
            $table->string('utm_campaign')->nullable();  // Facultatif
            $table->string('utm_source')->nullable();  // Facultatif
            $table->string('utm_medium')->nullable();  // Facultatif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shortlinks');
    }
};
