<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\StockTransaction\ListCompanyStockTransactionsUseCase;
use App\Application\UseCases\StockTransaction\ListStockTransactionsUseCase;
use App\Presentation\Resources\Stock\StockTransactionResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Stock Transactions", description="Movimentações de estoque")
 */
final class StockTransactionController
{
    /**
     * @OA\Get(
     *     path="/company/stock-transactions",
     *     summary="List stock transactions (company)",
     *     tags={"Stock Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="reference_type",
     *         in="query",
     *         description="Filter by reference type",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"purchase_item","feeding","adjustment","transfer","stocking","sale"},
     *             example="adjustment"
     *         )
     *     ),
     *     @OA\Parameter(name="reference_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of stock transactions",
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
     *                         @OA\Property(property="direction", type="string", enum={"in","out"}, example="out"),
     *                         @OA\Property(property="quantity", type="number", format="float", example=10.5),
     *                         @OA\Property(property="unit", type="string", example="kg"),
     *                         @OA\Property(property="unitPrice", type="number", format="float", example=5.50),
     *                         @OA\Property(property="totalCost", type="number", format="float", example=57.75),
     *                         @OA\Property(property="referenceType", type="string", example="adjustment"),
     *                         @OA\Property(property="referenceId", type="string", format="uuid"),
     *                         @OA\Property(property="createdAt", type="string", format="date-time", nullable=true)
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
        ListCompanyStockTransactionsUseCase $useCase,
    ): JsonResponse {
        $pagination = $useCase->execute(
            filters: $request->only([
                'direction',
                'reference_type',
                'reference_id',
                'per_page',
                'page',
            ]),
        );

        return ApiResponse::success(
            data:       StockTransactionResource::collection($pagination->items()),
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
     *     path="/company/stock/{id}/transactions",
     *     summary="List stock transactions by stock ID",
     *     tags={"Stock Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Reference ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Parameter(
     *         name="direction",
     *         in="query",
     *         description="Filter by direction (in|out)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"in","out"}, example="out")
     *     ),
     *     @OA\Parameter(
     *         name="reference_type",
     *         in="query",
     *         description="Filter by reference type",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"purchase_item","feeding","adjustment","transfer","stocking","sale"},
     *             example="adjustment"
     *         )
     *     ),
     *     @OA\Parameter(name="reference_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of stock transactions",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(type="object")
     *                 )
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function byStock(
        Request $request,
        string $id,
        ListStockTransactionsUseCase $useCase,
    ): JsonResponse {
        $pagination = $useCase->execute(
            referenceId: $id,
            filters: $request->only(['direction', 'reference_type', 'reference_id', 'per_page', 'page']),
        );

        return ApiResponse::success(
            data:       StockTransactionResource::collection($pagination->items()),
            pagination: [
                'total'        => $pagination->total(),
                'current_page' => $pagination->currentPage(),
                'last_page'    => $pagination->lastPage(),
                'first_page'   => $pagination->firstPage(),
                'per_page'     => $pagination->perPage(),
            ],
        );
    }
}
