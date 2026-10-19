<?php

namespace RodrigoLima\Academico\Infrastructure\Persistence;

use RodrigoLima\Academico\Application\StudentRepository;
use RodrigoLima\Academico\Domain\Student;

final class RedBeanStudentRepository implements StudentRepository
{
    public function __construct(string $dsn, string $username, string $password)
    {
        require_once dirname(__DIR__, 3) . '/rb.php';
        \R::setup($dsn, $username, $password);
    }

    public function save(Student $student): void
    {
        $record = \R::dispense('students');
        $record->name = $student->name;
        $record->email = $student->email;
        $record->password = $student->passwordHash;
        \R::store($record);
    }
}
