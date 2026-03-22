<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\StockDTO;
use App\Application\UseCases\Stock\AdjustStockUseCase;
use App\Application\UseCases\Stock\CreateStockUseCase;
use App\Application\UseCases\Stock\DeleteStockUseCase;
use App\Application\UseCases\Stock\ListStocksUseCase;
use App\Application\UseCases\Stock\ShowStockUseCase;
use App\Application\UseCases\Stock\UpdateStockSettingsUseCase;
use App\Application\UseCases\Stock\UpdateStockUseCase;
use App\Presentation\Requests\Stock\StockAdjustRequest;
use App\Presentation\Requests\Stock\StockStoreRequest;
use App\Presentation\Requests\Stock\StockUpdateRequest;
use App\Presentation\Resources\Stock\StockResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class StockController
{
    /**
     * @OA\Get(
     *     path="/company/stocks",
     *     summary="List stocks",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=25)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of stocks",
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
     *                         @OA\Property(property="companyId", type="string", format="uuid"),
     *                         @OA\Property(property="supplyId", type="string", format="uuid"),
     *                         @OA\Property(property="supplierId", type="string", format="uuid", nullable=true),
     *                         @OA\Property(property="currentQuantity", type="number", format="float", example=100.5),
     *                         @OA\Property(property="unit", type="string", example="kg"),
     *                         @OA\Property(property="unitPrice", type="number", format="float", example=5.50),
     *                         @OA\Property(property="minimumStock", type="number", format="float", example=50),
     *                         @OA\Property(property="withdrawalQuantity", type="number", format="float", example=10),
     *                         @OA\Property(property="isBelowMinimum", type="boolean", example=false),
     *                         @OA\Property(
     *                             property="supply",
     *                             type="object",
     *                             nullable=true,
     *                             @OA\Property(property="id", type="string", format="uuid"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="defaultUnit", type="string")
     *                         ),
     *                         @OA\Property(
     *                             property="supplier",
     *                             type="object",
     *                             nullable=true,
     *                             @OA\Property(property="id", type="string", format="uuid"),
     *                             @OA\Property(property="name", type="string")
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
     */
    public function index(
        Request $request,
        ListStocksUseCase $useCase,
    ): JsonResponse {
        $pagination = $useCase->execute(
            filters: $request->only(['supply_id', 'supplier_id', 'per_page']),
        );
 
        return ApiResponse::success(
            data:       StockResource::collection($pagination->items()),
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
     *     path="/company/stock/{id}",
     *     summary="Get stock by ID",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Stock ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="companyId", type="string", format="uuid"),
     *                 @OA\Property(property="supplyId", type="string", format="uuid"),
     *                 @OA\Property(property="supplierId", type="string", format="uuid", nullable=true),
     *                 @OA\Property(property="currentQuantity", type="number", format="float", example=100.5),
     *                 @OA\Property(property="unit", type="string", example="kg"),
     *                 @OA\Property(property="unitPrice", type="number", format="float", example=5.50),
     *                 @OA\Property(property="minimumStock", type="number", format="float", example=50),
     *                 @OA\Property(property="withdrawalQuantity", type="number", format="float", example=10),
     *                 @OA\Property(property="isBelowMinimum", type="boolean", example=false),
     *                 @OA\Property(
     *                     property="supply",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="defaultUnit", type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="supplier",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Stock not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowStockUseCase $useCase,
    ): JsonResponse {
        $stock = $useCase->execute($id);
 
        return ApiResponse::success(
            data: new StockResource($stock->loadMissing(['supply', 'supplier'])),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/stock",
     *     summary="Create a stock",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"supply_id","quantity","unit","unit_price"},
     *             @OA\Property(property="companyId", type="string", format="uuid", nullable=true, description="Company ID"),
     *             @OA\Property(property="supplyId", type="string", format="uuid", description="Supply ID"),
     *             @OA\Property(property="supplierId", type="string", format="uuid", nullable=true, description="Supplier ID"),
     *             @OA\Property(
     *                 property="quantity",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 description="Initial quantity"
     *             ),
     *             @OA\Property(property="unit", type="string", maxLength=50, description="Unit of measure (kg, g, liter, ml, unit, box, piece)"),
     *             @OA\Property(
     *                 property="unitPrice",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 description="Unit price"
     *             ),
     *             @OA\Property(
     *                 property="totalCost",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 nullable=true,
     *                 description="Total cost (optional, overrides unitPrice when > 0)"
     *             ),
     *             @OA\Property(property="minimumStock", type="number", format="float", minimum=0, nullable=true, description="Minimum stock level"),
     *             @OA\Property(property="withdrawalQuantity", type="number", format="float", minimum=0, nullable=true, description="Withdrawal quantity"),
     *             @OA\Property(property="referenceId", type="string", format="uuid", nullable=true, description="Reference ID (e.g. purchase)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Stock created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="companyId", type="string", format="uuid"),
     *                 @OA\Property(property="supplyId", type="string", format="uuid"),
     *                 @OA\Property(property="supplierId", type="string", format="uuid", nullable=true),
     *                 @OA\Property(property="currentQuantity", type="number", format="float", example=100.5),
     *                 @OA\Property(property="unit", type="string", example="kg"),
     *                 @OA\Property(property="unitPrice", type="number", format="float", example=5.50),
     *                 @OA\Property(property="minimumStock", type="number", format="float", example=50),
     *                 @OA\Property(property="withdrawalQuantity", type="number", format="float", example=10),
     *                 @OA\Property(property="isBelowMinimum", type="boolean", example=false),
     *                 @OA\Property(
     *                     property="supply",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="defaultUnit", type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="supplier",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        StockStoreRequest $request,
        CreateStockUseCase $useCase,
    ): JsonResponse {
        $stock = $useCase->execute($request->validated());
 
        return ApiResponse::created(
            data:    new StockResource($stock),
            message: 'Stock entry created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/stock/{id}",
     *     summary="Update a stock",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Stock ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="currentQuantity",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 description="Current quantity"
     *             ),
     *             @OA\Property(property="unit", type="string", maxLength=50, description="Unit of measure"),
     *             @OA\Property(
     *                 property="minimumStock",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 description="Minimum stock level"
     *             ),
     *             @OA\Property(
     *                 property="withdrawalQuantity",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 description="Total withdrawn quantity"
     *             ),
     *             @OA\Property(
     *                 property="supplierId",
     *                 type="string",
     *                 format="uuid",
     *                 nullable=true,
     *                 description="Supplier ID"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="companyId", type="string", format="uuid"),
     *                 @OA\Property(property="supplyId", type="string", format="uuid"),
     *                 @OA\Property(property="supplierId", type="string", format="uuid", nullable=true),
     *                 @OA\Property(property="currentQuantity", type="number", format="float", example=100.5),
     *                 @OA\Property(property="unit", type="string", example="kg"),
     *                 @OA\Property(property="unitPrice", type="number", format="float", example=5.50),
     *                 @OA\Property(property="minimumStock", type="number", format="float", example=50),
     *                 @OA\Property(property="withdrawalQuantity", type="number", format="float", example=10),
     *                 @OA\Property(property="isBelowMinimum", type="boolean", example=false),
     *                 @OA\Property(
     *                     property="supply",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="defaultUnit", type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="supplier",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Stock not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        StockUpdateRequest $request,
        string $id,
        UpdateStockSettingsUseCase $useCase,
    ): JsonResponse {
        $stock = $useCase->execute($id, $request->validated());
 
        return ApiResponse::success(
            data:    new StockResource($stock),
            message: 'Stock settings updated successfully.',
        );
    }
    
    /**
     * @OA\Delete(
     *     path="/company/stock/{id}",
     *     summary="Delete a stock",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Stock ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Stock deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Stock successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Stock not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteStockUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);
 
        return ApiResponse::success(message: 'Stock deleted successfully.');
    }

    /**
     * @OA\Patch(
     *     path="/company/stock/{id}/adjust",
     *     summary="Ajustar estoque (contagem física)",
     *     description="Ajusta a quantidade do estoque com base na contagem física. Cria registro de ajuste de inventário e transação de auditoria.",
     *     tags={"Stocks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Stock ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"physicalQuantity"},
     *             @OA\Property(
     *                 property="physicalQuantity",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 description="Quantidade física contada no inventário"
     *             ),
     *             @OA\Property(
     *                 property="reason",
     *                 type="string",
     *                 maxLength=500,
     *                 nullable=true,
     *                 description="Motivo do ajuste"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Estoque ajustado com sucesso. Retorna o stock atualizado.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", format="uuid"),
     *                 @OA\Property(property="companyId", type="string", format="uuid"),
     *                 @OA\Property(property="supplyId", type="string", format="uuid"),
     *                 @OA\Property(property="supplierId", type="string", format="uuid", nullable=true),
     *                 @OA\Property(property="currentQuantity", type="number", format="float", example=100.5),
     *                 @OA\Property(property="unit", type="string", example="kg"),
     *                 @OA\Property(property="unitPrice", type="number", format="float", example=5.50),
     *                 @OA\Property(property="minimumStock", type="number", format="float", example=50),
     *                 @OA\Property(property="withdrawalQuantity", type="number", format="float", example=10),
     *                 @OA\Property(property="isBelowMinimum", type="boolean", example=false),
     *                 @OA\Property(
     *                     property="supply",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="defaultUnit", type="string")
     *                 ),
     *                 @OA\Property(
     *                     property="supplier",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string")
     *                 ),
     *                 @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
     *                 @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Erro de validação ou delta zero (quantidade física igual ao sistema)"),
     *     @OA\Response(response=404, description="Stock not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function adjust(
        string $id,
        StockAdjustRequest $request,
        AdjustStockUseCase $useCase,
    ): JsonResponse {
        $result = $useCase->execute($id, $request->validated());
        return ApiResponse::success(data: new StockResource($result->stock));
    }
}
