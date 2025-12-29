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
     * Usa camelCase para não expor estrutura do banco de dados
     *
     * @return array<string, list<\Illuminate\Validation\Rules\In|string>
     * |array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        $companyId = $this->route('id') ?? $this->route('company');

        return [
            'name' => 'sometimes|string|max:255',
            'cnpj' => [
                'sometimes',
                'string',
                'unique:companies,cnpj,' . $companyId,
            ],
            'email'               => 'sometimes|nullable|email|max:255',
            'phone'               => 'sometimes|string|max:20',
            'active'              => 'sometimes|nullable|boolean',
            'addressStreet'       => 'sometimes|string|max:255',
            'addressNumber'       => 'sometimes|string|max:50',
            'addressComplement'   => 'sometimes|nullable|string|max:255',
            'addressNeighborhood' => 'sometimes|string|max:255',
            'addressCity'         => 'sometimes|string|max:255',
            'addressState'        => 'sometimes|string|size:2',
            'addressZipCode'      => 'sometimes|string|max:20',
        ];
    }
}
