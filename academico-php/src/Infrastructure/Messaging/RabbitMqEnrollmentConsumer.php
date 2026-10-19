<?php

namespace RodrigoLima\Academico\Infrastructure\Messaging;

use RodrigoLima\Academico\Application\EnrollStudent;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class RabbitMqEnrollmentConsumer
{
    public function __construct(
        private AMQPStreamConnection $connection,
        private EnrollStudent $enrollStudent,
    ) {
    }

    public function run(): void
    {
        $channel = $this->connection->channel();

        $queue = 'student_enrollment';
        $channel->exchange_declare('client_enrolled', 'fanout', durable: true, auto_delete: false);
        $channel->queue_declare($queue, durable: true, exclusive: false, auto_delete: false);
        $channel->queue_bind($queue, 'client_enrolled');
        $channel->basic_consume($queue, no_ack: true, callback: function (AMQPMessage $msg): void {
            $properties = json_decode($msg->body, true);
            $this->enrollStudent->execute($properties['name'], $properties['email']);
            echo 'Matrícula processada' . PHP_EOL;
        });

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $this->connection->close();
    }
}
