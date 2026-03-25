<?php

declare(strict_types=1);

namespace App\Application\UseCases\TankHistory;

use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\TankHistoryDTO;
use App\Domain\Models\Tank;
use App\Domain\Models\TankHistory;
use App\Domain\Repositories\TankHistoryRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final readonly class CreateTankHistoryUseCase
{
    public function __construct(
        private TankHistoryRepositoryInterface $repository,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(array $data): TankHistory
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = TankHistoryDTO::fromArray($data);

        $tank = Tank::where('id', $dto->tankId)
            ->where('company_id', $dto->companyId)
            ->first();

        if ($tank === null) {
            throw new ModelNotFoundException('Tank not found or does not belong to this company.');
        }

        return DB::transaction(function () use ($dto, $tank): TankHistory {
            $history = $this->repository->create($dto);

            if ($dto->event->blocksNewAllocations()) {
                $tank->update(['status' => $tank->statusForEvent($dto->event)]);
            }

            return $history;
        });
    }
}
