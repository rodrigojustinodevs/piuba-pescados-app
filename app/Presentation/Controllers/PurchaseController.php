<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\PurchaseDTO;
use App\Application\UseCases\Purchase\CreatePurchaseUseCase;
use App\Application\UseCases\Purchase\DeletePurchaseUseCase;
use App\Application\UseCases\Purchase\ListPurchasesUseCase;
use App\Application\UseCases\Purchase\ReceivePurchaseUseCase;
use App\Application\UseCases\Purchase\ShowPurchaseUseCase;
use App\Application\UseCases\Purchase\UpdatePurchaseUseCase;
use App\Presentation\Requests\Purchase\PurchaseStoreRequest;
use App\Presentation\Requests\Purchase\PurchaseUpdateRequest;
use App\Presentation\Resources\Purchase\PurchaseResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

/**
 * @OA\Schema(
 *     schema="PurchaseItem",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="supplyId", type="string", format="uuid"),
 *     @OA\Property(
 *         property="quantity",
 *         type="number",
 *         format="float",
 *         description="Quantidade do item (Value Object Quantity, > 0)"
 *     ),
 *     @OA\Property(
 *         property="unit",
 *         type="string",
 *         description="Unidade (Value Object Unit, ex: kg, g, unit)",
 *         example="kg"
 *     ),
 *     @OA\Property(
 *         property="unitPrice",
 *         type="number",
 *         format="float",
 *         description="Preço unitário (Value Object Money, >= 0)"
 *     ),
 *     @OA\Property(
 *         property="totalPrice",
 *         type="number",
 *         format="float",
 *         description="Total do item (quantity x unitPrice)"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Purchase",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="companyId", type="string", format="uuid"),
 *     @OA\Property(property="supplierId", type="string", format="uuid", nullable=true),
 *     @OA\Property(
 *         property="purchaseDate",
 *         type="string",
 *         format="date",
 *         example="2026-03-17"
 *     ),
 *     @OA\Property(
 *         property="totalPrice",
 *         type="number",
 *         format="float",
 *         description="Soma dos totais dos itens (Value Object Money)"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"draft","confirmed","received","cancelled"}
 *     ),
 *     @OA\Property(
 *         property="invoiceNumber",
 *         type="string",
 *         nullable=true,
 *         maxLength=100
 *     ),
 *     @OA\Property(
 *         property="receivedAt",
 *         type="string",
 *         format="date-time",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/PurchaseItem")
 *     ),
 *     @OA\Property(
 *         property="createdAt",
 *         type="string",
 *         format="date-time",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="updatedAt",
 *         type="string",
 *         format="date-time",
 *         nullable=true
 *     )
 * )
 */
class PurchaseController
{
    /**
     * @OA\Get(
     *     path="/company/purchases",
     *     summary="List purchases",
     *     tags={"Purchases"},
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
     *         description="Paginated list of purchases",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Purchase")
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
        ListPurchasesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only([
                'status', 'supplier_id', 'date_from', 'date_to', 'per_page', 'page',
            ]),
        );
 
        return ApiResponse::success(
            data:       PurchaseResource::collection($paginator->items()),
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
     *     path="/company/purchase/{id}",
     *     summary="Get purchase by ID",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Purchase ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Purchase found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 ref="#/components/schemas/Purchase"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Purchase not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowPurchaseUseCase $useCase,
    ): JsonResponse {
        $purchase = $useCase->execute($id);
 
        return ApiResponse::success(
            data: new PurchaseResource($purchase->loadMissing(['supplier', 'items'])),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/purchase",
     *     summary="Create a purchase",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"supplierId","purchaseDate","items"},
     *             @OA\Property(
     *                 property="companyId",
     *                 type="string",
     *                 format="uuid",
     *                 nullable=true,
     *                 description="Company ID"
     *             ),
     *             @OA\Property(
     *                 property="supplierId",
     *                 type="string",
     *                 format="uuid",
     *                 description="Supplier ID"
     *             ),
     *             @OA\Property(
     *                 property="invoiceNumber",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=100
     *             ),
     *             @OA\Property(
     *                 property="purchaseDate",
     *                 type="string",
     *                 format="date",
     *                 example="2026-03-17"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 nullable=true,
     *                 enum={"draft","confirmed","received","cancelled"}
     *             ),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     required={"supplyId","quantity","unitPrice"},
     *                     @OA\Property(
     *                         property="supplyId",
     *                         type="string",
     *                         format="uuid"
     *                     ),
     *                     @OA\Property(
     *                         property="quantity",
     *                         type="number",
     *                         format="float",
     *                         minimum=0.000001,
     *                         description="Quantidade (VO Quantity, > 0)"
     *                     ),
     *                     @OA\Property(
     *                         property="unit",
     *                         type="string",
     *                         nullable=true,
     *                         description="Unidade (VO Unit: kg, g, unit)",
     *                         example="kg"
     *                     ),
     *                     @OA\Property(
     *                         property="unitPrice",
     *                         type="number",
     *                         format="float",
     *                         minimum=0,
     *                         description="Preço unitário (VO Money, >= 0)"
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Purchase created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(
     *                 property="response",
     *                 ref="#/components/schemas/Purchase"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        PurchaseStoreRequest $request,
        CreatePurchaseUseCase $useCase,
    ): JsonResponse {
        $purchase = $useCase->execute($request->validated());
 
        return ApiResponse::created(
            data:    new PurchaseResource($purchase->load(['supplier', 'items'])),
            message: 'Purchase created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/purchase/{id}",
     *     summary="Update a purchase",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Purchase ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="supplierId", type="string", format="uuid", nullable=true),
     *             @OA\Property(
     *                 property="invoiceNumber",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=100
     *             ),
     *             @OA\Property(
     *                 property="totalPrice",
     *                 type="number",
     *                 format="float",
     *                 minimum=0,
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="purchaseDate",
     *                 type="string",
     *                 format="date",
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"draft","confirmed","received","cancelled"},
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="receivedAt",
     *                 type="string",
     *                 format="date-time",
     *                 nullable=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Purchase updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 ref="#/components/schemas/Purchase"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Purchase not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        PurchaseUpdateRequest $request,
        string $id,
        UpdatePurchaseUseCase $useCase,
    ): JsonResponse {
        $purchase = $useCase->execute($id, $request->validated());
 
        return ApiResponse::success(
            data:    new PurchaseResource($purchase->load(['supplier', 'items'])),
            message: 'Purchase updated successfully.',
        );
    }

    /**
     * @OA\Patch(
     *     path="/company/purchase/{id}/receive",
     *     summary="Receive a purchase",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Purchase ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Purchase received",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 ref="#/components/schemas/Purchase"
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Purchase not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function receive(
        string $id,
        ReceivePurchaseUseCase $useCase,
    ): JsonResponse {
        $purchase = $useCase->execute($id);
 
        return ApiResponse::success(
            data:    new PurchaseResource($purchase->load(['supplier', 'items'])),
            message: 'Purchase received successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/purchase/{id}",
     *     summary="Delete a purchase",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Purchase ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Purchase deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Purchase deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Purchase not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeletePurchaseUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);
 
        return ApiResponse::success(message: 'Purchase deleted successfully.');
    }
}