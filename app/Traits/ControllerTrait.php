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
public function find(Request $request, Builder $query, array $searchableColumns, array $searchablePeriodColumns = [])
{
    $this->validateFindRequest($request, $searchableColumns, $searchablePeriodColumns);

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
    }

    // Búsqueda tipo LIKE para search_input
    if ($searchInput !== null && trim($searchInput) !== '' && !empty($searchColumns)) {
        $query->where(function($query) use ($searchColumns, $searchInput) {
            foreach ($searchColumns as $column) {
                $query->orWhereRaw("LOWER($column) LIKE ?", ['%' . strtolower($searchInput) . '%']);
            }
        });
    }

    // Filtros de periodo 
    if (!empty($searchablePeriodColumns) && $request->filled('period_filters') && is_array($request->input('period_filters'))) {
        foreach ($request->input('period_filters') as $filter) {
            $column = $filter['column'] ?? null;
            $start = $filter['start'] ?? null;
            $end   = $filter['end'] ?? null;
            if ($column && in_array($column, $searchablePeriodColumns)) {
                if ($start && $end) {
                    $query->whereBetween($column, [
                        $start . ' 00:00:00',
                        $end . ' 23:59:59'
                    ]);
                } elseif ($start) {
                    $query->where($column, '>=', $start . ' 00:00:00');
                } elseif ($end) {
                    $query->where($column, '<=', $end . ' 23:59:59');
                }
            }
        }
    }

    return $query;
}
    /**
     * Valida los campos para el método find
     */
   protected function validateFindRequest(Request $request, array $columns, array $periodColumns = [])
{
    $rules = [
        'search_columns'    => ['sometimes', 'nullable', 'array'],
        'search_columns.*'  => ['sometimes', 'nullable', 'string', Rule::in($columns)],
        'search_input'      => ['sometimes', 'nullable', 'string'],
    ];

    $messages = [
        'search_columns.*.in' => 'La columna ":input" no es válida. Columnas válidas: ' . implode(', ', $columns),
    ];

    if (!empty($periodColumns)) {
        $rules['period_filters'] = ['sometimes', 'nullable', 'array'];
        $rules['period_filters.*.column'] = ['required_with:period_filters', 'string', Rule::in($periodColumns)];
        $rules['period_filters.*.start']  = ['nullable', 'date_format:Y-m-d'];
        $rules['period_filters.*.end']    = ['nullable', 'date_format:Y-m-d', 'after_or_equal:period_filters.*.start'];

        $messages['period_filters.*.column.in'] = 'La columna de periodo ":input" no es válida. Columnas válidas: ' . implode(', ', $periodColumns);
        $messages['period_filters.*.start.date_format'] = 'La fecha de inicio debe tener el formato Y-m-d.';
        $messages['period_filters.*.end.date_format'] = 'La fecha de fin debe tener el formato Y-m-d.';
        $messages['period_filters.*.end.after_or_equal'] = 'La fecha de fin debe ser igual o posterior a la fecha de inicio.';
    }

    // Validación adicional: si hay columna, debe haber al menos start o end
    $data = $request->all();
    if (!empty($periodColumns) && !empty($data['period_filters']) && is_array($data['period_filters'])) {
        foreach ($data['period_filters'] as $idx => $filter) {
            if (
                !empty($filter['column']) &&
                empty($filter['start']) &&
                empty($filter['end'])
            ) {
                // Lanza error de validación para este índice
                $msg = "En el filtro de periodo #".($idx+1).", si especificas la columna debes indicar al menos una de las fechas (start o end).";
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "period_filters.$idx" => [$msg]
                ]);
            }
        }
    }

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