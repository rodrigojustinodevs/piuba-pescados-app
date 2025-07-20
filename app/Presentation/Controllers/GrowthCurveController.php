<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\GrowthCurveService;
use App\Presentation\Requests\GrowthCurve\GrowthCurveStoreRequest;
use App\Presentation\Requests\GrowthCurve\GrowthCurveUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class GrowthCurveController
{
    public function __construct(
        protected GrowthCurveService $growthCurveService
    ) {
    }

    /**
     * Display a listing of growth curves.
     */
    public function index(): JsonResponse
    {
        try {
            $curves     = $this->growthCurveService->showAll();
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
    public function show(string $id): JsonResponse
    {
        try {
            $curve = $this->growthCurveService->show($id);

            if (!$curve instanceof \App\Application\DTOs\GrowthCurveDTO || $curve->isEmpty()) {

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
    public function store(GrowthCurveStoreRequest $request): JsonResponse
    {
        try {
            $curve = $this->growthCurveService->create($request->validated());

            return ApiResponse::created($curve->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified growth curve.
     */
    public function update(GrowthCurveUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $curve = $this->growthCurveService->update($id, $request->validated());

            return ApiResponse::success($curve->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified growth curve.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->growthCurveService->delete($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Growth curve not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Growth curve successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting growth curve', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
