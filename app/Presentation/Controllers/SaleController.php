<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Sale\CancelSaleUseCase;
use App\Application\UseCases\Sale\DeleteSaleUseCase;
use App\Application\UseCases\Sale\ListSalesUseCase;
use App\Application\UseCases\Sale\ProcessHarvestSaleUseCase;
use App\Application\UseCases\Sale\ShowSaleUseCase;
use App\Application\UseCases\Sale\UpdateSaleUseCase;
use App\Presentation\Requests\Sale\SaleStoreRequest;
use App\Presentation\Requests\Sale\SaleUpdateRequest;
use App\Presentation\Resources\Sale\SaleResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Sales", description="Vendas")
 * @OA\Schema(
 *     schema="Sale",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="totalWeight", type="number", format="float"),
 *     @OA\Property(property="pricePerKg", type="number", format="float"),
 *     @OA\Property(property="totalRevenue", type="number", format="float"),
 *     @OA\Property(property="saleDate", type="string", format="date"),
 *     @OA\Property(property="status", type="string", enum={"pending","confirmed","cancelled"}),
 *     @OA\Property(property="statusLabel", type="string", example="Pending"),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="batchId", type="string", format="uuid"),
 *     @OA\Property(property="stockingId", type="string", format="uuid", nullable=true),
 *     @OA\Property(
 *         property="company",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="client",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="batch",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="stocking",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="quantity", type="integer"),
 *         @OA\Property(property="averageWeight", type="number", format="float")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class SaleController
{
    /**
     * @OA\Get(
     *     path="/company/sales",
     *     summary="List sales",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="client_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="batch_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","confirmed","cancelled"})
     *     ),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Paginated list of sales"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(
        Request $request,
        ListSalesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only([
                'client_id', 'batch_id', 'status',
                'date_from', 'date_to', 'per_page', 'page',
            ]),
        );

        return ApiResponse::success(
            data:       SaleResource::collection($paginator->items()),
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
     *     path="/company/sale/{id}",
     *     summary="Get sale by ID",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sale ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Sale found"),
     *     @OA\Response(response=404, description="Sale not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowSaleUseCase $useCase,
    ): JsonResponse {
        $sale = $useCase->execute($id);

        return ApiResponse::success(
            data: new SaleResource($sale),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/sale",
     *     summary="Create a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"clientId","batchId","totalWeight","pricePerKg","saleDate"},
     *             @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="clientId", type="string", format="uuid"),
     *             @OA\Property(property="batchId", type="string", format="uuid"),
     *             @OA\Property(property="stockingId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="financialCategoryId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="totalWeight", type="number", format="float", minimum=0.001),
     *             @OA\Property(property="pricePerKg", type="number", format="float", minimum=0),
     *             @OA\Property(property="saleDate", type="string", format="date"),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending","confirmed","cancelled"},
     *                 nullable=true
     *             ),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="isTotalHarvest", type="boolean", nullable=true),
     *             @OA\Property(
     *                 property="tolerancePercent",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 maximum=50,
     *                 nullable=true
     *             ),
     *             @OA\Property(property="needsInvoice", type="boolean", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Sale created"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Related entities not found")
     * )
     */
    public function store(
        SaleStoreRequest $request,
        ProcessHarvestSaleUseCase $useCase,
    ): JsonResponse {
        $sale = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new SaleResource($sale),
            message: 'Sale registered successfully.',
        );
    }

    /**
     * Delegação: {@see UpdateSaleUseCase} → {@see \App\Application\Actions\Sale\UpdateSaleAction}
     * (transação, locks, trava financeira, biomassa, despesca e sincronização do contas a receber).
     *
     * @OA\Put(
     *     path="/company/sale/{id}",
     *     summary="Update a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sale ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="clientId", type="string", format="uuid"),
     *             @OA\Property(property="totalWeight", type="number", format="float", minimum=0.001),
     *             @OA\Property(property="pricePerKg", type="number", format="float", minimum=0),
     *             @OA\Property(property="saleDate", type="string", format="date"),
     *             @OA\Property(property="status", type="string", enum={"pending","confirmed","cancelled"}),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(
     *                 property="batchId",
     *                 type="string",
     *                 format="uuid",
     *                 nullable=true,
     *                 description="Must match the sale batch; cannot be changed."
     *             ),
     *             @OA\Property(
     *                 property="stockingId",
     *                 type="string",
     *                 format="uuid",
     *                 nullable=true,
     *                 description="Must match the sale stocking; cannot be changed."
     *             ),
     *             @OA\Property(property="isTotalHarvest", type="boolean", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Sale updated"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Sale not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        SaleUpdateRequest $request,
        string $id,
        UpdateSaleUseCase $useCase,
    ): JsonResponse {
        $sale = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new SaleResource($sale),
            message: 'Sale updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/sale/{id}",
     *     summary="Delete a sale",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sale ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Sale deleted"),
     *     @OA\Response(response=404, description="Sale not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteSaleUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Sale deleted successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/company/sale/{id}/cancel",
     *     summary="Cancel a sale",
     *     description="Cancela a venda (rota distinta de excluir permanentemente o registro).",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Sale ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Sale cancelled"),
     *     @OA\Response(response=404, description="Sale not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function cancel(
        string $id,
        CancelSaleUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Sale cancelled successfully.');
    }
}
