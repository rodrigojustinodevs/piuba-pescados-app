<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class CNPJ
{
    public function __construct(
        private string $value
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $cnpj = $this->sanitize();

        if ($cnpj === '' || $cnpj === '0') {
            throw new InvalidArgumentException('CNPJ cannot be empty.');
        }

        if (strlen($cnpj) !== 14) {
            throw new InvalidArgumentException('CNPJ must have 14 digits.');
        }

        if (! $this->isValidCNPJ($cnpj)) {
            throw new InvalidArgumentException("Invalid CNPJ: {$this->value}");
        }
    }

    private function sanitize(): string
    {
        return preg_replace('/[^0-9]/', '', $this->value);
    }

    private function isValidCNPJ(string $cnpj): bool
    {
        // Verifica se todos os dígitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Validação dos dígitos verificadores
        $length = strlen($cnpj);

        if ($length !== 14) {
            return false;
        }

        $sum    = 0;
        $weight = 5;

        // Primeiro dígito verificador
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weight;
            $weight = $weight === 2 ? 9 : $weight - 1;
        }

        $remainder = $sum % 11;
        $digit1    = $remainder < 2 ? 0 : 11 - $remainder;

        if ((int) $cnpj[12] !== $digit1) {
            return false;
        }

        $sum    = 0;
        $weight = 6;

        // Segundo dígito verificador
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weight;
            $weight = $weight === 2 ? 9 : $weight - 1;
        }

        $remainder = $sum % 11;
        $digit2    = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $cnpj[13] === $digit2;
    }

    public function value(): string
    {
        return $this->sanitize();
    }

    public function formatted(): string
    {
        $cnpj = $this->value();

        return sprintf(
            '%s.%s.%s/%s-%s',
            substr($cnpj, 0, 2),
            substr($cnpj, 2, 3),
            substr($cnpj, 5, 3),
            substr($cnpj, 8, 4),
            substr($cnpj, 12, 2)
        );
    }

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    public function toString(): string
    {
        return $this->value();
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
