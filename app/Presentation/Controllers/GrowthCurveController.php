<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\GrowthCurve\CreateGrowthCurveUseCase;
use App\Application\UseCases\GrowthCurve\DeleteGrowthCurveUseCase;
use App\Application\UseCases\GrowthCurve\ListGrowthCurvesUseCase;
use App\Application\UseCases\GrowthCurve\ShowGrowthCurveUseCase;
use App\Application\UseCases\GrowthCurve\UpdateGrowthCurveUseCase;
use App\Presentation\Requests\GrowthCurve\GrowthCurveStoreRequest;
use App\Presentation\Requests\GrowthCurve\GrowthCurveUpdateRequest;
use App\Presentation\Resources\GrowthCurve\GrowthCurveResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(name="Growth Curves", description="Curva de crescimento (registros por lote)")
 */
final class GrowthCurveController
{
    /**
     * @OA\Get(
     *     path="/company/growth-curves",
     *     summary="Listar curvas de crescimento",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="batch_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="company_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/GrowthCurveResource")
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request, ListGrowthCurvesUseCase $useCase): JsonResponse
    {
        $result     = $useCase->execute($request->all());
        $collection = GrowthCurveResource::collection($result->items());

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
     *     path="/company/growth-curve/{id}",
     *     summary="Obter registro da curva por ID",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/GrowthCurveResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowGrowthCurveUseCase $useCase): JsonResponse
    {
        $curve = $useCase->execute($id);

        return ApiResponse::success(new GrowthCurveResource($curve), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Post(
     *     path="/company/growth-curve",
     *     summary="Criar registro na curva de crescimento",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batchId","averageWeight"},
     *             @OA\Property(property="batchId", type="string", format="uuid", description="ID do lote"),
     *             @OA\Property(property="averageWeight", type="number", format="float", description="Peso médio (g)")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Criado"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(GrowthCurveStoreRequest $request, CreateGrowthCurveUseCase $useCase): JsonResponse
    {
        $curve = $useCase->execute($request->validated());

        return ApiResponse::created(new GrowthCurveResource($curve));
    }

    /**
     * @OA\Put(
     *     path="/company/growth-curve/{id}",
     *     summary="Atualizar registro da curva",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="averageWeight", type="number", format="float")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Atualizado"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        GrowthCurveUpdateRequest $request,
        string $id,
        UpdateGrowthCurveUseCase $useCase,
    ): JsonResponse {
        $curve = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new GrowthCurveResource($curve), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Delete(
     *     path="/company/growth-curve/{id}",
     *     summary="Excluir registro da curva",
     *     tags={"Growth Curves"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Excluído"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteGrowthCurveUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(null, Response::HTTP_OK, 'Growth curve successfully deleted');
    }
}
