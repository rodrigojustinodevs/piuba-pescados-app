<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'company_id' => ['sometimes', 'uuid', 'exists:companies,id'],
            'plan'       => ['sometimes', 'string', 'in:basic,premium,enterprise'],
            'start_date' => ['sometimes', 'date'],
            'end_date'   => ['sometimes', 'date', 'after_or_equal:start_date'],
            'status'     => ['sometimes', 'string', 'in:active,canceled'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'company_id.uuid'   => 'The company ID must be a valid UUID.',
            'company_id.exists' => 'The selected company does not exist.',

            'plan.string' => 'The plan must be a string.',
            'plan.in'     => 'The plan must be one of: basic, premium, enterprise.',

            'start_date.date' => 'The start date must be a valid date.',

            'end_date.date'           => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be the same or after the start date.',

            'status.string' => 'The status must be a string.',
            'status.in'     => 'The status must be either active or canceled.',
        ];
    }
}
