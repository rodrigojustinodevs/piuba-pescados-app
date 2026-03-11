<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Mortality;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MortalityStoreRequest extends FormRequest
{
    #[\Override]
    protected function prepareForValidation(): void
    {
        $data = [];

        if ($this->has('batch_id') && ! $this->has('batchId')) {
            $data['batchId'] = $this->input('batch_id');
        }

        if ($this->has('mortality_date') && ! $this->has('mortalityDate')) {
            $data['mortalityDate'] = $this->input('mortality_date');
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'batchId' => [
                'required',
                'uuid',
                Rule::exists('batches', 'id')->where('status', 'active'),
            ],
            'mortalityDate' => ['required', 'date', 'date_format:Y-m-d'],
            'quantity'      => ['required', 'integer', 'min:1'],
            'cause'         => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'batchId.exists' => 'The batch informed does not exist or is not active. '
                . 'Only active batches allow mortality.',
            'mortalityDate.required'    => 'The mortality date is required.',
            'mortalityDate.date'        => 'The mortality date must be a valid date.',
            'mortalityDate.date_format' => 'The mortality date must be in Y-m-d format.',
            'quantity.required'         => 'The quantity is required.',
            'quantity.integer'          => 'The quantity must be an integer.',
            'quantity.min'              => 'The quantity must be at least 1.',
            'cause.required'            => 'The cause is required.',
            'cause.string'              => 'The cause must be a valid text.',
            'cause.max'                 => 'The cause must not exceed 255 characters.',
        ];
    }
}
