<?php

namespace App\Services;

use Log;
use Illuminate\Support\Facades\Validator;

class ApiOperacionesService
{
    protected $redirector;
    protected $headers;

  
    protected $token;

    public function __construct()
{
    $base_uri = env('OPERACIONES_API_URI');
    $this->token = env('OPERACIONES_API_TOKEN'); // Asegúrate de definir esto en tu archivo .env
    $config = [
        'base_uri' => $base_uri,
        'timeout' => 120
    ];

    $this->redirector = new HttpRedirectorService($config);
    $this->headers = [
        'Authorization' => 'Bearer ' . $this->token,
        'Accept'        => 'application/json'
    ];
}

public function NewUser($query = [], $data = [], $headers = [])
{
    $endpoint = 'form-ventas';
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);

    return $this->redirector->post($endpoint, $query, $data, $headers);
}


public function getSelectorPlans($query = [], $data = [], $headers = [])
{
    $endpoint = 'selectors/0193734f-312c-73c2-aafd-c0cd339e23eb';
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);


    $response =$this->redirector->get($endpoint, $query, $data, $headers);
    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];


    return  $data;
}

public function getSelectorPromotionType($query = [], $data = [], $headers = [])
{
    $endpoint = 'selectors/01937981-6f6b-7e62-ab69-17eda3922b4d';
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);
    $response =$this->redirector->get($endpoint, $query, $data, $headers);
    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];
    return $data;
}

public function getSelectorInstalationType($query = [], $data = [], $headers = [])
{
    $endpoint = 'selectors/0193734f-312c-73c2-aafd-c0ccb16f34d3';
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);
    $response =$this->redirector->get($endpoint, $query, $data, $headers);
    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];
    return $data;

}

public function getSelectorPaymentType($query = [], $data = [], $headers = [])
{
    $endpoint = 'selectors/0193921e-e718-7a84-bdde-b8320318681c';
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);


    $response =$this->redirector->get($endpoint, $query, $data, $headers);
    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];
    return $data;
}

public function getCheckVialabilityType($query = [], $data = [], $headers = [])
{
    $endpoint = 'selectors/0194655d-17da-78db-87b5-9258046d9d03';
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);


    $response =$this->redirector->get($endpoint, $query, $data, $headers);
    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];
    return $data;
}

public function getZones($query = [], $data = [], $headers = [])
{
    $endpoint = 'selectors/0195a9dd-2d1e-734a-ba97-bcb69bd18980';
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);


    $response =$this->redirector->get($endpoint, $query, $data, $headers);
    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];
    return $data;
}


public function getCategories($query = [], $data = [], $headers = [])
{
    $endpoint = 'selectors/0195a9a1-a0e9-79f9-ae62-e8d9e4f0fb78';
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);


    $response =$this->redirector->get($endpoint, $query, $data, $headers);
    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];
    return $data;
}



public function getSelectorOption($uuid, $query = [], $data = [], $headers = [])
{
    $endpoint = 'selectors/options/';
    $endpoint = $endpoint . $uuid;
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);

    $response =$this->redirector->get($endpoint, $query, $data, $headers);

    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];
    return $data;
}

public function facturarMetraje($query = [], $data = [], $headers = [])
{
    $endpoint = 'mikrowisp/facturar-metraje';

    
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);

    $response =$this->redirector->post($endpoint, $query, $data, $headers);


    return $response;
}


public function getTicketInvoices($uuid, $query = [], $data = [], $headers = [])
{
    $endpoint = 'form-ventas/invoices/';
    $endpoint = $endpoint . $uuid;
  
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);

    $response =$this->redirector->get($endpoint, $query, $data, $headers);

    
    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];
    return $data;
}


public function getUserMikrowisp(string $id)
{
    $endpoint = 'mikrowisp/user/' . $id;
    $query = [];
    $data = [];
    $headers = [];
   
    $headers = array_merge(['Authorization' => 'Bearer ' . $this->token], $this->headers);


    $response =$this->redirector->get($endpoint, $query, $data, $headers);
    $content = $response->content();
    $content = json_decode($content, true);
    $data = $content['data'] ?? [];
    return $data;
}


}

