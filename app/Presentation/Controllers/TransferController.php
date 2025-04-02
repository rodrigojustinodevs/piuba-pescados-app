<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\TransferService;
use App\Presentation\Requests\Transfer\TransferStoreRequest;
use App\Presentation\Requests\Transfer\TransferUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class TransferController
{
    public function __construct(
        protected TransferService $transferService
    ) {
    }

    /**
     * Display a listing of transfers.
     */
    public function index(): JsonResponse
    {
        try {
            $transfers  = $this->transferService->showAllTransfers();
            $data       = $transfers->toArray(request());
            $pagination = $transfers->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified transfer.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->showTransfer($id);

            if (! $transfer instanceof \App\Application\DTOs\TransferDTO || $transfer->isEmpty()) {
                return ApiResponse::error(null, 'Transfer not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($transfer->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Transfer not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created transfer.
     */
    public function store(TransferStoreRequest $request): JsonResponse
    {
        try {
            $transfer = $this->transferService->create($request->validated());

            return ApiResponse::created($transfer->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified transfer.
     */
    public function update(TransferUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->updateTransfer($id, $request->validated());

            return ApiResponse::success($transfer->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified transfer.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->transferService->deleteTransfer($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Transfer not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Transfer successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting transfer', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
