<?php

declare(strict_types=1);

namespace App\Application\UseCases\Settlement;

use App\Application\DTOs\SettlementDTO;
use App\Domain\Repositories\SettlementRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CreateSettlementUseCase
{
    public function __construct(
        protected SettlementRepositoryInterface $settlementRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): SettlementDTO
    {
        return DB::transaction(function () use ($data): SettlementDTO {
            $settlement = $this->settlementRepository->create($data);

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
        });
    }
}
