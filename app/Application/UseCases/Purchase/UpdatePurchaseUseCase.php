<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\Actions\ApplyPurchaseToStockAction;
use App\Application\Contracts\CompanyResolverInterface;
use App\Application\DTOs\PurchaseDTO;
use App\Domain\Enums\PurchaseStatus;
use App\Domain\Models\Purchase;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use Illuminate\Support\Facades\DB;

final class UpdatePurchaseUseCase
{
    public function __construct(
        private readonly PurchaseRepositoryInterface $repository,
        private readonly ApplyPurchaseToStockAction  $applyToStock,
        private readonly CompanyResolverInterface    $companyResolver,
    ) {}

    /**
     * @param array<string, mixed> $data Dados já validados pelo FormRequest
     */
    public function execute(string $id, array $data): Purchase
    {
        $purchase = $this->repository->findOrFail($id);

        $data['company_id'] = $this->companyResolver->resolve(
            hint: $data['company_id'] ?? $data['companyId'] ?? (string) $purchase->company_id,
        );

        $dto         = PurchaseDTO::fromArray($data);
        $wasReceived = PurchaseStatus::from($purchase->status)->isReceived();

        return DB::transaction(function () use ($purchase, $dto, $wasReceived): Purchase {
            $updated = $this->repository->update($purchase->id, [
                'supplier_id'    => $dto->supplierId,
                'purchase_date'  => $dto->purchaseDate,
                'invoice_number' => $dto->invoiceNumber,
                'status'         => $dto->status->value,
                'total_price'    => $dto->totalPrice(),
                'received_at'    => $dto->receivedAt,
            ]);

            $this->repository->syncItems($updated, $dto->items);

            if (! $wasReceived && $dto->status->isReceived()) {
                $this->applyToStock->execute($updated->load('items'));
            }

            return $updated->refresh();
        });
    }
}