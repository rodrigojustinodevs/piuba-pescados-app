<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\StockService;
use App\Presentation\Requests\Stock\StockStoreRequest;
use App\Presentation\Requests\Stock\StockUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class StockController
{
    public function __construct(
        protected StockService $stockService
    ) {
    }

    /**
     * Display a listing of stocks.
     */
    public function index(): JsonResponse
    {
        try {
            $stocks     = $this->stockService->showAllStocks();
            $data       = $stocks->toArray(request());
            $pagination = $stocks->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified stock.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $stock = $this->stockService->showStock($id);

            if (! $stock instanceof \App\Application\DTOs\StockDTO || $stock->isEmpty()) {
                return ApiResponse::error(null, 'Stock not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($stock->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Stock not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created stock.
     */
    public function store(StockStoreRequest $request): JsonResponse
    {
        try {
            $stock = $this->stockService->create($request->validated());

            return ApiResponse::created($stock->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified stock.
     */
    public function update(StockUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $stock = $this->stockService->updateStock($id, $request->validated());

            return ApiResponse::success($stock->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified stock.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->stockService->deleteStock($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Stock not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Stock successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting stock', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
