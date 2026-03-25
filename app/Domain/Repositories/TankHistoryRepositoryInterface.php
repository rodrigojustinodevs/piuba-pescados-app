<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\TankHistoryDTO;
use App\Domain\Models\TankHistory;

interface TankHistoryRepositoryInterface
{
    public function create(TankHistoryDTO $dto): TankHistory;

    public function findOrFail(string $id): TankHistory;

    /**
     * @param array{
     *     company_id: string,
     *     tank_id?: string|null,
     *     event?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;
}
