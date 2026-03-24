<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\CostAllocation\CreateCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\DeleteCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\ListCostAllocationsUseCase;
use App\Application\UseCases\CostAllocation\ShowCostAllocationUseCase;
use App\Presentation\Requests\CostAllocation\CostAllocationStoreRequest;
use App\Presentation\Resources\CostAllocation\CostAllocationResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CostAllocationController
{
    public function index(
        Request $request,
        ListCostAllocationsUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only([
                'financial_transaction_id', 'allocation_method', 'per_page', 'page',
            ]),
        );

        return ApiResponse::success(
            data:       CostAllocationResource::collection($paginator->items()),
            pagination: [
                'total'        => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'first_page'   => $paginator->firstPage(),
                'per_page'     => $paginator->perPage(),
            ],
        );
    }

    public function show(
        string $id,
        ShowCostAllocationUseCase $useCase,
    ): JsonResponse {
        return ApiResponse::success(
            data: new CostAllocationResource($useCase->execute($id)),
        );
    }

    public function store(
        CostAllocationStoreRequest $request,
        CreateCostAllocationUseCase $useCase,
    ): JsonResponse {
        $allocation = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new CostAllocationResource($allocation),
            message: 'Rateio de custo criado com sucesso.',
        );
    }

    /**
     * Full reversal — deletes the allocation and undoes all side-effects.
     * Editing a partial allocation is intentionally NOT supported.
     */
    public function destroy(
        string $id,
        DeleteCostAllocationUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(
            message: 'Rateio estornado com sucesso. A transação financeira foi liberada para novo rateio.',
        );
    }
}
