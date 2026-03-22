<?php

declare(strict_types=1);

namespace App\Infrastructure\Mappers;

use App\Application\DTOs\PurchaseItemDTO;
use App\Domain\Models\PurchaseItem;

final class PurchaseItemMapper
{
    /**
     * Eloquent Model → DTO de resposta (saída da API / leitura).
     */
    public function toDTO(PurchaseItem $item): PurchaseItemDTO
    {
        return new PurchaseItemDTO(
            supplyId:   (string) $item->supply_id,
            quantity:   (float)  $item->quantity,
            unit:       (string) $item->unit,
            unitPrice:  (float)  $item->unit_price,
            id:         (string) $item->id,
            totalPrice: (float)  $item->total_price,
        );
    }

    /**
     * DTO → array de persistência (entrada no banco).
     *
     * Separado do toPersistence() do próprio DTO para manter o DTO
     * sem conhecimento de detalhes de coluna — o Mapper é o tradutor.
     *
     * @return array<string, mixed>
     */
    public function toPersistence(PurchaseItemDTO $dto): array
    {
        return [
            'supply_id'   => $dto->supplyId,
            'quantity'    => $dto->quantity,
            'unit'        => $dto->unit,
            'unit_price'  => $dto->unitPrice,
            'total_price' => $dto->resolvedTotalPrice(),
        ];
    }

    /**
     * Array bruto (payload validado) → DTO de entrada.
     * Centraliza a normalização de chaves camelCase/snake_case.
     *
     * @param array<string, mixed> $data
     */
    public function fromArray(array $data): PurchaseItemDTO
    {
        return PurchaseItemDTO::fromArray($data);
    }

    /**
     * Coleção de Models → array de DTOs.
     *
     * @param  iterable<PurchaseItem>  $items
     * @return PurchaseItemDTO[]
     */
    public function toDTOCollection(iterable $items): array
    {
        return array_map(
            $this->toDTO(...),
            is_array($items) ? $items : iterator_to_array($items),
        );
    }
}
