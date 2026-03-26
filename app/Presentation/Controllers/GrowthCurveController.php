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

/**
 * @OA\Schema(
 *     schema="GrowthCurve",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="species", type="string", example="tilapia"),
 *     @OA\Property(property="age", type="integer", example=30),
 *     @OA\Property(property="expected_weight", type="number", format="float", example=120.5),
 *     @OA\Property(property="created_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updated_at", type="string", format="date-time", nullable=true)
 * )
 */
class GrowthCurveController
{
    /**
     * @OA\Get(
     *     path="/company/growth-curves",
     *     summary="List growth curves",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of growth curves",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/GrowthCurve")
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
     * @OA\Get(
     *     path="/company/growth-curve/{id}",
     *     summary="Get growth curve by ID",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Growth curve found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/GrowthCurve")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Growth curve not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
     * @OA\Post(
     *     path="/company/growth-curve",
     *     summary="Create growth curve",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"species","age","expected_weight"},
     *             @OA\Property(property="species", type="string"),
     *             @OA\Property(property="age", type="integer"),
     *             @OA\Property(property="expected_weight", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Growth curve created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/GrowthCurve")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
     * @OA\Put(
     *     path="/company/growth-curve/{id}",
     *     summary="Update growth curve",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="species", type="string"),
     *             @OA\Property(property="age", type="integer"),
     *             @OA\Property(property="expected_weight", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Growth curve updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/GrowthCurve")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Growth curve not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
     * @OA\Delete(
     *     path="/company/growth-curve/{id}",
     *     summary="Delete growth curve",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Growth curve deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Growth curve successfully deleted"),
     *             @OA\Property(property="response", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Growth curve not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
