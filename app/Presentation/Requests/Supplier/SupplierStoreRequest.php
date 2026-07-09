<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Supplier;

use App\Domain\Enums\SupplierCategoryEnum;
use App\Domain\Enums\SupplierStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierStoreRequest extends FormRequest
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

        if (! $this->has('tradeName') && $this->has('trade_name')) {
            $this->merge(['tradeName' => $this->input('trade_name')]);
        }

        if (! $this->has('stateRegistration') && $this->has('state_registration')) {
            $this->merge(['stateRegistration' => $this->input('state_registration')]);
        }

        if (! $this->has('paymentTerms') && $this->has('payment_terms')) {
            $this->merge(['paymentTerms' => $this->input('payment_terms')]);
        }

        if ($this->filled('document')) {
            $this->merge(['document' => preg_replace('/\D/', '', (string) $this->input('document'))]);
        }

        if (is_array($this->input('address'))) {
            $address = $this->input('address');

            if (! array_key_exists('zip_code', $address) && array_key_exists('zipCode', $address)) {
                $address['zip_code'] = $address['zipCode'];
                unset($address['zipCode']);
            }

            $this->merge(['address' => $address]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'companyId' => ['required', 'uuid', 'exists:companies,id'],
            'name'      => ['required', 'string', 'max:255'],
            'contact'   => ['required', 'string', 'max:255'],
            'phone'     => ['required', 'string', 'max:20'],
            'email'     => ['required', 'email', 'max:255'],
            'tradeName' => ['nullable', 'string', 'max:255'],
            'document'  => ['nullable', 'string', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! in_array(strlen((string) $value), [11, 14], strict: true)) {
                    $fail('The document must contain 11 digits (CPF) or 14 digits (CNPJ).');
                }
            }],
            'stateRegistration'    => ['nullable', 'string', 'max:30'],
            'category'             => ['nullable', Rule::in(array_column(SupplierCategoryEnum::cases(), 'value'))],
            'paymentTerms'         => ['nullable', 'string', 'max:255'],
            'rating'               => ['nullable', 'numeric', 'min:0', 'max:5'],
            'status'               => ['nullable', Rule::in(array_column(SupplierStatusEnum::cases(), 'value'))],
            'address'              => ['nullable', 'array'],
            'address.street'       => ['nullable', 'string', 'max:255'],
            'address.number'       => ['nullable', 'string', 'max:20'],
            'address.complement'   => ['nullable', 'string', 'max:255'],
            'address.neighborhood' => ['nullable', 'string', 'max:255'],
            'address.city'         => ['nullable', 'string', 'max:255'],
            'address.state'        => ['nullable', 'string', 'max:2'],
            'address.zip_code'     => ['nullable', 'string', 'max:10'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'companyId.required' => 'The company ID is required.',
            'companyId.uuid'     => 'The company ID must be a valid UUID.',
            'companyId.exists'   => 'The selected company does not exist.',
            'name.required'      => 'The supplier name is required.',
            'name.string'        => 'The supplier name must be a string.',
            'name.max'           => 'The supplier name may not be greater than 255 characters.',
            'contact.required'   => 'The contact name is required.',
            'contact.string'     => 'The contact name must be a string.',
            'contact.max'        => 'The contact name may not be greater than 255 characters.',
            'phone.required'     => 'The phone number is required.',
            'phone.string'       => 'The phone number must be a string.',
            'phone.max'          => 'The phone number may not be greater than 20 characters.',
            'email.required'     => 'The email address is required.',
            'email.email'        => 'The email address must be valid.',
            'email.max'          => 'The email address may not be greater than 255 characters.',
        ];
    }
}
