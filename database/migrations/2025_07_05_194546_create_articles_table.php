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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->boolean('active')->default(true);
            $table->string('acronym')->unique()->nullable();
            $table->unsignedBigInteger('article_type_id')->nullable(); // Relación con el tipo de artículo, puede ser nula si no pertenece a ningún tipo

            $table->foreign('article_type_id')->references('id')->on('article_types')->onDelete('restrict'); // evita borrar el tipo de artículo si tiene artículos relacionados

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
