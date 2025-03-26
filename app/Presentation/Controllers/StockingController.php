<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\StockingService;
use App\Presentation\Requests\Stocking\StockingStoreRequest;
use App\Presentation\Requests\Stocking\StockingUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class StockingController
{
    public function __construct(
        protected StockingService $stockingService
    ) {
    }

    /**
     * Display a listing of stockings.
     */
    public function index(): JsonResponse
    {
        try {
            $stockings  = $this->stockingService->showAllStockings();
            $data       = $stockings->toArray(request());
            $pagination = $stockings->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified stocking.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $stocking = $this->stockingService->showStocking($id);

            if (! $stocking instanceof \App\Application\DTOs\StockingDTO || $stocking->isEmpty()) {
                return ApiResponse::error(null, 'Stocking not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($stocking->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Stocking not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created stocking.
     */
    public function store(StockingStoreRequest $request): JsonResponse
    {
        try {
            $stocking = $this->stockingService->create($request->validated());

            return ApiResponse::created($stocking->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified stocking.
     */
    public function update(StockingUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $stocking = $this->stockingService->updateStocking($id, $request->validated());

            return ApiResponse::success($stocking->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified stocking.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->stockingService->deleteStocking($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Stocking not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Stocking successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting stocking', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
