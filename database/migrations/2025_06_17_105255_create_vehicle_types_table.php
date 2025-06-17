<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicle_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('vehicle_types')->insert([
            ['name' => 'Station Wagon', 'code' => 'WAGON', 'description' => 'Véhicules de direction (DG, DGA)'],
            ['name' => 'Crossover', 'code' => 'CROSS', 'description' => 'Véhicules chefs de structures'],
            ['name' => 'SUV', 'code' => 'SUV', 'description' => 'Véhicules de liaison'],
            ['name' => 'Berline', 'code' => 'BERLINE', 'description' => 'Véhicules direction et hôtes'],
            ['name' => 'Tout Terrain', 'code' => 'PICKUP', 'description' => 'Pick-up'],
            ['name' => 'Coaster', 'code' => 'COASTER', 'description' => 'Transport du personnel'],
            ['name' => 'Mini Bus', 'code' => 'MINIBUS', 'description' => 'Transport FMO et personnel'],
            ['name' => 'Bus', 'code' => 'BUS', 'description' => 'Transport collectif'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_types');
    }
};
