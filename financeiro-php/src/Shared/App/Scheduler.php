<?php

namespace RodrigoLima\Financeiro\Shared\App;

interface Scheduler
{
    public function schedule(string $taskName, mixed $data): void;
}
