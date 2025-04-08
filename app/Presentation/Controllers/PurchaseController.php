<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\PurchaseDTO;
use App\Application\Services\PurchaseService;
use App\Presentation\Requests\Purchase\PurchaseStoreRequest;
use App\Presentation\Requests\Purchase\PurchaseUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class PurchaseController
{
    public function __construct(
        protected PurchaseService $purchaseService
    ) {
    }

    public function index(): JsonResponse
    {
        try {
            $purchases  = $this->purchaseService->showAll();
            $data       = $purchases->toArray(request());
            $pagination = $purchases->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $purchase = $this->purchaseService->show($id);

            if (! $purchase instanceof PurchaseDTO || $purchase->isEmpty()) {
                return ApiResponse::error(null, 'Purchase not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($purchase->toArray());
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function store(PurchaseStoreRequest $request): JsonResponse
    {
        try {
            $purchase = $this->purchaseService->create($request->validated());

            return ApiResponse::created($purchase->toArray());
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function update(PurchaseUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $purchase = $this->purchaseService->update($id, $request->validated());

            return ApiResponse::success($purchase->toArray());
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->purchaseService->delete($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Purchase not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Purchase deleted');
        } catch (Throwable $e) {
            return ApiResponse::error($e);
        }
    }
}
