<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\SettlementService;
use App\Presentation\Requests\Settlement\SettlementStoreRequest;
use App\Presentation\Requests\Settlement\SettlementUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SettlementController
{
    public function __construct(
        protected SettlementService $settlementService
    ) {
    }

    /**
     * Display a listing of settlements.
     */
    public function index(): JsonResponse
    {
        try {
            $settlements  = $this->settlementService->showAllSettlements();
            $data       = $settlements->toArray(request());
            $pagination = $settlements->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified settlement.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $settlement = $this->settlementService->showSettlement($id);

            if (! $settlement instanceof \App\Application\DTOs\SettlementDTO || $settlement->isEmpty()) {
                return ApiResponse::error(null, 'Settlement not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($settlement->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Settlement not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created settlement.
     */
    public function store(SettlementStoreRequest $request): JsonResponse
    {
        try {
            $settlement = $this->settlementService->create($request->validated());

            return ApiResponse::created($settlement->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified settlement.
     */
    public function update(SettlementUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $settlement = $this->settlementService->updateSettlement($id, $request->validated());

            return ApiResponse::success($settlement->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified settlement.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->settlementService->deleteSettlement($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Settlement not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Settlement successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting settlement', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
