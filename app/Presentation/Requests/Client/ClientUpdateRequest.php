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
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'price_group'  => ['nullable', 'string', 'in:wholesale,retail,consumer'],
        ];
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id'      => $this->input('company_id', $this->input('companyId')),
            'person_type'     => $this->input('person_type', $this->input('personType')),
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

            'document_number.unique' => 'Este CPF/CNPJ já está cadastrado para esta empresa.',

            'address.string' => 'The address must be a string.',
            'address.max'    => 'The address may not be greater than 255 characters.',

            'credit_limit.numeric' => 'O limite de crédito deve ser um valor numérico.',
            'credit_limit.min'     => 'O limite de crédito não pode ser negativo.',

            'price_group.string' => 'O grupo de preço deve ser uma string.',
            'price_group.in'     => 'O grupo de preço deve ser: wholesale, retail ou consumer.',
        ];
    }
}
