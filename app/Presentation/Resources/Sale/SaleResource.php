<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Sale;

use App\Domain\Enums\PaymentMethod;
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

        /** @var PaymentMethod|null $paymentMethod */
        $paymentMethod = $this->payment_method;

        return [
            'id'                  => $this->id,
            'code'                => $this->code,
            'clientId'            => $this->client_id,
            'financialCategoryId' => $this->financial_category_id,
            'invoiceNumber'       => $this->invoice_number,
            'needsInvoice'        => (bool) $this->needs_invoice,
            'totalRevenue'        => (float) $this->total_revenue,
            'discount'            => (float) $this->discount,
            'shipping'            => (float) $this->shipping,
            'taxes'               => (float) $this->taxes,
            'saleDate'            => $this->sale_date->toDateString(),
            'dueDate'             => $this->due_date?->toDateString(),
            'paidDate'            => $this->paid_date?->toDateString(),
            'deliveredAt'         => $this->delivered_at?->toDateTimeString(),
            'paymentMethod'       => $paymentMethod?->value,
            'paymentMethodLabel'  => $paymentMethod?->label(),
            'status'              => $status->value,
            'statusLabel'         => $status->label(),
            'notes'               => $this->notes,
            'responsibleUserId'   => $this->responsible_user_id,

            'company' => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),

            'client' => $this->whenLoaded('client', fn (): array => [
                'id'   => $this->client->id,
                'name' => $this->client->name,
            ]),

            'stocking' => $this->whenLoaded('stocking', fn (): ?array => $this->stocking ? [
                'id'            => $this->stocking->id,
                'quantity'      => $this->stocking->quantity,
                'averageWeight' => $this->stocking->average_weight,
            ] : null),

            'responsibleUser' => $this->whenLoaded('responsibleUser', fn (): ?array => $this->responsibleUser ? [
                'id'   => $this->responsibleUser->id,
                'name' => $this->responsibleUser->name,
            ] : null),

            'items' => $this->whenLoaded(
                'items',
                fn () => SaleItemResource::collection($this->items),
            ),

            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
