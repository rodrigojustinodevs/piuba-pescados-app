<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\TransferInputDTO;
use App\Domain\Models\Transfer;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TransferRepositoryInterface;

final class TransferRepository implements TransferRepositoryInterface
{
    private const array DEFAULT_RELATIONS = [
        'batch:id,name',
        'originTank:id,name',
        'destinationTank:id,name',
    ];

    /**
     * @param array{
     *     companyId?: string|null,
     *     batchId?: string|null,
     *     originTankId?: string|null,
     *     destinationTankId?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters = []): PaginationInterface
    {
        $paginator = Transfer::query()
            ->with(self::DEFAULT_RELATIONS)
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('company_id', $filters['companyId']),
            )
            ->when(
                ! empty($filters['batchId']),
                static fn ($q) => $q->where('batch_id', $filters['batchId']),
            )
            ->when(
                ! empty($filters['originTankId']),
                static fn ($q) => $q->where('origin_tank_id', $filters['originTankId']),
            )
            ->when(
                ! empty($filters['destinationTankId']),
                static fn ($q) => $q->where('destination_tank_id', $filters['destinationTankId']),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function findOrFail(string $id): Transfer
    {
        return Transfer::with(self::DEFAULT_RELATIONS)->findOrFail($id);
    }

    public function create(TransferInputDTO $dto): Transfer
    {
        /** @var Transfer $transfer */
        $transfer = Transfer::create($dto->toPersistence());

        return $transfer->load(self::DEFAULT_RELATIONS);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Transfer
    {
        $transfer = $this->findOrFail($id);

        if ($attributes !== []) {
            $transfer->update($attributes);
            $transfer->refresh();
        }

        return $transfer->load(self::DEFAULT_RELATIONS);
    }

    public function delete(string $id): void
    {
        $this->findOrFail($id)->delete();
    }
}
