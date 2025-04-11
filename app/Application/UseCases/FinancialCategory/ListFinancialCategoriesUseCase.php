<?php

declare(strict_types=1);

namespace App\Application\UseCases\FinancialCategory;

use App\Domain\Repositories\FinancialCategoryRepositoryInterface;
use App\Presentation\Resources\FinancialCategory\FinancialCategoryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListFinancialCategoriesUseCase
{
    public function __construct(
        protected FinancialCategoryRepositoryInterface $financialCategoryRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->financialCategoryRepository->paginate();

        return FinancialCategoryResource::collection($response->items())
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
