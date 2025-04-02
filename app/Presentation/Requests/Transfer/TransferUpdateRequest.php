<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class TransferUpdateRequest extends FormRequest
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
            'batche_id'           => ['sometimes', 'uuid', 'exists:batches,id'],
            'origin_tank_id'      => ['sometimes', 'uuid', 'exists:tanks,id'],
            'destination_tank_id' => ['sometimes', 'uuid', 'exists:tanks,id'],
            'description'         => ['sometimes', 'string'],
            'quantity'            => ['sometimes', 'integer', 'min:1'],
        ];
    }
}
