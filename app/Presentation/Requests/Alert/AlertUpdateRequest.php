<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Alert;

use Illuminate\Foundation\Http\FormRequest;

class AlertUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alert_type' => ['sometimes', 'string', 'max:100'],
            'message'    => ['sometimes', 'string'],
            'status'     => ['sometimes', 'in:pending,resolved'],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'alert_type.string' => 'The alert type must be a string.',
            'alert_type.max'    => 'The alert type may not be greater than 100 characters.',

            'message.string' => 'The message must be a string.',

            'data_criacao.date' => 'A data de criação deve ser uma data válida.',

            'status.string' => 'The status must be a string.',
            'status.in'     => 'The status must be either pending or resolved.',
        ];
    }
}
