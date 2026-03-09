<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Alert;
use App\Domain\Models\Batch;

interface AlertRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Alert;

    public function findById(string $id): ?Alert;

    /**
     * Paginate supplier records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Alert;

    public function delete(string $id): bool;

    /**
     * Create a high FCR alert for the given batch.
     */
    public function createHighFcrAlert(Batch $batch, float $fcr, float $threshold): Alert;

    /**
     * Create a density alert for the given batch.
     */
    public function createDensityAlert(Batch $batch, float $density, float $threshold): Alert;

    /**
     * Create a ration deviation alert (quantity provided vs recommended ration).
     */
    public function createRationDeviationAlert(
        Batch $batch,
        float $quantityProvided,
        float $recommendedRation
    ): Alert;
}
