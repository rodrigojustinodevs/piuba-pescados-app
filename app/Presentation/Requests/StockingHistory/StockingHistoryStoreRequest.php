<?php

declare(strict_types=1);

namespace App\Presentation\Requests\StockingHistory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StockingHistoryStoreRequest extends FormRequest
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
            'stockingId' => [
                'required',
                'uuid',
                'exists:stockings,id',
            ],

            'event' => [
                'required',
                'string',
                'in:biometry,mortality,transfer,medication',
            ],

            'eventDate' => [
                'required',
                'date',
            ],

            // Required for mortality and transfer
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
            ],

            // Required for biometry
            'averageWeight' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Add conditional cross-field validation after base rules pass.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v): void {
            $event = $this->input('event');

            if (in_array($event, ['mortality', 'transfer'], true) && empty($this->input('quantity'))) {
                $v->errors()->add('quantity', "The quantity field is required for {$event} events.");
            }

            if ($event === 'biometry' && empty($this->input('averageWeight'))) {
                $v->errors()->add('averageWeight', 'The average weight field is required for biometry events.');
            }
        });
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'stockingId.required' => 'The stocking ID is required.',
            'stockingId.uuid'     => 'The stocking ID must be a valid UUID.',
            'stockingId.exists'   => 'The selected stocking does not exist.',

            'event.required' => 'The event type is required.',
            'event.in'       => 'The event must be one of: biometry, mortality, transfer, medication.',

            'eventDate.required' => 'The event date is required.',
            'eventDate.date'     => 'The event date must be a valid date.',

            'quantity.integer' => 'The quantity must be an integer.',
            'quantity.min'     => 'The quantity must be at least 1.',

            'averageWeight.numeric' => 'The average weight must be a number.',
            'averageWeight.gt'      => 'The average weight must be greater than zero.',
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeInput(): array
    {
        $data = [];

        $map = [
            'stocking_id'    => 'stockingId',
            'event_date'     => 'eventDate',
            'average_weight' => 'averageWeight',
        ];

        foreach ($map as $snake => $camel) {
            if ($this->has($snake) && ! $this->has($camel)) {
                $data[$camel] = $this->input($snake);
            }
        }

        return $data;
    }
}
