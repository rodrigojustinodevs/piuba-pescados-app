<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $company_id
 * @property-read string $name
 * @property-read string|null $trade_name
 * @property-read string $document_number
 * @property-read string $person_type
 * @property-read string|null $address
 * @property-read string|null $city
 * @property-read string|null $state
 * @property-read \App\Domain\Enums\ClientStatusEnum $status
 * @property-read string|null $email
 * @property-read string|null $phone
 * @property-read string|null $contact
 * @property-read float|null $credit_limit
 * @property-read bool $is_defaulter
 * @property-read \App\Domain\Enums\PriceGroup|null $price_group
 * @property-read string|null $notes
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
            'companyId'      => $this->company_id,
            'personType'     => $this->person_type,
            'name'           => $this->name,
            'tradeName'      => $this->trade_name,
            'documentNumber' => $this->document_number,
            'contact'        => $this->contact,
            'email'          => $this->email,
            'phone'          => $this->phone,
            'priceGroup'     => $this->price_group?->value,
            'city'           => $this->city,
            'state'          => $this->state,
            'address'        => $this->address,
            'status'         => $this->status->value,
            'creditLimit'    => $this->credit_limit,
            'isDefaulter'    => (bool) $this->is_defaulter,
            'notes'          => $this->notes,
            'company'        => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
