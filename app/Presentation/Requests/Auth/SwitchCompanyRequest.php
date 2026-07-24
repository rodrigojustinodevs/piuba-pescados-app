<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

final class SwitchCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'companyId' => ['required', 'string', 'exists:companies,id'],
        ];
    }
}
