<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\FeedingService;
use App\Presentation\Requests\Feeding\FeedingStoreRequest;
use App\Presentation\Requests\Feeding\FeedingUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class FeedingController
{
    public function __construct(
        protected FeedingService $feedingService
    ) {
    }

    /**
     * Display a listing of feedings.
     */
    public function index(): JsonResponse
    {
        try {
            $feedings   = $this->feedingService->showAllFeedings();
            $data       = $feedings->toArray(request());
            $pagination = $feedings->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified feeding.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $feeding = $this->feedingService->showFeeding($id);

            if (! $feeding instanceof \App\Application\DTOs\FeedingDTO || $feeding->isEmpty()) {
                return ApiResponse::error(null, 'Feeding not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($feeding->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Feeding not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created feeding.
     */
    public function store(FeedingStoreRequest $request): JsonResponse
    {
        try {
            $feeding = $this->feedingService->create($request->validated());

            return ApiResponse::created($feeding->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified feeding.
     */
    public function update(FeedingUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $feeding = $this->feedingService->updateFeeding($id, $request->validated());

            return ApiResponse::success($feeding->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified feeding.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->feedingService->deleteFeeding($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Feeding not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Feeding successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting feeding', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
