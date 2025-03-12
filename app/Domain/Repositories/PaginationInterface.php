<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

interface PaginationInterface
{
    /**
     * Get the total number of items.
     *
     * @return int
     */
    public function total(): int;

    /**
     * Get the items in the current page.
     *
     * @return array<int, mixed>
     */
    public function items(): array;

    /**
     * Get the current page number.
     *
     * @return int
     */
    public function currentPage(): int;

    /**
     * Get the number of items per page.
     *
     * @return int
     */
    public function perPage(): int;

    /**
     * Get the first page number.
     *
     * @return int
     */
    public function firstPage(): int;

    /**
     * Get the last page number.
     *
     * @return int
     */
    public function lastPage(): int;
}
