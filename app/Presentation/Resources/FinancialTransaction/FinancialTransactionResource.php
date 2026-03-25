<?php

declare(strict_types=1);

namespace App\Presentation\Resources\FinancialTransaction;

use App\Domain\Enums\FinancialTransactionReferenceType;
use App\Domain\Enums\FinancialTransactionStatus;
use App\Domain\Enums\FinancialType;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read string                               $id
 * @property-read string                               $company_id
 * @property-read string                               $financial_category_id
 * @property-read FinancialType                        $type
 * @property-read FinancialTransactionStatus           $status
 * @property-read float                                $amount
 * @property-read \Illuminate\Support\Carbon           $due_date
 * @property-read \Illuminate\Support\Carbon|null      $payment_date
 * @property-read FinancialTransactionReferenceType|null $reference_type
 * @property-read string|null                          $reference_id
 * @property-read string|null                          $description
 * @property-read string|null                          $notes
 * @property-read \Illuminate\Support\Carbon|null      $created_at
 * @property-read \Illuminate\Support\Carbon|null      $updated_at
 * @property-read \App\Domain\Models\Company|null      $company
 * @property-read \App\Domain\Models\FinancialCategory|null $category
 */
class FinancialTransactionResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    #[\Override]
    public function toArray($request): array
    {
        $type   = $this->type;
        $status = $this->status;

        return [
            'id'            => $this->id,
            'type'          => $type->value,
            'typeLabel'     => $type->label(),
            'status'        => $status->value,
            'statusLabel'   => $status->label(),
            'amount'        => (float) $this->amount,
            'dueDate'       => $this->due_date->toDateString(),
            'paymentDate'   => $this->payment_date?->toDateString(),
            'description'   => $this->description,
            'notes'         => $this->notes,
            'referenceType' => $this->reference_type?->value,
            'referenceId'   => $this->reference_id,
            'company'       => $this->whenLoaded('company', fn (): array => [
                'name' => $this->company->name,
            ]),
            'category' => $this->whenLoaded('category', fn (): array => [
                'id'        => $this->category->id,
                'name'      => $this->category->name,
                'type'      => $this->category->type->value,
                'typeLabel' => $this->category->type->label(),
            ]),
            'createdAt' => $this->created_at?->toDateTimeString(),
            'updatedAt' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
