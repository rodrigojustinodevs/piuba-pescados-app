<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Enums\SaleStatus;
use App\Domain\Exceptions\InvalidSaleStatusTransitionException;
use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Marks a sale as delivered and records the delivery timestamp.
 * Allowed for confirmed or paid sales.
 */
final readonly class DeliverSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
    ) {
    }

    public function execute(string $id): Sale
    {
        return DB::transaction(function () use ($id): Sale {
            $sale = $this->saleRepository->findOrFailLocked($id);

            if (! in_array($sale->status, [SaleStatus::CONFIRMED, SaleStatus::PAID], true)) {
                throw new InvalidSaleStatusTransitionException($sale->status->value, SaleStatus::DELIVERED->value);
            }

            return $this->saleRepository->update($id, [
                'status'       => SaleStatus::DELIVERED->value,
                'delivered_at' => now()->toDateTimeString(),
            ]);
        });
    }
}
