<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Batch\CreateBatchUseCase;
use App\Application\UseCases\Batch\DeleteBatchUseCase;
use App\Application\UseCases\Batch\FinishBatchUseCase;
use App\Application\UseCases\Batch\ListBatchesUseCase;
use App\Application\UseCases\Batch\ShowBatchUseCase;
use App\Application\UseCases\Batch\UpdateBatchUseCase;
use App\Presentation\Requests\Batch\BatchFinishRequest;
use App\Presentation\Requests\Batch\BatchStoreRequest;
use App\Presentation\Requests\Batch\BatchUpdateRequest;
use App\Presentation\Resources\Batch\BatchResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Batch",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="entryDate", type="string", format="date", example="2025-01-15"),
 *     @OA\Property(property="initialQuantity", type="integer", minimum=1),
 *     @OA\Property(property="species", type="string"),
 *     @OA\Property(property="status", type="string", enum={"active","finished"}),
 *     @OA\Property(property="cultivation", type="string", enum={"growout","nursery"}, nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class BatchController
{
    /**
     * @OA\Get(
     *     path="/company/batches",
     *     summary="List batches",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"active","finished"})
     *     ),
     *     @OA\Parameter(name="tank_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="species", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of batches",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Batch"))
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
        ListBatchesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['status', 'tank_id', 'species', 'per_page', 'page']),
        );

        return ApiResponse::success(
            data:       BatchResource::collection($paginator->items()),
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
     *     path="/company/batch/{id}",
     *     summary="Get batch by ID",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Batch found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Batch")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowBatchUseCase $useCase,
    ): JsonResponse {
        $batch = $useCase->execute($id);

        return ApiResponse::success(
            data: new BatchResource($batch),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/batch",
     *     summary="Create a batch",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tankId","name","entryDate","initialQuantity","species","cultivation"},
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="entryDate", type="string", format="date", example="2025-01-15"),
     *             @OA\Property(property="initialQuantity", type="integer", minimum=1),
     *             @OA\Property(property="species", type="string", maxLength=255),
     *             @OA\Property(property="cultivation", type="string", enum={"growout","nursery"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Batch created"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        BatchStoreRequest $request,
        CreateBatchUseCase $useCase,
    ): JsonResponse {
        $batch = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new BatchResource($batch),
            message: 'Batch created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/batch/{id}",
     *     summary="Update a batch",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="tankId", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="description", type="string", nullable=true),
     *             @OA\Property(property="entryDate", type="string", format="date"),
     *             @OA\Property(property="initialQuantity", type="integer", minimum=1),
     *             @OA\Property(property="species", type="string", maxLength=255),
     *             @OA\Property(property="status", type="string", enum={"active","finished"}),
     *             @OA\Property(property="cultivation", type="string", enum={"growout","nursery"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Batch updated"),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        BatchUpdateRequest $request,
        string $id,
        UpdateBatchUseCase $useCase,
    ): JsonResponse {
        $batch = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new BatchResource($batch),
            message: 'Batch updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/batch/{id}",
     *     summary="Delete a batch",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Batch deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Batch deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteBatchUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Batch deleted successfully.');
    }

    /**
     * @OA\Post(
     *     path="/company/batch/{id}/finish",
     *     summary="Finish a batch (harvest)",
     *     description="Records the harvest and finishes the batch."
     *         " Returns a biological and financial performance report.",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"total_weight","price_per_kg"},
     *             @OA\Property(property="total_weight", type="number", format="float", minimum=0, example=1250.5),
     *             @OA\Property(property="price_per_kg", type="number", format="float", minimum=0, example=12.00),
     *             @OA\Property(
     *                 property="harvest_date",
     *                 type="string",
     *                 format="date",
     *                 nullable=true,
     *                 example="2025-03-10"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Batch finished with performance report"),
     *     @OA\Response(response=404, description="Batch not found"),
     *     @OA\Response(response=422, description="Batch already finished or validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function finish(
        BatchFinishRequest $request,
        string $id,
        FinishBatchUseCase $useCase,
    ): JsonResponse {
        $report = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    $report,
            message: 'Batch finished successfully.',
        );
    }
}
