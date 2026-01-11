<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batche;

use App\Application\DTOs\BatcheDTO;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Infrastructure\Mappers\BatcheMapper;
use Illuminate\Support\Facades\DB;

class CreateBatcheUseCase
{
    public function __construct(
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): BatcheDTO
    {
        return DB::transaction(function () use ($data): BatcheDTO {
            $mappedData = BatcheMapper::fromRequest($data);

            $batche = $this->batcheRepository->create($mappedData);

            return BatcheMapper::toDTO($batche);
        });
    }
}
