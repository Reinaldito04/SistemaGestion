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

    /**
     * Realiza búsqueda por los campos enviados en el request (search_columns[]).
     * Si no se envían, usa los definidos por el programador ($searchableColumns).
     * Solo permite usar columnas que estén en el array $searchableColumns.
     * Si alguna columna del request no es válida, lanza excepción y muestra las válidas.
     * El request es validado antes de ejecutar la lógica.
     */
    public function find(Request $request, Builder $query, array $searchableColumns)
    {
        $this->validateFindRequest($request, $searchableColumns);

        $searchInput = $request->input('search_input', null);

        $requestedColumns = $request->input('search_columns', []);
        if (is_string($requestedColumns)) {
            $requestedColumns = [$requestedColumns];
        }
        $requestedColumns = array_filter((array)$requestedColumns, fn($col) => !empty($col));

        if (empty($requestedColumns)) {
            $searchColumns = $searchableColumns;
        } else {
            $searchColumns = array_intersect($requestedColumns, $searchableColumns);

            // Esto ya no es necesario, porque el validateFindRequest lo valida antes
            // $invalidColumns = array_diff($requestedColumns, $searchableColumns);
            // if (count($invalidColumns)) {
            //     throw new \InvalidArgumentException(
            //         'Columna(s) no válida(s) en search_columns: ' . implode(', ', $invalidColumns) .
            //         '. Las columnas válidas son: ' . implode(', ', $searchableColumns)
            //     );
            // }
        }

        if ($searchInput !== null && trim($searchInput) !== '' && !empty($searchColumns)) {
            $query->where(function($query) use ($searchColumns, $searchInput) {
                foreach ($searchColumns as $column) {
                    $query->orWhereRaw("LOWER($column) LIKE ?", ['%' . strtolower($searchInput) . '%']);
                }
            });
        }

        return $query;
    }

    /**
     * Valida los campos para el método find
     */
        protected function validateFindRequest(Request $request, array $columns)
        {
            $rules = [
                'search_columns'    => ['sometimes', 'nullable', 'array'],
                'search_columns.*'  => ['sometimes', 'nullable', 'string', Rule::in($columns)],
                'search_input'      => ['sometimes', 'nullable', 'string'],
            ];

            $messages = [
                'search_columns.*.in' => 'La columna ":input" no es válida. Columnas válidas: ' . implode(', ', $columns),
            ];

            $request->validate($rules, $messages);
        }

    public function isUuid($string)
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $string);
    }

    public function isId($value)
    {
        return is_numeric($value) && $value > 0 && intval($value) == $value;
    }

    /**
     * Paginación simple. Valida los parámetros necesarios antes de paginar.
     */
    public function paginate(Request $request, Builder $query, array $columns)
    {
        $this->validatePaginateRequest($request, $columns);

        $perPage = intval($request->input('per_page', 10));
        if ($perPage < 1) $perPage = 10;

        $limit = intval($request->input('limit', 1000));
        if ($limit < 1) $limit = 1000;

        $page = $request->input('page', 1);
        $paginate = $request->input('paginate', true);
        $orderBy = $request->input('order_column', null);
        $orderDirection = $request->input('order_direction', 'asc');

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
    }

    /**
     * Valida los campos para el método paginate
     */
    protected function validatePaginateRequest(Request $request, array $columns)
    {
        $rules = [
            'paginate'          => ['sometimes', 'nullable', 'boolean'],
            'per_page'          => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit'             => ['sometimes', 'nullable', 'integer', 'min:1'],
            'order_column'      => ['sometimes', 'nullable', 'string', Rule::in($columns)],
            'order_direction'   => ['sometimes', 'nullable', 'string', Rule::in(['asc', 'desc'])],
            'search_column'     => ['sometimes', 'nullable', 'string', Rule::in(array_merge($columns, ['*']))],
            'page'              => ['sometimes', 'nullable', 'integer'],
        ];
        $request->validate($rules);
    }

    public function retrieveByUuid(Builder $query, $uuid)
    {
        if (!$this->isUuid($uuid)) {
            return response()->json(['message' => 'UUID inválido'], 400);
        }
        $resource = $query->where('uuid', $uuid)->first();
        if (!$resource) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
        if ($resource->offsetExists('id')) {
            $resource->makeHidden('id');
        }
        return $resource;
    }
    public function retrieveById(Builder $query, $id)
    {
        if (!$this->isId($id)) {
            // Lanza una excepción, no retornes un response
            throw new \InvalidArgumentException('ID inválido: ' . $id);
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
            return response()->json(['message' => 'UUID inválido'], 400);
        }
        $resource = $query->where('uuid', $uuid)->first();
        if (!$resource) {
            return response()->json(['message' => 'Registro no encontrado'], 404);
        }
        $resource->delete();
        return response()->json(['message' => 'Registro eliminado correctamente']);
    }

    public function eraseById(Builder $query, $id)
{
    if (!$this->isId($id)) {
        return response()->json(['message' => 'ID inválido'], 400);
    }
    $resource = $query->where('id', $id)->first();
    if (!$resource) {
      return response()->json(['message' => 'Registro no encontrado'], 404);
    }
    $resource->delete();
    return response()->json(['message' => 'Registro eliminado correctamente']);
}
}