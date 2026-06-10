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
     *     companyId?: string|null
     *     search?: string|null,
     *     sensorId?: string|null,
     *     type?: string|null,
     *     tankId?: string|null,
     *     dateFrom?: string|null,
     *     dateTo?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function paginate(array $filters): PaginationInterface
    {
        $search = $filters['search'] ?? null;
        $paginator = SensorReading::with([
            'sensor' => static fn ($q) => $q->select('id', 'sensor_type', 'status', 'tank_id')
                ->with([
                    'tank' => static fn ($tankQuery) => $tankQuery->select('id', 'name'),
                ]),
        ])
            ->when(
                ! empty($filters['companyId']),
                static fn ($q) => $q->whereHas('sensor', static fn ($s) => $s->where('company_id', $filters['companyId'])),
            )
            ->when(
                is_string($search) && $search !== '',
                static function ($q) use ($search): void {
                    $term = '%' . $search . '%';
                    $q->where(static function ($sub) use ($term): void {
                        $sub->where('notes', 'like', $term)
                            ->orWhere('value', 'like', $term)
                            ->orWhereHas(
                                'sensor',
                                static fn ($s) => $s->whereAny(['name', 'serial_number'], 'like', $term),
                            );
                    });
                },
            )
            ->when(
                ! empty($filters['sensorId']),
                static fn ($q) => $q->where('sensor_id', $filters['sensorId']),
            )
            ->when(
                ! empty($filters['type']),
                static fn ($q) => $q->where('type', $filters['type']),
            )
            ->when(
                ! empty($filters['tankId']),
                static fn ($q) => $q->whereHas('sensor', static fn ($s) => $s->where('tank_id', $filters['tankId'])),
            )
            ->when(
                ! empty($filters['dateFrom']),
                static fn ($q) => $q->whereDate('measured_at', '>=', $filters['dateFrom']),
            )
            ->when(
                ! empty($filters['dateTo']),
                static fn ($q) => $q->whereDate('measured_at', '<=', $filters['dateTo']),
            )
            ->latest('measured_at')
            ->paginate((int) ($filters['perPage'] ?? 25));

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
