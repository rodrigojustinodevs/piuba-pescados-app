<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\BiometryService;
use App\Presentation\Requests\Biometry\BiometryStoreRequest;
use App\Presentation\Requests\Biometry\BiometryUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class BiometryController
{
    public function __construct(
        protected BiometryService $biometryService
    ) {
    }

    /**
     * Display a listing of biometries.
     */
    public function index(): JsonResponse
    {
        try {
            $biometries = $this->biometryService->showAllBiometries();
            $data       = $biometries->toArray(request());
            $pagination = $biometries->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified biometry.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $biometry = $this->biometryService->showBiometry($id);

            if (! $biometry instanceof \App\Application\DTOs\BiometryDTO || $biometry->isEmpty()) {
                return ApiResponse::error(null, 'Biometry not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($biometry->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Biometry not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created biometry.
     */
    public function store(BiometryStoreRequest $request): JsonResponse
    {
        try {
            $biometry = $this->biometryService->create($request->validated());

            return ApiResponse::created($biometry->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified biometry.
     */
    public function update(BiometryUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $biometry = $this->biometryService->updateBiometry($id, $request->validated());

            return ApiResponse::success($biometry->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified biometry.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->biometryService->deleteBiometry($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Biometry not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Biometry successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting biometry', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
