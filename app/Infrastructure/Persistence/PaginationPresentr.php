<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Repositories\PaginationInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class PaginationPresentr implements PaginationInterface
{

    protected $paginator;

    /**
     * @param LengthAwarePaginator $paginator
     */
    public function __construct(
        LengthAwarePaginator $paginator
    ){
        $this->paginator = $paginator;
    }


    /**
     * @return int
     */
    public function total(): int
    {
        return (int) $this->paginator->total() ?? 0;
    }

    /**
     * @return array
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
        return (int) $this->paginator->currentPage() ?? 1;
    }

    /**
     * @return int
     */
    public function perPage(): int
    {
        return (int) $this->paginator->perPage() ?? 1;
    }

    /**
     * @return int
     */
    public function firstPage(): int
    {
        return (int) $this->paginator->firstItem() ?? 1;
    }

    /**
     * @return int
     */
    public function lastPage(): int
    {
        return (int) $this->paginator->lastPage() ?? 1;
    }
}
