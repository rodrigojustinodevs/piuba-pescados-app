<?php

declare(strict_types=1);

namespace App\Application\UseCases\SalesOrder;

use App\Domain\Models\SalesOrder;
use App\Domain\Repositories\SalesOrderRepositoryInterface;

final readonly class UpdateSalesOrderUseCase
{
    public function __construct(
        private SalesOrderRepositoryInterface $salesOrderRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): SalesOrder
    {
        $order = $this->salesOrderRepository->findOrFail($id);

        $order->update($data);

        return $order;
    }
}
