<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Purchase;

use App\Domain\Enums\PurchasePaymentMethod;
use App\Domain\Enums\PurchasePaymentStatus;
use App\Domain\Enums\PurchaseStatus;
use App\Domain\Models\PurchaseItem;
use App\Domain\Models\PurchasePayment;
use App\Domain\Models\Supply;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                     $id
 * @property-read string                     $code
 * @property-read string                     $company_id
 * @property-read string                     $supplier_id
 * @property-read string|null                $invoice_number
 * @property-read string                     $total_price
 * @property-read string                     $freight
 * @property-read string                     $other_costs
 * @property-read PurchaseStatus             $status
 * @property-read PurchasePaymentStatus      $payment_status
 * @property-read PurchasePaymentMethod|null $payment_method
 * @property-read \Illuminate\Support\Carbon|null $order_date
 * @property-read \Illuminate\Support\Carbon|null $expected_date
 * @property-read \Illuminate\Support\Carbon|null $received_date
 * @property-read string|null                $notes
 * @property-read string|null                $responsible
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null  $company
 * @property-read \App\Domain\Models\Supplier|null $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchaseItem>   $items
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchasePayment> $payments
 * @method float getTotalQuantity()
 * @method float getTotalReceived()
 * @method float getTotalPending()
 * @method float getReceiveProgress()
 * @method float getTotalAmount()
 * @method float getTotalPaid()
 * @method float getOutstandingBalance()
 * @method float getPaymentProgress()
 */
final class PurchaseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'code'               => $this->code,
            'companyId'          => $this->company_id,
            'supplierId'         => $this->supplier_id,
            'invoiceNumber'      => $this->invoice_number,
            'totalPrice'         => (float) $this->total_price,
            'freight'            => (float) $this->freight,
            'otherCosts'         => (float) $this->other_costs,
            'status'             => $this->status->value,
            'statusLabel'        => $this->status->label(),
            'paymentStatus'      => $this->payment_status->value,
            'paymentStatusLabel' => $this->payment_status->label(),
            'paymentMethod'      => $this->payment_method?->value,
            'paymentMethodLabel' => $this->payment_method?->label(),
            'orderDate'          => $this->order_date?->toDateTimeString(),
            'expectedDate'       => $this->expected_date?->toDateString(),
            'receivedDate'       => $this->received_date?->toDateTimeString(),
            'notes'              => $this->notes,
            'responsible'        => $this->responsible,
            'createdAt'          => $this->created_at?->toDateTimeString(),
            'updatedAt'          => $this->updated_at?->toDateTimeString(),

            'company' => $this->whenLoaded('company', fn (): array => [
                'id'   => $this->company->id,
                'name' => $this->company->name,
            ]),

            'supplier' => $this->whenLoaded('supplier', fn (): array => [
                'id'   => $this->supplier->id,
                'name' => $this->supplier->name,
            ]),

            'receiving' => $this->whenLoaded('items', fn (): array => [
                'totalPurchased' => $this->getTotalQuantity(),
                'totalReceived'  => $this->getTotalReceived(),
                'totalPending'   => $this->getTotalPending(),
                'progress'       => $this->getReceiveProgress(),
            ]),

            'items' => $this->whenLoaded(
                'items',
                fn (): array => $this->items->map(static function (PurchaseItem $item): array {
                    $supply = $item->relationLoaded('supply') ? $item->supply : null;

                    return [
                        'id'               => $item->id,
                        'supplyId'         => $item->supply_id,
                        'supplyName'       => $supply instanceof Supply ? $supply->name : null,
                        'quantity'         => (float) $item->quantity,
                        'receivedQuantity' => (float) $item->received_quantity,
                        'pendingQuantity'  => $item->getPendingQuantity(),
                        'unit'             => $item->unit,
                        'unitPrice'        => (float) $item->unit_price,
                        'totalPrice'       => (float) $item->total_price,
                    ];
                })->all()
            ),

            'payment' => [
                'total'    => $this->getTotalAmount(),
                'paid'     => $this->getTotalPaid(),
                'balance'  => $this->getOutstandingBalance(),
                'progress' => $this->getPaymentProgress(),
                'status'   => $this->payment_status->value,
            ],

            'payments' => $this->whenLoaded(
                'payments',
                fn (): array => $this->payments->map(static fn(PurchasePayment $payment): array => [
                    'id'            => $payment->id,
                    'paymentDate'   => $payment->payment_date->toDateTimeString(),
                    'amount'        => (float) $payment->amount,
                    'paymentMethod' => $payment->payment_method,
                    'reference'     => $payment->reference,
                    'notes'         => $payment->notes,
                ])->all()
            ),
        ];
    }
}
