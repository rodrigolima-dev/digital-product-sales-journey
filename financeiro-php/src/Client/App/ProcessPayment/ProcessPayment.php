<?php

namespace RodrigoLima\Financeiro\Client\App\ProcessPayment;

use RodrigoLima\Financeiro\Client\App\EnrollClient\EnrollClientInputData;
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

class ProcessPayment
{
    public function __construct(
        private ClientRepository $clientRepository,
        private MessagingQueue $messagingQueue,
    ) {
    }

    public function __invoke(EnrollClientInputData $data)
    {
        $document = new Document($data->clientDocument);
        $cardInfo = new CardInformation(
            new OwnerFullName($data->cardOwnerFullName),
            new CardNumber($data->cardNumber),
            new CardExpirationDate($data->cardExpirationMonth, $data->cardExpirationYear),
            new SecurityCode($data->cardSecurityCode),
        );
        $email = new Email($data->email);
        $client = new Client($document, $cardInfo, $email);

        $this->clientRepository->add($client);

        $this->messagingQueue->send(
            json_encode([
                'cpf' => $client->documentNumber(),
                'name' => $client->fullName(),
                'email' => $client->email(),
            ]),
            'client_enrolled'
        );
    }
}
