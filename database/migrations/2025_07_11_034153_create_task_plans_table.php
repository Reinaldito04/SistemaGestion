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
         Schema::create('task_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();

            $table->string('activity_title');
            $table->text('activity_description')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('audited_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->foreignId('article_id')->constrained('articles')->onDelete('restrict');
            $table->foreignId('sector_id')->constrained('sectors')->onDelete('restrict');

            $table->enum('frequency', ['daily', 'weekly', 'monthly', 'yearly']);
            $table->json('days')->nullable();
            $table->time('deadline_time');

            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();

            $table->boolean('is_active')->default(true); // <<--- Aquí el campo para activar/desactivar

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_plans');
    }
};
