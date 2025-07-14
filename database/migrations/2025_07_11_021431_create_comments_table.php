<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Kalnoy\Nestedset\NestedSet;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id(); // Usamos bigIncrements automáticamente con ->id()
            $table->string('title')->nullable();
            $table->boolean('active')->default(false);
            $table->text('body');

            // Relaciones polimórficas
            $table->morphs('commentable'); // commentable_id + commentable_type
            $table->morphs('creator');     // creator_id + creator_type

            // Estructura jerárquica
            NestedSet::columns($table);   // Añade _lft, _rgt, parent_id

            $table->timestamps(); // created_at y updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
