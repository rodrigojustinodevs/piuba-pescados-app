<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\WaterQualityDTO;
use App\Application\Services\WaterQualityService;
use App\Presentation\Requests\WaterQuality\WaterQualityStoreRequest;
use App\Presentation\Requests\WaterQuality\WaterQualityUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class WaterQualityController
{
    public function __construct(
        protected WaterQualityService $waterQualityService
    ) {
    }

    /**
     * Display a listing of water quality records.
     */
    public function index(): JsonResponse
    {
        try {
            $records    = $this->waterQualityService->showAllWaterQualities();
            $data       = $records->toArray(request());
            $pagination = $records->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified water quality record.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $record = $this->waterQualityService->showWaterQuality($id);

            if (! $record instanceof WaterQualityDTO || $record->isEmpty()) {
                return ApiResponse::error(null, 'Water quality record not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($record->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                'Water quality record not found',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Store a newly created water quality record.
     */
    public function store(WaterQualityStoreRequest $request): JsonResponse
    {
        try {
            $record = $this->waterQualityService->create($request->validated());

            return ApiResponse::created($record->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified water quality record.
     */
    public function update(WaterQualityUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $record = $this->waterQualityService->updateWaterQuality($id, $request->validated());

            return ApiResponse::success($record->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified water quality record.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->waterQualityService->deleteWaterQuality($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Water quality record not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Water quality record successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                'Error deleting water quality record',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
