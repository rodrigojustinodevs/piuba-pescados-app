<?php

declare(strict_types=1);

namespace App\Domain\Repositories;

use App\Application\DTOs\SensorDTO;
use App\Domain\Models\Sensor;

interface SensorRepositoryInterface
{
    /**
     * Create a new sensor record.
     *
     */
    public function create(SensorDTO $dto): Sensor;

    /**
     * Update an existing sensor record.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Sensor;

    /**
     * Delete a sensor record.
     */
    public function delete(string $id): bool;

    /**
     * Paginate sensor records.
     *
     * @param array{
     *     company_id: string,
     *     tank_id?: string|null,
     *     sensor_type?: string|null,
     *     status?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface;

    /**
     * Find a sensor by a specific field.
     */
    public function showSensor(string $field, string | int $value): ?Sensor;

    /**
     * Find a sensor by ID.
     */
    public function findOrFail(string $id): Sensor;

    /**
     * Find a sensor by company ID.
     */
    public function findByCompany(string $companyId): ?Sensor;
}
