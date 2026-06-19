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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'batchId'           => ['required', 'uuid', 'exists:batches,id'],
            'originTankId'      => ['required', 'uuid', 'exists:tanks,id'],
            'destinationTankId' => ['required', 'uuid', 'exists:tanks,id', 'different:originTankId'],
            'companyId'         => ['sometimes', 'uuid', 'exists:companies,id'],

            'description' => ['required', 'string'],
            'quantity'    => ['required', 'integer', 'min:1'],

            'transferDate' => ['required', 'date'],

            'status' => [
                'required',
                'in:completed,scheduled,cancelled',
            ],

            'reason' => [
                'required',
                'in:growth,density,biosecurity,maintenance,harvest_prep,other',
            ],

            'responsible' => ['required', 'string', 'max:255'],

            'averageWeight' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
