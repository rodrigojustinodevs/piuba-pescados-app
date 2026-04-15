<?php

declare(strict_types=1);

namespace App\Application\DTOs;

final readonly class SalesOrderUpdateDTO
{
    /**
     * @param SalesOrderItemDTO[]|null $items         null = nao alterar itens
     * @param bool                     $notesProvided Verdadeiro se 'notes' foi enviado (distingue ausente de null)
     */
    public function __construct(
        public ?string $clientId,
        public ?string $issueDate,
        public ?string $expirationDate,
        public ?string $notes,
        public ?array $items,
        private bool $notesProvided = false,
    ) {
    }

    /**
     * Recalcula total_amount apenas quando os itens foram enviados.
     * Retorna null quando itens nao foram alterados.
     */
    public function totalAmount(): ?float
    {
        if ($this->items === null) {
            return null;
        }

        return round(
            array_sum(
                array_map(static fn (SalesOrderItemDTO $i): float => $i->subtotal(), $this->items)
            ),
            2
        );
    }

    /**
     * Retorna os atributos escalares presentes no patch.
     * Nao sobrescreve campos ausentes.
     *
     * @return array<string, mixed>
     */
    public function toScalarAttributes(): array
    {
        $attributes = [];

        if ($this->clientId !== null) {
            $attributes['client_id'] = $this->clientId;
        }

        if ($this->issueDate !== null) {
            $attributes['issue_date'] = $this->issueDate;
        }

        if ($this->expirationDate !== null) {
            $attributes['expiration_date'] = $this->expirationDate;
        }

        if ($this->notesProvided) {
            $attributes['notes'] = $this->notes;
        }

        $total = $this->totalAmount();

        if ($total !== null) {
            $attributes['total_amount'] = $total;
        }

        return $attributes;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $items = null;

        if (array_key_exists('items', $data) && is_array($data['items'])) {
            $items = array_map(
                SalesOrderItemDTO::fromArray(...),
                $data['items'],
            );
        }

        return new self(
            clientId:       isset($data['client_id']) ? (string) $data['client_id'] : null,
            issueDate:      isset($data['issue_date']) ? (string) $data['issue_date'] : null,
            expirationDate: isset($data['expiration_date']) ? (string) $data['expiration_date'] : null,
            notes:          array_key_exists('notes', $data)
                                ? ($data['notes'] !== null ? (string) $data['notes'] : null)
                                : null,
            items:          $items,
            notesProvided:  array_key_exists('notes', $data),
        );
    }
}
