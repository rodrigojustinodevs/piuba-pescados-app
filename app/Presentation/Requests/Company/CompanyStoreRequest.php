<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class CompanyStoreRequest extends FormRequest
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
            'name'    => 'required|string|max:255',
            'cnpj'    => 'required|string|unique:companies,cnpj',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'required|string|max:20',
            'active'  => 'nullable|boolean',
            'address' => 'required|array',
            'address.street'      => 'required|string|max:255',
            'address.number'      => 'required|string|max:50',
            'address.complement'  => 'nullable|string|max:255',
            'address.neighborhood' => 'required|string|max:255',
            'address.city'        => 'required|string|max:255',
            'address.state'       => 'required|string|size:2',
            'address.zipCode'     => 'required|string|max:20',
        ];
    }
}
