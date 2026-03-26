<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Biometry\CreateBiometryUseCase;
use App\Application\UseCases\Biometry\DeleteBiometryUseCase;
use App\Application\UseCases\Biometry\ListBiometriesUseCase;
use App\Application\UseCases\Biometry\ShowBiometryUseCase;
use App\Application\UseCases\Biometry\UpdateBiometryUseCase;
use App\Presentation\Requests\Biometry\BiometryStoreRequest;
use App\Presentation\Requests\Biometry\BiometryUpdateRequest;
use App\Presentation\Resources\Biometry\BiometryResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(name="Biometry", description="Operações de biometria")
 */
final class BiometryController
{
    /**
     * @OA\Get(
     *     path="/company/biometries",
     *     summary="Listar biometrias",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Parameter(name="batch_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de biometrias",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/BiometryResource")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request, ListBiometriesUseCase $useCase): JsonResponse
    {
        $result     = $useCase->execute($request->all());
        $collection = BiometryResource::collection($result->items());

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
     *     path="/company/biometry/{id}",
     *     summary="Detalhar uma biometria",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Biometria encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/BiometryResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Biometry not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowBiometryUseCase $useCase): JsonResponse
    {
        $biometry = $useCase->execute($id);

        return ApiResponse::success(new BiometryResource($biometry), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Post(
     *     path="/company/biometry",
     *     summary="Criar biometria",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batchId","biometryDate"},
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="biometryDate", type="string", format="date", example="2025-02-20"),
     *             @OA\Property(property="averageWeight", type="number", format="float", example=15.5),
     *             @OA\Property(property="sampleWeight", type="number", format="float", example=25.5),
     *             @OA\Property(property="sampleQuantity", type="integer", example=100),
     *             @OA\Property(property="fcr", type="number", format="float", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Biometria criada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="data", ref="#/components/schemas/BiometryResource")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(BiometryStoreRequest $request, CreateBiometryUseCase $useCase): JsonResponse
    {
        $biometry = $useCase->execute($request->validated());

        return ApiResponse::created(new BiometryResource($biometry));
    }

    /**
     * @OA\Put(
     *     path="/company/biometry/{id}",
     *     summary="Atualizar biometria",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="biometryDate", type="string", format="date"),
     *             @OA\Property(property="averageWeight", type="number", format="float"),
     *             @OA\Property(property="sampleWeight", type="number", format="float"),
     *             @OA\Property(property="sampleQuantity", type="integer"),
     *             @OA\Property(property="fcr", type="number", format="float", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Biometria atualizada",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", ref="#/components/schemas/BiometryResource")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Biometry not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(BiometryUpdateRequest $request, string $id, UpdateBiometryUseCase $useCase): JsonResponse
    {
        $biometry = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new BiometryResource($biometry), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Delete(
     *     path="/company/biometry/{id}",
     *     summary="Excluir biometria",
     *     tags={"Biometry"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Biometria excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Biometry successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Biometry not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteBiometryUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(null, Response::HTTP_OK, 'Biometry successfully deleted');
    }
}
