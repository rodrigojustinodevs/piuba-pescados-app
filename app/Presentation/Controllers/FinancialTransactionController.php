<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\FinancialTransaction\CreateFinancialTransactionUseCase;
use App\Application\UseCases\FinancialTransaction\DeleteFinancialTransactionUseCase;
use App\Application\UseCases\FinancialTransaction\ListFinancialTransactionsUseCase;
use App\Application\UseCases\FinancialTransaction\ShowFinancialTransactionUseCase;
use App\Application\UseCases\FinancialTransaction\UpdateFinancialTransactionUseCase;
use App\Presentation\Requests\FinancialTransaction\FinancialTransactionStoreRequest;
use App\Presentation\Requests\FinancialTransaction\FinancialTransactionUpdateRequest;
use App\Presentation\Resources\FinancialTransaction\FinancialTransactionResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="FinancialTransaction",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="type", type="string", enum={"revenue","expense","investment"}),
 *     @OA\Property(property="typeLabel", type="string", example="Revenue"),
 *     @OA\Property(property="status", type="string", enum={"pending","paid","overdue","cancelled"}),
 *     @OA\Property(property="statusLabel", type="string", example="Pending"),
 *     @OA\Property(property="amount", type="number", format="float", example=1500.5),
 *     @OA\Property(property="dueDate", type="string", format="date", example="2026-03-25"),
 *     @OA\Property(property="paymentDate", type="string", format="date", nullable=true),
 *     @OA\Property(property="description", type="string", nullable=true),
 *     @OA\Property(property="notes", type="string", nullable=true),
 *     @OA\Property(
 *         property="referenceType",
 *         type="string",
 *         enum={"sale","purchase_item","cost_allocation"},
 *         nullable=true
 *     ),
 *     @OA\Property(property="referenceId", type="string", format="uuid", nullable=true),
 *     @OA\Property(
 *         property="company",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="category",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="type", type="string"),
 *         @OA\Property(property="typeLabel", type="string")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class FinancialTransactionController
{
    /**
     * @OA\Get(
     *     path="/company/financial-transactions",
     *     summary="List financial transactions",
     *     tags={"Financial Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(
 *         name="status",
 *         in="query",
 *         @OA\Schema(type="string", enum={"pending","paid","overdue","cancelled"})
 *     ),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"revenue","expense","investment"})),
     *     @OA\Parameter(name="financial_category_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(name="due_date_from", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="due_date_to", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of financial transactions",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/FinancialTransaction")
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
        ListFinancialTransactionsUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only([
                'status', 'type', 'financial_category_id',
                'due_date_from', 'due_date_to', 'per_page', 'page',
            ]),
        );

        return ApiResponse::success(
            data:       FinancialTransactionResource::collection($paginator->items()),
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
     *     path="/company/financial-transaction/{id}",
     *     summary="Get financial transaction by ID",
     *     tags={"Financial Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Financial transaction found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/FinancialTransaction")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowFinancialTransactionUseCase $useCase,
    ): JsonResponse {
        $transaction = $useCase->execute($id);

        return ApiResponse::success(
            data: new FinancialTransactionResource($transaction),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/financial-transaction",
     *     summary="Create financial transaction",
     *     description="Internal reference fields only; not accepted from the API.",
     *     tags={"Financial Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"financial_category_id","type","amount","due_date"},
     *             @OA\Property(property="company_id", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="financial_category_id", type="string", format="uuid"),
     *             @OA\Property(property="type", type="string", enum={"revenue","expense","investment"}),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"pending","paid","overdue","cancelled"},
     *                 nullable=true
     *             ),
     *             @OA\Property(property="amount", type="number", format="float", example=100.5),
     *             @OA\Property(property="due_date", type="string", format="date"),
     *             @OA\Property(property="payment_date", type="string", format="date", nullable=true),
     *             @OA\Property(property="description", type="string", maxLength=500, nullable=true),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Financial transaction successfully created."
     *             ),
     *             @OA\Property(property="response", ref="#/components/schemas/FinancialTransaction")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        FinancialTransactionStoreRequest $request,
        CreateFinancialTransactionUseCase $useCase,
    ): JsonResponse {
        $transaction = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new FinancialTransactionResource($transaction),
            message: 'Financial transaction successfully created.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/financial-transaction/{id}",
     *     summary="Update financial transaction",
     *     tags={"Financial Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="financial_category_id", type="string", format="uuid"),
     *             @OA\Property(property="type", type="string", enum={"revenue","expense","investment"}),
     *             @OA\Property(property="status", type="string", enum={"pending","paid","overdue","cancelled"}),
     *             @OA\Property(property="amount", type="number", format="float"),
     *             @OA\Property(property="due_date", type="string", format="date"),
     *             @OA\Property(property="payment_date", type="string", format="date", nullable=true),
     *             @OA\Property(property="description", type="string", maxLength=500, nullable=true),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Financial transaction updated successfully."
     *             ),
     *             @OA\Property(property="response", ref="#/components/schemas/FinancialTransaction")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        FinancialTransactionUpdateRequest $request,
        string $id,
        UpdateFinancialTransactionUseCase $useCase,
    ): JsonResponse {
        $transaction = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new FinancialTransactionResource($transaction),
            message: 'Financial transaction updated successfully.',
        );
    }

    /**
     * @OA\Delete(
     *     path="/company/financial-transaction/{id}",
     *     summary="Delete financial transaction",
     *     tags={"Financial Transactions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Financial transaction deleted successfully."
     *             ),
     *             @OA\Property(property="response", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteFinancialTransactionUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Financial transaction deleted successfully.');
    }
}
