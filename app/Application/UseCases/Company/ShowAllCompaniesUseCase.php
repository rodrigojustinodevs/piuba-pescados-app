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

    /**
     * @param int         $limit  Items per page (default 25). Page comes from request (?page=1).
     * @param string|null $search Optional search term (filters by name, cnpj, email).
     */
    public function execute(int $limit = 25, ?string $search = null): AnonymousResourceCollection
    {
        $response = $this->companyRepository->paginate($limit, $search);

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
