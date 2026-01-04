<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

final class Address
{
    public function __construct(
        private readonly ?string $street = null,
        private readonly ?string $number = null,
        private readonly ?string $complement = null,
        private readonly ?string $neighborhood = null,
        private readonly ?string $city = null,
        private readonly ?string $state = null,
        private readonly ?string $zipCode = null
    ) {
    }

    public function street(): ?string
    {
        return $this->street;
    }

    public function number(): ?string
    {
        return $this->number;
    }

    public function complement(): ?string
    {
        return $this->complement;
    }

    public function neighborhood(): ?string
    {
        return $this->neighborhood;
    }

    public function city(): ?string
    {
        return $this->city;
    }

    public function state(): ?string
    {
        return $this->state;
    }

    public function zipCode(): ?string
    {
        return $this->zipCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'street'       => $this->street,
            'number'       => $this->number,
            'complement'   => $this->complement,
            'neighborhood' => $this->neighborhood,
            'city'         => $this->city,
            'state'        => $this->state,
            'zipCode'      => $this->zipCode,
        ];
    }

    public function equals(self $other): bool
    {
        return $this->street === $other->street &&
               $this->number === $other->number &&
               $this->complement === $other->complement &&
               $this->neighborhood === $other->neighborhood &&
               $this->city === $other->city &&
               $this->state === $other->state &&
               $this->zipCode === $other->zipCode;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? null,
            number: $data['number'] ?? null,
            complement: $data['complement'] ?? null,
            neighborhood: $data['neighborhood'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            zipCode: $data['zipCode'] ?? $data['zip_code'] ?? null
        );
    }
}
