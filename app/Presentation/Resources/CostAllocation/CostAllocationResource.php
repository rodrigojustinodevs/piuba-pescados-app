<?php

declare(strict_types=1);

namespace App\Presentation\Resources\CostAllocation;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $description
 * @property-read float $amount
 * @property-read string|null $registration_date
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 */
class CostAllocationResource extends JsonResource
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
            'description'      => $this->description,
            'amount'           => $this->amount,
            'registrationDate' => $this->registration_date,
            'company'          => $this->whenLoaded('company', fn(): array => [
                'name' => $this->company->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
