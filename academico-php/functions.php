<?php

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exception\AMQPIOException;

function rabbitMqConnection(): AMQPStreamConnection
{
    do {
        try {
            $connection = new AMQPStreamConnection(
                getenv('RABBITMQ_HOST'),
                getenv('RABBITMQ_PORT'),
                getenv('RABBITMQ_USERNAME'),
                getenv('RABBITMQ_PASSWORD')
            );
        } catch (AMQPIOException) {
            sleep(5);
            echo 'Retrying' . PHP_EOL;
        }
    } while (!isset($connection));

    return $connection;
}
