<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Biometry;

use Illuminate\Foundation\Http\FormRequest;

class BiometryStoreRequest extends FormRequest
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
        if (! $this->has('batcheId') && $this->has('batche_id')) {
            $merge['batcheId'] = $this->input('batche_id');
        }
        if (! $this->has('biometryDate') && $this->has('biometry_date')) {
            $merge['biometryDate'] = $this->input('biometry_date');
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
     * Usa camelCase para não expor estrutura do banco.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>|string>
     */
    public function rules(): array
    {
        return [
            'batcheId'      => ['required', 'uuid', 'exists:batches,id'],
            'biometryDate'  => ['required', 'date'],
            'averageWeight' => ['required', 'numeric', 'min:0'],
            'fcr'           => ['required', 'numeric', 'min:0'],
        ];
    }
}
