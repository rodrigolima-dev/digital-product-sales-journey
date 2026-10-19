<?php

namespace RodrigoLima\Academico\Application;

use RodrigoLima\Academico\Domain\Student;

interface EnrollmentNotifier
{
    public function notify(Student $student, string $plainPassword): void;
}
