<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\SensorReading;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SensorReadingRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SensorReadingRepository implements SensorReadingRepositoryInterface
{
    /**
     * Create a new sensorReading.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): SensorReading
    {
        return SensorReading::create($data);
    }

    /**
     * Update an existing sensorReading.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?SensorReading
    {
        $sensorReading = SensorReading::find($id);

        if ($sensorReading) {
            $sensorReading->update($data);

            return $sensorReading;
        }

        return null;
    }

    /**
     * Get paginated companies.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<SensorReading> $paginator */
        $paginator = SensorReading::with([
            'sensor:id,sensor_type',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show sensorReading by field and value.
     */
    public function showSensorReading(string $field, string | int $value): ?SensorReading
    {
        return SensorReading::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $sensorReading = SensorReading::find($id);

        if (! $sensorReading) {
            return false;
        }

        return (bool) $sensorReading->delete();
    }
}
