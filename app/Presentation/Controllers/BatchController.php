<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\BatchDTO;
use App\Application\UseCases\Batch\CreateBatchUseCase;
use App\Application\UseCases\Batch\DeleteBatchUseCase;
use App\Application\UseCases\Batch\ListBatchesUseCase;
use App\Application\UseCases\Batch\ShowBatchUseCase;
use App\Application\UseCases\Batch\UpdateBatchUseCase;
use App\Presentation\Requests\Batch\BatchStoreRequest;
use App\Presentation\Requests\Batch\BatchUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class BatchController
{
    /**
     * @OA\Get(
     *     path="/company/batches",
     *     summary="List batches",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=15)),
     *     @OA\Response(response=200, description="Paginated list of batches"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(ListBatchesUseCase $useCase): JsonResponse
    {
        try {
            $batches    = $useCase->execute();
            $data       = $batches->toArray(request());
            $pagination = $batches->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/batch/{id}",
     *     summary="Get batch by ID",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Batch found"),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowBatchUseCase $useCase): JsonResponse
    {
        try {
            $batch = $useCase->execute($id);

            if (! $batch instanceof BatchDTO || $batch->isEmpty()) {
                return ApiResponse::error(null, 'Batch not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($batch->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Batch not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/company/batch",
     *     summary="Create a batch",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"tankId","name","entryDate","initialQuantity","species","cultivation"})),
     *     @OA\Response(response=201, description="Batch created"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(BatchStoreRequest $request, CreateBatchUseCase $useCase): JsonResponse
    {
        try {
            $batch = $useCase->execute($request->validated());

            return ApiResponse::created($batch->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/batch/{id}",
     *     summary="Update a batch",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="Batch updated"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(BatchUpdateRequest $request, string $id, UpdateBatchUseCase $useCase): JsonResponse
    {
        try {
            $batch = $useCase->execute($id, $request->validated());

            return ApiResponse::success($batch->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/batch/{id}",
     *     summary="Delete a batch",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Batch deleted"),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteBatchUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Batch not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Batch successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting batch', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
