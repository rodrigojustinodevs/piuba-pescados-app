<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\SalesOrder\CancelSalesOrderUseCase;
use App\Application\UseCases\SalesOrder\CreateSalesOrderUseCase;
use App\Application\UseCases\SalesOrder\DeleteSalesOrderUseCase;
use App\Application\UseCases\SalesOrder\ListSalesOrdersUseCase;
use App\Application\UseCases\SalesOrder\ShowSalesOrderUseCase;
use App\Application\UseCases\SalesOrder\UpdateSalesOrderUseCase;
use App\Presentation\Requests\SalesOrder\SalesOrderStoreRequest;
use App\Presentation\Requests\SalesOrder\SalesOrderUpdateRequest;
use App\Presentation\Resources\SalesOrder\SalesOrderResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Sales orders", description="Pedidos")
 */
final class SalesOrderController
{
    /**
     * @OA\Get(
     *     path="/company/sales-orders",
     *     summary="Lista pedidos/orçamentos da empresa",
     *     tags={"Sales orders"},
     *     security={{"bearerAuth":{}}},
     * @OA\Parameter(name="clientId", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     * @OA\Parameter(name="status",   in="query", required=false, @OA\Schema(type="string")),
     * @OA\Parameter(
     *     name="type",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="string", enum={"quotation","order"})
     * ),
     * @OA\Parameter(name="perPage",  in="query", required=false, @OA\Schema(type="integer", example=25)),
     * @OA\Parameter(name="page",     in="query", required=false, @OA\Schema(type="integer", example=1)),
     * @OA\Response(response=200,     description="Lista paginada"),
     * @OA\Response(response=401,     description="Não autorizado")
     * )
     */
    public function index(Request $request, ListSalesOrdersUseCase $useCase): JsonResponse
    {
        $paginator = $useCase->execute(
            $request->only(
                [
                'clientId', 'status', 'type', 'perPage', 'page', 'companyId',
                ]
            ),
        );

        return ApiResponse::success(
            data:       SalesOrderResource::collection($paginator->items()),
            pagination: [
                'total'        => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'first_page'   => $paginator->firstPage(),
                'per_page'     => $paginator->perPage(),
            ],
        );
    }

    /**
     * @OA\Get(
     *     path="/company/sales-order/{id}",
     *     summary="Detalhe de um pedido/orçamento",
     *     tags={"Sales orders"},
     *     security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id",   in="path", required=true, @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Encontrado"),
     * @OA\Response(response=404, description="Não encontrado"),
     * @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function show(string $id, ShowSalesOrderUseCase $useCase): JsonResponse
    {
        $order = $useCase->execute($id);

        return ApiResponse::success(SalesOrderResource::make($order));
    }

    /**
     * @OA\Delete(
     *     path="/company/sales-order/{id}",
     *     summary="Remove (soft delete) pedido/orçamento em rascunho ou aberto",
     *     tags={"Sales orders"},
     *     security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id",   in="path", required=true, @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Removido"),
     * @OA\Response(response=404, description="Não encontrado"),
     * @OA\Response(response=422, description="Status não permite exclusão"),
     * @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function destroy(string $id, DeleteSalesOrderUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(
            data:    null,
            status:  JsonResponse::HTTP_OK,
            message: 'Sales order deleted successfully.',
        );
    }

    /**
     * @OA\Post(
     *     path="/api/company/sale-order",
     *     summary="Cria um pedido (order) com itens",
     *     tags={"Sales orders"},
     *     security={{"bearerAuth":{}}},
     * @OA\Response(response=201, description="Pedido criado"),
     * @OA\Response(response=422, description="Erro de validação"),
     * @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function store(
        SalesOrderStoreRequest $request,
        CreateSalesOrderUseCase $useCase,
    ): JsonResponse {
        $order = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    SalesOrderResource::make($order),
            message: 'Order created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/api/company/sale-order/{id}",
     *     summary="Atualiza um pedido (order) com regras de validação do create",
     *     tags={"Sales orders"},
     *     security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id",   in="path", required=true, @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Pedido atualizado"),
     * @OA\Response(response=422, description="Erro de validação"),
     * @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function update(
        string $id,
        SalesOrderUpdateRequest $request,
        UpdateSalesOrderUseCase $useCase,
    ): JsonResponse {
        $order = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data: SalesOrderResource::make($order),
            message: 'Order updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/sales-order/{id}/cancel",
     *     summary="Cancela um pedido (order)",
     *     tags={"Sales orders"},
     *     security={{"bearerAuth":{}}},
     * @OA\Parameter(name="id",   in="path", required=true, @OA\Schema(type="string", format="uuid")),
     * @OA\Response(response=200, description="Pedido cancelado"),
     * @OA\Response(response=404, description="Não encontrado"),
     * @OA\Response(response=422, description="Status não permite cancelamento"),
     * @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function cancel(string $id, CancelSalesOrderUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Sales order cancelled successfully.');
    }
}
