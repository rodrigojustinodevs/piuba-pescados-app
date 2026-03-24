<?php

declare(strict_types=1);

namespace App\Application\UseCases\Stock;

use App\Application\Actions\Stock\RegisterStockTransactionAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\StockInputDTO;
use App\Application\DTOs\StockTransactionDTO;
use App\Domain\Enums\StockTransactionDirection;
use App\Domain\Enums\StockTransactionReferenceType;
use App\Domain\Enums\Unit;
use App\Domain\Exceptions\DuplicateStockException;
use App\Domain\Models\Stock;
use App\Domain\Repositories\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class CreateStockUseCase
{
    public function __construct(
        private StockRepositoryInterface $repository,
        private RegisterStockTransactionAction $registerTransaction,
        private CompanyResolverInterface $companyResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $data Dados validados pelo FormRequest
     */
    public function execute(array $data): Stock
    {
        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? null,
        );

        $dto = StockInputDTO::fromArray($data);

        return DB::transaction(function () use ($dto): Stock {
            $existing = $this->repository->findBySupply(
                companyId: $dto->companyId,
                supplyId: $dto->supplyId
            );

            if ($existing instanceof Stock) {
                throw new DuplicateStockException($dto->companyId, $dto->supplyId);
            }

            $stock = $this->repository->create($dto);

            $this->registerTransaction->execute(new StockTransactionDTO(
                companyId:     $dto->companyId,
                quantity:      $dto->quantity,
                unitPrice:     $dto->unitPrice,
                totalCost:     $dto->totalCost,
                unit:          Unit::from($dto->unit),
                direction:     StockTransactionDirection::IN,
                supplyId:      $dto->supplyId,
                referenceId:   $dto->referenceId,
                referenceType: StockTransactionReferenceType::PURCHASE_ITEM,
            ));

            return $stock->load(['supply', 'supplier']);
        });
    }
}
