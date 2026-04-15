<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\SalesQuotation\ConvertQuotationToOrderUseCase;
use App\Application\UseCases\SalesQuotation\CreateSalesQuotationUseCase;
use App\Application\UseCases\SalesQuotation\ListSalesQuotationsUseCase;
use App\Application\UseCases\SalesQuotation\ShowSalesQuotationUseCase;
use App\Application\UseCases\SalesQuotation\UpdateSalesQuotationUseCase;
use App\Presentation\Requests\SalesOrder\ConvertQuotationToOrderRequest;
use App\Presentation\Requests\SalesOrder\SalesQuotationStoreRequest;
use App\Presentation\Requests\SalesOrder\SalesQuotationUpdateRequest;
use App\Presentation\Resources\SalesOrder\SalesOrderResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Sales quotations", description="Orçamentos (cotações)")
 */
final class SalesQuotationController
{
    /**
     * @OA\Get(
     *     path="/company/sale-orders/quotations",
     *     summary="Lista orçamentos (cotações) da empresa",
     *     tags={"Sales quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="clientId", in="query", required=false, @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="perPage", in="query", required=false, @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Response(response=200, description="Lista paginada"),
     *     @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function index(Request $request, ListSalesQuotationsUseCase $useCase): JsonResponse
    {
        $paginator = $useCase->execute(
            $request->only([
                'clientId', 'status', 'type', 'perPage', 'page', 'companyId',
            ]),
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
     *     path="/company/sale-order/quotation/{id}",
     *     summary="Detalhe de um orçamento (cotação)",
     *     tags={"Sales quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Encontrado"),
     *     @OA\Response(response=404, description="Não encontrado"),
     *     @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function show(string $id, ShowSalesQuotationUseCase $useCase): JsonResponse
    {
        $order = $useCase->execute($id);

        return ApiResponse::success(SalesOrderResource::make($order));
    }

    /**
     * @OA\Post(
     *     path="/api/company/sale-order/quotation",
     *     summary="Cria um orçamento (cotação) com itens",
     *     tags={"Sales quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"clientId", "issueDate", "expirationDate", "items"},
     *             @OA\Property(property="companyId", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="clientId", type="string", format="uuid"),
     *             @OA\Property(property="issueDate", type="string", format="date"),
     *             @OA\Property(property="expirationDate", type="string", format="date"),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"stockingId", "quantity", "unitPrice"},
     *                     @OA\Property(property="stockingId", type="string", format="uuid"),
     *                     @OA\Property(property="quantity", type="number", example=100.5),
     *                     @OA\Property(property="unitPrice", type="number", example=18.9),
     *                     @OA\Property(property="measureUnit", type="string", enum={"kg","g","un"}, nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Orçamento criado"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function store(
        SalesQuotationStoreRequest $request,
        CreateSalesQuotationUseCase $useCase,
    ): JsonResponse {
        $order = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    SalesOrderResource::make($order),
            message: 'Quotation created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/api/company/sale-order/quotation/{id}",
     *     summary="Atualiza um orçamento",
     *     tags={"Sales quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="clientId",      type="string", format="uuid"),
     *             @OA\Property(property="issueDate",     type="string", format="date"),
     *             @OA\Property(property="expirationDate",type="string", format="date"),
     *             @OA\Property(property="notes",         type="string", nullable=true),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 description="Quando enviado, substitui TODOS os itens existentes.",
     *                 @OA\Items(
     *                     required={"stockingId","quantity","unitPrice"},
     *                     @OA\Property(property="stockingId", type="string", format="uuid"),
     *                     @OA\Property(property="quantity",   type="number"),
     *                     @OA\Property(property="unitPrice",  type="number"),
     *                     @OA\Property(property="measureUnit",type="string", enum={"kg","g","un"}, nullable=true)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Orçamento atualizado"),
     *     @OA\Response(response=404, description="Não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(
        SalesQuotationUpdateRequest $request,
        string $id,
        UpdateSalesQuotationUseCase $useCase,
    ): JsonResponse {
        $order = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    SalesOrderResource::make($order),
            message: 'Quotation updated successfully.',
        );
    }

    /**
     * @OA\Post(
     *     path="/api/company/sale-order/quotation/{id}/convert",
     *     summary="Converte um orçamento em pedido (vendas, baixa de biomassa, contas a receber)",
     *     tags={"Sales quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"expectedDeliveryDate","expectedPaymentDate","financialCategoryId","needsInvoice"},
     *             @OA\Property(property="expectedDeliveryDate", type="string", format="date"),
     *             @OA\Property(property="expectedPaymentDate", type="string", format="date"),
     *             @OA\Property(property="financialCategoryId", type="string", format="uuid"),
     *             @OA\Property(property="needsInvoice", type="boolean"),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Pedido gerado a partir do orçamento"),
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=401, description="Não autorizado")
     * )
     */
    public function convert(
        string $id,
        ConvertQuotationToOrderRequest $request,
        ConvertQuotationToOrderUseCase $useCase,
    ): JsonResponse {
        $order = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    SalesOrderResource::make($order),
            message: 'Quotation converted to order successfully.',
        );
    }
}
