<?php

declare(strict_types=1);

namespace App\Application\UseCases\Settlement;

use App\Application\DTOs\SettlementDTO;
use App\Domain\Repositories\SettlementRepositoryInterface;
use App\Infrastructure\Mappers\SettlementMapper;
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
            $mappedData = SettlementMapper::fromRequest($data);
            $settlement = $this->settlementRepository->create($mappedData);

            return SettlementMapper::toDTO($settlement);
        });
    }
}
