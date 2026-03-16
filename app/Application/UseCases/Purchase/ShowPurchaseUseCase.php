<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\DTOs\PurchaseDTO;
use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use App\Infrastructure\Mappers\PurchaseMapper;
use RuntimeException;

class ShowPurchaseUseCase
{
    public function __construct(
        protected PurchaseRepositoryInterface $purchaseRepository
    ) {
    }

    public function execute(string $id): ?PurchaseDTO
    {
        $purchase = $this->purchaseRepository->showPurchase('id', $id);

        if (! $purchase instanceof Purchase) {
            throw new RuntimeException('Purchase not found');
        }

        return PurchaseMapper::toDTO($purchase);
    }
}
