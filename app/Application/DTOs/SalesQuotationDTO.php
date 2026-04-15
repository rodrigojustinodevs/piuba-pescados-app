<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class SalesQuotationDTO
{
    /**
     * @param SalesOrderItemDTO[] $items
     */
    public function __construct(
        public string $companyId,
        public string $clientId,
        public string $issueDate,
        public string $expirationDate,
        public array $items,
        public ?string $notes = null,
    ) {
    }

    /**
     * Calcula o total do pedido somando os subtotais dos itens.
     * Lógica financeira no DTO — não no repositório.
     */
    public function totalAmount(): float
    {
        return round(
            array_sum(
                array_map(static fn (SalesOrderItemDTO $i): float => $i->subtotal(), $this->items)
            ),
            2
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $items = array_map(
            SalesOrderItemDTO::fromArray(...),
            (array) ($data['items'] ?? []),
        );

        return new self(
            companyId:      (string) ($data['company_id'] ?? $data['companyId'] ?? ''),
            clientId:       (string) ($data['client_id'] ?? $data['clientId'] ?? ''),
            issueDate:      (string) ($data['issue_date'] ?? $data['issueDate'] ?? ''),
            expirationDate: (string) ($data['expiration_date'] ?? $data['expirationDate'] ?? ''),
            items:          $items,
            notes:          isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toPersistence(): array
    {
        return [
            'company_id'      => $this->companyId,
            'client_id'       => $this->clientId,
            'issue_date'      => $this->issueDate,
            'expiration_date' => $this->expirationDate,
            'total_amount'    => $this->totalAmount(),
            'notes'           => $this->notes,
        ];
    }
}
