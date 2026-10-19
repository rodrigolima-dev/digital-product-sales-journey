<?php

namespace RodrigoLima\Academico\Application;

use RodrigoLima\Academico\Domain\Student;

interface StudentRepository
{
    public function save(Student $student): void;
}
