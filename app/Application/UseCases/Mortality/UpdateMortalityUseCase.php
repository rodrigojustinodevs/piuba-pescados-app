<?php

declare(strict_types=1);

namespace App\Application\UseCases\Mortality;

use App\Application\Actions\Mortality\CheckCriticalMortalityAction;
use App\Application\Actions\Mortality\ValidateMortalityQuantityAction;
use App\Application\DTOs\MortalityInputDTO;
use App\Domain\Models\Mortality;
use App\Domain\Repositories\MortalityRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateMortalityUseCase
{
    public function __construct(
        private MortalityRepositoryInterface $repository,
        private ValidateMortalityQuantityAction $validateQuantity,
        private CheckCriticalMortalityAction $checkCritical,
    ) {
    }

    /**
     * @param array<string, mixed> $data Validated data from the FormRequest
     */
    public function execute(string $id, array $data): Mortality
    {
        $dto       = MortalityInputDTO::fromArray($data);
        $mortality = $this->repository->findOrFail($id);

        $this->validateQuantity->execute($mortality->batch, $dto->quantity, $id);

        return DB::transaction(function () use ($id, $dto, $mortality): Mortality {
            $updated = $this->repository->update($id, [
                'quantity'       => $dto->quantity,
                'mortality_date' => $dto->mortalityDate,
                'cause'          => $dto->cause,
            ]);

            $this->checkCritical->execute($mortality->batch);

            return $updated;
        });
    }
}
