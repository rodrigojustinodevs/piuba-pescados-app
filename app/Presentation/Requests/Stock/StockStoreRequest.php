<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Stock;

use Illuminate\Foundation\Http\FormRequest;

class StockStoreRequest extends FormRequest
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
            'company_id'         => ['required', 'uuid', 'exists:companies,id'],
            'supply_name'        => ['required', 'string', 'max:255'],
            'current_quantity'   => ['required', 'numeric', 'min:0'],
            'unit'               => ['required', 'string', 'max:50'],
            'minimum_stock'      => ['required', 'numeric', 'min:0'],
            'withdrawn_quantity' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
