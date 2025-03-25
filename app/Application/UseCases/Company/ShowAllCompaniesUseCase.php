<?php

declare(strict_types=1);

namespace App\Application\UseCases\Company;

use App\Domain\Repositories\CompanyRepositoryInterface;
use App\Presentation\Resources\Company\CompanyResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShowAllCompaniesUseCase
{
    public function __construct(
        protected CompanyRepositoryInterface $companyRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->companyRepository->paginate();

        return CompanyResource::collection($response->items())
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
