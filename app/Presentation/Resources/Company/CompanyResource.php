<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Company;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string $cnpj
 * @property-read string $address
 * @property-read string $phone
 * @property-read string $status
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 */
class CompanyResource extends JsonResource
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
            'id'         => $this->id,
            'name'       => $this->name,
            'cnpj'       => $this->cnpj,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'address'    => [
                'street'      => $this->address_street,
                'number'      => $this->address_number,
                'complement'  => $this->address_complement,
                'neighborhood' => $this->address_neighborhood,
                'city'        => $this->address_city,
                'state'       => $this->address_state,
                'zipCode'     => $this->address_zip_code,
            ],
            'active'     => $this->status === 'active',
            'status'     => $this->status,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
