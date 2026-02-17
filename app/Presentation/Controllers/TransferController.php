<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\TransferService;
use App\Presentation\Requests\Transfer\TransferStoreRequest;
use App\Presentation\Requests\Transfer\TransferUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class TransferController
{
    public function __construct(
        protected TransferService $transferService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/company/transfers",
     *     summary="Listar transfers",
     *     tags={"Transfers"},
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
     *         description="Lista paginada de transfers",
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
     *                         @OA\Property(property="batcheId", type="string", format="uuid"),
     *                         @OA\Property(property="originTankId", type="string", format="uuid"),
     *                         @OA\Property(property="destinationTankId", type="string", format="uuid"),
     *                         @OA\Property(property="quantity", type="integer", example=100),
     *                         @OA\Property(
     *                             property="description",
     *                             type="string",
     *                             example="Transferência entre tanques"
     *                         ),
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
     * Display a listing of transfers.
     */
    public function index(): JsonResponse
    {
        try {
            $transfers  = $this->transferService->showAllTransfers();
            $data       = $transfers->toArray(request());
            $pagination = $transfers->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/transfer/{id}",
     *     summary="Detalhar um transfer",
     *     tags={"Transfers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do transfer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfer encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batcheId", type="string", format="uuid"),
     *                 @OA\Property(property="originTankId", type="string", format="uuid"),
     *                 @OA\Property(property="destinationTankId", type="string", format="uuid"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Transfer not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Display the specified transfer.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->showTransfer($id);

            if (! $transfer instanceof \App\Application\DTOs\TransferDTO || $transfer->isEmpty()) {
                return ApiResponse::error(null, 'Transfer not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($transfer->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Transfer not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/company/transfer",
     *     summary="Criar um transfer",
     *     tags={"Transfers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batcheId","originTankId","destinationTankId","quantity","description"},
     *             @OA\Property(
     *                 property="batcheId",
     *                 type="string",
     *                 format="uuid",
     *                 description="ID do lote (Batche)"
     *             ),
     *             @OA\Property(
     *                 property="originTankId",
     *                 type="string",
     *                 format="uuid",
     *                 description="ID do tanque de origem"
     *             ),
     *             @OA\Property(
     *                 property="destinationTankId",
     *                 type="string",
     *                 format="uuid",
     *                 description="ID do tanque de destino"
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 minimum=1,
     *                 description="Quantidade transferida",
     *                 example=100
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Descrição da transferência",
     *                 example="Transferência entre tanques"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transfer criado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batcheId", type="string", format="uuid"),
     *                 @OA\Property(property="originTankId", type="string", format="uuid"),
     *                 @OA\Property(property="destinationTankId", type="string", format="uuid"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Store a newly created transfer.
     */
    public function store(TransferStoreRequest $request): JsonResponse
    {
        try {
            $transfer = $this->transferService->create($request->validated());

            return ApiResponse::created($transfer->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/transfer/{id}",
     *     summary="Atualizar um transfer",
     *     tags={"Transfers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do transfer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="batcheId",
     *                 type="string",
     *                 format="uuid",
     *                 description="ID do lote (Batche)"
     *             ),
     *             @OA\Property(
     *                 property="originTankId",
     *                 type="string",
     *                 format="uuid",
     *                 description="ID do tanque de origem"
     *             ),
     *             @OA\Property(
     *                 property="destinationTankId",
     *                 type="string",
     *                 format="uuid",
     *                 description="ID do tanque de destino"
     *             ),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="integer",
     *                 minimum=1,
     *                 description="Quantidade transferida",
     *                 example=100
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 description="Descrição da transferência"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfer atualizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="batcheId", type="string", format="uuid"),
     *                 @OA\Property(property="originTankId", type="string", format="uuid"),
     *                 @OA\Property(property="destinationTankId", type="string", format="uuid"),
     *                 @OA\Property(property="quantity", type="integer", example=100),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Transfer not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Update the specified transfer.
     */
    public function update(TransferUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $transfer = $this->transferService->updateTransfer($id, $request->validated());

            return ApiResponse::success($transfer->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/transfer/{id}",
     *     summary="Remover um transfer",
     *     tags={"Transfers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do transfer",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transfer removido",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Transfer successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Transfer not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     * Remove the specified transfer.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->transferService->deleteTransfer($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Transfer not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Transfer successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting transfer', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
