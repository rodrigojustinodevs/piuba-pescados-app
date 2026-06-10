<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\TransferInputDTO;
use App\Domain\Models\Transfer;

interface TransferRepositoryInterface
{
    /**
     * @param array{
     *     companyId?: string|null,
     *     batchId?: string|null,
     *     originTankId?: string|null,
     *     destinationTankId?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface;

    public function findOrFail(string $id): Transfer;

    public function create(TransferInputDTO $dto): Transfer;

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Transfer;

    public function delete(string $id): void;
}
