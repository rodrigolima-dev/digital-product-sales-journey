<?php

namespace RodrigoLima\Academico\Application;

use RodrigoLima\Academico\Domain\Student;

final class EnrollStudent
{
    public function __construct(
        private StudentRepository $students,
        private EnrollmentNotifier $notifier,
    ) {
    }

    public function execute(string $name, string $email): void
    {
        $plainPassword = '123456';
        $student = new Student($name, $email, password_hash($plainPassword, PASSWORD_ARGON2ID));

        $this->students->save($student);
        $this->notifier->notify($student, $plainPassword);
    }
}
