<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Domain\Models\Company;

class PaginationPresentr implements PaginationInterface
{
    /**
     * Constructor accepts a paginator with specified value type.
     * @param LengthAwarePaginator<Company> $paginator
     */
    public function __construct(protected LengthAwarePaginator $paginator)
    {
    }

    /**
     * Get the total number of items.
     *
     * @return int
     */
    public function total(): int
    {
        return (int) $this->paginator->total();
    }

    /**
     * Get the items of the current page.
     *
     * @return array<Company>
     */
    public function items(): array
    {
        return $this->paginator->items();
    }

    /**
     * Get the current page number.
     *
     * @return int
     */
    public function currentPage(): int
    {
        return (int) $this->paginator->currentPage();
    }

    /**
     * Get the number of items per page.
     *
     * @return int
     */
    public function perPage(): int
    {
        return (int) $this->paginator->perPage();
    }

    /**
     * Get the first item index of the current page.
     *
     * @return int
     */
    public function firstPage(): int
    {
        return (int) $this->paginator->firstItem();
    }

    /**
     * Get the last page number.
     *
     * @return int
     */
    public function lastPage(): int
    {
        return (int) $this->paginator->lastPage();
    }
}
