<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\SaleDTO;
use App\Application\UseCases\Sale\CreateSaleUseCase;
use App\Application\UseCases\Sale\DeleteSaleUseCase;
use App\Application\UseCases\Sale\ListSalesUseCase;
use App\Application\UseCases\Sale\ShowSaleUseCase;
use App\Application\UseCases\Sale\UpdateSaleUseCase;
use App\Presentation\Requests\Sale\SaleStoreRequest;
use App\Presentation\Requests\Sale\SaleUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SaleController
{
    /**
     * Display a listing of sales.
     */
    public function index(ListSalesUseCase $useCase): JsonResponse
    {
        try {
            $sales      = $useCase->execute();
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
    public function show(string $id, ShowSaleUseCase $useCase): JsonResponse
    {
        try {
            $sale = $useCase->execute($id);

            if (! $sale instanceof SaleDTO || $sale->isEmpty()) {
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
    public function store(SaleStoreRequest $request, CreateSaleUseCase $useCase): JsonResponse
    {
        try {
            $sale = $useCase->execute($request->validated());

            return ApiResponse::created($sale->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified sale.
     */
    public function update(SaleUpdateRequest $request, string $id, UpdateSaleUseCase $useCase): JsonResponse
    {
        try {
            $sale = $useCase->execute($id, $request->validated());

            return ApiResponse::success($sale->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified sale.
     */
    public function destroy(string $id, DeleteSaleUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Sale not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Sale successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting sale', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
