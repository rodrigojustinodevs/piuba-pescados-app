<?php

declare(strict_types=1);

namespace App\Application\UseCases\Supply;

use App\Domain\Models\Supply;
use App\Domain\Repositories\SupplyRepositoryInterface;

final readonly class ShowSupplyUseCase
{
    public function __construct(
        private SupplyRepositoryInterface $supplyRepository,
    ) {
    }

    public function execute(string $id): Supply
    {
        return $this->supplyRepository->findOrFail($id);
    }
}
