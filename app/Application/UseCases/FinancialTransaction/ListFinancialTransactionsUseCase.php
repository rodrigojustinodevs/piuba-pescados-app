<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialTransaction;

use App\Domain\Repositories\FinancialTransactionRepositoryInterface;
use App\Presentation\Resources\FinancialTransaction\FinancialTransactionResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListFinancialTransactionsUseCase
{
    public function __construct(
        protected FinancialTransactionRepositoryInterface $financialTransactionRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->financialTransactionRepository->paginate();

        return FinancialTransactionResource::collection($response->items())
            ->additional([
                'pagination' => [
                    'total'        => $response->total(),
                    'current_page' => $response->currentPage(),
                    'last_page'    => $response->lastPage(),
                    'first_page'   => $response->firstPage(),
                    'per_page'     => $response->perPage(),
                ],
            ]);
    }
}
