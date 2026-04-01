<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\WaterQuality\CreateWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\DeleteWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\ListWaterQualitiesUseCase;
use App\Application\UseCases\WaterQuality\ShowWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\UpdateWaterQualityUseCase;
use App\Presentation\Requests\WaterQuality\WaterQualityStoreRequest;
use App\Presentation\Requests\WaterQuality\WaterQualityUpdateRequest;
use App\Presentation\Resources\WaterQuality\WaterQualityResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Water Qualities", description="Water quality measurements by tank")
 * @OA\Schema(
 *     schema="WaterQuality",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="measuredAt", type="string", format="date-time"),
 *     @OA\Property(property="ph", type="number", format="float", nullable=true, minimum=0, maximum=14),
 *     @OA\Property(property="dissolvedOxygen", type="number", format="float", nullable=true, minimum=0),
 *     @OA\Property(property="temperature", type="number", format="float", nullable=true, minimum=-10, maximum=50),
 *     @OA\Property(property="ammonia", type="number", format="float", nullable=true, minimum=0),
 *     @OA\Property(property="salinity", type="number", format="float", nullable=true, minimum=0),
 *     @OA\Property(property="turbidity", type="number", format="float", nullable=true, minimum=0),
 *     @OA\Property(property="notes", type="string", nullable=true, maxLength=500),
 *     @OA\Property(
 *         property="tank",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
final class WaterQualityController
{
    /**
     * @OA\Get(
     *     path="/company/water-qualities",
     *     summary="List water quality records",
     *     tags={"Water Qualities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="tank_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Paginated list of water quality records"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListWaterQualitiesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['tank_id', 'date_from', 'date_to', 'per_page', 'page']),
        );

        return ApiResponse::success(
            data:       WaterQualityResource::collection($paginator->items()),
            pagination: [
                'total'        => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'first_page'   => $paginator->firstPage(),
                'per_page'     => $paginator->perPage(),
            ],
        );
    }

    /**
     * @OA\Get(
     *     path="/company/water-quality/{id}",
     *     summary="Get water quality record by ID",
     *     tags={"Water Qualities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Water quality record ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Water quality record found"),
     *     @OA\Response(response=404, description="Water quality record not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowWaterQualityUseCase $useCase): JsonResponse
    {
        $record = $useCase->execute($id);

        return ApiResponse::success(
            data: new WaterQualityResource($record->loadMissing('tank')),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/water-quality",
     *     summary="Create a water quality record",
     *     tags={"Water Qualities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tankId","measuredAt"},
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(
     *                 property="measuredAt",
     *                 type="string",
     *                 format="date-time",
     *                 example="2026-03-30 08:00:00"
     *             ),
     *             @OA\Property(property="ph", type="number", format="float", nullable=true, minimum=0, maximum=14),
     *             @OA\Property(property="dissolvedOxygen", type="number", format="float", nullable=true, minimum=0),
     *             @OA\Property(
     *                 property="temperature",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 minimum=-10,
     *                 maximum=50
     *             ),
     *             @OA\Property(property="ammonia", type="number", format="float", nullable=true, minimum=0),
     *             @OA\Property(property="salinity", type="number", format="float", nullable=true, minimum=0),
     *             @OA\Property(property="turbidity", type="number", format="float", nullable=true, minimum=0),
     *             @OA\Property(property="notes", type="string", nullable=true, maxLength=500)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Water quality record created"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Tank not found")
     * )
     */
    public function store(WaterQualityStoreRequest $request, CreateWaterQualityUseCase $useCase): JsonResponse
    {
        $record = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new WaterQualityResource($record),
            message: 'Water quality record created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/water-quality/{id}",
     *     summary="Update a water quality record",
     *     tags={"Water Qualities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Water quality record ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="measuredAt", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="ph", type="number", format="float", nullable=true, minimum=0, maximum=14),
     *             @OA\Property(property="dissolvedOxygen", type="number", format="float", nullable=true, minimum=0),
     *             @OA\Property(
     *                 property="temperature",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 minimum=-10,
     *                 maximum=50
     *             ),
     *             @OA\Property(property="ammonia", type="number", format="float", nullable=true, minimum=0),
     *             @OA\Property(property="salinity", type="number", format="float", nullable=true, minimum=0),
     *             @OA\Property(property="turbidity", type="number", format="float", nullable=true, minimum=0),
     *             @OA\Property(property="notes", type="string", nullable=true, maxLength=500)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Water quality record updated"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Water quality record not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        WaterQualityUpdateRequest $request,
        string $id,
        UpdateWaterQualityUseCase $useCase
    ): JsonResponse {
        $record = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new WaterQualityResource($record),
            message: 'Water quality record updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/water-quality/{id}",
     *     summary="Delete a water quality record",
     *     tags={"Water Qualities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Water quality record ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Water quality record deleted"),
     *     @OA\Response(response=404, description="Water quality record not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteWaterQualityUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Water quality record deleted successfully.');
    }
}
