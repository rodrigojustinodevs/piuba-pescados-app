<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\HarvestService;
use App\Presentation\Requests\Harvest\HarvestStoreRequest;
use App\Presentation\Requests\Harvest\HarvestUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class HarvestController
{
    public function __construct(
        protected HarvestService $harvestService
    ) {
    }

    /**
     * Display a listing of harvests.
     */
    public function index(): JsonResponse
    {
        try {
            $harvests  = $this->harvestService->showAllHarvests();
            $data      = $harvests->toArray(request());
            $pagination = $harvests->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified harvest.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $harvest = $this->harvestService->showHarvest($id);

            if (! $harvest instanceof \App\Application\DTOs\HarvestDTO || $harvest->isEmpty()) {
                return ApiResponse::error(null, 'Harvest not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($harvest->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Harvest not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created harvest.
     */
    public function store(HarvestStoreRequest $request): JsonResponse
    {
        try {
            $harvest = $this->harvestService->create($request->validated());

            return ApiResponse::created($harvest->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified harvest.
     */
    public function update(HarvestUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $harvest = $this->harvestService->updateHarvest($id, $request->validated());

            return ApiResponse::success($harvest->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified harvest.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->harvestService->deleteHarvest($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Harvest not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Harvest successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting harvest', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
