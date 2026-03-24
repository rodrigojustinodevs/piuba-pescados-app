<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\FinancialCategory\ActivateFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\CreateFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\DeactivateFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\DeleteFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\ListFinancialCategoriesUseCase;
use App\Application\UseCases\FinancialCategory\ShowFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\UpdateFinancialCategoryUseCase;
use App\Presentation\Requests\FinancialCategory\FinancialCategoryStoreRequest;
use App\Presentation\Requests\FinancialCategory\FinancialCategoryUpdateRequest;
use App\Presentation\Resources\FinancialCategory\FinancialCategoryResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="FinancialCategory",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="Racao"),
 *     @OA\Property(property="type", type="string", enum={"revenue","expense","investment"}),
 *     @OA\Property(property="typeLabel", type="string", example="Expense"),
 *     @OA\Property(property="status", type="string", enum={"active","inactive"}),
 *     @OA\Property(property="statusLabel", type="string", example="Ativa"),
 *     @OA\Property(
 *         property="company",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="id", type="string", format="uuid"),
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class FinancialCategoryController
{
    /**
     * @OA\Get(
     *     path="/company/financial-categories",
     *     summary="List financial categories",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="type", in="query", @OA\Schema(type="string", enum={"revenue","expense","investment"})),
     *     @OA\Parameter(name="status", in="query", @OA\Schema(type="string", enum={"active","inactive"})),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of financial categories",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/FinancialCategory")
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
        ListFinancialCategoriesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['type', 'status', 'per_page', 'page']),
        );

        return ApiResponse::success(
            data:       FinancialCategoryResource::collection($paginator->items()),
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
     *     path="/company/financial-category/{id}",
     *     summary="Get financial category by ID",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Financial category found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/FinancialCategory")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Financial category not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(
        string $id,
        ShowFinancialCategoryUseCase $useCase,
    ): JsonResponse {
        $category = $useCase->execute($id);

        return ApiResponse::success(
            data: new FinancialCategoryResource($category),
        );
    }

    /**
     * @OA\Post(
     *     path="/company/financial-category",
     *     summary="Create financial category",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","type"},
     *             @OA\Property(property="company_id", type="string", format="uuid", nullable=true),
     *             @OA\Property(property="name", type="string", maxLength=100),
     *             @OA\Property(property="type", type="string", enum={"revenue","expense","investment"}),
     *             @OA\Property(property="status", type="string", enum={"active","inactive"}, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Financial category created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/FinancialCategory")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(
        FinancialCategoryStoreRequest $request,
        CreateFinancialCategoryUseCase $useCase,
    ): JsonResponse {
        $category = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new FinancialCategoryResource($category),
            message: 'Category created successfully.',
        );
    }

    /**
     * @OA\Put(
     *     path="/company/financial-category/{id}",
     *     summary="Update financial category",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=100),
     *             @OA\Property(property="type", type="string", enum={"revenue","expense","investment"}),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Financial category updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/FinancialCategory")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Financial category not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        FinancialCategoryUpdateRequest $request,
        string $id,
        UpdateFinancialCategoryUseCase $useCase,
    ): JsonResponse {
        $category = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new FinancialCategoryResource($category),
            message: 'Category updated successfully.',
        );
    }

    /**
     * Hard delete — only succeeds when no transactions are linked.
     * The exception FinancialCategoryHasTransactionsException is handled by
     * the global exception Handler and returns a 422 domain error.
     *
     * @OA\Delete(
     *     path="/company/financial-category/{id}",
     *     summary="Delete financial category",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Financial category deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="response", nullable=true),
     *             @OA\Property(property="message", type="string", example="Category deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Financial category not found"),
     *     @OA\Response(response=422, description="Category has linked transactions")
     * )
     */
    public function destroy(
        string $id,
        DeleteFinancialCategoryUseCase $useCase,
    ): JsonResponse {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Category deleted successfully.');
    }

    /**
     * Inactivates a category that has linked transactions, preserving history.
     *
     * @OA\Patch(
     *     path="/company/financial-category/{id}/inactive",
     *     summary="Set financial category to inactive",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Financial category deactivated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category deactivated successfully."
     *             ),
     *             @OA\Property(property="response", ref="#/components/schemas/FinancialCategory")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Financial category not found")
     * )
     */
    public function inactive(
        string $id,
        DeactivateFinancialCategoryUseCase $useCase,
    ): JsonResponse {
        $category = $useCase->execute($id);

        return ApiResponse::success(
            data:    new FinancialCategoryResource($category),
            message: 'Category deactivated successfully.',
        );
    }

    /**
     * @OA\Patch(
     *     path="/company/financial-category/{id}/active",
     *     summary="Set financial category to active",
     *     tags={"Financial Categories"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Financial category activated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category activated successfully."
     *             ),
     *             @OA\Property(property="response", ref="#/components/schemas/FinancialCategory")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=404, description="Financial category not found")
     * )
     */
    public function active(
        string $id,
        ActivateFinancialCategoryUseCase $useCase,
    ): JsonResponse {
        $category = $useCase->execute($id);

        return ApiResponse::success(
            data:    new FinancialCategoryResource($category),
            message: 'Category activated successfully.',
        );
    }
}
