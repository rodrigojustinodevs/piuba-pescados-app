<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\StockingHistory\CreateStockingHistoryUseCase;
use App\Application\UseCases\StockingHistory\ListStockingHistoriesUseCase;
use App\Presentation\Requests\StockingHistory\StockingHistoryStoreRequest;
use App\Presentation\Resources\StockingHistory\StockingHistoryResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="StockingHistory",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="companyId", type="string", format="uuid"),
 *     @OA\Property(property="stockingId", type="string", format="uuid"),
 *     @OA\Property(property="event", type="string", enum={"biometry","mortality","transfer","medication"}),
 *     @OA\Property(property="eventLabel", type="string", example="Biometria"),
 *     @OA\Property(property="eventDate", type="string", format="date"),
 *     @OA\Property(property="quantity", type="integer", nullable=true),
 *     @OA\Property(property="averageWeight", type="number", format="float", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class StockingHistoryController
{
    /**
     * @OA\Get(
     *     path="/company/stocking-histories",
     *     summary="List stocking history events",
     *     tags={"Stocking History"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="stocking_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(
     *         name="event",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"biometry","mortality","transfer","medication"})
     *     ),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Response(response=200, description="Paginated list of stocking history events"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListStockingHistoriesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['stocking_id', 'event', 'date_from', 'date_to', 'per_page']),
        );

        return ApiResponse::success(
            data:       StockingHistoryResource::collection($paginator->items()),
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
     *     path="/company/stocking-history",
     *     summary="Register a stocking history event",
     *     tags={"Stocking History"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"stockingId","event","eventDate"},
     *             @OA\Property(property="stockingId", type="string", format="uuid"),
     *             @OA\Property(property="event", type="string", enum={"biometry","mortality","transfer","medication"}),
     *             @OA\Property(property="eventDate", type="string", format="date", example="2026-03-25"),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 nullable=true,
     *                 minimum=1,
     *                 description="Required for mortality and transfer"
     *             ),
     *             @OA\Property(
     *                 property="averageWeight",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 description="Required for biometry (g)"
     *             ),
     *             @OA\Property(property="notes", type="string", nullable=true, maxLength=1000)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Stocking history event created"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Stocking not found"),
     *     @OA\Response(response=422, description="Business rule violation (e.g. closed stocking)")
     * )
     */
    public function store(
        StockingHistoryStoreRequest $request,
        CreateStockingHistoryUseCase $useCase,
    ): JsonResponse {
        $history = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new StockingHistoryResource($history),
            message: 'Stocking history event registered successfully.',
        );
    }
}
