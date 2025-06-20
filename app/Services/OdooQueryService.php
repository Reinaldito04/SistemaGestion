<?php

namespace App\Services;

use Illuminate\Http\Request;

class OdooQueryService
{
    /**
     * Obtiene la configuración general para la consulta a Odoo.
     *
     * @param Request $request
     * @return array
     */

    public function parseRequest(Request $request, string $model): array
    {

        $validated = $this->validateRequest($request);

        // Determinar si se requiere paginación.
        $paginate = $this->getPaginate($validated);

        return [
            'model'          => $model,
            'limit'          => $paginate ? $this->getLimit($validated) : null,
            'page'           => $paginate ? $this->getPage($validated) : null,
            'offset'         => $paginate ? $this->getOffset($validated) : 0,
            'paginate'       => $paginate,
            'orderColumn'    => $validated['order_column']    ?? 'id',
            'orderDirection' => $validated['order_direction'] ?? 'asc',
            'fields'         => $this->getFields($validated),
            'domain'         => $this->buildDomain($validated)
        ];
    }


    /**
     * Realiza la validación de parámetros de la petición.
     *
     * @param Request $request
     * @return array
     */
    protected function validateRequest(Request $request): array
    {
        return $request->validate([
            'per_page'             => 'sometimes|integer|min:1|max:100000',
            'page'                 => 'sometimes|integer|min:1',
            'order_column'         => 'sometimes|string',
            'order_direction'      => 'sometimes|in:asc,desc',
            'paginate'             => 'sometimes|boolean',
            'search_column'        => 'sometimes|string',
            'search_input'         => 'sometimes|string',
            'search_operator'      => 'sometimes|in:contains,equals,starts_with,ends_with,greater_than,less_than',
            'search_case_sensitive'=> 'sometimes|boolean',
            'model'                => 'sometimes|string',
            'fields'               => 'sometimes|string',
            'filter'               => 'sometimes|array',
        ]);
    }

    /**
     * Obtiene el límite de registros para la consulta.
     *
     * @param array $validated
     * @return int
     */
    protected function getLimit(array $validated): int
    {
        // Si no se pagina, no se retorna un límite
        return (int)($validated['per_page'] ?? 10);
    }

    /**
     * Obtiene la página actual.
     *
     * @param array $validated
     * @return int
     */
    protected function getPage(array $validated): int
    {
        return (int)($validated['page'] ?? 1);
    }

    /**
     * Calcula el offset basado en la página y el límite.
     *
     * @param array $validated
     * @return int
     */
    protected function getOffset(array $validated): int
    {
        $page = $this->getPage($validated);
        $limit = $this->getLimit($validated);
        return ($page - 1) * $limit;
    }

    /**
     * Determina si se debe aplicar la paginación.
     *
     * Si se envía paginate como 0 o false, se interpreta como sin paginación.
     *
     * @param array $validated
     * @return bool
     */
    protected function getPaginate(array $validated): bool
    {
        // Se evalúa en base a la existencia y valor de paginate en el request.
        // Teniendo en cuenta que paginate puede venir como 0 (falso) o 1 (verdadero).
        return isset($validated['paginate'])
            ? (bool)$validated['paginate']
            : true;
    }

    /**
     * Procesa los campos a recuperar y los devuelve en un array.
     *
     * @param array $validated
     * @return array
     */
    protected function getFields(array $validated): array
    {
        if (!empty($validated['fields'])) {
            return array_map('trim', explode(',', $validated['fields']));
        }
        return [];
    }

    /**
     * Construye el dominio de búsqueda con operadores y filtros.
     *
     * @param array $validated
     * @return array
     */
    protected function buildDomain(array $validated): array
    {
        $domain = [];

        // Filtros extra
        if (isset($validated['filter']) && is_array($validated['filter'])) {
            foreach ($validated['filter'] as $filter) {
                if (!empty($filter['field'] ?? '') && isset($filter['value'])) {
                    $operator = $filter['operator'] ?? '=';
                    $domain[] = [$filter['field'], $operator, $filter['value']];
                }
            }
        }

        // Parámetros de búsqueda
        if (!empty($validated['search_input'] ?? '') && !empty($validated['search_column'] ?? '')) {
            $operator = $this->mapSearchOperatorToOdoo($validated['search_operator'] ?? 'contains');
            $value = $this->formatSearchValue($validated['search_operator'] ?? 'contains', $validated['search_input']);

            // Ajuste por caso sensible
            $caseSensitive = $validated['search_case_sensitive'] ?? false;
            if (!$caseSensitive && in_array($operator, ['like', 'ilike', '=like', '=ilike'])) {
                $operator = str_replace('like', 'ilike', $operator);
            }
            $domain[] = [$validated['search_column'], $operator, $value];
        }
        return $domain;
    }

    /**
     * Mapea el operador de búsqueda a los operadores que Odoo espera.
     *
     * @param string $operator
     * @return string
     */
    protected function mapSearchOperatorToOdoo(string $operator): string
    {
        $map = [
            'contains'     => 'like',
            'equals'       => '=',
            'starts_with'  => 'like',
            'ends_with'    => 'like',
            'greater_than' => '>',
            'less_than'    => '<',
        ];

        return $map[$operator] ?? 'like';
    }

    /**
     * Formatea el valor de búsqueda según el operador.
     *
     * @param string $operator
     * @param string $value
     * @return string
     */
    protected function formatSearchValue(string $operator, string $value): string
    {
        switch ($operator) {
            case 'starts_with':
                return "$value%";
            case 'ends_with':
                return "%$value";
            case 'contains':
                return "%$value%";
            case 'equals':
                return $value;
            default:
                return $value;
        }
    }

    public function buildMeta(array $queryParams, int $totalCount): array
    {
        $limit = $queryParams['limit'];
        $page = $queryParams['page'];

        return [
            'total' => $totalCount,
            'per_page' => $limit,
            'current_page' => $page,
            'last_page' => $limit > 0 ? ceil($totalCount / $limit) : 1,
            'from' => ($page - 1) * $limit + 1,
            'to' => min($page * $limit, $totalCount)
        ];
    }


    public function getTotalCount(array $queryParams, array $response, ApiOdooHttpRedirectorService $apiOdoo): int
    {
        if (!$queryParams['paginate']) {
            return count($response['result'] ?? []);
        }

        $countResponse = $apiOdoo->executeKw(
            $queryParams['model'],
            'search_count',
            [$queryParams['domain']]
        );

        return $countResponse['result'] ?? 0;
    }

}
