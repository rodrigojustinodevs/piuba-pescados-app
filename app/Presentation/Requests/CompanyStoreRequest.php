<?php

declare(strict_types=1);

namespace App\Presentation\Requests;

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
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'name'    => ['required', 'string'],
            'status'  => ['sometimes', Rule::in(['active'])],
            'cnpj'    => ['required', 'string'],
            'address' => ['required', 'string'],
            'phone'   => ['required', 'string'],
        ];
    }
}
