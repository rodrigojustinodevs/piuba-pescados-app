<?php

declare(strict_types=1);

namespace App\Presentation\Requests\User;

use App\Domain\Enums\PositionEnum;
use App\Domain\Enums\RolesEnum;
use App\Domain\Enums\UserStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class UserStoreRequest extends FormRequest
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
     * @return array<string, array<int, ValidationRule|In|string>|string>
     */
    public function rules(): array
    {
        return [
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'phone'     => 'nullable|string|max:20',
            'status'    => ['sometimes', Rule::in(array_column(UserStatusEnum::cases(), 'value'))],
            'position'  => ['nullable', Rule::in(array_column(PositionEnum::cases(), 'value'))],
            'role'      => ['required', Rule::in(array_column(RolesEnum::cases(), 'value'))],
            'companyId' => ['sometimes', 'string', 'exists:companies,id'],
        ];
    }
}
