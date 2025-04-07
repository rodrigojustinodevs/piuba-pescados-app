<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\SupplierService;
use App\Presentation\Requests\Supplier\SupplierStoreRequest;
use App\Presentation\Requests\Supplier\SupplierUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SupplierController
{
    public function __construct(
        protected SupplierService $supplierService
    ) {
    }

    /**
     * Display a listing of suppliers.
     */
    public function index(): JsonResponse
    {
        try {
            $suppliers  = $this->supplierService->showAllSuppliers();
            $data       = $suppliers->toArray(request());
            $pagination = $suppliers->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified supplier.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $supplier = $this->supplierService->showSupplier($id);

            if (! $supplier instanceof \App\Application\DTOs\SupplierDTO || $supplier->isEmpty()) {
                return ApiResponse::error(null, 'Supplier not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($supplier->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Supplier not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created supplier.
     */
    public function store(SupplierStoreRequest $request): JsonResponse
    {
        try {
            $supplier = $this->supplierService->create($request->validated());

            return ApiResponse::created($supplier->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified supplier.
     */
    public function update(SupplierUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $supplier = $this->supplierService->updateSupplier($id, $request->validated());

            return ApiResponse::success($supplier->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified supplier.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->supplierService->deleteSupplier($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Supplier not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Supplier successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting supplier', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
