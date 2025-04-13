<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class ClientStoreRequest extends FormRequest
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
            'company_id'      => ['required', 'uuid', 'exists:companies,id'],
            'name'            => ['required', 'string', 'max:255'],
            'contact'         => ['nullable', 'string', 'max:255'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:255'],
            'person_type'     => ['required', 'string', 'in:individual,company'],
            'document_number' => ['nullable', 'string', 'regex:/^\d{11}|\d{14}$/'],
            'address'         => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'company_id.required' => 'The company ID is required.',
            'company_id.uuid'     => 'The company ID must be a valid UUID.',
            'company_id.exists'   => 'The selected company does not exist.',

            'name.required' => 'The client name is required.',
            'name.string'   => 'The name must be a string.',
            'name.max'      => 'The name may not be greater than 255 characters.',

            'contact.string' => 'The contact must be a string.',
            'contact.max'    => 'The contact may not be greater than 255 characters.',

            'phone.string' => 'The phone must be a string.',
            'phone.max'    => 'The phone may not be greater than 20 characters.',

            'email.email' => 'The email must be a valid email address.',
            'email.max'   => 'The email may not be greater than 255 characters.',

            'person_type.required' => 'The person type is required.',
            'person_type.string'   => 'The person type must be a string.',
            'person_type.in'       => 'The person type must be either "individual" or "company".',

            'document_number.string' => 'The document number must be a string.',
            'document_number.regex'  => 'The document number must be either 11 (CPF) or 14 (CNPJ) digits.',

            'address.string' => 'The address must be a string.',
            'address.max'    => 'The address may not be greater than 255 characters.',
        ];
    }
}
