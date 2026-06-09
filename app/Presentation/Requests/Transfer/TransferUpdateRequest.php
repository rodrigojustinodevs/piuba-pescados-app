<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class TransferUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $merge = [];

        if (! $this->has('batchId') && $this->has('batch_id')) {
            $merge['batchId'] = $this->input('batch_id');
        }

        if (! $this->has('originTankId') && $this->has('origin_tank_id')) {
            $merge['originTankId'] = $this->input('origin_tank_id');
        }

        if (! $this->has('destinationTankId') && $this->has('destination_tank_id')) {
            $merge['destinationTankId'] = $this->input('destination_tank_id');
        }

        if (! $this->has('transferDate') && $this->has('transfer_date')) {
            $merge['transferDate'] = $this->input('transfer_date');
        }

        if (! $this->has('averageWeight') && $this->has('average_weight')) {
            $merge['averageWeight'] = $this->input('average_weight');
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * Remapeia as chaves validadas de camelCase para snake_case antes de entregar ao UseCase.
     *
     * @param string|null $key
     * @param mixed       $default
     * @return array<string, mixed>
     */
    #[\Override]
    public function validated($key = null, $default = null): array
    {
        /** @var array<string, mixed> $data */
        $data = parent::validated($key, $default);

        $camelToSnake = [
            'batchId'           => 'batch_id',
            'originTankId'      => 'origin_tank_id',
            'destinationTankId' => 'destination_tank_id',
            'transferDate'      => 'transfer_date',
            'averageWeight'     => 'average_weight',
        ];

        $result = [];
        foreach ($data as $field => $value) {
            $result[$camelToSnake[$field] ?? $field] = $value;
        }

        return $result;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'batchId' => [
                'sometimes',
                'uuid',
                'exists:batches,id',
            ],

            'originTankId' => [
                'sometimes',
                'uuid',
                'exists:tanks,id',
            ],

            'destinationTankId' => [
                'sometimes',
                'uuid',
                'exists:tanks,id',
                'different:originTankId',
            ],

            'description' => [
                'sometimes',
                'string',
            ],

            'quantity' => [
                'sometimes',
                'integer',
                'min:1',
            ],

            'transferDate' => [
                'sometimes',
                'date',
            ],

            'status' => [
                'sometimes',
                'in:completed,scheduled,cancelled',
            ],

            'reason' => [
                'sometimes',
                'in:growth,density,biosecurity,maintenance,harvest_prep,other',
            ],

            'responsible' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'averageWeight' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
            ],
        ];
    }
}
