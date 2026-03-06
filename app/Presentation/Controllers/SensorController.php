<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\SensorDTO;
use App\Application\UseCases\Sensor\CreateSensorUseCase;
use App\Application\UseCases\Sensor\DeleteSensorUseCase;
use App\Application\UseCases\Sensor\ListSensorsUseCase;
use App\Application\UseCases\Sensor\ShowSensorUseCase;
use App\Application\UseCases\Sensor\UpdateSensorUseCase;
use App\Presentation\Requests\Sensor\SensorStoreRequest;
use App\Presentation\Requests\Sensor\SensorUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SensorController
{
    /**
     * Display a listing of sensors.
     */
    public function index(ListSensorsUseCase $useCase): JsonResponse
    {
        try {
            $sensors    = $useCase->execute();
            $data       = $sensors->toArray(request());
            $pagination = $sensors->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified sensor.
     */
    public function show(string $id, ShowSensorUseCase $useCase): JsonResponse
    {
        try {
            $sensor = $useCase->execute($id);

            if (! $sensor instanceof SensorDTO || $sensor->isEmpty()) {
                return ApiResponse::error(null, 'Sensor not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($sensor->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Sensor not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created sensor.
     */
    public function store(SensorStoreRequest $request, CreateSensorUseCase $useCase): JsonResponse
    {
        try {
            $sensor = $useCase->execute($request->validated());

            return ApiResponse::created($sensor->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified sensor.
     */
    public function update(SensorUpdateRequest $request, string $id, UpdateSensorUseCase $useCase): JsonResponse
    {
        try {
            $sensor = $useCase->execute($id, $request->validated());

            return ApiResponse::success($sensor->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified sensor.
     */
    public function destroy(string $id, DeleteSensorUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Sensor not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Sensor successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting sensor', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
