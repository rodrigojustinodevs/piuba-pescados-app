<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Feeding;

use Illuminate\Foundation\Http\FormRequest;

class FeedingUpdateRequest extends FormRequest
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
            'feeding_date'             => ['sometimes', 'date'],
            'quantity_provided'        => ['sometimes', 'numeric', 'min:0'],
            'feed_type'                => ['sometimes', 'string', 'max:100'],
            'stock_reduction_quantity' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}
