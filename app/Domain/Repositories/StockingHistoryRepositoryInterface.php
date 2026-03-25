<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\StockingHistoryDTO;
use App\Domain\Models\StockingHistory;

interface StockingHistoryRepositoryInterface
{
    public function create(StockingHistoryDTO $dto): StockingHistory;

    public function findOrFail(string $id): StockingHistory;

    /**
     * @param array{
     *     company_id: string,
     *     stocking_id?: string|null,
     *     event?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;
}
