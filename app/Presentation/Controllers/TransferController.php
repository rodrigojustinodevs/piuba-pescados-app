<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Transfer\CreateTransferUseCase;
use App\Application\UseCases\Transfer\DeleteTransferUseCase;
use App\Application\UseCases\Transfer\ListTransfersUseCase;
use App\Application\UseCases\Transfer\ShowTransferUseCase;
use App\Application\UseCases\Transfer\UpdateTransferUseCase;
use App\Presentation\Requests\Transfer\TransferStoreRequest;
use App\Presentation\Requests\Transfer\TransferUpdateRequest;
use App\Presentation\Resources\Transfer\TransferResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Transfers", description="Transferências de lote entre tanques")
 *
 * Exceções tratadas pelo Handler:
 *  - ModelNotFoundException                  → 404
 *  - TransferBatchOriginMismatchException    → 422
 *  - TankAlreadyHasActiveBatchException      → 422
 *  - TransferSameTankException               → 422
 *  - ValidationException                     → 422
 */
final class TransferController
{
    /**
     * @OA\Get(
     *     path="/company/transfers",
     *     summary="Listar transferências",
     *     tags={"Transfers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="batch_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="origin_tank_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="destination_tank_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de transferências",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/TransferResource")
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListTransfersUseCase $useCase,
    ): JsonResponse {
        $pagination = $useCase->execute(
            // $request->only() — nunca $request->all()
            filters: $request->only(['batch_id', 'origin_tank_id', 'destination_tank_id', 'per_page']),
        );

        return ApiResponse::success(
            data:       TransferResource::collection($pagination->items()),
            pagination: [
                'total'        => $pagination->total(),
                'current_page' => $pagination->currentPage(),
                'last_page'    => $pagination->lastPage(),
                'first_page'   => $pagination->firstPage(),
                'per_page'     => $pagination->perPage(),
            ],
        );
    }

    /**
     * @OA\Get(
     *     path="/company/transfer/{id}",
     *     summary="Obter transferência por ID",
     *     tags={"Transfers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Transferência encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/TransferResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowTransferUseCase $useCase,
    ): JsonResponse {
        return ApiResponse::success(
            data: new TransferResource($useCase->execute($id)),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/transfer",
     *     summary="Criar transferência",
     *     tags={"Transfers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"batch_id","origin_tank_id","destination_tank_id"},
     *             @OA\Property(property="batch_id", type="string", format="uuid"),
     *             @OA\Property(property="origin_tank_id", type="string", format="uuid"),
     *             @OA\Property(property="destination_tank_id", type="string", format="uuid"),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Transferência criada"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Erro de validação/regra de negócio")
     * )
     */
    public function store(
        TransferStoreRequest $request,
        CreateTransferUseCase $useCase,
    ): JsonResponse {
        $transfer = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new TransferResource($transfer),
            message: 'Transfer created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/transfer/{id}",
     *     summary="Atualizar transferência",
     *     tags={"Transfers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="batch_id", type="string", format="uuid"),
     *             @OA\Property(property="origin_tank_id", type="string", format="uuid"),
     *             @OA\Property(property="destination_tank_id", type="string", format="uuid"),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Transferência atualizada"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Erro de validação/regra de negócio")
     * )
     */
    public function update(
        TransferUpdateRequest $request,
        string $id,
        UpdateTransferUseCase $useCase,
    ): JsonResponse {
        $transfer = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new TransferResource($transfer),
            message: 'Transfer updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/transfer/{id}",
     *     summary="Excluir transferência",
     *     tags={"Transfers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Transferência removida"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(
        string $id,
        DeleteTransferUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Transfer deleted successfully.');
    }
}
