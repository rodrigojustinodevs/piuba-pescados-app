<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedInventory;

use App\Domain\Models\FeedInventory;
use App\Domain\Repositories\FeedInventoryRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class UpdateFeedInventoryUseCase
{
    public function __construct(
        private FeedInventoryRepositoryInterface $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function execute(string $id, array $data): FeedInventory
    {
        return DB::transaction(fn (): FeedInventory => $this->repository->update($id, $data));
    }
}
