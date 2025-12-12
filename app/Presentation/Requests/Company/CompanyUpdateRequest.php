<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class CompanyUpdateRequest extends FormRequest
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
     * @return array<string, list<\Illuminate\Validation\Rules\In|string>
     * |array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->route('id') ?? $this->route('company');

        return [
            'name'    => 'sometimes|string|max:255',
            'cnpj'    => [
                'sometimes',
                'string',
                'unique:companies,cnpj,' . $companyId,
            ],
            'email'   => 'sometimes|nullable|email|max:255',
            'phone'   => 'sometimes|string|max:20',
            'active'  => 'sometimes|nullable|boolean',
            'address' => 'sometimes|array',
            'address.street'      => 'sometimes|required_with:address|string|max:255',
            'address.number'      => 'sometimes|required_with:address|string|max:50',
            'address.complement'  => 'sometimes|nullable|string|max:255',
            'address.neighborhood' => 'sometimes|required_with:address|string|max:255',
            'address.city'        => 'sometimes|required_with:address|string|max:255',
            'address.state'       => 'sometimes|required_with:address|string|size:2',
            'address.zipCode'     => 'sometimes|required_with:address|string|max:20',
        ];
    }
}
