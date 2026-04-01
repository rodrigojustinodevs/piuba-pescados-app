<?php

declare(strict_types=1);

namespace App\Application\UseCases\StockingHistory;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\StockingHistoryDTO;
use App\Domain\Enums\StockingHistoryEvent;
use App\Domain\Exceptions\ClosedStockingException;
use App\Domain\Models\StockingHistory;
use App\Domain\Repositories\StockingHistoryRepositoryInterface;
use App\Domain\Repositories\StockingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateStockingHistoryUseCase
{
    public function __construct(
        private StockingHistoryRepositoryInterface $repository,
        private StockingRepositoryInterface $stockingRepository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public function execute(array $data): StockingHistory
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto      = StockingHistoryDTO::fromArray($data);
        $stocking = $this->stockingRepository->findByCompanyOrFail($dto->stockingId, $dto->companyId);

        if ($stocking->isClosed()) {
            throw new ClosedStockingException($stocking->id);
        }

        return DB::transaction(function () use ($dto, $stocking): StockingHistory {
            $history = $this->repository->create($dto);

            // Model calcula; UseCase persiste — separação de cálculo e persistência
            $attributes = match ($dto->event) {
                StockingHistoryEvent::BIOMETRY  => $stocking->biometryAttributes((float) $dto->averageWeight),
                StockingHistoryEvent::MORTALITY => $stocking->mortalityAttributes((int) $dto->quantity),
                default                         => null,
            };

            if ($attributes !== null) {
                $stocking->update($attributes);
            }

            return $history;
        });
    }
}
