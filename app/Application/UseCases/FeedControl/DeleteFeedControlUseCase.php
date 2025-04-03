<?php

declare(strict_types=1);

namespace App\Application\UseCases\FeedControl;

use App\Domain\Repositories\FeedControlRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteFeedControlUseCase
{
    public function __construct(
        protected FeedControlRepositoryInterface $feedcontrolRepository
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->feedcontrolRepository->delete($id));
    }
}
