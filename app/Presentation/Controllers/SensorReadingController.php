<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\SensorReadingService;
use App\Presentation\Requests\SensorReading\SensorReadingStoreRequest;
use App\Presentation\Requests\SensorReading\SensorReadingUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SensorReadingController
{
    public function __construct(
        protected SensorReadingService $sensorReadingService
    ) {
    }

    /**
     * Display a listing of sensor readings.
     */
    public function index(): JsonResponse
    {
        try {
            $readings   = $this->sensorReadingService->showAllSensorReadings();
            $data       = $readings->toArray(request());
            $pagination = $readings->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified sensor reading.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $reading = $this->sensorReadingService->showSensorReading($id);

            if (!$reading instanceof \App\Application\DTOs\SensorReadingDTO || $reading->isEmpty()) {
                return ApiResponse::error(null, 'Sensor reading not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($reading->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Sensor reading not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created sensor reading.
     */
    public function store(SensorReadingStoreRequest $request): JsonResponse
    {
        try {
            $reading = $this->sensorReadingService->create($request->validated());

            return ApiResponse::created($reading->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified sensor reading.
     */
    public function update(SensorReadingUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $reading = $this->sensorReadingService->updateSensorReading($id, $request->validated());

            return ApiResponse::success($reading->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified sensor reading.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->sensorReadingService->deleteSensorReading($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Sensor reading not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Sensor reading successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting sensor reading', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
