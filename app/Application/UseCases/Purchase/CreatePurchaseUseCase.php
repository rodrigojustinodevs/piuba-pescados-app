<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\DTOs\PurchaseDTO;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use App\Infrastructure\Mappers\PurchaseMapper;
use Illuminate\Support\Facades\DB;

class CreatePurchaseUseCase
{
    public function __construct(
        protected PurchaseRepositoryInterface $purchaseRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): PurchaseDTO
    {
        return DB::transaction(function () use ($data): PurchaseDTO {
            $payload = PurchaseMapper::fromRequest($data);
            $purchase = $this->purchaseRepository->create($payload);

            $purchase->load(['supplier:id,name', 'company:id,name', 'stocking:id,stocking_date']);

            return PurchaseMapper::toDTO($purchase);
        });
    }
}
