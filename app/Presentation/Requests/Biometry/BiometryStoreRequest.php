<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Biometry;

use Illuminate\Foundation\Http\FormRequest;

class BiometryStoreRequest extends FormRequest
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
            'batche_id'      => ['required', 'uuid', 'exists:batches,id'],
            'biometry_date'  => ['required', 'date'],
            'average_weight' => ['required', 'numeric', 'min:0'],
            'fcr'            => ['required', 'numeric', 'min:0'],
        ];
    }
}
