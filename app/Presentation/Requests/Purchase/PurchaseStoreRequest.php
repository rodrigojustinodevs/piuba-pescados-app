<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Purchase;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseStoreRequest extends FormRequest
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
            'companyId' => [
                'sometimes',
                'uuid',
                'exists:companies,id',
            ],

            'supplierId' => [
                'required',
                'uuid',
                'exists:suppliers,id',
            ],

            'invoiceNumber' => [
                'nullable',
                'string',
                'max:100',
            ],

            'purchaseDate' => [
                'required',
                'date',
            ],
            'status' => [
                'nullable',
                'string',
                'in:draft,confirmed,received,cancelled',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.supplyId' => [
                'required',
                'uuid',
                'exists:supplies,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.unit' => [
                'nullable',
                'string',
                'max:50',
            ],

            'items.*.unitPrice' => [
                'required',
                'numeric',
                'min:0',
            ],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'companyId.required' => 'The company ID is required.',
            'companyId.uuid'     => 'The company ID must be a valid UUID.',
            'companyId.exists'   => 'The selected company does not exist.',

            'supplierId.required' => 'The supplier ID is required.',
            'supplierId.uuid'     => 'The supplier ID must be a valid UUID.',
            'supplierId.exists'   => 'The selected supplier does not exist.',

            'purchaseDate.required' => 'The purchase date is required.',
            'purchaseDate.date'     => 'The purchase date must be a valid date.',

            'items.required' => 'At least one purchase item is required.',
            'items.array'    => 'Items must be an array.',

            'items.*.supplyId.required' => 'Each item must have a supply.',
            'items.*.supplyId.exists'   => 'The selected supply does not exist.',

            'items.*.quantity.required' => 'Item quantity is required.',
            'items.*.quantity.gt'       => 'Item quantity must be greater than zero.',

            'items.*.unitPrice.required' => 'Item unit price is required.',
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
            'total_price'    => 'totalPrice',
            'purchase_date'  => 'purchaseDate',
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
