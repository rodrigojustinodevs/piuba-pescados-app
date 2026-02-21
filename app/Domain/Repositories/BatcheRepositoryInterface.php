<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Batche;

interface BatcheRepositoryInterface
{
    /**
     * Create a new batche record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Batche;

    /**
     * Update an existing batche record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Batche;

    /**
     * Delete a batche record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate batche records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a batche by a specific field.
     */
    public function showBatche(string $field, string | int $value): ?Batche;

    /**
     * Verifica se existe outro lote ativo no tanque.
     * Útil para garantir a regra: "um tanque só pode ter um lote ativo por vez".
     */
    public function hasActiveBatcheInTank(string $tankId, ?string $exceptBatcheId = null): bool;
}
