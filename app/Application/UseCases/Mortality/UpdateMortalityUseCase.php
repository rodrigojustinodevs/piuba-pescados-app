<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\DTOs\MortalityInputDTO;
use App\Domain\Models\Mortality;
use App\Domain\Repositories\MortalityRepositoryInterface;
use App\Domain\Services\Mortality\MortalityService;
use App\Domain\Services\Mortality\MortalityValidatorService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateMortalityUseCase
{
    public function __construct(
        protected MortalityRepositoryInterface $mortalityRepository,
        protected MortalityValidatorService $mortalityValidator,
        protected MortalityService $mortalityService
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): Mortality
    {
        return DB::transaction(function () use ($id, $data): Mortality {
            $dto = MortalityInputDTO::fromArray($data);

            $mortality = $this->mortalityRepository->showMortality('id', $id);

            if (! $mortality instanceof Mortality) {
                throw new RuntimeException('Mortality not found');
            }

            $this->mortalityValidator->validate(
                $mortality->batch,
                $dto->quantity,
                $id
            );

            $this->mortalityRepository->update($id, [
                'quantity'       => $dto->quantity,
                'mortality_date' => $dto->mortalityDate,
                'cause'          => $dto->cause,
            ]);

            $this->mortalityService->checkAndDispatchIfCritical($mortality->batch);

            return $mortality;
        });
    }
}
