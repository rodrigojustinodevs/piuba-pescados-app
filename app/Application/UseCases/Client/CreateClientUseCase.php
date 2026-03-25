<?php

declare(strict_types=1);

namespace App\Application\UseCases\Client;

use App\Application\DTOs\ClientDTO;
use App\Application\DTOs\ClientInputDTO;
use App\Domain\Repositories\ClientRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateClientUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): ClientDTO
    {
        $dto = ClientInputDTO::fromArray($data);

        return DB::transaction(
            fn (): ClientDTO => ClientDTO::fromModel(
                $this->clientRepository->create($dto)
            )
        );
    }
}
