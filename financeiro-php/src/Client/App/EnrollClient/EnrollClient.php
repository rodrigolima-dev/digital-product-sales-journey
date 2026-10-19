<?php

namespace RodrigoLima\Financeiro\Client\App\EnrollClient;

use RodrigoLima\Financeiro\Client\Domain\Card\CardExpirationDate;
use RodrigoLima\Financeiro\Client\Domain\Card\CardInformation;
use RodrigoLima\Financeiro\Client\Domain\Card\CardNumber;
use RodrigoLima\Financeiro\Client\Domain\Card\OwnerFullName;
use RodrigoLima\Financeiro\Client\Domain\Card\SecurityCode;
use RodrigoLima\Financeiro\Client\Domain\Client;
use RodrigoLima\Financeiro\Client\Domain\ClientRepository;
use RodrigoLima\Financeiro\Client\Domain\Document;
use RodrigoLima\Financeiro\Client\Domain\Email;
use RodrigoLima\Financeiro\Shared\App\MessagingQueue;
use RodrigoLima\Financeiro\Shared\App\Scheduler;

class EnrollClient
{
    public function __construct(
        private Scheduler $taskScheduler
    ) {
    }

    public function __invoke(EnrollClientInputData $data): void
    {
        $this->taskScheduler->schedule('process_payment', $data);
    }
}
