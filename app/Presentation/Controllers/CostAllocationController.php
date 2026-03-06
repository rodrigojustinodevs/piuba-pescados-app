<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\CostAllocationDTO;
use App\Application\UseCases\CostAllocation\CreateCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\DeleteCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\ListCostAllocationsUseCase;
use App\Application\UseCases\CostAllocation\ShowCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\UpdateCostAllocationUseCase;
use App\Presentation\Requests\CostAllocation\CostAllocationStoreRequest;
use App\Presentation\Requests\CostAllocation\CostAllocationUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class CostAllocationController
{
    /**
     * Display a listing of cost allocations.
     */
    public function index(ListCostAllocationsUseCase $useCase): JsonResponse
    {
        try {
            $costAllocations = $useCase->execute();
            $data            = $costAllocations->toArray(request());
            $pagination      = $costAllocations->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified cost allocation.
     */
    public function show(string $id, ShowCostAllocationUseCase $useCase): JsonResponse
    {
        try {
            $costAllocation = $useCase->execute($id);

            if (! $costAllocation instanceof CostAllocationDTO || $costAllocation->isEmpty()) {
                return ApiResponse::error(
                    null,
                    'Cost allocation not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            return ApiResponse::success($costAllocation->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                'Cost allocation not found',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Store a newly created cost allocation.
     */
    public function store(CostAllocationStoreRequest $request, CreateCostAllocationUseCase $useCase): JsonResponse
    {
        try {
            $costAllocation = $useCase->execute($request->validated());

            return ApiResponse::created($costAllocation->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified cost allocation.
     */
    public function update(CostAllocationUpdateRequest $request, string $id, UpdateCostAllocationUseCase $useCase): JsonResponse
    {
        try {
            $costAllocation = $useCase->execute($id, $request->validated());

            return ApiResponse::success($costAllocation->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified cost allocation.
     */
    public function destroy(string $id, DeleteCostAllocationUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(
                    null,
                    'Cost allocation not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            return ApiResponse::success(
                null,
                Response::HTTP_OK,
                'Cost allocation successfully deleted'
            );
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                'Error deleting cost allocation',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
