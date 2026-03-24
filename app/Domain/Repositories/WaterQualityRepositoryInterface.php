<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\WaterQualityDTO;
use App\Domain\Models\WaterQuality;

interface WaterQualityRepositoryInterface
{
    /**
     * Create a new waterQuality record.
     *
     */
    public function create(WaterQualityDTO $dto): WaterQuality;

    /**
     * Update an existing waterQuality record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): WaterQuality;

    /**
     * Delete a waterQuality record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate waterQuality records.
     *
     * @param array{
     *     company_id: string,
     *     tank_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Find a waterQuality by a specific field.
     */
    public function showWaterQuality(string $field, string | int $value): ?WaterQuality;

    /**
     * Find a waterQuality by ID.
     */
    public function findOrFail(string $id): WaterQuality;

    /**
     * Find a waterQuality by company ID.
     */
    public function findByCompany(string $companyId): ?WaterQuality;

    /**
     * Get the latest water quality by tank.
     *
     * @return array<string, object>
     */
    public function getLatestByTank(string $companyId): array;

    /**
     * Get the trends for a given company, parameter, date from, date to, granularity and tank id.
     *
     * @return array<int, object>
     */
    public function getTrends(
        string $companyId,
        string $parameter,
        string $dateFrom,
        string $dateTo,
        string $granularity,
        ?string $tankId = null
    ): array;
}
