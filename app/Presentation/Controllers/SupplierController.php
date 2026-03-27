<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Supplier\CreateSupplierUseCase;
use App\Application\UseCases\Supplier\DeleteSupplierUseCase;
use App\Application\UseCases\Supplier\ListSuppliersUseCase;
use App\Application\UseCases\Supplier\ShowSupplierUseCase;
use App\Application\UseCases\Supplier\UpdateSupplierUseCase;
use App\Presentation\Requests\Supplier\SupplierStoreRequest;
use App\Presentation\Requests\Supplier\SupplierUpdateRequest;
use App\Presentation\Resources\Supplier\SupplierResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(name="Suppliers", description="Fornecedores")
 */
final class SupplierController
{
    /**
     * @OA\Get(
     *     path="/company/suppliers",
     *     summary="Listar fornecedores",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="company_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SupplierResource")
     *             ),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $request, ListSuppliersUseCase $useCase): JsonResponse
    {
        $result     = $useCase->execute($request->all());
        $collection = SupplierResource::collection($result->items());

        return ApiResponse::success($collection, Response::HTTP_OK, 'Success', [
            'total'        => $result->total(),
            'current_page' => $result->currentPage(),
            'last_page'    => $result->lastPage(),
            'first_page'   => $result->firstPage(),
            'per_page'     => $result->perPage(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/company/supplier/{id}",
     *     summary="Obter fornecedor por ID",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/SupplierResource")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowSupplierUseCase $useCase): JsonResponse
    {
        $supplier = $useCase->execute($id);

        return ApiResponse::success(new SupplierResource($supplier), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Post(
     *     path="/company/supplier",
     *     summary="Criar fornecedor",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"companyId","name","contact","phone","email"},
     *             @OA\Property(property="companyId", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="contact", type="string", maxLength=255),
     *             @OA\Property(property="phone", type="string", maxLength=20),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Criado"),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(SupplierStoreRequest $request, CreateSupplierUseCase $useCase): JsonResponse
    {
        $supplier = $useCase->execute($request->validated());

        return ApiResponse::created(new SupplierResource($supplier));
    }

    /**
     * @OA\Put(
     *     path="/company/supplier/{id}",
     *     summary="Atualizar fornecedor",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="companyId", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="contact", type="string", maxLength=255),
     *             @OA\Property(property="phone", type="string", maxLength=20),
     *             @OA\Property(property="email", type="string", format="email", maxLength=255)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Atualizado"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(SupplierUpdateRequest $request, string $id, UpdateSupplierUseCase $useCase): JsonResponse
    {
        $supplier = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new SupplierResource($supplier), Response::HTTP_OK, 'Success');
    }

    /**
     * @OA\Delete(
     *     path="/company/supplier/{id}",
     *     summary="Excluir fornecedor",
     *     tags={"Suppliers"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Excluído"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteSupplierUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(null, Response::HTTP_OK, 'Supplier successfully deleted');
    }
}
