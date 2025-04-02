<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @template T of \Illuminate\Database\Eloquent\Model
 */
class PaginationPresentr implements PaginationInterface
{
    /**
     * @param LengthAwarePaginator<T> $paginator
     */
    public function __construct(protected LengthAwarePaginator $paginator)
    {
    }

    public function total(): int
    {
        return (int) $this->paginator->total();
    }

    /**
     * @return array<T>
     */
    public function items(): array
    {
        return $this->paginator->items();
    }

    public function currentPage(): int
    {
        return (int) $this->paginator->currentPage();
    }

    public function perPage(): int
    {
        return (int) $this->paginator->perPage();
    }

    public function firstPage(): int
    {
        return (int) ($this->paginator->firstItem() ?? 1);
    }

    public function lastPage(): int
    {
        return (int) $this->paginator->lastPage();
    }
}
