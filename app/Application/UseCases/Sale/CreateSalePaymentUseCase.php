<?php

declare(strict_types=1);

namespace App\Application\UseCases\Sale;

use App\Application\DTOs\SalePaymentDTO;
use App\Domain\Enums\SaleStatus;
use App\Domain\Exceptions\InvalidSaleStatusTransitionException;
use App\Domain\Models\SalePayment;
use App\Domain\Repositories\SalePaymentRepositoryInterface;
use App\Domain\Repositories\SaleRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Registers a partial payment for a sale.
 * Automatically marks the sale as paid when the total amount is fully covered.
 */
final readonly class CreateSalePaymentUseCase
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
        private SalePaymentRepositoryInterface $paymentRepository,
    ) {
    }

    public function execute(string $saleId, SalePaymentDTO $dto): SalePayment
    {
        return DB::transaction(function () use ($saleId, $dto): SalePayment {
            $sale = $this->saleRepository->findOrFailLocked($saleId);

            if ($sale->status->isFinanciallySettled() || $sale->status->isCancelled()) {
                throw new InvalidSaleStatusTransitionException(
                    $sale->status->value,
                    'payment_registration',
                );
            }

            $payment = $this->paymentRepository->create($saleId, $dto->toArray());

            $totalPaid = $this->paymentRepository->totalPaidBySale($saleId);

            if ($totalPaid >= (float) $sale->total_revenue && ! $sale->status->isPaid()) {
                $this->saleRepository->update($saleId, [
                    'status'    => SaleStatus::PAID->value,
                    'paid_date' => $dto->paymentDate,
                ]);
            }

            return $payment;
        });
    }
}
