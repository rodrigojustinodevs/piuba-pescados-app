<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class SubscriptionStoreRequest extends FormRequest
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
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'plan'       => ['required', 'string', 'in:basic,premium,enterprise'],
            'start_date' => ['required', 'date'],
            'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
            'status'     => ['required', 'string', 'in:active,canceled'],
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

            'plan.required' => 'The subscription plan is required.',
            'plan.string'   => 'The plan must be a string.',
            'plan.in'       => 'The plan must be one of: basic, premium, enterprise.',

            'start_date.required' => 'The start date is required.',
            'start_date.date'     => 'The start date must be a valid date.',

            'end_date.required'       => 'The end date is required.',
            'end_date.date'           => 'The end date must be a valid date.',
            'end_date.after_or_equal' => 'The end date must be the same or after the start date.',

            'status.required' => 'The status is required.',
            'status.string'   => 'The status must be a string.',
            'status.in'       => 'The status must be either active or canceled.',
        ];
    }
}
