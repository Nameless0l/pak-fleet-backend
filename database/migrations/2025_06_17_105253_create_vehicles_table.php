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
       Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('brand');
            $table->string('model');
            $table->foreignId('vehicle_type_id')->constrained();
            $table->year('year')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->enum('status', ['active', 'maintenance', 'out_of_service'])->default('active');
            $table->boolean('under_warranty')->default(false);
            $table->date('warranty_end_date')->nullable();
            $table->json('specifications')->nullable(); // Pour stocker des specs additionnelles
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
