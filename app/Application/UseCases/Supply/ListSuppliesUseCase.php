<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supply;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\SupplyRepositoryInterface;

final readonly class ListSuppliesUseCase
{
    public function __construct(
        private SupplyRepositoryInterface $supplyRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->supplyRepository->paginate($filters);
    }
}
