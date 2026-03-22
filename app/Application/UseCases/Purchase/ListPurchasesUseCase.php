<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\Contracts\CompanyResolverInterface;
use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\PurchaseRepositoryInterface;

final class ListPurchasesUseCase
{
    public function __construct(
        private readonly PurchaseRepositoryInterface $purchaseRepository,
        private readonly CompanyResolverInterface    $companyResolver,
    ) {}

    /**
     * @param array{
     *     status?: string|null,
     *     supplier_id?: string|null,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     per_page?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        // company_id resolvido aqui — UseCase garante isolamento por empresa
        $filters['company_id'] = $this->companyResolver->resolve();

        return $this->purchaseRepository->paginate($filters);
    }
}