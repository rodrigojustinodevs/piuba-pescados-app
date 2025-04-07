<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class SupplierStoreRequest extends FormRequest
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
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'name'       => ['required', 'string', 'max:255'],
            'contact'    => ['required', 'string', 'max:255'],
            'phone'      => ['required', 'string', 'max:20'],
            'email'      => ['required', 'email', 'max:255'],
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

            'name.required' => 'The supplier name is required.',
            'name.string'   => 'The supplier name must be a string.',
            'name.max'      => 'The supplier name may not be greater than 255 characters.',

            'contact.required' => 'The contact name is required.',
            'contact.string'   => 'The contact name must be a string.',
            'contact.max'      => 'The contact name may not be greater than 255 characters.',

            'phone.required' => 'The phone number is required.',
            'phone.string'   => 'The phone number must be a string.',
            'phone.max'      => 'The phone number may not be greater than 20 characters.',

            'email.required' => 'The email address is required.',
            'email.email'    => 'The email address must be valid.',
            'email.max'      => 'The email address may not be greater than 255 characters.',
        ];
    }
}
