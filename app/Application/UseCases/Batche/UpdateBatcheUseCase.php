<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batche;

use App\Application\DTOs\BatcheDTO;
use App\Domain\Models\Batche;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Infrastructure\Mappers\BatcheMapper;
use Illuminate\Support\Facades\DB;
use RuntimeException;

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
        return DB::transaction(function () use ($id, $data): BatcheDTO {
            $mappedData = BatcheMapper::fromRequest($data);

            $batche = $this->batcheRepository->update($id, $mappedData);

            if (! $batche instanceof Batche) {
                throw new RuntimeException('Batche not found');
            }

            return BatcheMapper::toDTO($batche);
        });
    }
}
