<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class SalesOrderDTO
{
    /**
     * @param SalesOrderItemDTO[] $items
     */
    public function __construct(
        public string $companyId,
        public string $clientId,
        public string $issueDate,
        public string $expectedDeliveryDate,
        public string $type,
        public string $financialCategoryId,
        public string $expectedPaymentDate,
        public bool $needsInvoice,
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
            expectedDeliveryDate: (string) ($data['expected_delivery_date'] ?? $data['expectedDeliveryDate'] ?? ''),
            type:             (string) ($data['type'] ?? 'order'),
            financialCategoryId: isset($data['financial_category_id'])
                ? (string) $data['financial_category_id']
                : (isset($data['financialCategoryId']) ? (string) $data['financialCategoryId'] : null),
            expectedPaymentDate: isset($data['expected_payment_date'])
                ? (string) $data['expected_payment_date']
                : (isset($data['expectedPaymentDate']) ? (string) $data['expectedPaymentDate'] : null),
            needsInvoice:        (bool) ($data['needs_invoice'] ?? $data['needsInvoice'] ?? false),
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
            'company_id'             => $this->companyId,
            'client_id'              => $this->clientId,
            'issue_date'             => $this->issueDate,
            'expected_delivery_date' => $this->expectedDeliveryDate,
            'type'                   => $this->type,
            'notes'                  => $this->notes,
            'total_amount'           => $this->totalAmount(),
        ];
    }
}
