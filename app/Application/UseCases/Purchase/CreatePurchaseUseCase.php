<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\Actions\ApplyPurchaseToStockAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\PurchaseDTO;
use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class CreatePurchaseUseCase
{
    public function __construct(
        private readonly PurchaseRepositoryInterface $repository,
        private readonly ApplyPurchaseToStockAction  $applyToStock,
        private readonly CompanyResolverInterface    $companyResolver,
    ) {}

    /**
     * @param array<string, mixed> $data Dados já validados pelo FormRequest
     */
    public function execute(array $data): Purchase
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = PurchaseDTO::fromArray($data);

        return DB::transaction(function () use ($dto): Purchase {
            $purchase = $this->repository->create($dto);

            if ($dto->status->isReceived()) {
                $this->applyToStock->execute($purchase);
            }

            return $purchase;
        });
    }
}