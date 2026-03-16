<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\StockDTO;
use App\Application\UseCases\Stock\CreateStockUseCase;
use App\Application\UseCases\Stock\DeleteStockUseCase;
use App\Application\UseCases\Stock\ListStocksUseCase;
use App\Application\UseCases\Stock\ShowStockUseCase;
use App\Application\UseCases\Stock\UpdateStockUseCase;
use App\Presentation\Requests\Stock\StockStoreRequest;
use App\Presentation\Requests\Stock\StockUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
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
     *                         @OA\Property(property="currentQuantity", type="number", format="float", example=100.5),
     *                         @OA\Property(property="unit", type="string", example="kg"),
     *                         @OA\Property(property="minimumStock", type="number", format="float", example=50),
     *                         @OA\Property(property="withdrawnQuantity", type="number", format="float", example=10),
     *                         @OA\Property(
     *                             property="company",
     *                             type="object",
     *                             nullable=true,
     *                             @OA\Property(property="name", type="string", nullable=true)
     *                         ),
     *                         @OA\Property(
     *                             property="supplier",
     *                             type="object",
     *                             nullable=true,
     *                             @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *                             @OA\Property(property="name", type="string", nullable=true)
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
    public function index(ListStocksUseCase $useCase): JsonResponse
    {
        try {
            $stocks     = $useCase->execute();
            $data       = $stocks->toArray(request());
            $pagination = $stocks->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
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
     *                 @OA\Property(property="currentQuantity", type="number", format="float", example=100.5),
     *                 @OA\Property(property="unit", type="string", example="kg"),
     *                 @OA\Property(property="minimumStock", type="number", format="float", example=50),
     *                 @OA\Property(property="withdrawnQuantity", type="number", format="float", example=10),
     *                 @OA\Property(
     *                     property="company",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="name", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="supplier",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *                     @OA\Property(property="name", type="string", nullable=true)
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
    public function show(string $id, ShowStockUseCase $useCase): JsonResponse
    {
        try {
            $stock = $useCase->execute($id);

            if (! $stock instanceof StockDTO || $stock->isEmpty()) {
                return ApiResponse::error(null, 'Stock not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($stock->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Stock not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
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
     *             required={"companyId","currentQuantity","unit","minimumStock"},
     *             @OA\Property(property="companyId", type="string", format="uuid", description="Company ID"),
     *             @OA\Property(property="supplierId", type="string", format="uuid", nullable=true, description="Supplier ID"),
     *             @OA\Property(
     *                 property="totalCost",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 nullable=true,
     *                 description="Total cost of the stock entry (optional, overrides unitPrice when > 0)"
     *             ),
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
     *                 nullable=true,
     *                 description="Total withdrawn quantity"
     *             )
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
     *                 @OA\Property(property="currentQuantity", type="number", format="float", example=100.5),
     *                 @OA\Property(property="unit", type="string", example="kg"),
     *                 @OA\Property(property="minimumStock", type="number", format="float", example=50),
     *                 @OA\Property(property="withdrawnQuantity", type="number", format="float", example=10),
     *                 @OA\Property(
     *                     property="company",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="name", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="supplier",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *                     @OA\Property(property="name", type="string", nullable=true)
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
    public function store(StockStoreRequest $request, CreateStockUseCase $useCase): JsonResponse
    {
        try {
            $stock = $useCase->execute($request->validated());

            return ApiResponse::created($stock->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
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
     *             @OA\Property(property="supplierId", type="string", format="uuid", nullable=true, description="Supplier ID")
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
     *                 @OA\Property(property="currentQuantity", type="number", format="float", example=100.5),
     *                 @OA\Property(property="unit", type="string", example="kg"),
     *                 @OA\Property(property="minimumStock", type="number", format="float", example=50),
     *                 @OA\Property(property="withdrawnQuantity", type="number", format="float", example=10),
     *                 @OA\Property(
     *                     property="company",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="name", type="string", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="supplier",
     *                     type="object",
     *                     nullable=true,
     *                     @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *                     @OA\Property(property="name", type="string", nullable=true)
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
    public function update(StockUpdateRequest $request, string $id, UpdateStockUseCase $useCase): JsonResponse
    {
        try {
            $stock = $useCase->execute($id, $request->validated());

            return ApiResponse::success($stock->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
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
    public function destroy(string $id, DeleteStockUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Stock not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Stock successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting stock', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
