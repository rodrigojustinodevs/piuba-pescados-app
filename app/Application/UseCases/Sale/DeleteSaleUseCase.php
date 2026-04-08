<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Enums\SaleStatus;
use App\Domain\Exceptions\InvalidSaleStatusTransitionException;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteSaleUseCase
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
    ) {
    }

    public function execute(string $id): void
    {
        DB::transaction(function () use ($id): void {
            $sale = $this->saleRepository->findOrFail($id);

            if (! $sale->status->isCancelled()) {
                throw new InvalidSaleStatusTransitionException(
                    from: $sale->status,
                    to:   SaleStatus::CANCELLED,
                );
            }

            $this->saleRepository->delete($id);
        });
    }
}
