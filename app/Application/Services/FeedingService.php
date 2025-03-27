<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\FeedingDTO;
use App\Application\UseCases\Feeding\CreateFeedingUseCase;
use App\Application\UseCases\Feeding\DeleteFeedingUseCase;
use App\Application\UseCases\Feeding\ListFeedingsUseCase;
use App\Application\UseCases\Feeding\ShowFeedingUseCase;
use App\Application\UseCases\Feeding\UpdateFeedingUseCase;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedingService
{
    public function __construct(
        protected CreateFeedingUseCase $createFeedingUseCase,
        protected ListFeedingsUseCase $listFeedingsUseCase,
        protected ShowFeedingUseCase $showFeedingUseCase,
        protected UpdateFeedingUseCase $updateFeedingUseCase,
        protected DeleteFeedingUseCase $deleteFeedingUseCase
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): FeedingDTO
    {
        return $this->createFeedingUseCase->execute($data);
    }

    public function showAllFeedings(): AnonymousResourceCollection
    {
        return $this->listFeedingsUseCase->execute();
    }

    public function showFeeding(string $id): ?FeedingDTO
    {
        return $this->showFeedingUseCase->execute($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateFeeding(string $id, array $data): FeedingDTO
    {
        return $this->updateFeedingUseCase->execute($id, $data);
    }

    public function deleteFeeding(string $id): bool
    {
        return $this->deleteFeedingUseCase->execute($id);
    }
}
