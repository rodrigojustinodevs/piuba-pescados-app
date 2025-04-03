<?php

declare(strict_types=1);

namespace App\Application\UseCases\Biometry;

use App\Domain\Repositories\BiometryRepositoryInterface;
use App\Presentation\Resources\Biometry\BiometryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListBiometriesUseCase
{
    public function __construct(
        protected BiometryRepositoryInterface $biometryRepository
    ) {
    }

    public function execute(): AnonymousResourceCollection
    {
        $response = $this->biometryRepository->paginate();

        return BiometryResource::collection($response->items())
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
