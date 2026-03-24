<?php

declare(strict_types=1);

namespace App\Presentation\Requests\Dashboard;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class DashboardTrendsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<
     *     string,
     *     list<\Illuminate\Contracts\Validation\ValidationRule|\Illuminate\Validation\Rules\In|string>
     * >
     */
    public function rules(): array
    {
        return [
            'tank_id'   => ['sometimes', 'uuid', 'exists:tanks,id'],
            'parameter' => ['sometimes', Rule::in([
                'temperature', 'ph', 'dissolved_oxygen',
                'ammonia', 'salinity', 'turbidity',
            ])],
            'period'      => ['sometimes', Rule::in(['24h', '7d', '30d'])],
            'granularity' => ['sometimes', Rule::in(['hour', 'day'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    #[\Override]
    public function messages(): array
    {
        return [
            'parameter.in' => 'Parameter invalid. Use: temperature, ph, dissolved_oxygen, ammonia, '
                . 'salinity, turbidity.',
            'period.in' => 'Period invalid. Use: 24h, 7d, 30d.',
        ];
    }
}
