<?php

declare(strict_types=1);

namespace App\Application\UseCases\Settlement;

use App\Application\DTOs\SettlementDTO;
use App\Domain\Models\Settlement;
use App\Domain\Repositories\SettlementRepositoryInterface;
use App\Infrastructure\Mappers\SettlementMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateSettlementUseCase
{
    public function __construct(
        protected SettlementRepositoryInterface $settlementRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): SettlementDTO
    {
        return DB::transaction(function () use ($id, $data): SettlementDTO {
            $mappedData = SettlementMapper::fromRequest($data);
            $settlement = $this->settlementRepository->update($id, $mappedData);

            if (! $settlement instanceof Settlement) {
                throw new RuntimeException('Settlement not found');
            }

            return SettlementMapper::toDTO($settlement);
        });
    }
}
