<?php

namespace App\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    /**
     * Define qué clase se usará como modelo de comentarios.
     */
    protected function commentableModel(): string
    {
        return Comment::class;
    }

    /**
     * Comentarios raíz (sin padre).
     */
    public function comments(): MorphMany
    {
        return $this->morphMany($this->commentableModel(), 'commentable')
                    ->whereNull('parent_id');
    }

    /**
     * Todos los comentarios (incluye hijos).
     */
    public function allComments(): MorphMany
    {
        return $this->morphMany($this->commentableModel(), 'commentable');
    }

    /**
     * Comentarios raíz activos.
     */
    public function activeComments(): MorphMany
    {
        return $this->morphMany($this->commentableModel(), 'commentable')
                    ->whereNull('parent_id')
                    ->where('active', true);
    }

    /**
     * Todos los comentarios activos (incluye hijos).
     */
    public function allActiveComments(): MorphMany
    {
        return $this->morphMany($this->commentableModel(), 'commentable')
                    ->where('active', true);
    }

    /**
     * Crear comentario con opción de jerarquía.
     */
    public function comment(array $data, Model $creator, Model $parent = null): Comment
    {
        $comment = (new Comment())->createComment($this, $data, $creator);

        if ($parent) {
            $parent->appendNode($comment);
        }

        return $comment;
    }

    /**
     * Actualizar comentario.
     */
    public function updateComment(int|string $id, array $data, Model $parent = null): bool
    {
        $updated = (new Comment())->updateComment($id, $data);

        if ($parent && $updated) {
            $comment = Comment::find($id);
            $parent->appendNode($comment);
        }

        return $updated;
    }

    /**
     * Eliminar comentario.
     */
    public function deleteComment(int|string $id): bool
    {
        return (new Comment())->deleteComment($id);
    }

    /**
     * Total de comentarios (activos o no).
     */
    public function commentCount(): int
    {
        return $this->allComments()->count();
    }
}
