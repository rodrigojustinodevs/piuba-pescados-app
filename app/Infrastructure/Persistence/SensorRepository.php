<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SensorDTO;
use App\Domain\Models\Sensor;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SensorRepositoryInterface;

class SensorRepository implements SensorRepositoryInterface
{
    /**
     * Create a new sensor.
     *
     */
    public function create(SensorDTO $dto): Sensor
    {
        /** @var Sensor */
        return Sensor::create($dto->toPersistence());
    }

    /**
     * Update an existing sensor.
     *
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): Sensor
    {
        $sensor = $this->findOrFail($id);
        $sensor->update(array_filter($attributes, static fn ($v): bool => $v !== null));

        return $sensor->refresh();
    }

    /**
     * Get paginated records.
     *
     * @param array{
     *     company_id: string,
     *     tank_id?: string|null,
     *     sensor_type?: string|null,
     *     status?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = Sensor::with(['tank:id,name'])
            ->whereHas('tank', static fn ($q) => $q->where('company_id', $filters['company_id']))
            ->when(
                ! empty($filters['tank_id']),
                static fn ($q) => $q->where('tank_id', $filters['tank_id']),
            )
            ->when(
                ! empty($filters['sensor_type']),
                static fn ($q) => $q->where('sensor_type', $filters['sensor_type']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', $filters['status']),
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    /**
     * Show sensor by field and value.
     */
    public function showSensor(string $field, string | int $value): ?Sensor
    {
        return Sensor::where($field, $value)->first();
    }

    public function findOrFail(string $id): Sensor
    {
        return Sensor::findOrFail($id);
    }

    public function findByCompany(string $companyId): ?Sensor
    {
        return Sensor::where('company_id', $companyId)->first();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }
}
