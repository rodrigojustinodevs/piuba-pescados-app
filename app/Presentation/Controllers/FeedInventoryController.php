<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\FeedInventory\CreateFeedInventoryUseCase;
use App\Application\UseCases\FeedInventory\DeleteFeedInventoryUseCase;
use App\Application\UseCases\FeedInventory\ListFeedInventoriesUseCase;
use App\Application\UseCases\FeedInventory\ShowFeedInventoryUseCase;
use App\Application\UseCases\FeedInventory\UpdateFeedInventoryUseCase;
use App\Presentation\Requests\FeedInventory\FeedInventoryStoreRequest;
use App\Presentation\Requests\FeedInventory\FeedInventoryUpdateRequest;
use App\Presentation\Resources\FeedInventory\FeedInventoryResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(name="FeedInventory", description="Operações de estoque de ração")
 */
final class FeedInventoryController
{
    /**
     * @OA\Get(
     *     path="/company/feed-inventories",
     *     summary="Listar estoques de ração",
     *     tags={"FeedInventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="company_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="feed_type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de estoques de ração",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/FeedInventoryResource")
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request, ListFeedInventoriesUseCase $useCase): JsonResponse
    {
        $result     = $useCase->execute($request->all());
        $collection = FeedInventoryResource::collection($result->items());

        return ApiResponse::success($collection, Response::HTTP_OK, 'Success', [
            'total'        => $result->total(),
            'current_page' => $result->currentPage(),
            'last_page'    => $result->lastPage(),
            'first_page'   => $result->firstPage(),
            'per_page'     => $result->perPage(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/company/feed-inventory/{id}",
     *     summary="Detalhar estoque de ração",
     *     tags={"FeedInventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Estoque de ração encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/FeedInventoryResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="FeedInventory not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowFeedInventoryUseCase $useCase): JsonResponse
    {
        $feedInventory = $useCase->execute($id);

        return ApiResponse::success(new FeedInventoryResource($feedInventory), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Post(
     *     path="/company/feed-inventory",
     *     summary="Criar estoque de ração",
     *     tags={"FeedInventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company_id","feed_type","current_stock","minimum_stock","daily_consumption","total_consumption"},
     *             @OA\Property(property="company_id", type="string", format="uuid"),
     *             @OA\Property(property="feed_type", type="string", example="Ração Premium"),
     *             @OA\Property(property="current_stock", type="number", format="float", example=500.0),
     *             @OA\Property(property="minimum_stock", type="number", format="float", example=50.0),
     *             @OA\Property(property="daily_consumption", type="number", format="float", example=10.0),
     *             @OA\Property(property="total_consumption", type="number", format="float", example=100.0)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Estoque de ração criado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="data", ref="#/components/schemas/FeedInventoryResource")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(FeedInventoryStoreRequest $request, CreateFeedInventoryUseCase $useCase): JsonResponse
    {
        $feedInventory = $useCase->execute($request->validated());

        return ApiResponse::created(new FeedInventoryResource($feedInventory));
    }

    /**
     * @OA\Put(
     *     path="/company/feed-inventory/{id}",
     *     summary="Atualizar estoque de ração",
     *     tags={"FeedInventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="feed_type", type="string"),
     *             @OA\Property(property="current_stock", type="number", format="float"),
     *             @OA\Property(property="minimum_stock", type="number", format="float"),
     *             @OA\Property(property="daily_consumption", type="number", format="float"),
     *             @OA\Property(property="total_consumption", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estoque de ração atualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/FeedInventoryResource")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="FeedInventory not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        FeedInventoryUpdateRequest $request,
        string $id,
        UpdateFeedInventoryUseCase $useCase,
    ): JsonResponse {
        $feedInventory = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new FeedInventoryResource($feedInventory), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Delete(
     *     path="/company/feed-inventory/{id}",
     *     summary="Excluir estoque de ração",
     *     tags={"FeedInventory"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Estoque de ração excluído",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="FeedInventory successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="FeedInventory not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteFeedInventoryUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(null, Response::HTTP_OK, 'FeedInventory successfully deleted');
    }
}
