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
        Schema::create('files_assignables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('file_id');
            $table->unsignedBigInteger('assignable_id');
            $table->string('assignable_type');
            $table->foreign('file_id')->references('id')->on('files')
                  ->onUpdate('cascade')->onDelete('cascade');
            $table->index(['assignable_id', 'assignable_type'], 'assignable_index');
            $table->timestamps();
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files_assignables');
    }
};
