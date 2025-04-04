<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\MortalityService;
use App\Presentation\Requests\Mortality\MortalityStoreRequest;
use App\Presentation\Requests\Mortality\MortalityUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class MortalityController
{
    public function __construct(
        protected MortalityService $mortalityService
    ) {
    }

    /**
     * Display a listing of mortalities.
     */
    public function index(): JsonResponse
    {
        try {
            $mortalities = $this->mortalityService->showAllMortalities();
            $data        = $mortalities->toArray(request());
            $pagination  = $mortalities->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified mortality.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $mortality = $this->mortalityService->showMortality($id);

            if (! $mortality instanceof \App\Application\DTOs\MortalityDTO || $mortality->isEmpty()) {
                return ApiResponse::error(null, 'Mortality not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($mortality->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Mortality not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created mortality.
     */
    public function store(MortalityStoreRequest $request): JsonResponse
    {
        try {
            $mortality = $this->mortalityService->create($request->validated());

            return ApiResponse::created($mortality->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified mortality.
     */
    public function update(MortalityUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $mortality = $this->mortalityService->updateMortality($id, $request->validated());

            return ApiResponse::success($mortality->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified mortality.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->mortalityService->deleteMortality($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Mortality not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Mortality successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting mortality', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
