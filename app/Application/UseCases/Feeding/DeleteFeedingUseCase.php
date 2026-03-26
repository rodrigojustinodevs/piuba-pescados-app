<?php

declare(strict_types=1);

namespace App\Application\UseCases\Feeding;

use App\Domain\Repositories\FeedingRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteFeedingUseCase
{
    public function __construct(
        private FeedingRepositoryInterface $repository,
    ) {
    }

    public function execute(string $id): void
    {
        $this->repository->findOrFail($id);

        DB::transaction(fn (): bool => $this->repository->delete($id));
    }
}
