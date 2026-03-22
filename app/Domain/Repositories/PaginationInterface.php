<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface PaginationInterface
{
    public function total(): int;

    /** @return array<int, mixed> */
    public function items(): array;

    public function currentPage(): int;

    public function perPage(): int;

    public function firstPage(): int;

    public function lastPage(): int;
}
