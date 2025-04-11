<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\CostAllocationService;
use App\Presentation\Requests\CostAllocation\CostAllocationStoreRequest;
use App\Presentation\Requests\CostAllocation\CostAllocationUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class CostAllocationController
{
    public function __construct(
        protected CostAllocationService $costAllocationService
    ) {
    }

    /**
     * Display a listing of cost allocations.
     */
    public function index(): JsonResponse
    {
        try {
            $costAllocations = $this->costAllocationService->showAllCostAllocations();
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
    public function show(string $id): JsonResponse
    {
        try {
            $costAllocation = $this->costAllocationService->showCostAllocation($id);

            if (! $costAllocation instanceof \App\Application\DTOs\CostAllocationDTO || $costAllocation->isEmpty()) {
                return ApiResponse::error(null, 'Cost allocation not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($costAllocation->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Cost allocation not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created cost allocation.
     */
    public function store(CostAllocationStoreRequest $request): JsonResponse
    {
        try {
            $costAllocation = $this->costAllocationService->create($request->validated());

            return ApiResponse::created($costAllocation->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified cost allocation.
     */
    public function update(CostAllocationUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $costAllocation = $this->costAllocationService->updateCostAllocation($id, $request->validated());

            return ApiResponse::success($costAllocation->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified cost allocation.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->costAllocationService->deleteCostAllocation($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Cost allocation not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Cost allocation successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting cost allocation', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
