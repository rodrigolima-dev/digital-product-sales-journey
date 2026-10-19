<?php

require_once 'vendor/autoload.php';

use RodrigoLima\Academico\Application\EnrollStudent;
use RodrigoLima\Academico\Infrastructure\Messaging\RabbitMqEnrollmentConsumer;
use RodrigoLima\Academico\Infrastructure\Notification\SymfonyEnrollmentNotifier;
use RodrigoLima\Academico\Infrastructure\Persistence\RedBeanStudentRepository;

$students = new RedBeanStudentRepository(getenv('DB_DSN'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));
$notifier = new SymfonyEnrollmentNotifier(getenv('MAILER_DSN'), getenv('MAIL_FROM'));
$enrollStudent = new EnrollStudent($students, $notifier);

(new RabbitMqEnrollmentConsumer(rabbitMqConnection(), $enrollStudent))->run();
