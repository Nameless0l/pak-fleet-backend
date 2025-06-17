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
        Schema::create('spare_part_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_operation_id')->constrained()->onDelete('cascade');
            $table->foreignId('spare_part_id')->constrained();
            $table->integer('quantity_used');
            $table->decimal('unit_price', 10, 2); // Prix au moment de l'utilisation
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_part_usages');
    }
};
