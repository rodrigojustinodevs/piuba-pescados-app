<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\StockingService;
use App\Presentation\Requests\Stocking\StockingStoreRequest;
use App\Presentation\Requests\Stocking\StockingUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class StockingController
{
    public function __construct(
        protected StockingService $stockingService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/company/stockings",
     *     summary="List stockings",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=25)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of stockings",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="batchId", type="string", format="uuid", nullable=true),
     *                         @OA\Property(
     *                             property="batch",
     *                             type="object",
     *                             @OA\Property(property="id", type="string", format="uuid"),
     *                             @OA\Property(property="name", type="string", nullable=true)
     *                         ),
     *                         @OA\Property(property="stockingDate", type="string", format="date", example="2026-02-13"),
     *                         @OA\Property(property="quantity", type="integer", example=100),
     *                         @OA\Property(property="averageWeight", type="number", format="float", example=1.25),
     *                         @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=1),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="first_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $stockings  = $this->stockingService->showAllStockings();
            $data       = $stockings->toArray(request());
            $pagination = $stockings->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/stocking/{id}",
     *     summary="Get stocking by ID",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Stocking ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stocking found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batchId", type="string", format="uuid"),
     *                 @OA\Property(property="stockingDate", type="string", format="date", example="2026-02-13"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="averageWeight", type="number", format="float", example=1.25),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Stocking not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $stocking = $this->stockingService->showStocking($id);

            if (! $stocking instanceof \App\Application\DTOs\StockingDTO || $stocking->isEmpty()) {
                return ApiResponse::error(null, 'Stocking not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($stocking->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Stocking not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/company/stocking",
     *     summary="Create a stocking",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batchId","stockingDate","quantity","averageWeight"},
     *             @OA\Property(property="batchId", type="string", format="uuid", description="Batch ID"),
     *             @OA\Property(property="stockingDate", type="string", format="date", description="Stocking date", example="2026-02-13"),
     *             @OA\Property(property="quantity", type="integer", minimum=1, description="Quantity", example=100),
     *             @OA\Property(property="averageWeight", type="number", format="float", minimum=0, description="Average weight", example=1.25)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Stocking created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batchId", type="string", format="uuid"),
     *                 @OA\Property(property="stockingDate", type="string", format="date", example="2026-02-13"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="averageWeight", type="number", format="float", example=1.25),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(StockingStoreRequest $request): JsonResponse
    {
        try {
            $stocking = $this->stockingService->create($request->validated());

            return ApiResponse::created($stocking->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/stocking/{id}",
     *     summary="Update a stocking",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Stocking ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="stockingDate", type="string", format="date", example="2026-02-13"),
     *             @OA\Property(property="quantity", type="integer", minimum=1, example=100),
     *             @OA\Property(property="averageWeight", type="number", format="float", minimum=0, example=1.25)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stocking updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batchId", type="string", format="uuid"),
     *                 @OA\Property(property="stockingDate", type="string", format="date", example="2026-02-13"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="averageWeight", type="number", format="float", example=1.25),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Stocking not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(StockingUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $stocking = $this->stockingService->updateStocking($id, $request->validated());

            return ApiResponse::success($stocking->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/stocking/{id}",
     *     summary="Delete a stocking",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Stocking ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stocking deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Stocking successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Stocking not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->stockingService->deleteStocking($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Stocking not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Stocking successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting stocking', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
