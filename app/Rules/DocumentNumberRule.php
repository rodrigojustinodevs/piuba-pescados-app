<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida que o document_number corresponde ao person_type:
 *   - individual → CPF com exatamente 11 dígitos
 *   - company    → CNPJ com exatamente 14 dígitos
 *
 * Aceita somente dígitos (sem pontuação).
 */
final class DocumentNumberRule implements ValidationRule
{
    public function __construct(private readonly ?string $personType)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $digits = preg_replace('/\D/', '', (string) $value);

        if ($this->personType === 'individual') {
            if ($digits === null || strlen($digits) !== 11) {
                $fail('The :attribute must contain exactly 11 digits (CPF) for physical person.');
            }

            return;
        }

        if ($this->personType === 'company') {
            if ($digits === null || strlen($digits) !== 14) {
                $fail('The :attribute must contain exactly 14 digits (CNPJ) for legal person.');
            }

            return;
        }

        if ($digits === null || ! in_array(strlen($digits), [11, 14], strict: true)) {
            $fail('The :attribute must contain 11 digits (CPF) or 14 digits (CNPJ).');
        }
    }
}
