<?php

declare(strict_types=1);

namespace App\Application\UseCases\Client;

use App\Application\DTOs\ClientDTO;
use App\Domain\Exceptions\ClientDocumentAlreadyExistsException;
use App\Domain\Models\Client;
use App\Domain\Repositories\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;

class UpdateClientUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): ClientDTO
    {
        try {
            $client = $this->clientRepository->update($id, $data);
        } catch (QueryException $exception) {
            if ($this->isClientDocumentUniqueViolation($exception)) {
                throw new ClientDocumentAlreadyExistsException();
            }

            throw $exception;
        }

        if (! $client instanceof Client) {
            throw (new ModelNotFoundException())->setModel(Client::class, $id);
        }

        return ClientDTO::fromModel($client);
    }

    private function isClientDocumentUniqueViolation(QueryException $exception): bool
    {
        $errorInfo = $exception->errorInfo;
        $sqlState  = isset($errorInfo[0]) ? (string) $errorInfo[0] : '';
        $driverMsg = isset($errorInfo[2]) ? (string) $errorInfo[2] : $exception->getMessage();

        if ($sqlState !== '23000') {
            return false;
        }

        return str_contains($driverMsg, 'clients_company_document_unique');
    }
}
