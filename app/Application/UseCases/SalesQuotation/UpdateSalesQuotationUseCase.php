<?php

declare(strict_types=1);

namespace App\Application\UseCases\SalesQuotation;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\SalesOrderUpdateDTO;
use App\Domain\Enums\SalesOrderType;
use App\Domain\Models\SalesOrder;
use App\Domain\Repositories\SalesOrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateSalesQuotationUseCase
{
    public function __construct(
        private SalesOrderRepositoryInterface $salesOrderRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest (snake_case)
     */
    public function execute(string $id, array $data): SalesOrder
    {
        $order = $this->salesOrderRepository->findOrFail($id);

        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? null,
        );

        if ($order->type !== SalesOrderType::QUOTATION) {
            throw new \InvalidArgumentException('Order is not a quotation');
        }

        $dto = SalesOrderUpdateDTO::fromArray($data);

        return DB::transaction(function () use ($order, $dto): SalesOrder {
            $updated = $this->salesOrderRepository->update($order->id, $dto->toScalarAttributes());
            $this->salesOrderRepository->syncItems($updated, $dto->items);

            return $updated->refresh();
        });
    }
}
