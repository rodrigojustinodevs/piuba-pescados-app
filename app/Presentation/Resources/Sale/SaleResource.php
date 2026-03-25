<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                          $id
 * @property-read string                          $company_id
 * @property-read string                          $client_id
 * @property-read string                          $batch_id
 * @property-read string|null                     $stocking_id
 * @property-read string|null                     $financial_category_id
 * @property-read float                           $total_weight
 * @property-read float                           $price_per_kg
 * @property-read float                           $total_revenue
 * @property-read \Illuminate\Support\Carbon      $sale_date
 * @property-read SaleStatus                      $status
 * @property-read string|null                     $notes
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 * @property-read \App\Domain\Models\Client|null  $client
 * @property-read \App\Domain\Models\Batch|null   $batch
 * @property-read \App\Domain\Models\Stocking|null $stocking
 */
class SaleResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        $status = $this->status;

        return [
            'id'           => $this->id,
            'totalWeight'  => (float) $this->total_weight,
            'pricePerKg'   => (float) $this->price_per_kg,
            'totalRevenue' => (float) $this->total_revenue,
            'saleDate'     => $this->sale_date->toDateString(),
            'status'       => $status->value,
            'statusLabel'  => $status->label(),
            'notes'        => $this->notes,
            'batchId'      => $this->batch_id,
            'stockingId'   => $this->stocking_id,
            'company'      => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'client' => $this->whenLoaded('client', fn (): array => [
                'id'   => $this->client->id,
                'name' => $this->client->name,
            ]),
            'batch' => $this->whenLoaded('batch', fn (): array => [
                'id'   => $this->batch->id,
                'name' => $this->batch->name,
            ]),
            'stocking' => $this->whenLoaded('stocking', fn (): ?array => $this->stocking
                ? [
                    'id'            => $this->stocking->id,
                    'quantity'      => $this->stocking->quantity,
                    'averageWeight' => $this->stocking->average_weight,
                ]
                : null),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
