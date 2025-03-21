<?php

declare(strict_types=1);

namespace App\Presentation\Requests\RolePermission;

use Illuminate\Foundation\Http\FormRequest;

class PermisssionToUserInCompanyRequest extends FormRequest
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
            'user_id'         => ['required|uuid|exists:users,id'],
            'permission_name' => ['required|string|exists:permissions,name'],
            'company_id'      => ['required', 'uuid', 'exists:companies,id'],
        ];
    }
}
