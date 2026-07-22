<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\SalePaymentDTO;
use App\Application\UseCases\Sale\CancelSaleUseCase;
use App\Application\UseCases\Sale\CreateSalePaymentUseCase;
use App\Application\UseCases\Sale\DeleteSaleUseCase;
use App\Application\UseCases\Sale\DeliverSaleUseCase;
use App\Application\UseCases\Sale\ListSalePaymentsUseCase;
use App\Application\UseCases\Sale\ListSalesUseCase;
use App\Application\UseCases\Sale\PaySaleUseCase;
use App\Application\UseCases\Sale\ProcessHarvestSaleUseCase;
use App\Application\UseCases\Sale\ShowSaleUseCase;
use App\Application\UseCases\Sale\UpdateSaleUseCase;
use App\Domain\Repositories\SaleRepositoryInterface;
use App\Presentation\Requests\Sale\SalePaymentStoreRequest;
use App\Presentation\Requests\Sale\SaleStoreRequest;
use App\Presentation\Requests\Sale\SaleUpdateRequest;
use App\Presentation\Resources\Sale\SalePaymentResource;
use App\Presentation\Resources\Sale\SaleResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(name="Sales", description="Vendas e despescas")
 *
 * @OA\Schema(
 *     schema="SaleItem",
 *     type="object",
 *     description="Item individual da venda (produto/lote/stocking)",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="batchId", type="string", format="uuid", description="Lote de origem"),
 *     @OA\Property(property="stockingId", type="string", format="uuid", description="Povoamento de origem"),
 *     @OA\Property(property="productName", type="string", nullable=true, example="Tilápia G"),
 *     @OA\Property(property="species", type="string", nullable=true, example="Tilápia"),
 *     @OA\Property(
 *         property="category", type="string", nullable=true, example="G",
 *         description="Categoria/tamanho do produto"
 *     ),
 *     @OA\Property(
 *         property="totalWeight", type="number", format="float", example=500.5,
 *         description="Peso vendido neste item (kg)"
 *     ),
 *     @OA\Property(property="pricePerKg", type="number", format="float", example=12.50),
 *     @OA\Property(
 *         property="subtotal", type="number", format="float", example=6256.25,
 *         description="totalWeight × pricePerKg"
 *     ),
 *     @OA\Property(
 *         property="unitCost", type="number", format="float", example=8.40,
 *         description="CMV por kg — snapshot calculado no momento da venda"
 *     ),
 *     @OA\Property(
 *         property="totalCost", type="number", format="float", example=4202.10,
 *         description="CMV total do item"
 *     ),
 *     @OA\Property(
 *         property="isTotalHarvest", type="boolean", example=false,
 *         description="Se true, fechou o stocking ao vender"
 *     ),
 *     @OA\Property(property="notes", type="string", nullable=true),
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
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="Sale",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(
 *         property="code", type="string", nullable=true, example="VND-2026-0001",
 *         description="Código sequencial da venda"
 *     ),
 *     @OA\Property(property="clientId", type="string", format="uuid"),
 *     @OA\Property(property="financialCategoryId", type="string", format="uuid", nullable=true),
 *     @OA\Property(property="invoiceNumber", type="string", nullable=true, example="NF-001"),
 *     @OA\Property(property="needsInvoice", type="boolean", example=false),
 *     @OA\Property(
 *         property="totalRevenue", type="number", format="float", example=6256.25,
 *         description="Soma dos subtotais dos itens"
 *     ),
 *     @OA\Property(property="discount", type="number", format="float", example=0),
 *     @OA\Property(property="shipping", type="number", format="float", example=0),
 *     @OA\Property(property="taxes", type="number", format="float", example=0),
 *     @OA\Property(property="saleDate", type="string", format="date", example="2026-06-25"),
 *     @OA\Property(property="dueDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="paidDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="deliveredAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(
 *         property="paymentMethod",
 *         type="string",
 *         nullable=true,
 *         enum={"pix","bank_slip","bank_transfer","credit_card","debit_card","cash","check"}
 *     ),
 *     @OA\Property(property="paymentMethodLabel", type="string", nullable=true, example="PIX"),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"pending","confirmed","paid","delivered","overdue","cancelled"},
 *         example="pending"
 *     ),
 *     @OA\Property(property="statusLabel", type="string", example="Pendente"),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="responsibleUserId", type="string", format="uuid", nullable=true),
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
 *         property="stocking",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="quantity", type="integer"),
 *         @OA\Property(property="averageWeight", type="number", format="float")
 *     ),
 *     @OA\Property(
 *         property="responsibleUser",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         description="Itens da venda (produtos/lotes). Atualmente 1 item por venda.",
 *         @OA\Items(ref="#/components/schemas/SaleItem")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="SalePayment",
 *     type="object",
 *     description="Pagamento parcial ou total de uma venda",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="saleId", type="string", format="uuid"),
 *     @OA\Property(property="amount", type="number", format="float", example=1000.00),
 *     @OA\Property(
 *         property="paymentMethod",
 *         type="string",
 *         enum={"pix","bank_slip","bank_transfer","credit_card","debit_card","cash","check"}
 *     ),
 *     @OA\Property(property="paymentMethodLabel", type="string", example="PIX"),
 *     @OA\Property(property="paymentDate", type="string", format="date", example="2026-06-25"),
 *     @OA\Property(property="reference", type="string", nullable=true, example="TXN-12345"),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true)
 * )
 */
class SaleController
{
    /**
     * @OA\Get(
     *     path="/company/sales",
     *     summary="Listar vendas",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="client_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="batch_id", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", enum={"pending","confirmed","paid","delivered","overdue","cancelled"})
     *     ),
     *     @OA\Parameter(name="date_from", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="date_to", in="query", required=false, @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de vendas",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Sale")),
     *             @OA\Property(property="pagination", type="object",
     *                 @OA\Property(property="total", type="integer"),
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autenticado")
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
     *     summary="Buscar venda por ID",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="UUID da venda",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Venda encontrada",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Sale"))
     *     ),
     *     @OA\Response(response=404, description="Venda não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
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
     *     summary="Criar venda / despesca",
     *     description="Registra uma venda vinculada a itens de biomassa (stocking). Limitado a 1 item.",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"clientId","saleDate","items"},
     *             @OA\Property(property="clientId", type="string", format="uuid"),
     *             @OA\Property(property="saleDate", type="string", format="date", example="2026-06-25"),
     *             @OA\Property(property="financialCategoryId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="responsibleUserId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="dueDate", type="string", format="date", nullable=true),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending","confirmed"},
     *                 nullable=true,
     *                 description="Padrão: pending"
     *             ),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="needsInvoice", type="boolean", nullable=true),
     *             @OA\Property(property="discount", type="number", format="float", nullable=true),
     *             @OA\Property(property="shipping", type="number", format="float", nullable=true),
     *             @OA\Property(property="taxes", type="number", format="float", nullable=true),
     *             @OA\Property(
     *                 property="paymentMethod",
     *                 type="string",
     *                 nullable=true,
     *                 enum={"pix","bank_slip","bank_transfer","credit_card","debit_card","cash","check"}
     *             ),
     *             @OA\Property(property="invoiceNumber", type="string", nullable=true),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 minItems=1,
     *                 maxItems=1,
     *                 description="Itens da venda. Atualmente máximo de 1 item.",
     *                 @OA\Items(
     *                     required={"batchId","stockingId","totalWeight","pricePerKg"},
     *                     @OA\Property(property="batchId", type="string", format="uuid"),
     *                     @OA\Property(property="stockingId", type="string", format="uuid"),
     *                     @OA\Property(
     *                         property="totalWeight", type="number", format="float", minimum=0.001, example=500.5
     *                     ),
     *                     @OA\Property(
     *                         property="pricePerKg", type="number", format="float", minimum=0, example=12.50
     *                     ),
     *                     @OA\Property(
     *                         property="isTotalHarvest", type="boolean", nullable=true,
     *                         description="Se true, fecha o stocking e o lote ao vender toda a biomassa"
     *                     ),
     *                     @OA\Property(property="category", type="string", nullable=true, example="G"),
     *                     @OA\Property(property="notes", type="string", nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Venda criada com sucesso",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Sale"))
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=409, description="Biomassa insuficiente ou stocking fechado"),
     *     @OA\Response(response=401, description="Não autenticado"),
     *     @OA\Response(response=404, description="Entidades relacionadas não encontradas")
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
     * Todos os campos são opcionais (PATCH semântico em endpoint PUT).
     * Campos imutáveis: client_id, batch_id, stocking_id.
     *
     * @OA\Put(
     *     path="/company/sale/{id}",
     *     summary="Atualizar venda",
     *     description="Atualiza campos editáveis. Imutáveis são ignorados. Sincroniza sale_items[0] e receber.",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="UUID da venda",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="totalWeight", type="number", format="float", minimum=0.001),
     *             @OA\Property(property="pricePerKg", type="number", format="float", minimum=0),
     *             @OA\Property(property="saleDate", type="string", format="date"),
     *             @OA\Property(property="dueDate", type="string", format="date", nullable=true),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending","confirmed","paid","delivered","overdue","cancelled"}
     *             ),
     *             @OA\Property(property="isTotalHarvest", type="boolean"),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(property="discount", type="number", format="float"),
     *             @OA\Property(property="shipping", type="number", format="float"),
     *             @OA\Property(property="taxes", type="number", format="float"),
     *             @OA\Property(
     *                 property="paymentMethod",
     *                 type="string",
     *                 nullable=true,
     *                 enum={"pix","bank_slip","bank_transfer","credit_card","debit_card","cash","check"}
     *             ),
     *             @OA\Property(property="invoiceNumber", type="string", nullable=true),
     *             @OA\Property(property="responsibleUserId", type="string", format="uuid", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Venda atualizada",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Sale"))
     *     ),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=409, description="Biomassa insuficiente"),
     *     @OA\Response(response=404, description="Venda não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
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
     *     summary="Excluir venda",
     *     description="Soft delete. A venda precisa estar com status CANCELLED antes de ser excluída.",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="UUID da venda",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Venda excluída"),
     *     @OA\Response(response=409, description="Venda não está cancelada"),
     *     @OA\Response(response=404, description="Venda não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function destroy(
        string $id,
        DeleteSaleUseCase $useCase,
        SaleRepositoryInterface $saleRepository,
    ): JsonResponse {
        Gate::authorize('delete', $saleRepository->findOrFail($id));

        $useCase->execute($id);

        return ApiResponse::success(message: 'Sale deleted successfully.');
    }

    /**
     * @OA\Patch(
     *     path="/company/sale/{id}/cancel",
     *     summary="Cancelar venda",
     *     description="Cancela a venda e estorna movimentações financeiras vinculadas.",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(response=200, description="Venda cancelada"),
     *     @OA\Response(response=409, description="Venda não pode ser cancelada neste status"),
     *     @OA\Response(response=404, description="Venda não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function cancel(
        string $id,
        CancelSaleUseCase $useCase,
        SaleRepositoryInterface $saleRepository,
    ): JsonResponse {
        Gate::authorize('cancel', $saleRepository->findOrFail($id));

        $useCase->execute($id);

        return ApiResponse::success(message: 'Sale cancelled successfully.');
    }

    /**
     * @OA\Patch(
     *     path="/company/sale/{id}/pay",
     *     summary="Marcar venda como paga",
     *     description="Transição direta para status PAID. Allowed from: pending, confirmed, overdue.",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="paid_date", type="string", format="date", nullable=true,
     *                 description="Data do pagamento. Padrão: hoje"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Venda marcada como paga",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Sale"))
     *     ),
     *     @OA\Response(response=409, description="Transição de status inválida"),
     *     @OA\Response(response=404, description="Venda não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function pay(
        Request $request,
        string $id,
        PaySaleUseCase $useCase,
    ): JsonResponse {
        $sale = $useCase->execute($id, $request->input('paid_date'));

        return ApiResponse::success(
            data:    new SaleResource($sale),
            message: 'Sale marked as paid.',
        );
    }

    /**
     * @OA\Patch(
     *     path="/company/sale/{id}/deliver",
     *     summary="Marcar venda como entregue",
     *     description="Transição para status DELIVERED. Allowed from: confirmed, paid.",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Venda marcada como entregue",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/Sale"))
     *     ),
     *     @OA\Response(response=409, description="Transição de status inválida"),
     *     @OA\Response(response=404, description="Venda não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function deliver(
        string $id,
        DeliverSaleUseCase $useCase,
    ): JsonResponse {
        $sale = $useCase->execute($id);

        return ApiResponse::success(
            data:    new SaleResource($sale),
            message: 'Sale marked as delivered.',
        );
    }

    /**
     * @OA\Get(
     *     path="/company/sale/{id}/payments",
     *     summary="Listar pagamentos da venda",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="UUID da venda",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pagamentos",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SalePayment"))
     *         )
     *     ),
     *     @OA\Response(response=404, description="Venda não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function payments(
        string $id,
        ListSalePaymentsUseCase $useCase,
    ): JsonResponse {
        $payments = $useCase->execute($id);

        return ApiResponse::success(
            data: SalePaymentResource::collection($payments),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/sale/{id}/payments",
     *     summary="Registrar pagamento parcial ou total",
     *     description="Registra um pagamento contra a venda. Ao atingir total_revenue, a venda é marcada como PAID.",
     *     tags={"Sales"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="UUID da venda",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount","paymentMethod","paymentDate"},
     *             @OA\Property(property="amount", type="number", format="float", minimum=0.01, example=1500.00),
     *             @OA\Property(
     *                 property="paymentMethod",
     *                 type="string",
     *                 enum={"pix","bank_slip","bank_transfer","credit_card","debit_card","cash","check"}
     *             ),
     *             @OA\Property(property="paymentDate", type="string", format="date", example="2026-06-25"),
     *             @OA\Property(property="reference", type="string", nullable=true, example="TXN-98765"),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pagamento registrado",
     *         @OA\JsonContent(@OA\Property(property="data", ref="#/components/schemas/SalePayment"))
     *     ),
     *     @OA\Response(response=409, description="Venda já está paga ou cancelada"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=404, description="Venda não encontrada"),
     *     @OA\Response(response=401, description="Não autenticado")
     * )
     */
    public function storePayment(
        SalePaymentStoreRequest $request,
        string $id,
        CreateSalePaymentUseCase $useCase,
    ): JsonResponse {
        $dto     = SalePaymentDTO::fromArray($request->validated());
        $payment = $useCase->execute($id, $dto);

        return ApiResponse::created(
            data:    new SalePaymentResource($payment),
            message: 'Payment registered successfully.',
        );
    }
}
