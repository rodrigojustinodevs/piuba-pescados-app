<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Company\CreateCompanyUseCase;
use App\Application\UseCases\Company\DeleteCompanyUseCase;
use App\Application\UseCases\Company\ShowAllCompaniesUseCase;
use App\Application\UseCases\Company\ShowCompanyUseCase;
use App\Application\UseCases\Company\UpdateCompanyUseCase;
use App\Presentation\Requests\Company\CompanyStoreRequest;
use App\Presentation\Requests\Company\CompanyUpdateRequest;
use App\Presentation\Resources\Company\CompanyResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * @OA\Tag(name="Companies", description="Empresas")
 * @OA\Schema(
 *     schema="Company",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="name", type="string", example="Aquacultura Piuba"),
 *     @OA\Property(property="cnpj", type="string", example="12.345.678/0001-90"),
 *     @OA\Property(property="email", type="string", format="email", nullable=true),
 *     @OA\Property(property="phone", type="string", example="(85) 99999-9999"),
 *     @OA\Property(
 *         property="address",
 *         type="object",
 *         @OA\Property(property="street", type="string", nullable=true),
 *         @OA\Property(property="number", type="string", nullable=true),
 *         @OA\Property(property="complement", type="string", nullable=true),
 *         @OA\Property(property="neighborhood", type="string", nullable=true),
 *         @OA\Property(property="city", type="string", nullable=true),
 *         @OA\Property(property="state", type="string", nullable=true),
 *         @OA\Property(property="zipCode", type="string", nullable=true)
 *     ),
 *     @OA\Property(property="active", type="boolean", example=true),
 *     @OA\Property(property="status", type="string", enum={"active","inactive"}),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
final class CompanyController
{
    /**
     * Display a listing of companies.
     *
     * Query params: page (int), limit (int), search (string - filters by name, cnpj, email).
     *
     * @OA\Get(
     *     path="/company/companies",
     *     summary="List companies",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="limit", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Parameter(name="search", in="query", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of companies",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Company")
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
    public function index(Request $request, ShowAllCompaniesUseCase $useCase): JsonResponse
    {
        $result     = $useCase->execute($request->all());
        $collection = CompanyResource::collection($result->items());

        return ApiResponse::success($collection, Response::HTTP_OK, 'Success', [
            'total'        => $result->total(),
            'current_page' => $result->currentPage(),
            'last_page'    => $result->lastPage(),
            'first_page'   => $result->firstPage(),
            'per_page'     => $result->perPage(),
        ]);
    }

    /**
     * Display the specified company.
     *
     * @OA\Get(
     *     path="/company/company/{id}",
     *     summary="Get company by ID",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Company found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Company")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Company not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowCompanyUseCase $useCase): JsonResponse
    {
        $company = $useCase->execute($id);

        return ApiResponse::success(new CompanyResource($company), Response::HTTP_OK, 'Success');
    }

    /**
     * Store a newly created company.
     *
     * @OA\Post(
     *     path="/company/company",
     *     summary="Create company",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","cnpj","phone"},
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="cnpj", type="string", maxLength=18),
     *             @OA\Property(property="email", type="string", format="email", nullable=true),
     *             @OA\Property(property="phone", type="string", maxLength=20),
     *             @OA\Property(property="addressStreet", type="string", nullable=true),
     *             @OA\Property(property="addressNumber", type="string", nullable=true),
     *             @OA\Property(property="addressComplement", type="string", nullable=true),
     *             @OA\Property(property="addressNeighborhood", type="string", nullable=true),
     *             @OA\Property(property="addressCity", type="string", nullable=true),
     *             @OA\Property(property="addressState", type="string", nullable=true),
     *             @OA\Property(property="addressZipCode", type="string", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"active","inactive"}, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Company created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/Company")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(CompanyStoreRequest $request, CreateCompanyUseCase $useCase): JsonResponse
    {
        $company = $useCase->execute($request->validated());

        return ApiResponse::created(new CompanyResource($company));
    }

    /**
     * Update the specified company.
     *
     * @OA\Put(
     *     path="/company/company/{id}",
     *     summary="Update company",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="cnpj", type="string", maxLength=18),
     *             @OA\Property(property="email", type="string", format="email", nullable=true),
     *             @OA\Property(property="phone", type="string", maxLength=20),
     *             @OA\Property(property="addressStreet", type="string", nullable=true),
     *             @OA\Property(property="addressNumber", type="string", nullable=true),
     *             @OA\Property(property="addressComplement", type="string", nullable=true),
     *             @OA\Property(property="addressNeighborhood", type="string", nullable=true),
     *             @OA\Property(property="addressCity", type="string", nullable=true),
     *             @OA\Property(property="addressState", type="string", nullable=true),
     *             @OA\Property(property="addressZipCode", type="string", nullable=true),
     *             @OA\Property(property="status", type="string", enum={"active","inactive"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Company updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Company")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Company not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(CompanyUpdateRequest $request, string $id, UpdateCompanyUseCase $useCase): JsonResponse
    {
        $company = $useCase->execute($id, $request->validated());

        return ApiResponse::success(new CompanyResource($company), Response::HTTP_OK, 'Success');
    }

    /**
     * Remove the specified company.
     *
     * @OA\Delete(
     *     path="/company/company/{id}",
     *     summary="Delete company",
     *     tags={"Companies"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Company deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Company successfully deleted"),
     *             @OA\Property(property="response", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Company not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteCompanyUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(null, Response::HTTP_OK, 'Company successfully deleted');
    }
}
