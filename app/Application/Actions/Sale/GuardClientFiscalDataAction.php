<?php

declare(strict_types=1);

namespace App\Application\Actions\Sale;

use App\Domain\Exceptions\ClientMissingFiscalDataException;
use App\Domain\Repositories\ClientRepositoryInterface;

final readonly class GuardClientFiscalDataAction
{
    public function __construct(
        private ClientRepositoryInterface $clientRepository,
    ) {
    }

    /**
     * Valida que o cliente possui os dados fiscais obrigatórios para emissão de nota fiscal:
     *  - document_number (CPF ou CNPJ)
     *  - address
     *
     * Só executa se $needsInvoice for true.
     *
     * @throws ClientMissingFiscalDataException
     */
    public function execute(string $clientId, bool $needsInvoice): void
    {
        if (! $needsInvoice) {
            return;
        }

        $client = $this->clientRepository->findOrFail($clientId);

        if (empty($client->document_number) || empty($client->address)) {
            throw new ClientMissingFiscalDataException($clientId);
        }
    }
}
