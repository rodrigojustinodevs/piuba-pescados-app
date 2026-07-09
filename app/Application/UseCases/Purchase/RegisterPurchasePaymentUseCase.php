<?php

declare(strict_types=1);

namespace App\Application\UseCases\Purchase;

use App\Application\DTOs\RegisterPurchasePaymentDTO;
use App\Domain\Exceptions\PurchasePaymentException;
use App\Domain\Models\Purchase;
use App\Domain\Models\PurchasePayment;
use App\Domain\Repositories\PurchaseRepositoryInterface;
use Illuminate\Support\Facades\DB;

final readonly class RegisterPurchasePaymentUseCase
{
    public function __construct(
        private PurchaseRepositoryInterface $repository,
    ) {
    }

    public function execute(RegisterPurchasePaymentDTO $dto): Purchase
    {
        return DB::transaction(function () use ($dto): Purchase {
            $purchase = $this->repository->findOrFail($dto->purchaseId);
            $purchase->load('payments');

            if (! $purchase->canRegisterPayment()) {
                if ($purchase->payment_status->value === 'paid') {
                    throw PurchasePaymentException::alreadyPaid();
                }

                throw PurchasePaymentException::cancelled();
            }

            $balance = $purchase->getOutstandingBalance();

            if ($dto->amount > $balance) {
                throw PurchasePaymentException::amountExceedsBalance($dto->amount, $balance);
            }

            PurchasePayment::create([
                'purchase_id'    => $dto->purchaseId,
                'payment_date'   => $dto->paymentDate,
                'amount'         => $dto->amount,
                'payment_method' => $dto->paymentMethod,
                'reference'      => $dto->reference,
                'notes'          => $dto->notes,
            ]);

            $purchase->unsetRelation('payments');
            $purchase->updatePaymentStatus();

            return $purchase->refresh()->load(['supplier', 'company', 'items.supply', 'payments']);
        });
    }
}
