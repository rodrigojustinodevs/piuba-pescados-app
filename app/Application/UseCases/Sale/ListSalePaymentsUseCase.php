<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Domain\Models\SalePayment;
use App\Domain\Repositories\SalePaymentRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Collection;

final readonly class ListSalePaymentsUseCase
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private SalePaymentRepositoryInterface $paymentRepository,
    ) {
    }

    /** @return Collection<int, SalePayment> */
    public function execute(string $saleId): Collection
    {
        $this->saleRepository->findOrFail($saleId);

        return $this->paymentRepository->findBySale($saleId);
    }
}
