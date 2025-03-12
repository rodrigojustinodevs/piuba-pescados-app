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
        return [
            'name' => 'sometimes|string',
            'cnpj' => [
                'sometimes',
                'string',
                'unique:companies,cnpj,' . $this->route('company'), // Ignora o valor do próprio CNPJ ao atualizar
            ],
            'cell_phone' => 'string',
        ];
    }
}
