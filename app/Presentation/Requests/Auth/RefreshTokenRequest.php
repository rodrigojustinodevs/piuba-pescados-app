<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class RefreshTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        // O token vem pelo header Authorization: Bearer <token>
        // Não há body para validar — a regra de negócio é do middleware jwt.refresh
        return [];
    }
}
