<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\ReceivePurchaseDTO;
use App\Application\DTOs\RegisterPurchasePaymentDTO;
use App\Application\UseCases\Purchase\CancelPurchaseUseCase;
use App\Application\UseCases\Purchase\CreatePurchaseUseCase;
use App\Application\UseCases\Purchase\DeletePurchaseUseCase;
use App\Application\UseCases\Purchase\GetPurchasePaymentsUseCase;
use App\Application\UseCases\Purchase\ListPurchasesUseCase;
use App\Application\UseCases\Purchase\ReceivePurchaseUseCase;
use App\Application\UseCases\Purchase\RegisterPurchasePaymentUseCase;
use App\Application\UseCases\Purchase\ShowPurchaseUseCase;
use App\Application\UseCases\Purchase\UpdatePurchaseUseCase;
use App\Presentation\Requests\Purchase\PurchaseStoreRequest;
use App\Presentation\Requests\Purchase\PurchaseUpdateRequest;
use App\Presentation\Requests\Purchase\ReceivePurchaseRequest;
use App\Presentation\Requests\Purchase\RegisterPurchasePaymentRequest;
use App\Presentation\Resources\Purchase\PurchasePaymentHistoryResource;
use App\Presentation\Resources\Purchase\PurchaseResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Purchases", description="Compras")
 *
 * @OA\Schema(
 *     schema="PurchaseItem",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="supplyId", type="string", format="uuid"),
 *     @OA\Property(property="supplyName", type="string", nullable=true),
 *     @OA\Property(property="quantity", type="number", format="float"),
 *     @OA\Property(property="unit", type="string", example="kg"),
 *     @OA\Property(property="unitPrice", type="number", format="float"),
 *     @OA\Property(property="totalPrice", type="number", format="float")
 * )
 *
 * @OA\Schema(
 *     schema="Purchase",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="code", type="string", example="PC-2026-0001"),
 *     @OA\Property(property="companyId", type="string", format="uuid"),
 *     @OA\Property(property="supplierId", type="string", format="uuid", nullable=true),
 *     @OA\Property(property="invoiceNumber", type="string", nullable=true),
 *     @OA\Property(property="totalPrice", type="number", format="float"),
 *     @OA\Property(property="freight", type="number", format="float"),
 *     @OA\Property(property="otherCosts", type="number", format="float"),
 *     @OA\Property(property="status", type="string", enum={"draft","submitted","approved","partially_received","received","cancelled"}),
 *     @OA\Property(property="statusLabel", type="string"),
 *     @OA\Property(property="paymentStatus", type="string", enum={"pending","partial","paid"}),
 *     @OA\Property(property="paymentStatusLabel", type="string"),
 *     @OA\Property(property="paymentMethod", type="string", nullable=true, enum={"bank_slip","pix","bank_transfer","credit_card","cash","net_terms"}),
 *     @OA\Property(property="paymentMethodLabel", type="string", nullable=true),
 *     @OA\Property(property="orderDate", type="string", format="date-time", example="2026-03-12T10:00:00.000Z"),
 *     @OA\Property(property="expectedDate", type="string", format="date", nullable=true, example="2026-03-20"),
 *     @OA\Property(property="receivedDate", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="responsible", type="string", nullable=true),
 *     @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/PurchaseItem")),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
final class PurchaseController
{
    /**
     * @OA\Get(
     *     path="/company/purchases",
     *     summary="Listar compras",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"draft","submitted","approved","partially_received","received","cancelled"})),
     *     @OA\Parameter(name="payment_status", in="query",
     *         @OA\Schema(type="string", enum={"pending","partial","paid"})),
     *     @OA\Parameter(name="payment_method", in="query", @OA\Schema(type="string", enum={"bank_slip","pix","bank_transfer","credit_card","cash","net_terms"})),
     *     @OA\Parameter(name="supplier_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="code", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="date_from", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de compras",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", type="array", @OA\Items(ref="#/components/schemas/Purchase")),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="first_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer")
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
        $filters = $request->only([
            'status', 'supplierId', 'code', 'dateFrom', 'dateTo', 'perPage', 'page',
        ]);

        if ($request->has('payment_status')) {
            $filters['paymentStatus'] = $request->input('payment_status');
        }

        if ($request->has('payment_method')) {
            $filters['paymentMethod'] = $request->input('payment_method');
        }

        if ($request->has('supplier_id')) {
            $filters['supplierId'] = $request->input('supplier_id');
        }

        if ($request->has('date_from')) {
            $filters['dateFrom'] = $request->input('date_from');
        }

        if ($request->has('date_to')) {
            $filters['dateTo'] = $request->input('date_to');
        }

        $paginator = $useCase->execute(filters: $filters);

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
     *     summary="Obter compra por ID",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Compra encontrada",
     *         @OA\JsonContent(@OA\Property(property="response", ref="#/components/schemas/Purchase"))),
     *     @OA\Response(response=404, description="Not found"),
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
     *     summary="Criar compra",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"supplierId","code","orderDate","paymentStatus","items"},
     *             @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="supplierId", type="string", format="uuid"),
     *             @OA\Property(property="code", type="string", maxLength=50, example="PC-2026-0001"),
     *             @OA\Property(property="invoiceNumber", type="string", nullable=true, maxLength=100),
     *             @OA\Property(property="orderDate", type="string", format="date-time",
     *                 example="2026-03-12T10:00:00.000Z"),
     *             @OA\Property(property="expectedDate", type="string", format="date",
     *                 nullable=true, example="2026-03-20"),
     *             @OA\Property(property="status", type="string", nullable=true, enum={"draft","submitted","approved","partially_received","received","cancelled"}),
     *             @OA\Property(property="paymentStatus", type="string", enum={"pending","partial","paid"}),
     *             @OA\Property(property="paymentMethod", type="string", nullable=true, enum={"bank_slip","pix","bank_transfer","credit_card","cash","net_terms"}),
     *             @OA\Property(property="freight", type="number", format="float", example=480),
     *             @OA\Property(property="otherCosts", type="number", format="float", example=0),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="responsible", type="string", nullable=true, example="Carla Mendes"),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 required={"supplyId","quantity","unitPrice"},
     *                 @OA\Property(property="supplyId", type="string", format="uuid"),
     *                 @OA\Property(property="quantity", type="number", format="float", minimum=0.000001),
     *                 @OA\Property(property="unit", type="string", nullable=true, example="kg"),
     *                 @OA\Property(property="unitPrice", type="number", format="float", minimum=0)
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=201, description="Compra criada",
     *         @OA\JsonContent(@OA\Property(property="response", ref="#/components/schemas/Purchase"))),
     *     @OA\Response(response=422, description="Validation error"),
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
     *     summary="Atualizar compra",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"supplierId","orderDate","items"},
     *             @OA\Property(property="supplierId", type="string", format="uuid"),
     *             @OA\Property(property="code", type="string", maxLength=50),
     *             @OA\Property(property="invoiceNumber", type="string", nullable=true),
     *             @OA\Property(property="orderDate", type="string", format="date-time"),
     *             @OA\Property(property="expectedDate", type="string", format="date", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"draft","submitted","approved"}),
     *             @OA\Property(property="paymentStatus", type="string", enum={"pending","partial","paid"}),
     *             @OA\Property(property="paymentMethod", type="string", nullable=true, enum={"bank_slip","pix","bank_transfer","credit_card","cash","net_terms"}),
     *             @OA\Property(property="freight", type="number", format="float"),
     *             @OA\Property(property="otherCosts", type="number", format="float"),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="responsible", type="string", nullable=true),
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 required={"supplyId","quantity","unit","unitPrice"},
     *                 @OA\Property(property="id", type="string", format="uuid", nullable=true),
     *                 @OA\Property(property="supplyId", type="string", format="uuid"),
     *                 @OA\Property(property="quantity", type="number", format="float"),
     *                 @OA\Property(property="unit", type="string"),
     *                 @OA\Property(property="unitPrice", type="number", format="float")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Atualizado",
     *         @OA\JsonContent(@OA\Property(property="response", ref="#/components/schemas/Purchase"))),
     *     @OA\Response(response=404, description="Not found"),
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
     *     summary="Registrar recebimento de itens da compra",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 required={"purchase_item_id","received_quantity"},
     *                 @OA\Property(property="purchase_item_id", type="string", format="uuid"),
     *                 @OA\Property(property="received_quantity", type="number",
     *                     format="float", minimum=0.0001, example=200)
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Recebimento registrado",
     *         @OA\JsonContent(@OA\Property(property="response", ref="#/components/schemas/Purchase"))),
     *     @OA\Response(response=422, description="Validação ou regra de negócio violada"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function receive(
        ReceivePurchaseRequest $request,
        string $id,
        ReceivePurchaseUseCase $useCase,
    ): JsonResponse {
        $dto      = ReceivePurchaseDTO::fromArray($id, $request->validated());
        $purchase = $useCase->execute($dto);

        return ApiResponse::success(
            data:    new PurchaseResource($purchase),
            message: 'Purchase received successfully.',
        );
    }

    /**
     * @OA\Patch(
     *     path="/company/purchase/{id}/cancel",
     *     summary="Cancelar compra",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Compra cancelada",
     *         @OA\JsonContent(@OA\Property(property="response", ref="#/components/schemas/Purchase"))),
     *     @OA\Response(response=400, description="Transição de status inválida"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function cancel(
        string $id,
        CancelPurchaseUseCase $useCase,
    ): JsonResponse {
        $purchase = $useCase->execute($id);

        return ApiResponse::success(
            data:    new PurchaseResource($purchase->load(['supplier', 'items'])),
            message: 'Purchase cancelled successfully.',
        );
    }

    /**
     * @OA\Get(
     *     path="/company/purchase/{id}/payments",
     *     summary="Histórico de pagamentos de uma compra",
     *     description="Retorna o resumo financeiro e o histórico de pagamentos ordenados por data decrescente.",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Histórico retornado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", type="object",
     *                 @OA\Property(property="purchase", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="code", type="string", example="PC-2026-0001"),
     *                     @OA\Property(property="totalAmount", type="number", format="float", example=150.00),
     *                     @OA\Property(property="totalPaid", type="number", format="float", example=130.00),
     *                     @OA\Property(property="balance", type="number", format="float", example=20.00),
     *                     @OA\Property(property="progress", type="number", format="float", example=86.67),
     *                     @OA\Property(property="paymentStatus", type="string", enum={"pending","partial","paid"})
     *                 ),
     *                 @OA\Property(property="payments", type="array", @OA\Items(
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="paymentDate", type="string", format="date-time"),
     *                     @OA\Property(property="amount", type="number", format="float"),
     *                     @OA\Property(property="paymentMethod", type="string"),
     *                     @OA\Property(property="reference", type="string", nullable=true),
     *                     @OA\Property(property="notes", type="string", nullable=true),
     *                     @OA\Property(property="createdAt", type="string", format="date-time")
     *                 ))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=404, description="Compra não encontrada"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function getPayments(
        string $id,
        GetPurchasePaymentsUseCase $useCase,
    ): JsonResponse {
        $purchase = $useCase->execute($id);

        return ApiResponse::success(
            data: new PurchasePaymentHistoryResource($purchase),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/purchase/{id}/payments",
     *     summary="Registrar pagamento de uma compra",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"payment_date","amount","payment_method"},
     *             @OA\Property(property="payment_date", type="string",
     *                 format="date-time", example="2026-06-12T14:30:00"),
     *             @OA\Property(property="amount", type="number", format="float", minimum=0.01, example=1500.00),
     *             @OA\Property(property="payment_method", type="string",
     *                 enum={"bank_slip","pix","bank_transfer","credit_card","cash","net_terms"}, example="pix"),
     *             @OA\Property(property="reference", type="string",
     *                 nullable=true, maxLength=255, example="PIX-845632"),
     *             @OA\Property(property="notes", type="string",
     *                 nullable=true, example="Pagamento da primeira parcela.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pagamento registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Payment registered successfully."),
     *             @OA\Property(property="response", ref="#/components/schemas/Purchase")
     *         )
     *     ),
     *     @OA\Response(response=422,
     *         description="Regra de negócio violada (compra cancelada, já paga, ou valor excede saldo)"),
     *     @OA\Response(response=404, description="Compra não encontrada"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function registerPayment(
        RegisterPurchasePaymentRequest $request,
        string $id,
        RegisterPurchasePaymentUseCase $useCase,
    ): JsonResponse {
        $dto      = RegisterPurchasePaymentDTO::fromArray($id, $request->validated());
        $purchase = $useCase->execute($dto);

        return ApiResponse::success(
            data:    new PurchaseResource($purchase),
            message: 'Payment registered successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/purchase/{id}",
     *     summary="Remover compra",
     *     tags={"Purchases"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Compra removida"),
     *     @OA\Response(response=404, description="Not found"),
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
