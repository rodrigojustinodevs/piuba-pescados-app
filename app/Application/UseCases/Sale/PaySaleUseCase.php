<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Enums\SaleStatus;
use App\Domain\Exceptions\InvalidSaleStatusTransitionException;
use App\Domain\Models\Sale;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Marks a sale as paid and records the payment date.
 * Only allowed for sales in pending, confirmed, or overdue status.
 */
final readonly class PaySaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
    ) {
    }

    public function execute(string $id, ?string $paidDate = null): Sale
    {
        return DB::transaction(function () use ($id, $paidDate): Sale {
            $sale = $this->saleRepository->findOrFailLocked($id);

            if (! in_array($sale->status, [SaleStatus::PENDING, SaleStatus::CONFIRMED, SaleStatus::OVERDUE], true)) {
                throw new InvalidSaleStatusTransitionException($sale->status->value, SaleStatus::PAID->value);
            }

            return $this->saleRepository->update($id, [
                'status'    => SaleStatus::PAID->value,
                'paid_date' => $paidDate ?? now()->toDateString(),
            ]);
        });
    }
}
