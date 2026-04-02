<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Supply\CreateSupplyUseCase;
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
 * @OA\Tag(name="Supplies", description="Insumos")
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
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada",
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
        $filters = $request->only(['page', 'per_page']);

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
     *         description="Encontrado",
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
     *             required={"name","defaultUnit"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="category", type="string", nullable=true, maxLength=255),
     *             @OA\Property(property="defaultUnit", type="string", enum={"kg","g","liter","ml","unit","box","piece"})
     *         )
     *     ),
     *     @OA\Response(response=201, description="Criado"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(SupplyStoreRequest $request, CreateSupplyUseCase $useCase): JsonResponse
    {
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
     *             @OA\Property(property="category", type="string", nullable=true, maxLength=255),
     *             @OA\Property(property="defaultUnit", type="string", enum={"kg","g","liter","ml","unit","box","piece"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Atualizado"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(SupplyUpdateRequest $request, string $id, UpdateSupplyUseCase $useCase): JsonResponse
    {
        $supply = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new SupplyResource($supply), Response::HTTP_OK, 'Success');
    }
}
