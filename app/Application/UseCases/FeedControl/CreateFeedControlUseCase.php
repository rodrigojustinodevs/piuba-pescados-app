<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedControl;

use App\Application\DTOs\FeedControlDTO;
use App\Domain\Repositories\FeedControlRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CreateFeedControlUseCase
{
    public function __construct(
        protected FeedControlRepositoryInterface $feedControlRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(array $data): FeedControlDTO
    {
        return DB::transaction(function () use ($data): FeedControlDTO {
            $feedControl = $this->feedControlRepository->create($data);

            return new FeedControlDTO(
                id: $feedControl->id,
                feedType: $feedControl->feed_type,
                currentStock: $feedControl->current_stock,
                minimumStock: $feedControl->minimum_stock,
                dailyConsumption: $feedControl->daily_consumption,
                totalConsumption: $feedControl->total_consumption,
                company: [
                    'name' => $feedControl->company->name ?? '',
                ],
                createdAt: $feedControl->created_at?->toDateTimeString(),
                updatedAt: $feedControl->updated_at?->toDateTimeString()
            );
        });
    }
}
