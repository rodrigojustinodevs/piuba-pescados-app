<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Purchase;

use App\Domain\Models\PurchasePayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                                                           $id
 * @property-read string                                                           $code
 * @property-read float                                                            $total_price
 * @property-read \App\Domain\Enums\PurchasePaymentStatus                         $payment_status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PurchasePayment>  $payments
 * @method float getTotalAmount()
 * @method float getTotalPaid()
 * @method float getOutstandingBalance()
 * @method float getPaymentProgress()
 */
final class PurchasePaymentHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        return [
            'purchase' => [
                'id'            => $this->id,
                'code'          => $this->code,
                'totalAmount'   => $this->getTotalAmount(),
                'totalPaid'     => $this->getTotalPaid(),
                'balance'       => $this->getOutstandingBalance(),
                'progress'      => $this->getPaymentProgress(),
                'paymentStatus' => $this->payment_status->value,
            ],

            'payments' => $this->whenLoaded(
                'payments',
                fn (): array => $this->payments->map(static fn (PurchasePayment $payment): array => [
                    'id'            => $payment->id,
                    'paymentDate'   => $payment->payment_date->toDateTimeString(),
                    'amount'        => (float) $payment->amount,
                    'paymentMethod' => $payment->payment_method,
                    'reference'     => $payment->reference,
                    'notes'         => $payment->notes,
                    'createdAt'     => $payment->created_at->toDateTimeString(),
                ])->all()
            ),
        ];
    }
}
