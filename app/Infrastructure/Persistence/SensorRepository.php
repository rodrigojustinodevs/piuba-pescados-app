<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Sensor;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SensorRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SensorRepository implements SensorRepositoryInterface
{
    /**
     * Create a new sensor.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): Sensor
    {
        return Sensor::create($data);
    }

    /**
     * Update an existing sensor.
     *
     * @param array<string, mixed> $data
     */
    public function update(string $id, array $data): ?Sensor
    {
        $sensor = Sensor::find($id);

        if ($sensor) {
            $sensor->update($data);

            return $sensor;
        }

        return null;
    }

    /**
     * Get paginated companies.
     */
    public function paginate(int $page = 25): PaginationInterface
    {
        /** @var LengthAwarePaginator<Sensor> $paginator */
        $paginator = Sensor::with([
            'tank:id,name',
        ])->paginate($page);

        return new PaginationPresentr($paginator);
    }

    /**
     * Show sensor by field and value.
     */
    public function showSensor(string $field, string | int $value): ?Sensor
    {
        return Sensor::where($field, $value)->first();
    }

    public function delete(string $id): bool
    {
        $sensor = Sensor::find($id);

        if (! $sensor) {
            return false;
        }

        return (bool) $sensor->delete();
    }
}
