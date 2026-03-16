<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\DTOs\PurchaseDTO;
use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use App\Infrastructure\Mappers\PurchaseMapper;
use RuntimeException;

class UpdatePurchaseUseCase
{
    public function __construct(
        protected PurchaseRepositoryInterface $purchaseRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): PurchaseDTO
    {
        $payload = PurchaseMapper::fromRequest($data);
        $purchase = $this->purchaseRepository->update($id, $payload);

        if (! $purchase instanceof Purchase) {
            throw new RuntimeException('Purchase not found');
        }

        $purchase->load(['supplier:id,name', 'company:id,name', 'stocking:id,stocking_date']);
        return PurchaseMapper::toDTO($purchase);
    }
}
