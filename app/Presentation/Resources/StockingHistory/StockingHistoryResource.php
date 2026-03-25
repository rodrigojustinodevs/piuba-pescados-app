<?php

declare(strict_types=1);

namespace App\Presentation\Resources\StockingHistory;

use App\Domain\Enums\StockingHistoryEvent;
use App\Domain\Models\Stocking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                    $id
 * @property-read string                    $company_id
 * @property-read string                    $stocking_id
 * @property-read StockingHistoryEvent      $event
 * @property-read \Illuminate\Support\Carbon $event_date
 * @property-read int|null                  $quantity
 * @property-read float|null                $average_weight
 * @property-read string|null               $notes
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read Stocking|null             $stocking
 */
final class StockingHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'companyId'     => $this->company_id,
            'stockingId'    => $this->stocking_id,
            'event'         => $this->event->value,
            'eventLabel'    => $this->event->label(),
            'eventDate'     => $this->event_date->toDateString(),
            'quantity'      => $this->quantity,
            'averageWeight' => $this->average_weight !== null ? (float) $this->average_weight : null,
            'notes'         => $this->notes,
            'createdAt'     => $this->created_at?->toDateTimeString(),
            'updatedAt'     => $this->updated_at?->toDateTimeString(),

            'stocking' => $this->whenLoaded('stocking', fn (): array => [
                'id'               => $this->stocking->id,
                'status'           => $this->stocking->status,
                'currentQuantity'  => $this->stocking->current_quantity,
                'averageWeight'    => (float) $this->stocking->average_weight,
                'estimatedBiomass' => $this->stocking->estimated_biomass !== null
                    ? (float) $this->stocking->estimated_biomass
                    : null,
            ]),
        ];
    }
}
