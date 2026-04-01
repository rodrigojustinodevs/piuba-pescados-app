<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Application\DTOs\SensorReadingDTO;
use App\Domain\Models\SensorReading;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SensorReadingRepositoryInterface;

final class SensorReadingRepository implements SensorReadingRepositoryInterface
{
    public function findOrFail(string $id): SensorReading
    {
        /** @var SensorReading */
        return SensorReading::with(['sensor.tank'])->findOrFail($id);
    }

    public function create(SensorReadingDTO $dto): SensorReading
    {
        /** @var SensorReading */
        return SensorReading::create($dto->toPersistence());
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(string $id, array $attributes): SensorReading
    {
        $sensorReading = $this->findOrFail($id);
        $sensorReading->update(array_filter($attributes, static fn ($v): bool => $v !== null));

        return $sensorReading->refresh();
    }

    /**
     * @param array{
     *     company_id: string,
     *     sensor_id?: string|null,
     *     tank_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $paginator = SensorReading::with([
            'sensor' => static fn ($q) => $q->select('id', 'sensor_type', 'status', 'tank_id')
                ->with([
                    'tank' => static fn ($tankQuery) => $tankQuery->select('id', 'name'),
                ]),
        ])
            ->whereHas('sensor', static fn ($q) => $q->where('company_id', $filters['company_id']))
            ->when(
                ! empty($filters['sensor_id']),
                static fn ($q) => $q->where('sensor_id', $filters['sensor_id']),
            )
            ->when(
                ! empty($filters['tank_id']),
                static fn ($q) => $q->whereHas('sensor', static fn ($s) => $s->where('tank_id', $filters['tank_id'])),
            )
            ->when(
                ! empty($filters['date_from']),
                static fn ($q) => $q->whereDate('measured_at', '>=', $filters['date_from']),
            )
            ->when(
                ! empty($filters['date_to']),
                static fn ($q) => $q->whereDate('measured_at', '<=', $filters['date_to']),
            )
            ->latest('measured_at')
            ->paginate((int) ($filters['per_page'] ?? 25));

        return new PaginationPresentr($paginator);
    }

    public function showSensorReading(string $field, string | int $value): ?SensorReading
    {
        return SensorReading::where($field, $value)->first();
    }

    public function findByCompany(string $companyId): ?SensorReading
    {
        return SensorReading::where('company_id', $companyId)->first();
    }

    public function delete(string $id): bool
    {
        return (bool) $this->findOrFail($id)->delete();
    }

    public function countReadingsLast24h(string $companyId): int
    {
        return SensorReading::where('company_id', $companyId)
            ->where('measured_at', '>=', now()->subHours(24))
            ->whereNull('deleted_at')
            ->count();
    }
}
