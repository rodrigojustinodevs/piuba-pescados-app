<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\TankService;
use App\Presentation\Requests\Tank\TankStoreRequest;
use App\Presentation\Requests\Tank\TankUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class TankController
{
    public function __construct(
        protected TankService $tankService
    ) {
    }

    /**
     * Display a listing of tanks.
     */
    public function index(): JsonResponse
    {
        try {
            $tanks      = $this->tankService->showAllTanks();
            $data       = $tanks->toArray(request());
            $pagination = $tanks->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * Display the specified tank.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $tank = $this->tankService->showTank($id);

            if (! $tank || $tank->isEmpty()) {
                return ApiResponse::error(null, 'Tank not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($tank->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Tank not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created tank.
     */
    public function store(TankStoreRequest $request): JsonResponse
    {
        try {
            $tank = $this->tankService->create($request->validated());

            return ApiResponse::created($tank->toArray());
        } catch (Throwable $exception) {
            return $this->handleException($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified tank.
     */
    public function update(TankUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $tank = $this->tankService->updateTank($id, $request->validated());

            return ApiResponse::success($tank->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return $this->handleException($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified tank.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->tankService->deleteTank($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Tank not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Tank successfully deleted');
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Error deleting tank', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Handle exceptions and return a formatted error response.
     */
    private function handleException(
        Throwable $exception,
        string $userMessage = 'An error occurred',
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        return ApiResponse::error(
            [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ],
            $userMessage,
            $statusCode
        );
    }
}
