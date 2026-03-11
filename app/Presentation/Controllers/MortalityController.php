<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\MortalityDTO;
use App\Application\UseCases\Mortality\CreateMortalityUseCase;
use App\Application\UseCases\Mortality\DeleteMortalityUseCase;
use App\Application\UseCases\Mortality\ListMortalitiesUseCase;
use App\Application\UseCases\Mortality\ShowMortalityUseCase;
use App\Application\UseCases\Mortality\SurvivalRateUseCase;
use App\Application\UseCases\Mortality\UpdateMortalityUseCase;
use App\Presentation\Requests\Mortality\MortalityStoreRequest;
use App\Presentation\Requests\Mortality\MortalityUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class MortalityController
{
    /**
     * Display a listing of mortalities.
     */
    public function index(ListMortalitiesUseCase $useCase): JsonResponse
    {
        try {
            $mortalities = $useCase->execute();
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
    public function show(string $id, ShowMortalityUseCase $useCase): JsonResponse
    {
        try {
            $mortality = $useCase->execute($id);

            if (! $mortality instanceof MortalityDTO || $mortality->isEmpty()) {
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
    public function store(MortalityStoreRequest $request, CreateMortalityUseCase $useCase): JsonResponse
    {
        try {
            $mortality = $useCase->execute($request->validated());

            return ApiResponse::created($mortality->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified mortality.
     */
    public function update(MortalityUpdateRequest $request, string $id, UpdateMortalityUseCase $useCase): JsonResponse
    {
        try {
            $mortality = $useCase->execute($id, $request->validated());

            return ApiResponse::success($mortality->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified mortality.
     */
    public function destroy(string $id, DeleteMortalityUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Mortality not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Mortality successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting mortality', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function survivalRate(string $batchId, SurvivalRateUseCase $useCase): JsonResponse
    {
        try {
            $survivalRate = $useCase->execute($batchId);

            return ApiResponse::success($survivalRate->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error getting survival rate', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
