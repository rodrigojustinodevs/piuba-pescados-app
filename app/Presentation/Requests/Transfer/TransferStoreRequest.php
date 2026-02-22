<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;

class TransferStoreRequest extends FormRequest
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
        // Compatibilidade com payloads antigos (snake_case)
        // Só faz merge quando o campo alternativo existe, para não injetar null.
        $merge = [];

        if (! $this->has('batcheId') && $this->has('batche_id')) {
            $merge['batcheId'] = $this->input('batche_id');
        }

        if (! $this->has('originTankId') && $this->has('origin_tank_id')) {
            $merge['originTankId'] = $this->input('origin_tank_id');
        }

        if (! $this->has('destinationTankId') && $this->has('destination_tank_id')) {
            $merge['destinationTankId'] = $this->input('destination_tank_id');
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'batcheId'          => ['required', 'uuid', 'exists:batches,id'],
            'originTankId'      => ['required', 'uuid', 'exists:tanks,id'],
            'destinationTankId' => ['required', 'uuid', 'exists:tanks,id', 'different:originTankId'],
            'description'       => ['required', 'string'],
            'quantity'          => ['required', 'integer', 'min:1'],
        ];
    }
}
