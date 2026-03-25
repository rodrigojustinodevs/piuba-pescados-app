<?php

declare(strict_types=1);

namespace App\Application\UseCases\Client;

use App\Domain\Models\Client;
use App\Domain\Repositories\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Anonimiza os dados sensíveis do cliente (LGPD) e realiza o soft-delete.
 *
 * Preserva id e nome para manter o histórico financeiro da fazenda íntegro.
 * Campos mascarados: email, phone, address, contact, document_number.
 */
class AnonymizeClientUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
    }

    public function execute(string $id): void
    {
        DB::transaction(function () use ($id): void {
            $anonymized = $this->clientRepository->anonymize($id);

            if (! $anonymized) {
                throw (new ModelNotFoundException())->setModel(Client::class, $id);
            }

            $this->clientRepository->delete($id);
        });
    }
}
