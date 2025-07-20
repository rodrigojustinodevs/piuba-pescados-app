<?php

declare(strict_types=1);

namespace App\Application\UseCases\Alert;

use App\Domain\Repositories\AlertRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DeleteAlertUseCase
{
    public function __construct(protected AlertRepositoryInterface $repository)
    {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn (): bool => $this->repository->delete($id));
    }
}
