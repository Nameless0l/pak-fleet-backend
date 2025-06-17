<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('maintenance_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['preventive', 'corrective', 'ameliorative']);
            $table->text('description')->nullable();
            $table->decimal('default_cost', 10, 2)->default(0);
            $table->timestamps();
        });

        // Insertion des types de maintenance
        DB::table('maintenance_types')->insert([
            // Maintenance préventive
            ['name' => 'Vidange moteur', 'category' => 'preventive', 'default_cost' => 150000],
            ['name' => 'Changement filtre à huile', 'category' => 'preventive', 'default_cost' => 45000],
            ['name' => 'Changement filtre à air', 'category' => 'preventive', 'default_cost' => 35000],
            ['name' => 'Changement filtre carburant', 'category' => 'preventive', 'default_cost' => 40000],
            ['name' => 'Inspection planifiée', 'category' => 'preventive', 'default_cost' => 75000],

            // Maintenance corrective
            ['name' => 'Réparation moteur', 'category' => 'corrective', 'default_cost' => 500000],
            ['name' => 'Réparation transmission', 'category' => 'corrective', 'default_cost' => 350000],
            ['name' => 'Réparation système électrique', 'category' => 'corrective', 'default_cost' => 200000],

            // Maintenance améliorative
            ['name' => 'Réfection peinture', 'category' => 'ameliorative', 'default_cost' => 1500000],
            ['name' => 'Réhabilitation carrosserie', 'category' => 'ameliorative', 'default_cost' => 2000000],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_types');
    }
};
