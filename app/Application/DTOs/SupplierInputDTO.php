<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class SupplierInputDTO
{
    /**
     * @param array<string, mixed>|null $address
     */
    public function __construct(
        public string $companyId,
        public string $name,
        public string $contact,
        public string $phone,
        public string $email,
        public ?string $tradeName = null,
        public ?string $document = null,
        public ?string $stateRegistration = null,
        public ?string $category = null,
        public ?string $paymentTerms = null,
        public ?float $rating = null,
        public ?array $address = null,
        public ?string $status = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            companyId:         (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            name:              (string) ($data['name'] ?? ''),
            contact:           (string) ($data['contact'] ?? ''),
            phone:             (string) ($data['phone'] ?? ''),
            email:             (string) ($data['email'] ?? ''),
            tradeName:         isset($data['trade_name']) ? (string) $data['trade_name']
                              : (isset($data['tradeName']) ? (string) $data['tradeName'] : null),
            document:          isset($data['document']) ? (string) $data['document'] : null,
            stateRegistration: isset($data['state_registration']) ? (string) $data['state_registration']
                              : (isset($data['stateRegistration']) ? (string) $data['stateRegistration'] : null),
            category:          isset($data['category']) ? (string) $data['category'] : 'other',
            paymentTerms:      isset($data['payment_terms']) ? (string) $data['payment_terms']
                              : (isset($data['paymentTerms']) ? (string) $data['paymentTerms'] : null),
            rating:            isset($data['rating']) ? (float) $data['rating'] : 0.0,
            address:           isset($data['address']) && is_array($data['address']) ? $data['address'] : null,
            status:            isset($data['status']) ? (string) $data['status'] : 'active',
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'company_id'         => $this->companyId,
            'name'               => $this->name,
            'contact'            => $this->contact,
            'phone'              => $this->phone,
            'email'              => $this->email,
            'trade_name'         => $this->tradeName,
            'document'           => $this->document,
            'state_registration' => $this->stateRegistration,
            'category'           => $this->category,
            'payment_terms'      => $this->paymentTerms,
            'rating'             => $this->rating,
            'address'            => $this->address,
            'status'             => $this->status,
        ];
    }
}
