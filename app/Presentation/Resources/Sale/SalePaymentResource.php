<?php

declare(strict_types=1);

namespace App\Presentation\Resources\Sale;

use App\Domain\Enums\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Domain\Models\SalePayment
 */
final class SalePaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray(Request $request): array
    {
        /** @var PaymentMethod $method */
        $method = $this->payment_method;

        return [
            'id'            => $this->id,
            'saleId'        => $this->sale_id,
            'amount'        => (float) $this->amount,
            'paymentMethod' => $method->value,
            'paymentMethodLabel' => $method->label(),
            'paymentDate'   => $this->payment_date->toDateString(),
            'reference'     => $this->reference,
            'notes'         => $this->notes,
            'createdAt'     => $this->created_at?->toDateTimeString(),
        ];
    }
}
