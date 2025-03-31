<?php

declare(strict_types=1);

namespace App\Presentation\Requests\FeedControl;

use Illuminate\Foundation\Http\FormRequest;

class FeedControlStoreRequest extends FormRequest
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
            'company_id'        => ['required', 'uuid', 'exists:companies,id'],
            'feed_type'         => ['required', 'string', 'max:100'],
            'current_stock'     => ['required', 'numeric', 'min:0'],
            'minimum_stock'     => ['required', 'numeric', 'min:0'],
            'daily_consumption' => ['required', 'numeric', 'min:0'],
            'total_consumption' => ['required', 'numeric', 'min:0'],
        ];
    }
}
