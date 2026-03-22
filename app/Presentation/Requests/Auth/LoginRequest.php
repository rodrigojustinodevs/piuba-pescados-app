<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required'    => 'The email is required.',
            'email.email'       => 'The email must be a valid email address.',
            'password.required' => 'The password is required.',
            'password.min'      => 'The password must be at least 6 characters.',
        ];
    }
}