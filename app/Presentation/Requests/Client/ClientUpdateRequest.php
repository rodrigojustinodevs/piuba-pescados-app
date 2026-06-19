<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Client;

use App\Rules\DocumentNumberRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Contracts\Validation\Rule|\Illuminate\Validation\Rules\Unique|string>|string>
     */
    public function rules(): array
    {
        $clientId   = $this->route('client');
        $companyId  = $this->input('company_id');
        $personType = $this->input('person_type');

        return [
            'company_id'      => ['sometimes', 'uuid', 'exists:companies,id'],
            'name'            => ['sometimes', 'string', 'max:255'],
            'trade_name'      => ['nullable', 'string', 'max:255'],
            'contact'         => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:255'],
            'person_type'     => ['sometimes', 'string', 'in:individual,company'],
            'document_number' => [
                'nullable',
                'string',
                new DocumentNumberRule($personType),
                Rule::unique('clients')
                    ->where('company_id', $companyId)
                    ->ignore($clientId),
            ],
            'address'      => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:255'],
            'state'        => ['nullable', 'string', 'max:2'],
            'status'       => ['sometimes', 'string', 'in:active,inactive,prospect'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'price_group'  => ['nullable', 'string', 'in:wholesale,retail,consumer'],
            'notes'        => ['nullable', 'string'],
        ];
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id'      => $this->input('company_id', $this->input('companyId')),
            'person_type'     => $this->input('person_type', $this->input('personType')),
            'trade_name'      => $this->input('trade_name', $this->input('tradeName')),
            'document_number' => $this->input('document_number', $this->input('documentNumber')),
            'credit_limit'    => $this->input('credit_limit', $this->input('creditLimit')),
            'price_group'     => $this->input('price_group', $this->input('priceGroup')),
        ]);
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

            'name.string' => 'The name must be a string.',
            'name.max'    => 'The name may not be greater than 255 characters.',

            'contact.string' => 'The contact must be a string.',
            'contact.max'    => 'The contact may not be greater than 255 characters.',

            'phone.string' => 'The phone must be a string.',
            'phone.max'    => 'The phone may not be greater than 20 characters.',

            'email.email' => 'The email must be a valid email address.',
            'email.max'   => 'The email may not be greater than 255 characters.',

            'person_type.string' => 'The person type must be a string.',
            'person_type.in'     => 'The person type must be either "individual" or "company".',

            'document_number.unique' => 'This CPF/CNPJ is already registered for this company.',

            'trade_name.string' => 'The trade name must be a string.',
            'trade_name.max'    => 'The trade name may not be greater than 255 characters.',

            'address.string' => 'The address must be a string.',
            'address.max'    => 'The address may not be greater than 255 characters.',

            'city.string' => 'The city must be a string.',
            'city.max'    => 'The city may not be greater than 255 characters.',

            'state.string' => 'The state must be a string.',
            'state.max'    => 'The state must be a 2-character code.',

            'status.in' => 'The status must be either "active", "inactive" or "prospect".',

            'credit_limit.numeric' => 'The credit limit must be a numeric value.',
            'credit_limit.min'     => 'The credit limit cannot be negative.',

            'price_group.string' => 'The price group must be a string.',
            'price_group.in'     => 'The price group must be: wholesale, retail or consumer.',

            'notes.string' => 'The notes must be a string.',
        ];
    }
}
