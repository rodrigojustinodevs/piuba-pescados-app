<?php

declare(strict_types=1);

namespace App\Application\UseCases\SalesQuotation;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SalesQuotationDTO;
use App\Domain\Models\SalesOrder;
use App\Domain\Repositories\SalesOrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateSalesQuotationUseCase
{
    public function __construct(
        private CompanyResolverInterface $companyResolver,
        private SalesOrderRepositoryInterface $salesOrderRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data Payload validado pelo FormRequest (snake_case)
     */
    public function execute(array $data): SalesOrder
    {
        // CompanyResolver pertence ao UseCase, não ao FormRequest
        $data['company_id'] = $this->companyResolver->resolve(
            hint: isset($data['company_id']) && is_string($data['company_id'])
                ? $data['company_id']
                : (isset($data['companyId']) && is_string($data['companyId'])
                    ? $data['companyId']
                    : null),
        );

        $dto = SalesQuotationDTO::fromArray($data);

        return DB::transaction(
            fn (): SalesOrder => $this->salesOrderRepository->createQuotationWithItems($dto)
        );
    }
}
