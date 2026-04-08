<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Sale;

use App\Domain\Enums\SaleStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Models\Sale
 */
final class SaleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        /** @var SaleStatus $status */
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

            'company' => $this->whenLoaded('company', fn (): array => [
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

            'stocking' => $this->whenLoaded('stocking', fn (): ?array => $this->stocking ? [
                'id'            => $this->stocking->id,
                'quantity'      => $this->stocking->quantity,
                'averageWeight' => $this->stocking->average_weight,
            ] : null),

            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
