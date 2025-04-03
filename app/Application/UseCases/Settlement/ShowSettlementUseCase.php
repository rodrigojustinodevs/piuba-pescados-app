<?php

declare(strict_types=1);

namespace App\Application\UseCases\Settlement;

use App\Application\DTOs\SettlementDTO;
use App\Domain\Models\Settlement;
use App\Domain\Repositories\SettlementRepositoryInterface;
use Carbon\Carbon;
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

        $settlementDate = $settlement->settlement_date instanceof Carbon
            ? $settlement->settlement_date
            : Carbon::parse($settlement->settlement_date);

        return new SettlementDTO(
            id: $settlement->id,
            batcheId: $settlement->batche_id,
            settlementDate: $settlementDate->toDateString(),
            quantity: $settlement->quantity,
            averageWeight: $settlement->average_weight,
            createdAt: $settlement->created_at?->toDateTimeString(),
            updatedAt: $settlement->updated_at?->toDateTimeString()
        );
    }
}
