<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Domain\Models\Sensor;

interface SensorRepositoryInterface
{
    /**
     * Create a new sensor record.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Sensor;

    /**
     * Update an existing sensor record.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Sensor;

    /**
     * Delete a sensor record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate sensor records.
     */
    public function paginate(int $page = 25): PaginationInterface;

    /**
     * Find a sensor by a specific field.
     */
    public function showSensor(string $field, string | int $value): ?Sensor;
}
