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
       Schema::create('spare_parts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit'); // pièce, litre, etc.
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('minimum_stock')->default(5);
            $table->string('category'); // filtration, lubrification, pneumatique, batterie
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spare_parts');
    }
};
