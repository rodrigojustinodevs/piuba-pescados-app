<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Subscription;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $plan
 * @property-read string $start_date
 * @property-read string $end_date
 * @property-read string $status
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read \App\Domain\Models\Company|null $company
 */
class SubscriptionResource extends JsonResource
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
            'id'        => $this->id,
            'plan'      => $this->plan,
            'startDate' => $this->start_date,
            'endDate'   => $this->end_date,
            'status'    => $this->status,
            'company'   => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name ?? null,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
