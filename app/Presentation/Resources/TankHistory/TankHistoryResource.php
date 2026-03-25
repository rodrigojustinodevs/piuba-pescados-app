<?php

declare(strict_types=1);

namespace App\Presentation\Resources\TankHistory;

use App\Domain\Enums\TankHistoryEvent;
use App\Domain\Models\Tank;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string               $id
 * @property-read string               $company_id
 * @property-read string               $tank_id
 * @property-read TankHistoryEvent     $event
 * @property-read \Illuminate\Support\Carbon $event_date
 * @property-read string|null          $description
 * @property-read string|null          $performed_by
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read Tank|null            $tank
 */
final class TankHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'companyId'   => $this->company_id,
            'tankId'      => $this->tank_id,
            'event'       => $this->event->value,
            'eventLabel'  => $this->event->label(),
            'eventDate'   => $this->event_date->toDateString(),
            'description' => $this->description,
            'performedBy' => $this->performed_by,
            'createdAt'   => $this->created_at?->toDateTimeString(),
            'updatedAt'   => $this->updated_at?->toDateTimeString(),

            'tank' => $this->whenLoaded('tank', fn (): array => [
                'id'     => $this->tank->id,
                'name'   => $this->tank->name,
                'status' => $this->tank->status,
            ]),
        ];
    }
}
