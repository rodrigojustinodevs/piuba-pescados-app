<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Purchase;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $company_id
 * @property-read string $supplier_id
 * @property-read string $input_name
 * @property-read float $quantity
 * @property-read float $total_price
 * @property-read string $purchase_date
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 * @property-read \App\Domain\Models\Supplier|null $supplier
 */
class PurchaseResource extends JsonResource
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
            'id'           => $this->id,
            'companyId'    => $this->company_id,
            'supplierId'   => $this->supplier_id,
            'inputName'    => $this->input_name,
            'quantity'     => $this->quantity,
            'totalPrice'   => $this->total_price,
            'purchaseDate' => $this->purchase_date,
            'company'      => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name ?? '',
            ]),
            'supplier' => $this->whenLoaded('supplier', fn (): array => [
                'id'   => $this->supplier->id ?? '',
                'name' => $this->supplier->name ?? '',
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
