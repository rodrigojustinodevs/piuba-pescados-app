<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\CostAllocation\CreateCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\DeleteCostAllocationUseCase;
use App\Application\UseCases\CostAllocation\ListCostAllocationsUseCase;
use App\Application\UseCases\CostAllocation\ShowCostAllocationUseCase;
use App\Presentation\Requests\CostAllocation\CostAllocationStoreRequest;
use App\Presentation\Resources\CostAllocation\CostAllocationResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Cost Allocations", description="Rateios de custo")
 * @OA\Schema(
 *     schema="CostAllocation",
 *     type="object",     @OA\Property(
 *         property="company",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(
 *         property="financialTransaction",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="description", type="string", nullable=true),
 *         @OA\Property(property="amount", type="number", format="float"),
 *         @OA\Property(property="dueDate", type="string", format="date"),
 *         @OA\Property(property="isAllocated", type="boolean")
 *     ),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="string", format="uuid"),
 *             @OA\Property(property="stockingId", type="string", format="uuid"),
 *             @OA\Property(property="percentage", type="number", format="float"),
 *             @OA\Property(property="amount", type="number", format="float")
 *         )
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true)
 * )
 */
final class CostAllocationController
{
    /**
     * @OA\Get(
     *     path="/company/cost-allocations",
     *     summary="List cost allocations",
     *     tags={"Cost Allocation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="financial_transaction_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(
     *         name="allocation_method",
     *         in="query",
     *         @OA\Schema(type="string", enum={"flat","biomass","volume"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of cost allocations",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/CostAllocation")
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
        ListCostAllocationsUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only([
                'financial_transaction_id', 'allocation_method', 'per_page', 'page',
            ]),
        );

        return ApiResponse::success(
            data:       CostAllocationResource::collection($paginator->items()),
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
     *     path="/company/cost-allocation/{id}",
     *     summary="Get cost allocation by ID",
     *     tags={"Cost Allocation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Cost allocation found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/CostAllocation")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Cost allocation not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowCostAllocationUseCase $useCase,
    ): JsonResponse {
        return ApiResponse::success(
            data: new CostAllocationResource($useCase->execute($id)),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/cost-allocation",
     *     summary="Create cost allocation",
     *     tags={"Cost Allocation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"financial_transaction_id","allocation_method","allocations"},
     *             @OA\Property(property="company_id", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="financial_transaction_id", type="string", format="uuid"),
     *             @OA\Property(property="allocation_method", type="string", enum={"flat","biomass","volume"}),
     *             @OA\Property(property="notes", type="string", nullable=true),
     *             @OA\Property(
     *                 property="allocations",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"stocking_id"},
     *                     @OA\Property(property="stocking_id", type="string", format="uuid")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Cost allocation created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Rateio de custo criado com sucesso."),
     *             @OA\Property(property="response", ref="#/components/schemas/CostAllocation")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=422, description="Business rule violation")
     * )
     */
    public function store(
        CostAllocationStoreRequest $request,
        CreateCostAllocationUseCase $useCase,
    ): JsonResponse {
        $allocation = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new CostAllocationResource($allocation),
            message: 'Rateio de custo criado com sucesso.',
        );
    }

    /**
     * Full reversal — deletes the allocation and undoes all side-effects.
     * Editing a partial allocation is intentionally NOT supported.
     *
     * @OA\Delete(
     *     path="/company/cost-allocation/{id}",
     *     summary="Reverse and delete cost allocation",
     *     tags={"Cost Allocation"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Cost allocation reversed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Rateio estornado com sucesso. A transação financeira foi liberada para novo rateio."
     *             ),
     *             @OA\Property(property="response", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Cost allocation not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(
        string $id,
        DeleteCostAllocationUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(
            message: 'Rateio estornado com sucesso. A transação financeira foi liberada para novo rateio.',
        );
    }
}
