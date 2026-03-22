<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;

final readonly class ShowPurchaseUseCase
{
    public function __construct(
        private PurchaseRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): Purchase
    {
        return $this->repository->findOrFail($id);
    }
}
