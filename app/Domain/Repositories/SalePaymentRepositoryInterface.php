<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\SalePayment;
use Illuminate\Support\Collection;

interface SalePaymentRepositoryInterface
{
    /** @param array<string, mixed> $attributes */
    public function create(string $saleId, array $attributes): SalePayment;

    /** @return Collection<int, SalePayment> */
    public function findBySale(string $saleId): Collection;

    public function totalPaidBySale(string $saleId): float;
}
