<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Batch;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BatchDistributionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Purchase Header
            'supplierId'  => ['required', 'uuid', 'exists:suppliers,id'],
            'totalCost'   => ['required', 'numeric', 'min:0'],
            'entryDate'   => ['required', 'date', 'before_or_equal:today'],
            'species'     => ['required', 'string', 'max:255'],
            'cultivation' => ['required', Rule::in(['growout', 'nursery'])],
            'notes'       => ['nullable', 'string', 'max:1000'],
            'companyId'   => ['sometimes', 'nullable', 'uuid', 'exists:companies,id'],

            // Distribution Array
            'distribution'                 => ['required', 'array', 'min:1'],
            'distribution.*.tankId'        => ['required', 'uuid', 'exists:tanks,id'],
            'distribution.*.quantity'      => ['required', 'integer', 'min:1'],
            'distribution.*.averageWeight' => ['required', 'numeric', 'min:0.0001'],
        ];
    }

    /**
     * Custom messages for the nested distribution array.
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'supplierId.exists'                => 'The selected supplier is invalid.',
            'totalCost.required'               => 'The total cost is required.',
            'totalCost.numeric'                => 'The total cost must be a number.',
            'totalCost.min'                    => 'The total cost must be at least 0.',
            'entryDate.required'               => 'The entry date is required.',
            'entryDate.date'                   => 'The entry date must be a valid date.',
            'entryDate.before_or_equal'        => 'The entry date must be today or in the past.',
            'species.required'                 => 'The species is required.',
            'species.string'                   => 'The species must be a string.',
            'species.max'                      => 'The species must be less than 255 characters.',
            'cultivation.required'             => 'The cultivation is required.',
            'cultivation.in'                   => 'The cultivation must be either growout or nursery.',
            'notes.string'                     => 'The notes must be a string.',
            'notes.max'                        => 'The notes must be less than 1000 characters.',
            'distribution.required'            => 'At least one tank must be selected for distribution.',
            'distribution.*.tankId.exists'     => 'One of the selected tanks does not exist.',
            'distribution.*.quantity.min'      => 'Each tank must receive at least 1 animal.',
            'distribution.*.averageWeight.min' => 'Average weight must be a positive value.',
            'companyId.exists'                 => 'The selected company is invalid.',
        ];
    }
}
