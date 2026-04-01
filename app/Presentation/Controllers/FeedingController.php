<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Feeding\CreateFeedingUseCase;
use App\Application\UseCases\Feeding\DeleteFeedingUseCase;
use App\Application\UseCases\Feeding\ListFeedingsUseCase;
use App\Application\UseCases\Feeding\ShowFeedingUseCase;
use App\Application\UseCases\Feeding\UpdateFeedingUseCase;
use App\Presentation\Requests\Feeding\FeedingStoreRequest;
use App\Presentation\Requests\Feeding\FeedingUpdateRequest;
use App\Presentation\Resources\Feeding\FeedingResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Feedings", description="Alimentações")
 * @OA\Schema(
 *     schema="Feeding",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="batchId", type="string", format="uuid"),
 *     @OA\Property(property="feedingDate", type="string", format="date", example="2026-03-25"),
 *     @OA\Property(property="quantityProvided", type="number", format="float"),
 *     @OA\Property(property="feedType", type="string"),
 *     @OA\Property(property="stockId", type="string", format="uuid", nullable=true),
 *     @OA\Property(property="stockReductionQuantity", type="number", format="float"),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
final class FeedingController
{
    /**
     * @OA\Get(
     *     path="/company/feedings",
     *     summary="List feedings",
     *     tags={"Feedings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="batch_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="feed_type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of feedings",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Feeding"))
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="first_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListFeedingsUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['batch_id', 'feed_type', 'date_from', 'date_to', 'per_page', 'page']),
        );

        return ApiResponse::success(
            data:       FeedingResource::collection($paginator->items()),
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
     *     path="/company/feeding/{id}",
     *     summary="Get feeding by ID",
     *     tags={"Feedings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feeding found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Feeding")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Feeding not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowFeedingUseCase $useCase,
    ): JsonResponse {
        $feeding = $useCase->execute($id);

        return ApiResponse::success(
            data: new FeedingResource($feeding),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/feeding",
     *     summary="Create a feeding record",
     *     tags={"Feedings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batchId","feedingDate","quantityProvided","feedType","stockReductionQuantity"},
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="feedingDate", type="string", format="date", example="2026-03-25"),
     *             @OA\Property(property="quantityProvided", type="number", format="float", minimum=0),
     *             @OA\Property(property="feedType", type="string", maxLength=100),
     *             @OA\Property(property="stockId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="stockReductionQuantity", type="number", format="float", minimum=0)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Feeding created"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        FeedingStoreRequest $request,
        CreateFeedingUseCase $useCase,
    ): JsonResponse {
        $feeding = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new FeedingResource($feeding),
            message: 'Feeding created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/feeding/{id}",
     *     summary="Update a feeding record",
     *     tags={"Feedings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="feedingDate", type="string", format="date"),
     *             @OA\Property(property="quantityProvided", type="number", format="float", minimum=0),
     *             @OA\Property(property="feedType", type="string", maxLength=100),
     *             @OA\Property(property="stockId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="stockReductionQuantity", type="number", format="float", minimum=0)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Feeding updated"),
     *     @OA\Response(response=404, description="Feeding not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        FeedingUpdateRequest $request,
        string $id,
        UpdateFeedingUseCase $useCase,
    ): JsonResponse {
        $feeding = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new FeedingResource($feeding),
            message: 'Feeding updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/feeding/{id}",
     *     summary="Delete a feeding record",
     *     tags={"Feedings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Feeding deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Feeding deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Feeding not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteFeedingUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Feeding deleted successfully.');
    }
}
