<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use App\Domain\Enums\PurchasePaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class RegisterPurchasePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_date'   => ['required', 'date'],
            'amount'         => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', Rule::in(array_column(PurchasePaymentMethod::cases(), 'value'))],
            'reference'      => ['nullable', 'string', 'max:255'],
            'notes'          => ['nullable', 'string'],
        ];
    }
}
