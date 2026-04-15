<?php

declare(strict_types=1);

namespace App\Presentation\Resources\SalesOrder;

use App\Domain\Enums\SalesOrderStatus;
use App\Domain\Enums\SalesOrderType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Models\SalesOrder
 */
final class SalesOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        /** @var SalesOrderType $type */
        $type = $this->type;
        /** @var SalesOrderStatus $status */
        $status = $this->status;

        return [
            'id'             => $this->id,
            'companyId'      => $this->company_id,
            'clientId'       => $this->client_id,
            'type'           => $type->value,
            'status'         => $status->value,
            'statusLabel'    => $status->label(),
            'totalAmount'    => (float) $this->total_amount,
            'issueDate'      => $this->issue_date->toDateString(),
            'expirationDate' => $this->expiration_date?->toDateString(),
            'quotationId'    => $this->quotation_id,
            'notes'          => $this->notes,

            'company' => $this->whenLoaded('company', fn (): array => [
                'id'   => $this->company->id,
                'name' => $this->company->name,
            ]),

            'client' => $this->whenLoaded('client', fn (): array => [
                'id'   => $this->client->id,
                'name' => $this->client->name,
            ]),

            'items' => $this->when(
                $this->relationLoaded('items'),
                fn () => SalesOrderItemResource::collection($this->items),
            ),

            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
