<?php

declare(strict_types=1);

namespace App\Presentation\Requests\SalesOrder;

use App\Application\Contracts\CompanyResolverInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SalesQuotationStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    #[\Override]
    protected function prepareForValidation(): void
    {
        $map = [
            'clientId'       => 'client_id',
            'issueDate'      => 'issue_date',
            'expirationDate' => 'expiration_date',
        ];

        $normalized = [];

        foreach ($map as $camel => $snake) {
            if ($this->has($camel) && ! $this->has($snake)) {
                $normalized[$snake] = $this->input($camel);
            }
        }

        if ($this->has('items') && is_array($this->input('items'))) {
            $normalized['items'] = array_map(
                static function (mixed $row): array {
                    if (! is_array($row)) {
                        return [];
                    }

                    $item = [];

                    if (isset($row['stockingId']) && ! isset($row['stocking_id'])) {
                        $item['stocking_id'] = $row['stockingId'];
                    }

                    if (isset($row['unitPrice']) && ! isset($row['unit_price'])) {
                        $item['unit_price'] = $row['unitPrice'];
                    }

                    if (isset($row['measureUnit']) && ! isset($row['measure_unit'])) {
                        $item['measure_unit'] = $row['measureUnit'];
                    }

                    if (! isset($row['measure_unit'], $item['measure_unit']) && ! isset($row['measureUnit'])) {
                        $item['measure_unit'] = 'kg';
                    }

                    return array_merge($row, $item);
                },
                $this->input('items'),
            );
        }

        if ($normalized !== []) {
            $this->merge($normalized);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $companyId = $this->companyId();

        $clientRules = [
            'required',
            'uuid',
        ];

        if ($companyId !== null) {
            $clientRules[] = Rule::exists('clients', 'id')
                ->where('company_id', $companyId)
                ->whereNull('deleted_at');
        } else {
            $clientRules[] = static function (string $attribute, mixed $value, \Closure $fail): void {
                $fail('Unable to resolve the authenticated user\'s company.');
            };
        }

        $stockingRules = [
            'required',
            'uuid',
        ];

        if ($companyId !== null) {
            $stockingRules[] = Rule::exists('stockings', 'id')->where(static function ($query) use ($companyId): void {
                $query->whereNull('deleted_at')
                    ->whereIn('batch_id', static function ($sub) use ($companyId): void {
                        $sub->select('id')
                            ->from('batches')
                            ->whereNull('deleted_at')
                            ->whereIn('tank_id', static function ($tankSub) use ($companyId): void {
                                $tankSub->select('id')
                                    ->from('tanks')
                                    ->where('company_id', $companyId)
                                    ->whereNull('deleted_at');
                            });
                    });
            });
        } else {
            $stockingRules[] = static function (string $attribute, mixed $value, \Closure $fail): void {
                $fail('Unable to resolve the authenticated user\'s company.');
            };
        }

        return [
            'client_id'       => $clientRules,
            'issue_date'      => ['required', 'date_format:Y-m-d'],
            'expiration_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:issue_date'],
            'notes'           => ['nullable', 'string', 'max:65535'],

            'items'                => ['required', 'array', 'min:1'],
            'items.*.stocking_id'  => $stockingRules,
            'items.*.quantity'     => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price'   => ['required', 'numeric', 'min:0'],
            'items.*.measure_unit' => ['sometimes', 'string', 'in:kg,g,un'],
        ];
    }

    #[\Override]
    public function messages(): array
    {
        return [
            'client_id.required'             => 'The client is required.',
            'client_id.exists'               => 'The client does not exist or does not belong to the company.',
            'issue_date.required'            => 'The issue date is required.',
            'issue_date.date_format'         => 'The issue date must be in Y-m-d format.',
            'expiration_date.required'       => 'The expiration date is required.',
            'expiration_date.after_or_equal' => 'The expiration date must be equal or greater than the issue date.',
            'items.required'                 => 'The quotation must have at least one item.',
            'items.*.stocking_id.required'   => 'The stocking of the item is required.',
            'items.*.stocking_id.exists'     => 'The stocking does not exist or does not belong to this company.',
            'items.*.quantity.gt'            => 'The quantity of the item must be greater than zero.',
            'items.*.unit_price.min'         => 'The unit price cannot be negative.',
            'items.*.measure_unit.in'        => 'The measure unit must be: kg, g or un.',
        ];
    }

    private function companyId(): ?string
    {
        $hint = $this->input('company_id');

        if (! is_string($hint) || $hint === '') {
            $camel = $this->input('companyId');
            $hint  = (is_string($camel) && $camel !== '') ? $camel : null;
        }

        return app(CompanyResolverInterface::class)->tryResolve($hint);
    }
}
