<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\GrowthCurveDTO;
use App\Application\UseCases\GrowthCurve\CreateGrowthCurveUseCase;
use App\Application\UseCases\GrowthCurve\DeleteGrowthCurveUseCase;
use App\Application\UseCases\GrowthCurve\ListGrowthCurvesUseCase;
use App\Application\UseCases\GrowthCurve\ShowGrowthCurveUseCase;
use App\Application\UseCases\GrowthCurve\UpdateGrowthCurveUseCase;
use App\Presentation\Requests\GrowthCurve\GrowthCurveStoreRequest;
use App\Presentation\Requests\GrowthCurve\GrowthCurveUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class GrowthCurveController
{
    /**
     * Display a listing of growth curves.
     */
    public function index(ListGrowthCurvesUseCase $useCase): JsonResponse
    {
        try {
            $curves     = $useCase->execute();
            $data       = $curves->toArray(request());
            $pagination = $curves->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified growth curve.
     */
    public function show(string $id, ShowGrowthCurveUseCase $useCase): JsonResponse
    {
        try {
            $curve = $useCase->execute($id);

            if (! $curve instanceof GrowthCurveDTO || $curve->isEmpty()) {
                return ApiResponse::error(null, 'Growth curve not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($curve->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Growth curve not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created growth curve.
     */
    public function store(GrowthCurveStoreRequest $request, CreateGrowthCurveUseCase $useCase): JsonResponse
    {
        try {
            $curve = $useCase->execute($request->validated());

            return ApiResponse::created($curve->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified growth curve.
     */
    public function update(
        GrowthCurveUpdateRequest $request,
        string $id,
        UpdateGrowthCurveUseCase $useCase
    ): JsonResponse {
        try {
            $curve = $useCase->execute($id, $request->validated());

            return ApiResponse::success($curve->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified growth curve.
     */
    public function destroy(string $id, DeleteGrowthCurveUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Growth curve not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Growth curve successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting growth curve', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
