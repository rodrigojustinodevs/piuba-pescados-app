<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supply;

use App\Domain\Repositories\SupplyRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class DeleteSupplyUseCase
{
    public function __construct(
        private SupplyRepositoryInterface $supplyRepository,
    ) {
    }

    public function execute(string $id): bool
    {
        return DB::transaction(fn(): bool => $this->supplyRepository->delete($id));
    }
}
