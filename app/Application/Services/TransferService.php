<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\TransferDTO;
use App\Application\UseCases\Transfer\CreateTransferUseCase;
use App\Application\UseCases\Transfer\DeleteTransferUseCase;
use App\Application\UseCases\Transfer\ListTransfersUseCase;
use App\Application\UseCases\Transfer\ShowTransferUseCase;
use App\Application\UseCases\Transfer\UpdateTransferUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TransferService
{
    public function __construct(
        protected CreateTransferUseCase $createTransferUseCase,
        protected ListTransfersUseCase $listTransfersUseCase,
        protected ShowTransferUseCase $showTransferUseCase,
        protected UpdateTransferUseCase $updateTransferUseCase,
        protected DeleteTransferUseCase $deleteTransferUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): TransferDTO
    {
        return $this->createTransferUseCase->execute($data);
    }

    public function showAllTransfers(): AnonymousResourceCollection
    {
        return $this->listTransfersUseCase->execute();
    }

    public function showTransfer(string $id): ?TransferDTO
    {
        return $this->showTransferUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateTransfer(string $id, array $data): TransferDTO
    {
        return $this->updateTransferUseCase->execute($id, $data);
    }

    public function deleteTransfer(string $id): bool
    {
        return $this->deleteTransferUseCase->execute($id);
    }
}
