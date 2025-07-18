<?php

namespace App\Http\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/*
 * Un trait es una estructura que te permite agrupar métodos para compartirlos entre múltiples clases, sin usar herencia.
 * Es útil cuando:
 * Necesitas reutilizar código en diferentes clases.
 * No puedes heredar de una clase común (porque PHP solo permite una clase padre por clase).
 * Quieres separar responsabilidades y mantener tu código organizado.
 *
 */


trait CanLoadRelationships
{
    public function loadRelationships(
        Model|QueryBuilder|EloquentBuilder|HasMany $for,
        ?array $relations = null
    ): Model|EloquentBuilder|QueryBuilder|HasMany {
        $relations = $relations ?? $this->relations ?? [];

        foreach ($relations as $relation) {
            $for->when($this->shouldIncludeRelation($relation),
                fn($q) => $for instanceof Model ? $for->load($relation) : $q->with($relation));
        }

        return $for;
    }

    protected function shouldIncludeRelation(string $relation): bool
    {
        $include = request()->query('include');

        if (!$include) {
            return false;
        }

        $relations = array_map('trim', explode(',', $include));

        return in_array($relation, $relations, true);
    }
}