<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\UseCases\Batche\CreateBatcheUseCase;
use App\Application\UseCases\Batche\ListBatchesUseCase;
use App\Application\UseCases\Batche\ShowBatcheUseCase;
use App\Application\UseCases\Batche\UpdateBatcheUseCase;
use App\Application\UseCases\Batche\DeleteBatcheUseCase;
use App\Application\DTOs\BatcheDTO;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BatcheService
{
    public function __construct(
        protected CreateBatcheUseCase $createBatcheUseCase,
        protected ListBatchesUseCase $listBatchesUseCase,
        protected ShowBatcheUseCase $showBatcheUseCase,
        protected UpdateBatcheUseCase $updateBatcheUseCase,
        protected DeleteBatcheUseCase $deleteBatcheUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): BatcheDTO
    {
        return $this->createBatcheUseCase->execute($data);
    }

    public function showAllBatches(): AnonymousResourceCollection
    {
        return $this->listBatchesUseCase->execute();
    }

    public function showBatche(string $id): ?BatcheDTO
    {
        return $this->showBatcheUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateBatche(string $id, array $data): BatcheDTO
    {
        return $this->updateBatcheUseCase->execute($id, $data);
    }

    public function deleteBatche(string $id): bool
    {
        return $this->deleteBatcheUseCase->execute($id);
    }
}
