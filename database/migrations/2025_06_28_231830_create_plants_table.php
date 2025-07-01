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
        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->string('acronym')->unique()->nullable();
            $table->unsignedBigInteger('area_id')->nullable(); // Relación con el área, puede ser nula si no pertenece a ninguna área

            $table->foreign('area_id')->references('id')->on('areas')->onDelete('restrict'); // evita borrar el área si tiene plantas relacionadas

            
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
