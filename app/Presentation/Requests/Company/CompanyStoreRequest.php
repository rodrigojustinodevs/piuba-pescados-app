<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Usa camelCase para não expor estrutura do banco de dados
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Contracts\Validation\Rule|\Illuminate\Validation\Rules\In|string>|string>
     */
    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:255',
            'cnpj'                => 'required|string|unique:companies,cnpj',
            'email'               => 'nullable|email|max:255',
            'phone'               => 'required|string|max:20',
            'active'              => 'nullable|boolean',
            'status'              => ['sometimes', Rule::in(['active', 'inactive'])],
            'addressStreet'       => 'required|string|max:255',
            'addressNumber'       => 'required|string|max:50',
            'addressComplement'   => 'nullable|string|max:255',
            'addressNeighborhood' => 'required|string|max:255',
            'addressCity'         => 'required|string|max:255',
            'addressState'        => 'required|string|size:2',
            'addressZipCode'      => 'required|string|max:20',
        ];
    }
}
