<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialTransaction;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\FinancialTransactionInputDTO;
use App\Application\Services\FinancialTransactionService;
use App\Domain\Models\FinancialTransaction;
use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateFinancialTransactionUseCase
{
    public function __construct(
        private FinancialTransactionRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
        private FinancialTransactionService $transactionService,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados já validados pelo FormRequest
     */
    public function execute(array $data): FinancialTransaction
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = FinancialTransactionInputDTO::fromArray($data);

        $this->transactionService->validateCategoryType(
            categoryId:      $dto->financialCategoryId,
            transactionType: $dto->type,
        );

        $dto = $this->transactionService->applyPaymentDateToDTO($dto);

        return DB::transaction(fn (): FinancialTransaction => $this->repository->create($dto));
    }
}
