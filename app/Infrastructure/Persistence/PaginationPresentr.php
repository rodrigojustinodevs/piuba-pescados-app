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
    /** @var LengthAwarePaginator<T> */
    protected LengthAwarePaginator $paginator;

    /**
     * @param LengthAwarePaginator<T> $paginator
     */
    public function __construct(LengthAwarePaginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @return int
     */
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

    /**
     * @return int
     */
    public function currentPage(): int
    {
        return (int) $this->paginator->currentPage();
    }

    /**
     * @return int
     */
    public function perPage(): int
    {
        return (int) $this->paginator->perPage();
    }

    /**
     * @return int
     */
    public function firstPage(): int
    {
        return $this->paginator->firstItem();
    }

    /**
     * @return int
     */
    public function lastPage(): int
    {
        return (int) $this->paginator->lastPage();
    }
}
