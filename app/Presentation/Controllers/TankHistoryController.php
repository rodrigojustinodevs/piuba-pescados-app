<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\TankHistory\CreateTankHistoryUseCase;
use App\Application\UseCases\TankHistory\ListTankHistoriesUseCase;
use App\Presentation\Requests\TankHistory\TankHistoryStoreRequest;
use App\Presentation\Resources\TankHistory\TankHistoryResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="TankHistory",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="companyId", type="string", format="uuid"),
 *     @OA\Property(property="tankId", type="string", format="uuid"),
 *     @OA\Property(property="event", type="string", enum={"cleaning","maintenance","fallowing"}),
 *     @OA\Property(property="eventLabel", type="string", example="Limpeza"),
 *     @OA\Property(property="eventDate", type="string", format="date"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="performedBy", type="string", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class TankHistoryController
{
    /**
     * @OA\Get(
     *     path="/company/tank-histories",
     *     summary="List tank history events",
     *     tags={"Tank History"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="tank_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(
     *         name="event",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"cleaning","maintenance","fallowing"})
     *     ),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Response(response=200, description="Paginated list of tank history events"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListTankHistoriesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['tank_id', 'event', 'date_from', 'date_to', 'per_page']),
        );

        return ApiResponse::success(
            data:       TankHistoryResource::collection($paginator->items()),
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
     * @OA\Post(
     *     path="/company/tank-history",
     *     summary="Register a tank history event",
     *     tags={"Tank History"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tankId","event","eventDate"},
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(property="event", type="string", enum={"cleaning","maintenance","fallowing"}),
     *             @OA\Property(property="eventDate", type="string", format="date", example="2026-03-25"),
     *             @OA\Property(property="description", type="string", nullable=true, maxLength=1000),
     *             @OA\Property(property="performedBy", type="string", nullable=true, maxLength=255)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Tank history event created"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Tank not found")
     * )
     */
    public function store(
        TankHistoryStoreRequest $request,
        CreateTankHistoryUseCase $useCase,
    ): JsonResponse {
        $history = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new TankHistoryResource($history),
            message: 'Tank history event registered successfully.',
        );
    }
}
