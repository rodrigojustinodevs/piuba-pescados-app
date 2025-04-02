<?php

declare(strict_types=1);

namespace App\Presentation\Resources\FeedControl;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $feed_type
 * @property-read float $current_stock
 * @property-read float $minimum_stock
 * @property-read float $daily_consumption
 * @property-read float $total_consumption
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 */
class FeedControlResource extends JsonResource
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
            'feedType'         => $this->feed_type,
            'currentStock'     => $this->current_stock,
            'minimumStock'     => $this->minimum_stock,
            'dailyConsumption' => $this->daily_consumption,
            'totalConsumption' => $this->total_consumption,
            'company'          => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
