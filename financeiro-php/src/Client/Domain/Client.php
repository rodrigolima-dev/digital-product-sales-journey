<?php

namespace RodrigoLima\Financeiro\Client\Domain;

use RodrigoLima\Financeiro\Client\Domain\Card\CardInformation;

class Client
{
    public function __construct(
        private Document $document,
        private CardInformation $cardInfo,
        private Email $email
    ) {
    }

    public function fullName(): string
    {
        return $this->cardInfo->ownerFullName();
    }

    public function documentNumber(): string
    {
        return $this->document;
    }

    public function email(): string
    {
        return $this->email;
    }
}
