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
     *     search?: string|null,
     *     companyId: string,
     *     tankId?: string|null,
     *     sensorType?: string|null,
     *     status?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $search = $filters['search'] ?? null;

        if (isset($filters['status'])) {
            $filters['status'] = SensorDTO::toPersistenceStatus($filters['status']);
        }

        $paginator = Sensor::with(['tank:id,name'])
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->where('company_id', $filters['companyId']),
            )
            ->when(
                is_string($search) && $search !== '',
                static fn ($q) => $q->whereAny(['name', 'serial_number', 'notes'], 'like', '%' . $search . '%'),
            )
            ->when(
                ! empty($filters['tankId']),
                static fn ($q) => $q->where('tank_id', $filters['tankId']),
            )
            ->when(
                ! empty($filters['sensorType']),
                static fn ($q) => $q->where('sensor_type', $filters['sensorType']),
            )
            ->when(
                ! empty($filters['status']),
                static fn ($q) => $q->where('status', $filters['status']),
            )
            ->latest()
            ->paginate((int) ($filters['perPage'] ?? 25));

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

    /** @return array<string, array<int, string>> */
    public function getAlertByTank(string $companyId): array
    {
        $sensors = Sensor::where('company_id', $companyId)
            ->whereIn('status', ['inactive', 'maintenance'])
            ->whereNull('deleted_at')
            ->get();

        return $sensors->groupBy('tank_id')
            ->map(
                static fn ($group) => $group
                    ->map(static fn ($s): string => "{$s->sensor_type} ({$s->status})")
                    ->values()
                    ->toArray()
            )
            ->toArray();
    }

    public function countInactiveSensors(string $companyId): int
    {
        return Sensor::where('company_id', $companyId)
            ->whereIn('status', ['inactive', 'maintenance'])
            ->whereNull('deleted_at')
            ->count();
    }
}
