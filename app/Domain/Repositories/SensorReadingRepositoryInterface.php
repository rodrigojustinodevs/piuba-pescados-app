<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\SensorReadingDTO;
use App\Domain\Models\SensorReading;

interface SensorReadingRepositoryInterface
{
    /**
     * Create a new sensor reading record.
     */
    public function create(SensorReadingDTO $dto): SensorReading;

    /**
     * Update an existing sensor reading record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): SensorReading;

    /**
     * Delete a sensor reading record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate sensor reading records.
     *
     * @param array{
     *     companyId?: string|null,
     *     search?: string|null,
     *     sensorId?: string|null,
     *     tankId?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Find a sensor reading by a specific field.
     */
    public function showSensorReading(string $field, string | int $value): ?SensorReading;

    /**
     * Find a sensor reading by ID.
     */
    public function findOrFail(string $id): SensorReading;

    /**
     * Find a sensor reading by company ID.
     */
    public function findByCompany(string $companyId): ?SensorReading;

    /**
     * Count readings last 24 hours.
     */
    public function countReadingsLast24h(string $companyId): int;
}
