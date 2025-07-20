<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Alert;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $alert_type
 * @property-read string $message
 * @property-read string $status
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 */
class AlertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        return [
            'id'        => $this->id,
            'alertType' => $this->alert_type,
            'message'   => $this->message,
            'status'    => $this->status,
            'company'   => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name ?? null,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
