<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\SupplierDTO;
use App\Application\UseCases\Supplier\CreateSupplierUseCase;
use App\Application\UseCases\Supplier\DeleteSupplierUseCase;
use App\Application\UseCases\Supplier\ListSuppliersUseCase;
use App\Application\UseCases\Supplier\ShowSupplierUseCase;
use App\Application\UseCases\Supplier\UpdateSupplierUseCase;
use App\Presentation\Requests\Supplier\SupplierStoreRequest;
use App\Presentation\Requests\Supplier\SupplierUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

/**
 * @OA\Schema(
 *     schema="Supplier",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="Fornecedor Nordeste"),
 *     @OA\Property(property="contact", type="string", example="Joao Silva"),
 *     @OA\Property(property="phone", type="string", example="(85) 99999-0000"),
 *     @OA\Property(property="email", type="string", format="email", example="contato@fornecedor.com"),
 *     @OA\Property(
 *         property="company",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class SupplierController
{
    /**
     * @OA\Get(
     *     path="/company/suppliers",
     *     summary="List suppliers",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of suppliers",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Supplier")
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
    public function index(ListSuppliersUseCase $useCase): JsonResponse
    {
        try {
            $suppliers  = $useCase->execute();
            $data       = $suppliers->toArray(request());
            $pagination = $suppliers->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/supplier/{id}",
     *     summary="Get supplier by ID",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Supplier")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Supplier not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowSupplierUseCase $useCase): JsonResponse
    {
        try {
            $supplier = $useCase->execute($id);

            if (! $supplier instanceof SupplierDTO || $supplier->isEmpty()) {
                return ApiResponse::error(null, 'Supplier not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($supplier->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Supplier not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/company/supplier",
     *     summary="Create supplier",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company_id","name","contact","phone","email"},
     *             @OA\Property(property="company_id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="contact", type="string", maxLength=255),
     *             @OA\Property(property="phone", type="string", maxLength=20),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Supplier created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/Supplier")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(SupplierStoreRequest $request, CreateSupplierUseCase $useCase): JsonResponse
    {
        try {
            $supplier = $useCase->execute($request->validated());

            return ApiResponse::created($supplier->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/supplier/{id}",
     *     summary="Update supplier",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="company_id", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="contact", type="string", maxLength=255),
     *             @OA\Property(property="phone", type="string", maxLength=20),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Supplier")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Supplier not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(SupplierUpdateRequest $request, string $id, UpdateSupplierUseCase $useCase): JsonResponse
    {
        try {
            $supplier = $useCase->execute($id, $request->validated());

            return ApiResponse::success($supplier->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/supplier/{id}",
     *     summary="Delete supplier",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Supplier deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Supplier successfully deleted"),
     *             @OA\Property(property="response", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Supplier not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteSupplierUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Supplier not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Supplier successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting supplier', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
