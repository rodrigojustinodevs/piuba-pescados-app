<?php

declare(strict_types=1);

namespace App\Presentation\Requests\User;

use App\Domain\Enums\PositionEnum;
use App\Domain\Enums\UserStatusEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class UserUpdateRequest extends FormRequest
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
        $userId = $this->route('id');

        return [
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email|max:255|unique:users,email,' . $userId,
            'password' => 'sometimes|string|min:6',
            'phone'    => 'sometimes|nullable|string|max:20',
            'status'   => ['sometimes', Rule::in(array_column(UserStatusEnum::cases(), 'value'))],
            'position' => ['sometimes', 'nullable', Rule::in(array_column(PositionEnum::cases(), 'value'))],
        ];
    }
}
