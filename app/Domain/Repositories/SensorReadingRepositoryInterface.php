<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\SensorReading;

interface SensorReadingRepositoryInterface
{
    /**
     * Create a new sensorReading record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): SensorReading;

    /**
     * Update an existing sensorReading record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?SensorReading;

    /**
     * Delete a sensorReading record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate sensorReading records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a sensorReading by a specific field.
     */
    public function showSensorReading(string $field, string | int $value): ?SensorReading;
}
