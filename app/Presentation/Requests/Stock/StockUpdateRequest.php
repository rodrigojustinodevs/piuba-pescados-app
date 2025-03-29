<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StockUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'supply_name'        => ['sometimes', 'string', 'max:255'],
            'current_quantity'   => ['sometimes', 'numeric', 'min:0'],
            'unit'               => ['sometimes', 'string', 'max:50'],
            'minimum_stock'      => ['sometimes', 'numeric', 'min:0'],
            'withdrawn_quantity' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
