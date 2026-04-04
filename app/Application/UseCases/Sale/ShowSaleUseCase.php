<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;

final class ShowSaleUseCase
{
    public function __construct(
        private readonly SaleRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): Sale
    {
        return $this->repository->findOrFail($id);
    }
}
