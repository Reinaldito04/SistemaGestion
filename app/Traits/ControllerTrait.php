<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

trait ControllerTrait
{
    public function isDate($value)
    {
        try {
            \Carbon\Carbon::parse($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    
  public function find(Request $request, Builder $query, array $searchColumns)
{
    try {
        $searchInput = $request->input('search_input', null);
        $searchColumn = $request->input('search_column', null);

        $searchColumns = array_filter($searchColumns, fn($col) => !empty($col));

        if ($searchInput !== null && trim($searchInput) !== '') {
            if ($searchColumn && in_array($searchColumn, $searchColumns)) {
                // Búsqueda insensible a mayúsculas/minúsculas en la columna específica
                $query->whereRaw("LOWER($searchColumn) LIKE ?", ['%' . strtolower($searchInput) . '%']);
            } else {
                $query->where(function($query) use ($searchColumns, $searchInput) {
                    foreach ($searchColumns as $column) {
                        $query->orWhereRaw("LOWER($column) LIKE ?", ['%' . strtolower($searchInput) . '%']);
                    }
                });
            }
        }

        return $query;
    } catch (\Exception $e) {
        // Opcional: dd('Error en find', $e->getMessage());
        return $query;
    }
}


    public function isUuid($string)
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $string);
    }

    public function isId($value)
{
    return is_numeric($value) && $value > 0 && intval($value) == $value;
}



  public function paginate(Request $request, Builder $query, array $columns, $extraRules = [])
{
    // Normaliza todos los campos vacíos ('') a null
    foreach ($request->all() as $key => $value) {
        if ($request->has($key) && $value === '') {
            $request->merge([$key => null]);
        }
    }

    $defaultRules = [
        'paginate'          => ['sometimes', 'nullable', 'boolean'],
        'per_page'          => ['sometimes', 'nullable', 'integer', 'min:1'],
        'limit'             => ['sometimes', 'nullable', 'integer', 'min:1'],
        'order_column'      => ['sometimes', 'nullable', 'string', Rule::in($columns)],
        'order_direction'   => ['sometimes', 'nullable', 'string', Rule::in(['asc', 'desc'])],
        'search_columns'    => ['sometimes', 'nullable', 'array'],
        'search_columns.*'  => ['sometimes', 'nullable', 'string', Rule::in($columns)],
        'search_column'     => ['sometimes', 'nullable', 'string', Rule::in(array_merge($columns, ['*']))],
        'search_input'      => ['sometimes', 'nullable', 'string'],
        'page'              => ['sometimes', 'nullable', 'integer'],
    ];

    if ($extraRules) {
        $rules = array_merge($defaultRules, $extraRules);
    } else {
        $rules = $defaultRules;
    }

    $validated = $request->validate($rules);

    // Aquí aseguras que per_page tenga valor válido
    $perPage = intval($request->input('per_page', 10));
    if ($perPage < 1) {
        $perPage = 10;
    }

    // Lo mismo para limit si lo usas
    $limit = intval($request->input('limit', 1000));
    if ($limit < 1) {
        $limit = 1000;
    }

    $page = $request->input('page', 1);
    $paginate = $request->input('paginate', true);
    $orderBy = $request->input('order_column', null);
    $orderDirection = $request->input('order_direction', 'asc');

    try {
        $query->when($orderBy, function ($query, $orderBy) use ($orderDirection) {
            return $query->orderBy($orderBy, $orderDirection);
        });

        if ($paginate) {
            $results = $query->paginate($perPage, ['*'], 'page', $page);
            $results->appends($request->except('page'));
        } else {
            $results = $query->limit($limit)->get();
            $results = ['data' => $results->toArray()];
        }

        return $results;
    } catch (\Exception $e) {
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, $page, [
            'path' => \Request::url(),
            'query' => ['page' => $page]
        ]);
    }
}
    
    

    

public function retrieveByUuid(Builder $query, $uuid)
{
    if (!$this->isUuid($uuid)) {
        throw new \InvalidArgumentException('UUID invalido: ' . $uuid);
    }
    $resource = $query->where('uuid', $uuid)->first();
    if (!$resource) {
        throw new \Exception('Registro no encontrado');
    }
    if ($resource->offsetExists('id')) {
        $resource->makeHidden('id');
    }
    return $resource;
}

public function retrieveById(Builder $query, $id)
{
    if (!$this->isId($id)) {
        throw new \InvalidArgumentException('id invalido: ' . $id);
    }
    $resource = $query->where('id', $id)->first();
    if (!$resource) {
        throw new \Exception('Registro no encontrado');
    }

    return $resource;
}

public function eraseByUuid(Builder $query, $uuid)
{
    if (!$this->isUuid($uuid)) {
        throw new \InvalidArgumentException('UUID invalido: ' . $uuid);
    }
    $resource = $query->where('uuid', $uuid)->first();
    if (!$resource) {
        throw new \Exception('Registro no encontrado');
    }
    $resource->delete();
    return response()->json(['message' => 'Registro eliminado correctamente']);
}


    
    
}
