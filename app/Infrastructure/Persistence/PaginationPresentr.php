<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * @template T of \Illuminate\Database\Eloquent\Model
 */
class PaginationPresentr implements PaginationInterface
{
    /** @var \Illuminate\Pagination\LengthAwarePaginator<T> */
    protected \Illuminate\Pagination\LengthAwarePaginator $paginator;

    /**
     * @param \Illuminate\Pagination\LengthAwarePaginator<T> $paginator
     */
    public function __construct(\Illuminate\Pagination\LengthAwarePaginator $paginator)
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
