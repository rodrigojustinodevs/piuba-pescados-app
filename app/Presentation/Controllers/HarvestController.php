<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\HarvestDTO;
use App\Application\UseCases\Harvest\CreateHarvestUseCase;
use App\Application\UseCases\Harvest\DeleteHarvestUseCase;
use App\Application\UseCases\Harvest\ListHarvestsUseCase;
use App\Application\UseCases\Harvest\ShowHarvestUseCase;
use App\Application\UseCases\Harvest\UpdateHarvestUseCase;
use App\Presentation\Requests\Harvest\HarvestStoreRequest;
use App\Presentation\Requests\Harvest\HarvestUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

/**
 * @OA\Schema(
 *     schema="Harvest",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="batchId", type="string", format="uuid"),
 *     @OA\Property(property="harvestDate", type="string", format="date", example="2026-03-25"),
 *     @OA\Property(property="totalWeight", type="number", format="float", example=850.5),
 *     @OA\Property(property="pricePerKg", type="number", format="float", example=12.3),
 *     @OA\Property(property="totalRevenue", type="number", format="float", example=10461.15),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class HarvestController
{
    /**
     * @OA\Get(
     *     path="/company/harvests",
     *     summary="List harvests",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of harvests",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Harvest")
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
    public function index(ListHarvestsUseCase $useCase): JsonResponse
    {
        try {
            $harvests   = $useCase->execute();
            $data       = $harvests->toArray(request());
            $pagination = $harvests->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/harvest/{id}",
     *     summary="Get harvest by ID",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Harvest found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Harvest")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Harvest not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowHarvestUseCase $useCase): JsonResponse
    {
        try {
            $harvest = $useCase->execute($id);

            if (! $harvest instanceof HarvestDTO || $harvest->isEmpty()) {
                return ApiResponse::error(null, 'Harvest not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($harvest->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Harvest not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/company/harvest",
     *     summary="Create harvest",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batch_id","harvest_date","total_weight","price_per_kg"},
     *             @OA\Property(property="batch_id", type="string", format="uuid"),
     *             @OA\Property(property="harvest_date", type="string", format="date"),
     *             @OA\Property(property="total_weight", type="number", format="float"),
     *             @OA\Property(property="price_per_kg", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Harvest created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/Harvest")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(HarvestStoreRequest $request, CreateHarvestUseCase $useCase): JsonResponse
    {
        try {
            $harvest = $useCase->execute($request->validated());

            return ApiResponse::created($harvest->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/harvest/{id}",
     *     summary="Update harvest",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="harvest_date", type="string", format="date"),
     *             @OA\Property(property="total_weight", type="number", format="float"),
     *             @OA\Property(property="price_per_kg", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Harvest updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Harvest")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Harvest not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(HarvestUpdateRequest $request, string $id, UpdateHarvestUseCase $useCase): JsonResponse
    {
        try {
            $harvest = $useCase->execute($id, $request->validated());

            return ApiResponse::success($harvest->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/harvest/{id}",
     *     summary="Delete harvest",
     *     tags={"Harvest"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Harvest deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Harvest successfully deleted"),
     *             @OA\Property(property="response", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Harvest not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteHarvestUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Harvest not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Harvest successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting harvest', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
