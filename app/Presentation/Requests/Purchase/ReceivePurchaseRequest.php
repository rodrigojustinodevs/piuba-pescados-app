<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

final class ReceivePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $items = $this->input('items') ?? $this->json('items');

        if (! is_array($items) || $items === []) {
            return;
        }

        $this->merge([
            'items' => array_map(function (array $item): array {
                if (isset($item['purchaseItemId']) && ! isset($item['purchase_item_id'])) {
                    $item['purchase_item_id'] = $item['purchaseItemId'];
                }

                if (isset($item['receivedQuantity']) && ! isset($item['received_quantity'])) {
                    $item['received_quantity'] = $item['receivedQuantity'];
                }

                return $item;
            }, $items),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.purchase_item_id'  => ['required', 'uuid'],
            'items.*.received_quantity' => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    /** @return array<string, string> */
    #[\Override]
    public function messages(): array
    {
        return [
            'items.required'                     => 'Ao menos um item é obrigatório.',
            'items.array'                        => 'Itens deve ser um array.',
            'items.min'                          => 'Informe ao menos 1 item para recebimento.',
            'items.*.purchase_item_id.required'  => 'O ID do item da compra é obrigatório.',
            'items.*.purchase_item_id.uuid'      => 'O ID do item deve ser um UUID válido.',
            'items.*.received_quantity.required' => 'A quantidade recebida é obrigatória.',
            'items.*.received_quantity.numeric'  => 'A quantidade recebida deve ser numérica.',
            'items.*.received_quantity.min'      => 'A quantidade recebida deve ser maior que zero.',
        ];
    }
}
