<?php

declare(strict_types=1);

namespace App\Presentation\Requests\User;

use App\Domain\Enums\RolesEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserAssignRoleRequest extends FormRequest
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
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Validation\Rules\In|string>>
     */
    public function rules(): array
    {
        return [
            'role'      => ['required', Rule::in(array_column(RolesEnum::cases(), 'value'))],
            'companyId' => ['sometimes', 'string', 'exists:companies,id'],
        ];
    }
}
