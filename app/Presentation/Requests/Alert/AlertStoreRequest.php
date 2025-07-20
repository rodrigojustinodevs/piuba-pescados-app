<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Alert;

use Illuminate\Foundation\Http\FormRequest;

class AlertStoreRequest extends FormRequest
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
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'alert_type' => ['required', 'string', 'max:100'],
            'message'    => ['required', 'string'],
            'status'     => ['required', 'in:pending,resolved'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'company_id.required' => 'The company ID is required.',
            'company_id.uuid'     => 'The company ID must be a valid UUID.',
            'company_id.exists'   => 'The selected company does not exist.',

            'alert_type.required' => 'The alert type is required.',
            'alert_type.string'   => 'The alert type must be a string.',
            'alert_type.max'      => 'The alert type may not be greater than 100 characters.',

            'message.required' => 'The message is required.',
            'message.string'   => 'The message must be a string.',

            'status.required' => 'The status is required.',
            'status.string'   => 'The status must be a string.',
            'status.in'       => 'The status must be either pending or resolved.',
        ];
    }
}
