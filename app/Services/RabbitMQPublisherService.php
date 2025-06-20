<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQPublisherService
{
    protected $connection;
    protected $channel;
    protected $exchangeName;
    protected $queueName;

    public function __construct()
    {
        $this->exchangeName = env('RABBITMQ_EXCHANGE', 'inventory_exchange');
        $this->queueName = env('RABBITMQ_QUEUE', 'jobs_queue');

        $this->connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', 'localhost'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest'),
            env('RABBITMQ_VHOST', '/')
        );

        $this->channel = $this->connection->channel();

        // Declarar exchange tipo topic
        $this->channel->exchange_declare(
            $this->exchangeName,
            'topic',
            false,
            true,
            false
        );

        // Asegurar que la cola existe (no la crea si ya existe)
        $this->channel->queue_declare(
            $this->queueName,
            false, // passive
            true,  // durable
            false, // exclusive
            false  // auto-delete
        );

        // Crear binding entre exchange y cola - ESTA ES LA PARTE CLAVE QUE FALTA
        $this->channel->queue_bind(
            $this->queueName,         // nombre de la cola
            $this->exchangeName,      // nombre del exchange
            'inventory.stock.update'  // routing key
        );
    }

    public function publishMessage($routingKey, $data)
    {
        $message = new AMQPMessage(
            json_encode($data),
            ['content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
        );

        $this->channel->basic_publish($message, $this->exchangeName, $routingKey);

        return true;
    }

    public function close()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
