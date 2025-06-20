<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class HttpRedirectorService
{
    protected $client;

    public function __construct($config)
    {
        $this->client = new Client($config);
    }

    public function get($endpoint, $query)
    {
        try {
            $response = $this->client->request('GET', $endpoint, ['query' => $query]);
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (ClientException | ServerException $e) {
            $data = json_decode($e->getResponse()->getBody()->getContents(), true);
        }

        return $data;
    }

    public function post($endpoint, $json)
    {
        try {
            $response = $this->client->request('POST', $endpoint, ['json' => $json]);
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (ClientException | ServerException $e) {
            $data = json_decode($e->getResponse()->getBody()->getContents(), true);
        }

        return $data;
    }

    public function put($endpoint, $json)
    {
        try {
            $response = $this->client->request('PUT', $endpoint, ['json' => $json]);
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (ClientException | ServerException $e) {
            $data = json_decode($e->getResponse()->getBody()->getContents(), true);
        }
        return $data;
    }

    public function delete($endpoint, $query = [])
    {
        try {
            $response = $this->client->request('DELETE', $endpoint, ['query' => $query]);
            $data = json_decode($response->getBody()->getContents(), true);
        } catch (ClientException | ServerException $e) {
            $data = json_decode($e->getResponse()->getBody()->getContents(), true);
        }
        return $data;
    }
}
