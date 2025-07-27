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
        Schema::create('tasks', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();

        // Relaciones con usuarios con restricción al eliminar
        $table->foreignId('created_by')
            ->constrained('users')
            ->onDelete('restrict');

        $table->foreignId('audited_by')
            ->nullable()
            ->constrained('users')
            ->onDelete('restrict');

        // Relaciones con artículos y sectores
        $table->foreignId('article_id')
            ->constrained('articles')
            ->onDelete('restrict');

        $table->foreignId('sector_id')
            ->constrained('sectors')
            ->onDelete('restrict');

        $table->timestamp('deadline_at')->nullable();

         $table->foreignId('task_plan_id')
                ->nullable()
                ->constrained('task_plans')
                ->onDelete('set null')
                ->after('id'); 

        // Campos de fecha
        $table->timestamp('executed_at')->nullable();
        $table->timestamp('approved_at')->nullable();
        $table->timestamp('canceled_at')->nullable();

        $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
