<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedControl;

use App\Application\DTOs\FeedControlDTO;
use App\Domain\Models\FeedControl;
use App\Domain\Repositories\FeedControlRepositoryInterface;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateFeedControlUseCase
{
    public function __construct(
        protected FeedControlRepositoryInterface $feedControlRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @throws RuntimeException
     */
    public function execute(string $id, array $data): FeedControlDTO
    {
        return DB::transaction(function () use ($id, $data): FeedControlDTO {
            $feedControl = $this->feedControlRepository->update($id, $data);

            if (! $feedControl instanceof FeedControl) {
                throw new RuntimeException('FeedControl not found');
            }

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
