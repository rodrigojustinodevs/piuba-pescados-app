<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\BatcheService;
use App\Presentation\Requests\Batche\BatcheStoreRequest;
use App\Presentation\Requests\Batche\BatcheUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class BatcheController
{
    public function __construct(
        protected BatcheService $batcheService
    ) {
    }

    /**
     * Display a listing of batches.
     */
    public function index(): JsonResponse
    {
        try {
            $batches      = $this->batcheService->showAllBatches();
            $data       = $batches->toArray(request());
            $pagination = $batches->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * Display the specified batche.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $batche = $this->batcheService->showBatche($id);

            if (! $batche instanceof \App\Application\DTOs\BatcheDTO || $batche->isEmpty()) {
                return ApiResponse::error(null, 'Batche not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($batche->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Batche not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created batche.
     */
    public function store(BatcheStoreRequest $request): JsonResponse
    {
        try {
            $batche = $this->batcheService->create($request->validated());

            return ApiResponse::created($batche->toArray());
        } catch (Throwable $exception) {
            return $this->handleException($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified batche.
     */
    public function update(BatcheUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $batche = $this->batcheService->updateBatche($id, $request->validated());

            return ApiResponse::success($batche->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return $this->handleException($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified batche.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->batcheService->deleteBatche($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Batche not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Batche successfully deleted');
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Error deleting batche', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle exceptions and return a formatted error response.
     */
    private function handleException(
        Throwable $exception,
        string $userMessage = 'An error occurred',
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        return ApiResponse::error(
            [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ],
            $userMessage,
            $statusCode
        );
    }
}
