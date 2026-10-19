<?php

namespace RodrigoLima\Academico\Infrastructure\Notification;

use RodrigoLima\Academico\Application\EnrollmentNotifier;
use RodrigoLima\Academico\Domain\Student;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

final class SymfonyEnrollmentNotifier implements EnrollmentNotifier
{
    public function __construct(private string $dsn, private string $from)
    {
    }

    public function notify(Student $student, string $plainPassword): void
    {
        $mensagem = <<<FIM
    Olá, $student->name! Seu pagamento foi confirmado e sua matrícula foi criada com sucesso.
    Para acessar sua conta e começar a estudar conosco, acesse: http://localhost:4200/login.
    Seus dados de acesso são:
    E-mail: $student->email
    Senha: $plainPassword

    Bons estudos!
    FIM;

        $email = (new Email())
            ->from($this->from)
            ->to($student->email)
            ->subject('Matrícula confirmada')
            ->text($mensagem);

        $transport = Transport::fromDsn($this->dsn);
        $mailer = new Mailer($transport);
        $mailer->send($email);
    }
}
