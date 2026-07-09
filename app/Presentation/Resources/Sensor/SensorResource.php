<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Sensor;

use App\Application\DTOs\SensorDTO;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string      $id
 * @property-read string      $sensor_type
 * @property-read string      $tank_id
 * @property-read string|null $name
 * @property-read string|null $serial_number
 * @property-read int|null    $battery
 * @property-read string|null $unit
 * @property-read float|null  $last_reading
 * @property-read string      $installation_date
 * @property-read string|null $notes
 * @property-read string      $status
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Tank|null $tank
 */
class SensorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'sensorType'       => $this->sensor_type,
            'name'             => $this->name,
            'serialNumber'     => $this->serial_number,
            'battery'          => $this->battery,
            'unit'             => $this->unit,
            'lastReading'      => $this->last_reading !== null ? (float) $this->last_reading : null,
            'installationDate' => $this->installation_date,
            'notes'            => $this->notes,
            'status'           => SensorDTO::toOutputStatus((string) $this->status),
            'tank'             => $this->whenLoaded('tank', fn (): array => [
                'id'   => $this->tank->id,
                'name' => $this->tank->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
