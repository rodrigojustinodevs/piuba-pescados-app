<?php

declare(strict_types=1);

namespace App\Application\UseCases\Settlement;

use App\Application\DTOs\SettlementDTO;
use App\Domain\Models\Settlement;
use App\Domain\Repositories\SettlementRepositoryInterface;
use App\Infrastructure\Mappers\SettlementMapper;
use RuntimeException;

class ShowSettlementUseCase
{
    public function __construct(
        protected SettlementRepositoryInterface $settlementRepository
    ) {
    }

    public function execute(string $id): ?SettlementDTO
    {
        $settlement = $this->settlementRepository->showSettlement('id', $id);

        if (! $settlement instanceof Settlement) {
            throw new RuntimeException('Settlement not found');
        }

        return SettlementMapper::toDTO($settlement);
    }
}
