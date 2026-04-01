<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Supply\ListSuppliesUseCase;
use App\Application\UseCases\Supply\ShowSupplyUseCase;
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
     *     @OA\Parameter(name="company_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="companyId", in="query", @OA\Schema(type="string", format="uuid")),
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
}
