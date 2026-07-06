<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\SalePayment;
use App\Domain\Repositories\SalePaymentRepositoryInterface;
use Illuminate\Support\Collection;

final class SalePaymentRepository implements SalePaymentRepositoryInterface
{
    public function create(string $saleId, array $attributes): SalePayment
    {
        /** @var SalePayment $payment */
        $payment = SalePayment::create([...$attributes, 'sale_id' => $saleId]);

        return $payment;
    }

    /** @return Collection<int, SalePayment> */
    public function findBySale(string $saleId): Collection
    {
        return SalePayment::where('sale_id', $saleId)
            ->orderBy('payment_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function totalPaidBySale(string $saleId): float
    {
        return (float) SalePayment::where('sale_id', $saleId)->sum('amount');
    }
}
