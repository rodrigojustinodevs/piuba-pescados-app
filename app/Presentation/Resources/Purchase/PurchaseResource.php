<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                                                           $id
 * @property-read string                                                           $company_id
 * @property-read string                                                           $supplier_id
 * @property-read string|null                                                      $invoice_number
 * @property-read string                                                           $total_price
 * @property-read string                                                           $status
 * @property-read \Illuminate\Support\Carbon|null                                  $purchase_date
 * @property-read \Illuminate\Support\Carbon|null                                  $received_at
 * @property-read \Illuminate\Support\Carbon|null                                  $created_at
 * @property-read \Illuminate\Support\Carbon|null                                  $updated_at
 * @property-read \App\Domain\Models\Company|null                                  $company
 * @property-read \App\Domain\Models\Supplier|null                                 $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Domain\Models\PurchaseItem> $items
 */
final class PurchaseResource extends JsonResource
{
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'companyId'     => $this->company_id,
            'supplierId'    => $this->supplier_id,
            'invoiceNumber' => $this->invoice_number,
            'totalPrice'    => (float) $this->total_price,
            'status'        => $this->status,
            'purchaseDate'  => $this->purchase_date?->toDateString(),
            'receivedAt'    => $this->received_at?->toDateTimeString(),
            'createdAt'     => $this->created_at?->toDateTimeString(),
            'updatedAt'     => $this->updated_at?->toDateTimeString(),

            'company'  => $this->whenLoaded('company', fn (): array => [
                'id'   => $this->company->id,
                'name' => $this->company->name,
            ]),

            'supplier' => $this->whenLoaded('supplier', fn (): array => [
                'id'   => $this->supplier->id,
                'name' => $this->supplier->name,
            ]),

            'items' => $this->whenLoaded('items', fn (): array =>
                $this->items->map(static fn ($item): array => [
                    'id'         => $item->id,
                    'supplyId'   => $item->supply_id,
                    'supplyName' => $item->relationLoaded('supply') ? $item->supply->name : null,
                    'quantity'   => (float) $item->quantity,
                    'unit'       => $item->unit,
                    'unitPrice'  => (float) $item->unit_price,
                    'totalPrice' => (float) $item->total_price,
                ])->all()
            ),
        ];
    }
}