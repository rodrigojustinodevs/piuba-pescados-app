<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Mortality\CreateMortalityUseCase;
use App\Application\UseCases\Mortality\DeleteMortalityUseCase;
use App\Application\UseCases\Mortality\ListMortalitiesUseCase;
use App\Application\UseCases\Mortality\ShowMortalityUseCase;
use App\Application\UseCases\Mortality\SurvivalRateUseCase;
use App\Application\UseCases\Mortality\UpdateMortalityUseCase;
use App\Presentation\Requests\Mortality\MortalityStoreRequest;
use App\Presentation\Requests\Mortality\MortalityUpdateRequest;
use App\Presentation\Resources\Mortality\MortalityResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Mortality",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="batchId", type="string", format="uuid"),
 *     @OA\Property(property="mortalityDate", type="string", format="date", example="2026-03-25"),
 *     @OA\Property(property="quantity", type="integer", minimum=1, description="Number of dead fish"),
 *     @OA\Property(property="cause", type="string", maxLength=255),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class MortalityController
{
    /**
     * @OA\Get(
     *     path="/company/mortalities",
     *     summary="List mortalities",
     *     tags={"Mortalities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="batch_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="cause", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of mortalities",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Mortality"))
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
        ListMortalitiesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['batch_id', 'date_from', 'date_to', 'cause', 'per_page', 'page']),
        );

        return ApiResponse::success(
            data:       MortalityResource::collection($paginator->items()),
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
     *     path="/company/mortality/{id}",
     *     summary="Get mortality by ID",
     *     tags={"Mortalities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mortality found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Mortality")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Mortality not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowMortalityUseCase $useCase,
    ): JsonResponse {
        $mortality = $useCase->execute($id);

        return ApiResponse::success(
            data: new MortalityResource($mortality),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/mortality",
     *     summary="Create a mortality record",
     *     tags={"Mortalities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batchId","mortalityDate","quantity","cause"},
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="mortalityDate", type="string", format="date", example="2026-03-25"),
     *             @OA\Property(property="quantity", type="integer", minimum=1),
     *             @OA\Property(property="cause", type="string", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mortality created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/Mortality")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        MortalityStoreRequest $request,
        CreateMortalityUseCase $useCase,
    ): JsonResponse {
        $mortality = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new MortalityResource($mortality),
            message: 'Mortality created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/mortality/{id}",
     *     summary="Update a mortality record",
     *     tags={"Mortalities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="mortalityDate", type="string", format="date"),
     *             @OA\Property(property="quantity", type="integer", minimum=1),
     *             @OA\Property(property="cause", type="string", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mortality updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Mortality")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Mortality not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        MortalityUpdateRequest $request,
        string $id,
        UpdateMortalityUseCase $useCase,
    ): JsonResponse {
        $mortality = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new MortalityResource($mortality),
            message: 'Mortality updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/mortality/{id}",
     *     summary="Delete a mortality record",
     *     tags={"Mortalities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mortality deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Mortality deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Mortality not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteMortalityUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Mortality deleted successfully.');
    }

    /**
     * @OA\Get(
     *     path="/company/batch/{batchId}/survival-rate",
     *     summary="Get survival rate for a batch",
     *     tags={"Mortalities"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="batchId", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Survival rate calculated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="batchId", type="string", format="uuid"),
     *                 @OA\Property(property="initialQuantity", type="integer"),
     *                 @OA\Property(property="totalMortalities", type="integer"),
     *                 @OA\Property(property="currentSurvivors", type="integer"),
     *                 @OA\Property(property="survivalRate", type="number", format="float")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function survivalRate(
        string $batchId,
        SurvivalRateUseCase $useCase,
    ): JsonResponse {
        $result = $useCase->execute($batchId);

        return ApiResponse::success(data: $result->toArray());
    }
}
