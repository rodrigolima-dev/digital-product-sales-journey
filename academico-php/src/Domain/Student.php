<?php

namespace RodrigoLima\Academico\Domain;

final class Student
{
    public function __construct(
        public string $name,
        public string $email,
        public string $passwordHash,
    ) {
    }
}
