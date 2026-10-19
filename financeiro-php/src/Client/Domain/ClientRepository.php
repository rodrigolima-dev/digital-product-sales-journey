<?php

namespace RodrigoLima\Financeiro\Client\Domain;

interface ClientRepository
{
    public function add(Client $client): void;
}
