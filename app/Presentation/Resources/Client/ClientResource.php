<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $company_id
 * @property-read string $name
 * @property-read string $document_number
 * @property-read string $person_type
 * @property-read string $address
 * @property-read string|null $email
 * @property-read string|null $phone
 * @property-read string|null $contact
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 */
class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'personType'     => $this->person_type,
            'documentNumber' => $this->document_number,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'contact'        => $this->contact,
            'address'        => $this->address,
            'company'        => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
