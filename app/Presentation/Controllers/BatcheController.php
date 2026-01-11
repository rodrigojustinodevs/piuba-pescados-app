<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\BatcheService;
use App\Presentation\Requests\Batche\BatcheStoreRequest;
use App\Presentation\Requests\Batche\BatcheUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class BatcheController
{
    public function __construct(
        protected BatcheService $batcheService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/company/batches",
     *     summary="Listar lotes",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de lotes",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="entryDate", type="string", format="date"),
     *                     @OA\Property(property="initialQuantity", type="integer", example=1000),
     *                     @OA\Property(property="species", type="string", example="Tilapia"),
     *                     @OA\Property(property="status", type="string", example="active"),
     *                     @OA\Property(property="cultivation", type="string", example="daycare"),
     *                     @OA\Property(
     *                         property="tank",
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *                         @OA\Property(property="name", type="string", nullable=true)
     *                     ),
     *                     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Display a listing of batches.
     */
    public function index(): JsonResponse
    {
        try {
            $batches    = $this->batcheService->showAllBatches();
            $data       = $batches->toArray(request());
            $pagination = $batches->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/batche/{id}",
     *     summary="Detalhar um lote",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do lote",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lote encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="entryDate", type="string", format="date", example="2024-10-15"),
     *                 @OA\Property(property="initialQuantity", type="integer", example=1200),
     *                 @OA\Property(property="species", type="string", example="Camarão"),
     *                 @OA\Property(property="status", type="string", example="active"),
     *                 @OA\Property(property="cultivation", type="string", example="nursery"),
     *                 @OA\Property(
     *                     property="tank",
     *                     type="object",
     *                     @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *                     @OA\Property(property="name", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Batche not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Display the specified batche.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $batche = $this->batcheService->showBatche($id);

            if (! $batche instanceof \App\Application\DTOs\BatcheDTO || $batche->isEmpty()) {
                return ApiResponse::error(null, 'Batche not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($batche->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Batche not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/company/batche",
     *     summary="Criar um lote",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"tankId", "entryDate", "initialQuantity", "species", "cultivation"},
     *             @OA\Property(
     *                 property="tankId",
     *                 type="string",
     *                 format="uuid",
     *                 description="ID do tanque",
     *                 example="550e8400-e29b-41d4-a716-446655440000"
     *             ),
     *             @OA\Property(
     *                 property="entryDate",
     *                 type="string",
     *                 format="date",
     *                 description="Data de entrada",
     *                 example="2024-10-15"
     *             ),
     *             @OA\Property(
     *                 property="initialQuantity",
     *                 type="integer",
     *                 minimum=1,
     *                 description="Quantidade inicial",
     *                 example=1200
     *             ),
     *             @OA\Property(
     *                 property="species",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Espécie",
     *                 example="Tilapia"
     *             ),
     *             @OA\Property(
     *                 property="cultivation",
     *                 type="string",
     *                 enum={"daycare","nursery"},
     *                 description="Tipo de cultivo",
     *                 example="daycare"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Lote criado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Store a newly created batche.
     */
    public function store(BatcheStoreRequest $request): JsonResponse
    {
        try {
            $batche = $this->batcheService->create($request->validated());

            return ApiResponse::created($batche->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/batche/{id}",
     *     summary="Atualizar um lote",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do lote",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="tankId",
     *                 type="string",
     *                 format="uuid",
     *                 description="ID do tanque"
     *             ),
     *             @OA\Property(
     *                 property="entryDate",
     *                 type="string",
     *                 format="date",
     *                 description="Data de entrada"
     *             ),
     *             @OA\Property(
     *                 property="initialQuantity",
     *                 type="integer",
     *                 minimum=1,
     *                 description="Quantidade inicial"
     *             ),
     *             @OA\Property(
     *                 property="species",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Espécie"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"active","finished"},
     *                 description="Status do lote"
     *             ),
     *             @OA\Property(
     *                 property="cultivation",
     *                 type="string",
     *                 enum={"daycare","nursery"},
     *                 description="Tipo de cultivo"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lote atualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Batche not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Update the specified batche.
     */
    public function update(BatcheUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $batche = $this->batcheService->updateBatche($id, $request->validated());

            return ApiResponse::success($batche->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/batche/{id}",
     *     summary="Remover um lote",
     *     tags={"Batches"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do lote",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lote removido",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Batche successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Batche not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Remove the specified batche.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->batcheService->deleteBatche($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Batche not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Batche successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting batche', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
