<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\FeedControlDTO;
use App\Application\UseCases\FeedControl\CreateFeedControlUseCase;
use App\Application\UseCases\FeedControl\DeleteFeedControlUseCase;
use App\Application\UseCases\FeedControl\ListFeedControlsUseCase;
use App\Application\UseCases\FeedControl\ShowFeedControlUseCase;
use App\Application\UseCases\FeedControl\UpdateFeedControlUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedControlService
{
    public function __construct(
        protected CreateFeedControlUseCase $createFeedControlUseCase,
        protected ListFeedControlsUseCase $listFeedControlsUseCase,
        protected ShowFeedControlUseCase $showFeedControlUseCase,
        protected UpdateFeedControlUseCase $updateFeedControlUseCase,
        protected DeleteFeedControlUseCase $deleteFeedControlUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): FeedControlDTO
    {
        return $this->createFeedControlUseCase->execute($data);
    }

    public function showAllFeedControls(): AnonymousResourceCollection
    {
        return $this->listFeedControlsUseCase->execute();
    }

    public function showFeedControl(string $id): ?FeedControlDTO
    {
        return $this->showFeedControlUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFeedControl(string $id, array $data): FeedControlDTO
    {
        return $this->updateFeedControlUseCase->execute($id, $data);
    }

    public function deleteFeedControl(string $id): bool
    {
        return $this->deleteFeedControlUseCase->execute($id);
    }
}
