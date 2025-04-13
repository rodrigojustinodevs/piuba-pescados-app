<?php

declare(strict_types=1);

namespace App\Presentation\Resources\FinancialTransaction;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string $id
 * @property-read string $company_id
 * @property-read string $category_id
 * @property-read string $type
 * @property-read string $description
 * @property-read float $amount
 * @property-read \Illuminate\Support\Carbon $transaction_date
 * @property-read \Illuminate\Support\Carbon|null $created_at
 * @property-read \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Domain\Models\Company|null $company
 * @property-read \App\Domain\Models\FinancialCategory|null $category
 */
class FinancialTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'type'            => $this->type,
            'description'     => $this->description,
            'amount'          => $this->amount,
            'transactionDate' => $this->transaction_date,
            'company'         => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'category' => $this->whenLoaded('category', fn (): array => [
                'name' => $this->category->id,
                'type' => $this->category->name,
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
