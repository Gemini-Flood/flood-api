<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('flood_zones', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('risk_level')->comment('Niveau de risque (faible, modéré, élevé, extrême)');
            $table->text('historical_data')->nullable()->comment('Données historiques sur les inondations');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flood_zones');
    }
};
