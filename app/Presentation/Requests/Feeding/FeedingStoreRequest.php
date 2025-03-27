<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Feeding;

use Illuminate\Foundation\Http\FormRequest;

class FeedingStoreRequest extends FormRequest
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
            'batche_id'                => ['required', 'uuid', 'exists:batches,id'],
            'feeding_date'             => ['required', 'date'],
            'quantity_provided'        => ['required', 'numeric', 'min:0'],
            'feed_type'                => ['required', 'string', 'max:100'],
            'stock_reduction_quantity' => ['required', 'numeric', 'min:0'],
        ];
    }
}
