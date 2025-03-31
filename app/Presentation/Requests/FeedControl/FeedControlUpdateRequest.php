<?php

declare(strict_types=1);

namespace App\Presentation\Requests\FeedControl;

use Illuminate\Foundation\Http\FormRequest;

class FeedControlUpdateRequest extends FormRequest
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
            'feed_type'         => ['sometimes', 'string', 'max:100'],
            'current_stock'     => ['sometimes', 'numeric', 'min:0'],
            'minimum_stock'     => ['sometimes', 'numeric', 'min:0'],
            'daily_consumption' => ['sometimes', 'numeric', 'min:0'],
            'total_consumption' => ['sometimes', 'numeric', 'min:0'],
            'last_update'       => ['sometimes', 'date'],
            'last_adjustment'   => ['sometimes', 'date'],
        ];
    }
}
