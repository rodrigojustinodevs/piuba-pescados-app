<?php

declare(strict_types=1);

namespace App\Presentation\Requests\TankHistory;

use Illuminate\Foundation\Http\FormRequest;

class TankHistoryStoreRequest extends FormRequest
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
            'tankId' => [
                'required',
                'uuid',
                'exists:tanks,id',
            ],

            'event' => [
                'required',
                'string',
                'in:cleaning,maintenance,fallowing',
            ],

            'eventDate' => [
                'required',
                'date',
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'performedBy' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'tankId.required' => 'The tank ID is required.',
            'tankId.uuid'     => 'The tank ID must be a valid UUID.',
            'tankId.exists'   => 'The selected tank does not exist.',

            'event.required' => 'The event type is required.',
            'event.in'       => 'The event must be one of: cleaning, maintenance, fallowing.',

            'eventDate.required' => 'The event date is required.',
            'eventDate.date'     => 'The event date must be a valid date.',
        ];
    }

    /** @return array<string, mixed> */
    private function normalizeInput(): array
    {
        $data = [];

        $map = [
            'tank_id'      => 'tankId',
            'event_date'   => 'eventDate',
            'performed_by' => 'performedBy',
        ];

        foreach ($map as $snake => $camel) {
            if ($this->has($snake) && ! $this->has($camel)) {
                $data[$camel] = $this->input($snake);
            }
        }

        return $data;
    }
}
