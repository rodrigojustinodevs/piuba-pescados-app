<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use App\Domain\Enums\PurchasePaymentMethod;
use App\Domain\Enums\PurchasePaymentStatus;
use App\Domain\Enums\PurchaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PurchaseUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $purchaseId = $this->route('id');

        return [
            'companyId'     => ['sometimes', 'uuid', 'exists:companies,id'],
            'supplierId'    => ['required', 'uuid', 'exists:suppliers,id'],
            'orderDate'     => ['required', 'date'],
            'invoiceNumber' => ['sometimes', 'nullable', 'string', 'max:100'],

            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('purchases', 'code')->ignore($purchaseId)->whereNull('deleted_at'),
            ],

            'status' => [
                'nullable',
                Rule::in([
                    PurchaseStatus::DRAFT->value,
                    PurchaseStatus::SUBMITTED->value,
                    PurchaseStatus::APPROVED->value,
                ]),
            ],

            'paymentStatus' => ['sometimes', Rule::enum(PurchasePaymentStatus::class)],
            'paymentMethod' => ['nullable', Rule::enum(PurchasePaymentMethod::class)],
            'expectedDate'  => ['nullable', 'date'],
            'freight'       => ['sometimes', 'numeric', 'min:0'],
            'otherCosts'    => ['sometimes', 'numeric', 'min:0'],
            'notes'         => ['sometimes', 'nullable', 'string'],
            'responsible'   => ['sometimes', 'nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.id' => ['nullable', 'uuid'],

            'items.*.supplyId' => ['required', 'uuid', 'exists:supplies,id'],

            'items.*.quantity' => ['required', 'numeric', 'gt:0'],

            'items.*.unit' => ['required', 'string', 'max:20'],

            'items.*.unitPrice' => ['required', 'numeric', 'gt:0'],

            'items.*.discount' => ['sometimes', 'nullable', 'numeric', 'min:0'],

            'items.*.totalPrice' => ['sometimes', 'nullable', 'numeric', 'min:0'],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'supplierId.required' => 'Fornecedor é obrigatório.',
            'supplierId.uuid'     => 'Fornecedor inválido.',

            'orderDate.required' => 'Data do pedido é obrigatória.',
            'orderDate.date'     => 'Data inválida.',

            'code.unique' => 'Este código de compra já está em uso.',

            'status.in'          => 'Status inválido para atualização.',
            'paymentStatus.enum' => 'Status de pagamento inválido.',
            'paymentMethod.enum' => 'Forma de pagamento inválida.',

            'freight.numeric'    => 'O frete deve ser numérico.',
            'otherCosts.numeric' => 'Outros custos devem ser numéricos.',

            'items.required' => 'Itens são obrigatórios.',
            'items.array'    => 'Itens deve ser um array.',
            'items.min'      => 'A compra deve possuir ao menos 1 item.',

            'items.*.id.uuid' => 'ID do item inválido.',

            'items.*.supplyId.required' => 'Insumo é obrigatório.',
            'items.*.supplyId.uuid'     => 'Insumo inválido.',

            'items.*.quantity.required' => 'Quantidade é obrigatória.',
            'items.*.quantity.numeric'  => 'Quantidade deve ser numérica.',
            'items.*.quantity.gt'       => 'Quantidade deve ser maior que zero.',

            'items.*.unit.required' => 'Unidade é obrigatória.',
            'items.*.unit.max'      => 'Unidade muito longa.',

            'items.*.unitPrice.required' => 'Preço unitário é obrigatório.',
            'items.*.unitPrice.numeric'  => 'Preço deve ser numérico.',
            'items.*.unitPrice.gt'       => 'Preço deve ser maior que zero.',
        ];
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $normalized = [];

        $map = [
            'order_date'             => 'orderDate',
            'expected_date'          => 'expectedDate',
            'expectedDeliveryDate'   => 'expectedDate',
            'expected_delivery_date' => 'expectedDate',
            'payment_status'         => 'paymentStatus',
            'payment_method'         => 'paymentMethod',
            'other_costs'            => 'otherCosts',
            'invoice_number'         => 'invoiceNumber',
            'supplier_id'            => 'supplierId',
            'company_id'             => 'companyId',
            'referenceCode'          => 'code',
            'reference_code'         => 'code',
            'responsibleName'        => 'responsible',
            'responsible_name'       => 'responsible',
            'freightCost'            => 'freight',
            'freight_cost'           => 'freight',
        ];

        foreach ($map as $snake => $camel) {
            if ($this->has($snake) && ! $this->has($camel)) {
                $normalized[$camel] = $this->input($snake);
            }
        }

        if ($this->has('items')) {
            $normalized['items'] = array_map(fn (array $item): array => [
                'id'        => $item['id'] ?? null,
                'supplyId'  => $item['supplyId'] ?? $item['supply_id'] ?? null,
                'quantity'  => isset($item['quantity']) ? (float) $item['quantity'] : null,
                'unit'      => $item['unit'] ?? null,
                'unitPrice' => isset($item['unitPrice']) ? (float) $item['unitPrice']
                    : (isset($item['unit_price']) ? (float) $item['unit_price'] : null),
                'discount'   => isset($item['discount']) ? (float) $item['discount'] : null,
                'totalPrice' => isset($item['totalPrice']) ? (float) $item['totalPrice']
                    : (isset($item['total_price']) ? (float) $item['total_price'] : null),
            ], $this->input('items'));
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }
}
