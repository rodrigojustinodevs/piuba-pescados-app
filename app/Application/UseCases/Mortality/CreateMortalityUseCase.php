<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\Actions\Mortality\CheckCriticalMortalityAction;
use App\Application\Actions\Mortality\ValidateMortalityQuantityAction;
use App\Application\DTOs\MortalityInputDTO;
use App\Domain\Events\MortalityRecorded;
use App\Domain\Models\Mortality;
use App\Domain\Repositories\BatchRepositoryInterface;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Infrastructure\Security\CompanyContext;
use Illuminate\Support\Facades\DB;

final readonly class CreateMortalityUseCase
{
    public function __construct(
        private MortalityRepositoryInterface $repository,
        private BatchRepositoryInterface $batchRepository,
        private ValidateMortalityQuantityAction $validateQuantity,
        private CheckCriticalMortalityAction $checkCritical,
    ) {
    }

    /**
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(array $data): Mortality
    {
        $dto   = MortalityInputDTO::fromArray($data);
        $batch = $this->batchRepository->findOrFail($dto->batchId);

        $this->validateQuantity->execute($batch, $dto->quantity);

        return DB::transaction(function () use ($dto, $batch): Mortality {
            $mortality = $this->repository->create($dto);

            MortalityRecorded::dispatch($mortality, CompanyContext::getCompanyId() ?? '');

            $this->checkCritical->execute($batch);

            return $mortality;
        });
    }
}
