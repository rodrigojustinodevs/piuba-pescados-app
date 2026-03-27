<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Stocking\CreateStockingUseCase;
use App\Application\UseCases\Stocking\DeleteStockingUseCase;
use App\Application\UseCases\Stocking\ListStockingsUseCase;
use App\Application\UseCases\Stocking\ShowStockingUseCase;
use App\Application\UseCases\Stocking\UpdateStockingUseCase;
use App\Presentation\Requests\Stocking\StockingStoreRequest;
use App\Presentation\Requests\Stocking\StockingUpdateRequest;
use App\Presentation\Resources\Stocking\StockingResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(name="Stockings", description="Povoamentos (stockings)")
 */
final class StockingController
{
    /**
     * @OA\Get(
     *     path="/company/stockings",
     *     summary="Listar povoamentos",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="batch_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="company_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"active","closed"})),
     *     @OA\Parameter(name="date_from", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/StockingResource")
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request, ListStockingsUseCase $useCase): JsonResponse
    {
        $result     = $useCase->execute($request->all());
        $collection = StockingResource::collection($result->items());

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
     *     path="/company/stocking/{id}",
     *     summary="Obter povoamento por ID",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/StockingResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowStockingUseCase $useCase): JsonResponse
    {
        $stocking = $useCase->execute($id);

        return ApiResponse::success(new StockingResource($stocking), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Post(
     *     path="/company/stocking",
     *     summary="Criar povoamento",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batchId","stockingDate","quantity","averageWeight"},
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="stockingDate", type="string", format="date"),
     *             @OA\Property(property="quantity", type="integer", minimum=1),
     *             @OA\Property(property="averageWeight", type="number", format="float", minimum=0)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Criado"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(StockingStoreRequest $request, CreateStockingUseCase $useCase): JsonResponse
    {
        $stocking = $useCase->execute($request->validated());

        return ApiResponse::created(new StockingResource($stocking));
    }

    /**
     * @OA\Put(
     *     path="/company/stocking/{id}",
     *     summary="Atualizar povoamento",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="stockingDate", type="string", format="date"),
     *             @OA\Property(property="quantity", type="integer", minimum=1),
     *             @OA\Property(property="averageWeight", type="number", format="float", minimum=0)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Atualizado"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(StockingUpdateRequest $request, string $id, UpdateStockingUseCase $useCase): JsonResponse
    {
        $stocking = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new StockingResource($stocking), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Delete(
     *     path="/company/stocking/{id}",
     *     summary="Excluir povoamento",
     *     tags={"Stockings"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Excluído"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteStockingUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(null, Response::HTTP_OK, 'Stocking successfully deleted');
    }
}
