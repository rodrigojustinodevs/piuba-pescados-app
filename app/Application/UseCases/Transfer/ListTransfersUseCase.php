<?php

declare(strict_types=1);

namespace App\Application\UseCases\Transfer;

use App\Domain\Repositories\PaginationInterface;
use App\Domain\Repositories\TransferRepositoryInterface;

final readonly class ListTransfersUseCase
{
    public function __construct(
        private TransferRepositoryInterface $transferRepository,
    ) {
    }

    /**
     * @param array{
     *     companyId?: string|null,
     *     batchId?: string|null,
     *     originTankId?: string|null,
     *     destinationTankId?: string|null,
     *     perPage?: int,
     * } $filters
     */
    public function execute(array $filters = []): PaginationInterface
    {
        return $this->transferRepository->paginate($filters);
    }
}
