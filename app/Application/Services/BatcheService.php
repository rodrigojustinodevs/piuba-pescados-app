<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\BatcheDTO;
use App\Domain\Enums\Cultivation;
use App\Domain\Enums\Status;
use App\Domain\Models\Batche;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Presentation\Resources\Batche\BatcheResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class BatcheService
{
    public function __construct(
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): BatcheDTO
    {
        return DB::transaction(function () use ($data): BatcheDTO {
            $batche = $this->batcheRepository->create($data);

            return $this->mapToDTO($batche);
        });
    }

    public function showAllBatches(): AnonymousResourceCollection
    {
        $response = $this->batcheRepository->paginate();

        return BatcheResource::collection($response->items())
            ->additional([
                'pagination' => [
                    'total'        => $response->total(),
                    'current_page' => $response->currentPage(),
                    'last_page'    => $response->lastPage(),
                    'first_page'   => $response->firstPage(),
                    'per_page'     => $response->perPage(),
                ],
            ]);
    }

    /**
     * Returns the details of a batche.
     */
    public function showBatche(string $id): ?BatcheDTO
    {
        $batche = $this->batcheRepository->showBatche('id', $id);

        if (! $batche instanceof Batche) {
            return null;
        }

        return $this->mapToDTO($batche);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateBatche(string $id, array $data): BatcheDTO
    {
        return DB::transaction(function () use ($id, $data): BatcheDTO {
            $batche = $this->batcheRepository->update($id, $data);

            if (! $batche instanceof Batche) {
                throw new \Exception('Batche not found');
            }

            return $this->mapToDTO($batche);
        });
    }

    public function deleteBatche(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->batcheRepository->delete($id));
    }

    private function mapToDTO(?Batche $batche): ?BatcheDTO
    {
        if (! $batche instanceof Batche) {
            return null;
        }

        return new BatcheDTO(
            id: $batche->id,
            entryDate: $batche->entry_date?->toDateString(),
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
