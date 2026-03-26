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
use Throwable;

class CompanyController
{
    /**
     * Display a listing of companies.
     *
     * Query params: page (int), limit (int), search (string - filters by name, cnpj, email).
     */
    public function index(Request $request, ShowAllCompaniesUseCase $useCase): JsonResponse
    {
        try {
            $limit  = $request->integer('limit', 25);
            $search = $request->filled('search') ? trim((string) $request->input('search')) : null;

            $companies  = $useCase->execute($limit, $search);
            $data       = $companies->toArray($request);
            $pagination = $companies->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified company.
     */
    public function show(string $id, ShowCompanyUseCase $useCase): JsonResponse
    {
        try {
            $company = $useCase->execute($id);

            if (! $company instanceof \App\Domain\Models\Company) {
                return ApiResponse::error(null, 'Company not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(new CompanyResource($company), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Company not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created company.
     */
    public function store(CompanyStoreRequest $request, CreateCompanyUseCase $useCase): JsonResponse
    {
        try {
            $company = $useCase->execute($request->validated());

            return ApiResponse::created(new CompanyResource($company));
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified company.
     */
    public function update(CompanyUpdateRequest $request, string $id, UpdateCompanyUseCase $useCase): JsonResponse
    {
        try {
            $company = $useCase->execute($id, $request->validated());

            return ApiResponse::success(new CompanyResource($company), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified company.
     */
    public function destroy(string $id, DeleteCompanyUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Company not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Company successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting company', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
