<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\WaterQualityDTO;
use App\Application\UseCases\WaterQuality\CreateWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\DeleteWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\ListWaterQualitiesUseCase;
use App\Application\UseCases\WaterQuality\ShowWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\UpdateWaterQualityUseCase;
use App\Presentation\Requests\WaterQuality\WaterQualityStoreRequest;
use App\Presentation\Requests\WaterQuality\WaterQualityUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class WaterQualityController
{
    /**
     * Display a listing of water quality records.
     */
    public function index(ListWaterQualitiesUseCase $useCase): JsonResponse
    {
        try {
            $records    = $useCase->execute();
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
    public function show(string $id, ShowWaterQualityUseCase $useCase): JsonResponse
    {
        try {
            $record = $useCase->execute($id);

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
    public function store(WaterQualityStoreRequest $request, CreateWaterQualityUseCase $useCase): JsonResponse
    {
        try {
            $record = $useCase->execute($request->validated());

            return ApiResponse::created($record->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified water quality record.
     */
    public function update(
        WaterQualityUpdateRequest $request,
        string $id,
        UpdateWaterQualityUseCase $useCase
    ): JsonResponse {
        try {
            $record = $useCase->execute($id, $request->validated());

            return ApiResponse::success($record->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified water quality record.
     */
    public function destroy(string $id, DeleteWaterQualityUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

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
