<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Domain\Enums\PurchaseStatus;
use App\Domain\Exceptions\InvalidPurchaseStatusTransitionException;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class DeletePurchaseUseCase
{
    public function __construct(
        private readonly PurchaseRepositoryInterface $repository,
    ) {}

    public function execute(string $id): void
    {
        $purchase = $this->repository->findOrFail($id);
        $status   = PurchaseStatus::from($purchase->status);

        if ($status->isReceived()) {
            throw new InvalidPurchaseStatusTransitionException(
                from: $status,
                to:   PurchaseStatus::CANCELLED, // semantic proxy for "cannot delete"
            );
        }

        DB::transaction(function () use ($id): void {
            $this->repository->delete($id);
        });
    }
}   