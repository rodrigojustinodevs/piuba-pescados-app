<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Supply\CreateSupplyUseCase;
use App\Application\UseCases\Supply\DeleteSupplyUseCase;
use App\Application\UseCases\Supply\ListSuppliesUseCase;
use App\Application\UseCases\Supply\ShowSupplyUseCase;
use App\Application\UseCases\Supply\UpdateSupplyUseCase;
use App\Presentation\Requests\Supply\SupplyStoreRequest;
use App\Presentation\Requests\Supply\SupplyUpdateRequest;
use App\Presentation\Resources\Supply\SupplyResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(name="Supplies", description="Insumos e produtos da empresa")
 */
final class SupplyController
{
    /**
     * @OA\Get(
     *     path="/company/supplies",
     *     summary="Listar insumos",
     *     tags={"Supplies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="category", in="query", @OA\Schema(type="string", enum={"feed","medication","fertilizer","probiotic","equipment","packaging","finished_product","other"})),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"active","inactive","low_stock"})),
     *     @OA\Parameter(name="is_product", in="query", @OA\Schema(type="boolean")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de insumos",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SupplyResource")
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request, ListSuppliesUseCase $useCase): JsonResponse
    {
        $filters = $request->only(['page', 'per_page', 'category', 'status', 'is_product']);

        if ($request->has('is_product')) {
            $filters['isProduct'] = filter_var(
                $request->input('is_product'),
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        $result     = $useCase->execute($filters);
        $collection = SupplyResource::collection($result->items());

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
     *     path="/company/supply/{id}",
     *     summary="Obter insumo por ID",
     *     tags={"Supplies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Insumo encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/SupplyResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowSupplyUseCase $useCase): JsonResponse
    {
        $supply = $useCase->execute($id);

        return ApiResponse::success(new SupplyResource($supply), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Post(
     *     path="/company/supply",
     *     summary="Criar insumo",
     *     tags={"Supplies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","category","unit","unit_cost","sale_price","current_stock","min_stock","is_product"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Ração Inicial"),
     *             @OA\Property(property="sku", type="string", maxLength=100, nullable=true, example="SUP-0001"),
     *             @OA\Property(
     *                 property="category", type="string", example="feed",
     *                 enum={"feed","medication","fertilizer","probiotic","equipment","packaging","finished_product","other"}
     *             ),
     *             @OA\Property(property="unit", type="string", maxLength=50, example="kg"),
     *             @OA\Property(property="unit_cost", type="number", format="float", example=3.50),
     *             @OA\Property(property="sale_price", type="number", format="float", example=5.00),
     *             @OA\Property(property="current_stock", type="number", format="float", example=500.000),
     *             @OA\Property(property="min_stock", type="number", format="float", example=100.000),
     *             @OA\Property(property="supplier_id", type="string", format="uuid",
     *                 nullable=true, example="550e8400-e29b-41d4-a716-446655440000"),
     *             @OA\Property(property="is_product", type="boolean", example=false),
     *             @OA\Property(property="status", type="string",
     *                 enum={"active","inactive","low_stock"}, example="active"),
     *             @OA\Property(property="description", type="string",
     *                 nullable=true, example="Ração para fase inicial de engorda")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Insumo criado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", ref="#/components/schemas/SupplyResource")
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        SupplyStoreRequest $request,
        CreateSupplyUseCase $useCase,
    ): JsonResponse {
        $supply = $useCase->execute($request->validated());

        return ApiResponse::created(new SupplyResource($supply));
    }

    /**
     * @OA\Put(
     *     path="/company/supply/{id}",
     *     summary="Atualizar insumo",
     *     tags={"Supplies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="sku", type="string", maxLength=100, nullable=true),
     *             @OA\Property(property="category", type="string", enum={"feed","medication","fertilizer","probiotic","equipment","packaging","finished_product","other"}),
     *             @OA\Property(property="unit", type="string", maxLength=50),
     *             @OA\Property(property="unit_cost", type="number", format="float"),
     *             @OA\Property(property="sale_price", type="number", format="float"),
     *             @OA\Property(property="current_stock", type="number", format="float"),
     *             @OA\Property(property="min_stock", type="number", format="float"),
     *             @OA\Property(property="supplier_id", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="is_product", type="boolean"),
     *             @OA\Property(property="status", type="string", enum={"active","inactive","low_stock"}),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Atualizado",
     *         @OA\JsonContent(@OA\Property(property="response", ref="#/components/schemas/SupplyResource"))),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        SupplyUpdateRequest $request,
        string $id,
        UpdateSupplyUseCase $useCase,
    ): JsonResponse {
        $supply = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new SupplyResource($supply), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Delete(
     *     path="/company/supply/{id}",
     *     summary="Remover insumo",
     *     tags={"Supplies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Removido com sucesso"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteSupplyUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(null, Response::HTTP_OK, 'Insumo removido com sucesso.');
    }
}
