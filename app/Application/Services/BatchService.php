<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\BatchDTO;
use App\Application\UseCases\Batch\CreateBatchUseCase;
use App\Application\UseCases\Batch\DeleteBatchUseCase;
use App\Application\UseCases\Batch\ListBatchesUseCase;
use App\Application\UseCases\Batch\ShowBatchUseCase;
use App\Application\UseCases\Batch\UpdateBatchUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BatchService
{
    public function __construct(
        protected CreateBatchUseCase $createBatchUseCase,
        protected ListBatchesUseCase $listBatchesUseCase,
        protected ShowBatchUseCase $showBatchUseCase,
        protected UpdateBatchUseCase $updateBatchUseCase,
        protected DeleteBatchUseCase $deleteBatchUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): BatchDTO
    {
        return $this->createBatchUseCase->execute($data);
    }

    public function showAllBatches(): AnonymousResourceCollection
    {
        return $this->listBatchesUseCase->execute();
    }

    public function showBatch(string $id): ?BatchDTO
    {
        return $this->showBatchUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateBatch(string $id, array $data): BatchDTO
    {
        return $this->updateBatchUseCase->execute($id, $data);
    }

    public function deleteBatch(string $id): bool
    {
        return $this->deleteBatchUseCase->execute($id);
    }
}
