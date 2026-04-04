<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteSaleUseCase
{
    public function __construct(
        private readonly SaleRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->repository->findOrFail($id);

        DB::transaction(fn (): bool => $this->repository->delete($id));
    }
}
