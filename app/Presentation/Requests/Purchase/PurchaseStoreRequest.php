<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use App\Domain\Enums\PurchasePaymentMethod;
use App\Domain\Enums\PurchasePaymentStatus;
use App\Domain\Enums\PurchaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PurchaseStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $this->merge($this->normalizeInput());
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'companyId' => ['sometimes', 'uuid', 'exists:companies,id'],

            'supplierId' => ['required', 'uuid', 'exists:suppliers,id'],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('purchases', 'code')->whereNull('deleted_at'),
            ],

            'invoiceNumber' => ['nullable', 'string', 'max:100'],

            'orderDate' => ['required', 'date'],

            'expectedDate' => ['nullable', 'date'],

            'status' => ['nullable', Rule::enum(PurchaseStatus::class)],

            'paymentStatus' => ['required', Rule::enum(PurchasePaymentStatus::class)],

            'paymentMethod' => ['nullable', Rule::enum(PurchasePaymentMethod::class)],

            'freight'     => ['nullable', 'numeric', 'min:0'],
            'otherCosts'  => ['nullable', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string'],
            'responsible' => ['nullable', 'string', 'max:255'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.supplyId' => ['required', 'uuid', 'exists:supplies,id'],

            'items.*.quantity' => ['required', 'numeric', 'gt:0'],

            'items.*.unit' => ['nullable', 'string', 'max:50'],

            'items.*.unitPrice' => ['required', 'numeric', 'min:0'],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'companyId.uuid'   => 'O ID da empresa deve ser um UUID válido.',
            'companyId.exists' => 'A empresa selecionada não existe.',

            'supplierId.required' => 'O fornecedor é obrigatório.',
            'supplierId.uuid'     => 'O ID do fornecedor deve ser um UUID válido.',
            'supplierId.exists'   => 'O fornecedor selecionado não existe.',

            'code.required' => 'O código da compra é obrigatório.',
            'code.max'      => 'O código não pode ter mais de 50 caracteres.',
            'code.unique'   => 'Este código de compra já está em uso.',

            'orderDate.required' => 'A data do pedido é obrigatória.',
            'orderDate.date'     => 'A data do pedido deve ser uma data válida.',

            'paymentStatus.required' => 'O status de pagamento é obrigatório.',
            'paymentStatus.enum'     => 'Status de pagamento inválido.',

            'paymentMethod.enum' => 'Forma de pagamento inválida.',

            'status.enum' => 'Status inválido.',

            'freight.numeric'    => 'O frete deve ser numérico.',
            'freight.min'        => 'O frete não pode ser negativo.',
            'otherCosts.numeric' => 'Outros custos devem ser numéricos.',
            'otherCosts.min'     => 'Outros custos não podem ser negativos.',

            'items.required' => 'Ao menos um item é obrigatório.',
            'items.array'    => 'Itens deve ser um array.',
            'items.min'      => 'A compra deve possuir ao menos 1 item.',

            'items.*.supplyId.required' => 'O insumo de cada item é obrigatório.',
            'items.*.supplyId.exists'   => 'O insumo selecionado não existe.',

            'items.*.quantity.required' => 'A quantidade do item é obrigatória.',
            'items.*.quantity.gt'       => 'A quantidade deve ser maior que zero.',

            'items.*.unitPrice.required' => 'O preço unitário do item é obrigatório.',
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeInput(): array
    {
        $data = [];

        $map = [
            'company_id'     => 'companyId',
            'supplier_id'    => 'supplierId',
            'invoice_number' => 'invoiceNumber',
            'order_date'     => 'orderDate',
            'expected_date'  => 'expectedDate',
            'payment_status' => 'paymentStatus',
            'payment_method' => 'paymentMethod',
            'other_costs'    => 'otherCosts',
            'referenceCode'  => 'code',
            'reference_code' => 'code',
        ];

        foreach ($map as $snake => $camel) {
            if ($this->has($snake) && ! $this->has($camel)) {
                $data[$camel] = $this->input($snake);
            }
        }

        if ($this->has('items')) {
            $data['items'] = $this->normalizeItems($this->input('items'));
        }

        return $data;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        return array_map(function (array $item): array {
            if (isset($item['supply_id']) && ! isset($item['supplyId'])) {
                $item['supplyId'] = $item['supply_id'];
            }

            if (isset($item['unit_price']) && ! isset($item['unitPrice'])) {
                $item['unitPrice'] = $item['unit_price'];
            }

            if (isset($item['total_price']) && ! isset($item['totalPrice'])) {
                $item['totalPrice'] = $item['total_price'];
            }

            return $item;
        }, $items);
    }
}
