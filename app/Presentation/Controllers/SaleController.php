<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\SaleService;
use App\Presentation\Requests\Sale\SaleStoreRequest;
use App\Presentation\Requests\Sale\SaleUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SaleController
{
    public function __construct(
        protected SaleService $saleService
    ) {
    }

    /**
     * Display a listing of sales.
     */
    public function index(): JsonResponse
    {
        try {
            $sales      = $this->saleService->showAllSales();
            $data       = $sales->toArray(request());
            $pagination = $sales->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified sale.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $sale = $this->saleService->showSale($id);

            if (! $sale instanceof \App\Application\DTOs\SaleDTO || $sale->isEmpty()) {
                return ApiResponse::error(null, 'Sale not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($sale->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Sale not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created sale.
     */
    public function store(SaleStoreRequest $request): JsonResponse
    {
        try {
            $sale = $this->saleService->create($request->validated());

            return ApiResponse::created($sale->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified sale.
     */
    public function update(SaleUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $sale = $this->saleService->updateSale($id, $request->validated());

            return ApiResponse::success($sale->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified sale.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->saleService->deleteSale($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Sale not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Sale successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting sale', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
