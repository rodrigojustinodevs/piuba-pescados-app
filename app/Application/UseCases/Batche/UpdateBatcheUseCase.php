<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batche;

use App\Application\DTOs\BatcheDTO;
use App\Domain\Enums\Cultivation;
use App\Domain\Enums\Status;
use App\Domain\Repositories\BatcheRepositoryInterface;
use Carbon\Carbon;
use Exception;

class UpdateBatcheUseCase
{
    public function __construct(
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): BatcheDTO
    {
        $batche = $this->batcheRepository->update($id, $data);

        if (! $batche) {
            throw new Exception('Batche not found');
        }

        $entryDate = $batche->entry_date instanceof Carbon
            ? $batche->entry_date
            : Carbon::parse($batche->entry_date);

        return new BatcheDTO(
            id: $batche->id,
            entryDate:$entryDate->toDateString(),
            initialQuantity: $batche->initial_quantity,
            species: $batche->species,
            status: Status::from($batche->status),
            cultivation: Cultivation::from($batche->cultivation),
            tank: [
                'id'   => $batche->tank->id ?? '',
                'name' => $batche->tank->name ?? '',
            ],
            createdAt: $batche->created_at?->toDateTimeString(),
            updatedAt: $batche->updated_at?->toDateTimeString()
        );
    }
}
