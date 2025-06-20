<?php

namespace App\Services;

use App\Services\HttpRedirectorService;

class ApiOdooHttpRedirectorService
{
    protected $redirector;
    protected $database;
    protected $userId;
    protected $password;

    public function __construct()
    {
        $base_uri = env('API_ODOO_BASE_URI');

        $this->database = env('API_ODOO_DATABASE');
        $this->userId = (int)env('API_ODOO_USER_ID');
        $this->password = env('API_ODOO_PASSWORD');

        $config = [
            'base_uri' => $base_uri,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'timeout' => 120
        ];
        $this->redirector = new HttpRedirectorService($config);
    }

    /**
     * Ejecuta un método en un modelo de Odoo usando JSON-RPC
     *
     * @param string $model Nombre del modelo
     * @param string $method Método a ejecutar
     * @param array $args Argumentos adicionales para el método
     * @param array $kwargs Argumentos con nombre para el método
     * @return array Respuesta de Odoo
     */
    public function executeKw(string $model, string $method, array $args = [], array $kwargs = [])
    {
        $payload = [
            'jsonrpc' => '2.0',
            'method' => 'call',
            'params' => [
                'service' => 'object',
                'method' => 'execute_kw',
                'args' => [
                    $this->database,
                    $this->userId,
                    $this->password,
                    $model,
                    $method,
                    $args,
                    $kwargs
                ]
            ]
        ];

        return $this->redirector->post('', $payload);
    }

    /**
     * Busca y lee registros de un modelo con valores por defecto
     *
     * @param string $model Nombre del modelo
     * @param array $domain Filtros de búsqueda
     * @param array $fields Campos a retornar (vacío para todos)
     * @param int $limit Número máximo de registros a retornar
     * @param int $offset Número de registros a omitir
     * @param string $order Cláusula de ordenamiento
     * @return array Respuesta de Odoo
     */
    public function searchRead(
        string $model,
        array $domain = [],
        array $fields = [],
        ?int $limit = 10,
        int $offset = 0,
        string $orderColumn = 'id',
        string $orderDirection = 'asc',
        array $context = ['lang' => 'es_419']
    ) {
        $options = [
            'fields' => $fields,
            'order'  => "{$orderColumn} {$orderDirection}",
        ];

        // Solo agregar limit y offset si limit es mayor a 0
        if($limit > 0) {
            $options['limit'] = $limit;
            $options['offset'] = $offset;
        }

        $options['context'] = $context;

        return $this->executeKw($model, 'search_read', [$domain], $options);
    }


    /**
     * Crea un registro en un modelo
     *
     * @param string $model Nombre del modelo
     * @param array $values Valores para el nuevo registro
     * @return array Respuesta de Odoo
     */
    public function create(string $model, array $values)
    {
        return $this->executeKw($model, 'create', [$values]);
    }

    /**
     * Actualiza registros en un modelo
     *
     * @param string $model Nombre del modelo
     * @param int|array $ids ID(s) del registro a actualizar
     * @param array $values Valores a actualizar
     * @return array Respuesta de Odoo
     */
    public function write(string $model, $ids, array $values)
    {
        $ids = is_array($ids) ? $ids : [$ids];
        return $this->executeKw($model, 'write', [$ids, $values]);
    }

    /**
     * Elimina registros de un modelo
     *
     * @param string $model Nombre del modelo
     * @param int|array $ids ID(s) del registro a eliminar
     * @return array Respuesta de Odoo
     */
    public function unlink(string $model, $ids)
    {
        $ids = is_array($ids) ? $ids : [$ids];
        return $this->executeKw($model, 'unlink', [$ids]);
    }

    public function validateResponse(array $response, bool $validateResult = false)
    {
        if (isset($response['error'])) {
            return response()->json([
                'success' => false,
                'message' => $response['error']['data']['message'] ?? 'Error en la consulta a Odoo',
                'data'    => null
            ], 500);
        }

        if (!!$validateResult && (empty($response['result']) || !is_numeric($response['result']))) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto: respuesta inesperada en Odoo.',
                'data'    => null
            ], 500);
        }

        return $response['result'];
    }


    // Mantener métodos originales para compatibilidad
    public function get($endpoint, $query)
    {
        return $this->redirector->get($endpoint, $query);
    }

    public function post($endpoint, $json)
    {
        return $this->redirector->post($endpoint, $json);
    }

    public function put($endpoint, $json)
    {
        return $this->redirector->put($endpoint, $json);
    }

    public function delete($endpoint, $query)
    {
        return $this->redirector->delete($endpoint, $query);
    }
}
