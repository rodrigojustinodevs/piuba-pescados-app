<?php

declare(strict_types=1);

namespace App\Presentation\Resources\FinancialCategory;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string $type  // Corrigido para usar o símbolo $
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 */
class FinancialCategoryResource extends JsonResource
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
            'id'      => $this->id,
            'name'    => $this->name,
            'type'    => $this->type,
            'company' => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
