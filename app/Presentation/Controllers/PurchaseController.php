<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\PurchaseDTO;
use App\Application\UseCases\Purchase\CreatePurchaseUseCase;
use App\Application\UseCases\Purchase\DeletePurchaseUseCase;
use App\Application\UseCases\Purchase\ListPurchasesUseCase;
use App\Application\UseCases\Purchase\ShowPurchaseUseCase;
use App\Application\UseCases\Purchase\UpdatePurchaseUseCase;
use App\Presentation\Requests\Purchase\PurchaseStoreRequest;
use App\Presentation\Requests\Purchase\PurchaseUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class PurchaseController
{
    public function index(ListPurchasesUseCase $useCase): JsonResponse
    {
        try {
            $purchases  = $useCase->execute();
            $data       = $purchases->toArray(request());
            $pagination = $purchases->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function show(string $id, ShowPurchaseUseCase $useCase): JsonResponse
    {
        try {
            $purchase = $useCase->execute($id);

            if (! $purchase instanceof PurchaseDTO || $purchase->isEmpty()) {
                return ApiResponse::error(null, 'Purchase not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($purchase->toArray());
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function store(PurchaseStoreRequest $request, CreatePurchaseUseCase $useCase): JsonResponse
    {
        try {
            $purchase = $useCase->execute($request->validated());

            return ApiResponse::created($purchase->toArray());
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function update(PurchaseUpdateRequest $request, string $id, UpdatePurchaseUseCase $useCase): JsonResponse
    {
        try {
            $purchase = $useCase->execute($id, $request->validated());

            return ApiResponse::success($purchase->toArray());
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function destroy(string $id, DeletePurchaseUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Purchase not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Purchase deleted');
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }
}
