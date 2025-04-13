<?php

declare(strict_types=1);

namespace App\Application\UseCases\Client;

use App\Domain\Repositories\ClientRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteClientUseCase
{
    public function __construct(
        protected ClientRepositoryInterface $clientRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->clientRepository->delete($id));
    }
}
