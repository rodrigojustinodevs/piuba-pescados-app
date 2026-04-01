<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class SupplierUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        if (! $this->has('companyId') && $this->has('company_id')) {
            $this->merge(['companyId' => $this->input('company_id')]);
        }
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'companyId' => ['sometimes', 'uuid', 'exists:companies,id'],
            'name'      => ['sometimes', 'string', 'max:255'],
            'contact'   => ['sometimes', 'string', 'max:255'],
            'phone'     => ['sometimes', 'string', 'max:20'],
            'email'     => ['sometimes', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'companyId.uuid'   => 'The company ID must be a valid UUID.',
            'companyId.exists' => 'The selected company does not exist.',
            'name.string'      => 'The supplier name must be a string.',
            'name.max'         => 'The supplier name may not be greater than 255 characters.',
            'contact.string'   => 'The contact name must be a string.',
            'contact.max'      => 'The contact name may not be greater than 255 characters.',
            'phone.string'     => 'The phone number must be a string.',
            'phone.max'        => 'The phone number may not be greater than 20 characters.',
            'email.email'      => 'The email address must be valid.',
            'email.max'        => 'The email address may not be greater than 255 characters.',
        ];
    }
}
