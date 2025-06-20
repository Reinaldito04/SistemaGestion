<?php

namespace App\Services;

use Exception;
use App\Models\AmqpEvent;
use App\Rules\JsonObjectRule;
use Illuminate\Support\Facades\DB;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQService
{
    protected $connection;
    protected $channel;
    protected $defaultExchange;

    public function __construct()
    {
        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', '127.0.0.1'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest')
        );

        $this->channel = $this->connection->channel();
        $this->defaultExchange = 'default_exchange';

        // Declarar el exchange por defecto
        $this->declareExchange($this->defaultExchange, 'fanout');
    }

    public function declareExchange(string $exchange, string $type = 'direct')
    {
        $this->channel->exchange_declare($exchange, $type, false, true, false);
    }

    public function bindQueueToExchange(string $queue, string $exchange, string $routingKey = '')
    {
        // Declarar la cola
        $this->channel->queue_declare($queue, false, true, false, false);

        // Vincular la cola al exchange
        $this->channel->queue_bind($queue, $exchange, $routingKey);
    }

    private function publish(string $message, string $exchange = null, string $routingKey = '')
    {
        $exchange = $exchange ?? $this->defaultExchange;

        $msg = new AMQPMessage($message, [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
        ]);

        $this->channel->basic_publish($msg, $exchange, $routingKey);
    }

    public function consume(string $queue, callable $callback)
    {
        try {
            $this->channel->basic_consume($queue, '', false, true, false, false, $callback);

            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }
        } catch (\Exception $e) {
            // Registrar el mensaje del error en un log
            logger()->error('Error en el consumidor de RabbitMQ: ' . $e->getMessage());
            throw $e;
        }
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }

    public function sendEvent( array $data = [])
    {

        $rules = [
            'body' => ['required', new JsonObjectRule()],
            'exchange' => ['string', 'nullable'],
            'routing_key' => ['string', 'nullable'],
            'event_source' => ['string', 'nullable'],
        ];
        

        $this->validateData($data, $rules);


        $body = $data['body'];

        $body = is_array($body) ? json_encode($body) : $body; 

        $exchange = $data['exchange'] ?? $this->defaultExchange; 
        $routingKey=  $data['routing_key'] ?? '';


        try {
            DB::transaction(function () use ($body, $exchange, $routingKey, $data) {
                // Primero, guarda el evento en la base de datos
                $event = AmqpEvent::create([
                    'body' => $body,
                    'exchange' => $exchange,
                    'routing_key' => $routingKey,
                    'event_source' => $data['event_source'] ?? null,
                ]);
        
                // Luego, intenta publicar el evento
                $this->publish($body, $exchange, $routingKey);
        
                // Si todo va bien, la transacción se confirma automáticamente
            });
        } catch (Exception $e) {
            // Si ocurre cualquier error, la transacción hará rollback automáticamente
            //Log::error('Error al procesar el evento: ' . $e->getMessage()); // Opcional
            throw new \Exception('Error al procesar el evento: ' . $e->getMessage());
        }
        
    }


    private function validateData($data, $rules, $messages = [])
    {
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }  
}