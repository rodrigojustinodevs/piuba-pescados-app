<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeleteStockUseCase
{
    public function __construct(
        private readonly StockRepositoryInterface $repository,
    ) {}

    public function execute(string $id): void
    {
        $this->repository->findOrFail($id);

        DB::transaction(function () use ($id): void {
            $this->repository->delete($id);
        });
    }
}