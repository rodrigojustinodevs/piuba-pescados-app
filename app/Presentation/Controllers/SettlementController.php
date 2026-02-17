<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\SettlementService;
use App\Presentation\Requests\Settlement\SettlementStoreRequest;
use App\Presentation\Requests\Settlement\SettlementUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SettlementController
{
    public function __construct(
        protected SettlementService $settlementService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/company/settlements",
     *     summary="Listar settlements",
     *     tags={"Settlements"},
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
     *         @OA\Schema(type="integer", example=25)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de settlements",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="batcheId", type="string", format="uuid", nullable=true),
     *                         @OA\Property(
     *                             property="settlementDate",
     *                             type="string",
     *                             format="date",
     *                             example="2026-02-13"
     *                         ),
     *                         @OA\Property(property="quantity", type="integer", example=100),
     *                         @OA\Property(property="averageWeight", type="number", format="float", example=1.25),
     *                         @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                         @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *                     )
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=1),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="first_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Display a listing of settlements.
     */
    public function index(): JsonResponse
    {
        try {
            $settlements = $this->settlementService->showAllSettlements();
            $data        = $settlements->toArray(request());
            $pagination  = $settlements->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/settlement/{id}",
     *     summary="Detalhar um settlement",
     *     tags={"Settlements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do settlement",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settlement encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batcheId", type="string", format="uuid"),
     *                 @OA\Property(property="settlementDate", type="string", format="date", example="2026-02-13"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="averageWeight", type="number", format="float", example=1.25),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Settlement not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Display the specified settlement.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $settlement = $this->settlementService->showSettlement($id);

            if (! $settlement instanceof \App\Application\DTOs\SettlementDTO || $settlement->isEmpty()) {
                return ApiResponse::error(null, 'Settlement not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($settlement->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Settlement not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/company/settlement",
     *     summary="Criar um settlement",
     *     tags={"Settlements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batcheId","settlementDate","quantity","averageWeight"},
     *             @OA\Property(
     *                 property="batcheId",
     *                 type="string",
     *                 format="uuid",
     *                 description="ID do lote (Batche)"
     *             ),
     *             @OA\Property(
     *                 property="settlementDate",
     *                 type="string",
     *                 format="date",
     *                 description="Data do settlement",
     *                 example="2026-02-13"
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 minimum=1,
     *                 description="Quantidade",
     *                 example=100
     *             ),
     *             @OA\Property(
     *                 property="averageWeight",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 description="Peso médio",
     *                 example=1.25
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Settlement criado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batcheId", type="string", format="uuid"),
     *                 @OA\Property(property="settlementDate", type="string", format="date", example="2026-02-13"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="averageWeight", type="number", format="float", example=1.25),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Store a newly created settlement.
     */
    public function store(SettlementStoreRequest $request): JsonResponse
    {
        try {
            $settlement = $this->settlementService->create($request->validated());

            return ApiResponse::created($settlement->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/settlement/{id}",
     *     summary="Atualizar um settlement",
     *     tags={"Settlements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do settlement",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="batcheId", type="string", format="uuid", description="ID do lote (Batche)"),
     *             @OA\Property(
     *                 property="settlementDate",
     *                 type="string",
     *                 format="date",
     *                 description="Data do settlement",
     *                 example="2026-02-13"
     *             ),
     *             @OA\Property(property="quantity", type="integer", minimum=1, description="Quantidade", example=100),
     *             @OA\Property(
     *                 property="averageWeight",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 description="Peso médio",
     *                 example=1.25
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settlement atualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batcheId", type="string", format="uuid"),
     *                 @OA\Property(property="settlementDate", type="string", format="date", example="2026-02-13"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="averageWeight", type="number", format="float", example=1.25),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Settlement not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Update the specified settlement.
     */
    public function update(SettlementUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $settlement = $this->settlementService->updateSettlement($id, $request->validated());

            return ApiResponse::success($settlement->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/settlement/{id}",
     *     summary="Remover um settlement",
     *     tags={"Settlements"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do settlement",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Settlement removido",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Settlement successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Settlement not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Remove the specified settlement.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->settlementService->deleteSettlement($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Settlement not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Settlement successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting settlement', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
