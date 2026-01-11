<?php

declare(strict_types=1);

namespace App\Application\UseCases\Batche;

use App\Application\DTOs\BatcheDTO;
use App\Domain\Models\Batche;
use App\Domain\Repositories\BatcheRepositoryInterface;
use App\Infrastructure\Mappers\BatcheMapper;
use RuntimeException;

class ShowBatcheUseCase
{
    public function __construct(
        protected BatcheRepositoryInterface $batcheRepository
    ) {
    }

    public function execute(string $id): ?BatcheDTO
    {
        $batche = $this->batcheRepository->showBatche('id', $id);

        if (! $batche instanceof Batche) {
            throw new RuntimeException('Batche not found');
        }

        return BatcheMapper::toDTO($batche);
    }
}
