<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\AlertDTO;
use App\Application\UseCases\Alert\CreateAlertUseCase;
use App\Application\UseCases\Alert\DeleteAlertUseCase;
use App\Application\UseCases\Alert\ListAlertsUseCase;
use App\Application\UseCases\Alert\ShowAlertUseCase;
use App\Application\UseCases\Alert\UpdateAlertUseCase;
use App\Presentation\Requests\Alert\AlertStoreRequest;
use App\Presentation\Requests\Alert\AlertUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

final class AlertController
{
    /**
     * Display a listing of alerts.
     */
    public function index(ListAlertsUseCase $useCase): JsonResponse
    {
        try {
            $alerts     = $useCase->execute();
            $data       = $alerts->toArray(request());
            $pagination = $alerts->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified alert.
     */
    public function show(string $id, ShowAlertUseCase $useCase): JsonResponse
    {
        try {
            $alert = $useCase->execute($id);

            if (! $alert instanceof AlertDTO || $alert->isEmpty()) {
                return ApiResponse::error(null, 'Alert not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($alert->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Alert not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created alert.
     */
    public function store(AlertStoreRequest $request, CreateAlertUseCase $useCase): JsonResponse
    {
        try {
            $alert = $useCase->execute($request->validated());

            return ApiResponse::created($alert->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified alert.
     */
    public function update(AlertUpdateRequest $request, string $id, UpdateAlertUseCase $useCase): JsonResponse
    {
        try {
            $alert = $useCase->execute($id, $request->validated());

            return ApiResponse::success($alert->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified alert.
     */
    public function destroy(string $id, DeleteAlertUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Alert not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Alert successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting alert', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
