<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialTransaction;

use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Application\Services\FinancialTransactionService;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateFinancialTransactionUseCase
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $repository,
        private FinancialTransactionService $transactionService,
    ) {
    }

    /**
     * @param array<string, mixed> $data Data already validated by the FormRequest
     */
    public function execute(string $id, array $data): FinancialTransaction
    {
        $transaction = $this->repository->findOrFail($id);

        $this->transactionService->guardAmountImmutability(
            transaction: $transaction,
            newAmount:   isset($data['amount']) ? (float) $data['amount'] : null,
        );

        $dto = $this->reconstitute($transaction, $data);

        $this->transactionService->validateCategoryType(
            categoryId:      $dto->financialCategoryId,
            transactionType: $dto->type,
        );

        $dto = $this->transactionService->applyPaymentDateToDTO($dto);

        return DB::transaction(
            fn (): FinancialTransaction => $this->repository->update($id, [
                'financial_category_id' => $dto->financialCategoryId,
                'type'                  => $dto->type->value,
                'status'                => $dto->status->value,
                'amount'                => $dto->amount,
                'due_date'              => $dto->dueDate,
                'payment_date'          => $dto->paymentDate,
                'description'           => $dto->description,
                'notes'                 => $dto->notes,
            ])
        );
    }

    /**
     * Merges the persisted state with the incoming patch data, producing a
     * fully-typed DTO that represents the desired final state.
     *
     * Fields absent from $data keep the entity's current value.
     * Fields present as null in $data explicitly clear the field.
     *
     * @param array<string, mixed> $data
     */
    private function reconstitute(FinancialTransaction $transaction, array $data): FinancialTransactionInputDTO
    {
        return FinancialTransactionInputDTO::fromArray([
            'company_id'            => (string) $transaction->company_id,
            'financial_category_id' => (string) ($data['financial_category_id']
                                       ?? $transaction->financial_category_id),
            'type' => array_key_exists('type', $data)
                                       ? $data['type']
                                       : $transaction->type->value,
            'status' => array_key_exists('status', $data)
                                       ? $data['status']
                                       : $transaction->status->value,
            'amount' => array_key_exists('amount', $data)
                                       ? (float) $data['amount']
                                       : (float) $transaction->amount,
            'due_date'     => $data['due_date'] ?? $transaction->due_date->toDateString(),
            'payment_date' => array_key_exists('payment_date', $data)
                                       ? $data['payment_date']
                                       : $transaction->payment_date?->toDateString(),
            'description' => array_key_exists('description', $data)
                                       ? $data['description']
                                       : $transaction->description,
            'notes' => array_key_exists('notes', $data)
                                       ? $data['notes']
                                       : $transaction->notes,
            'reference_type' => $transaction->reference_type?->value,
            'reference_id'   => $transaction->reference_id,
        ]);
    }
}
