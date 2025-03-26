<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\CompanyService;
use App\Presentation\Requests\Company\CompanyStoreRequest;
use App\Presentation\Requests\Company\CompanyUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class CompanyController
{
    public function __construct(
        protected CompanyService $companyService
    ) {
    }

    /**
     * Display a listing of companies.
     */
    public function index(): JsonResponse
    {
        try {
            $companies  = $this->companyService->showAllCompanies();
            $data       = $companies->toArray(request());
            $pagination = $companies->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified company.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $company = $this->companyService->showCompany($id);

            if (! $company instanceof \App\Application\DTOs\CompanyDTO || $company->isEmpty()) {
                return ApiResponse::error(null, 'Company not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($company->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Company not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created company.
     */
    public function store(CompanyStoreRequest $request): JsonResponse
    {
        try {
            $company = $this->companyService->create($request->validated());

            return ApiResponse::created($company->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified company.
     */
    public function update(CompanyUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $company = $this->companyService->updateCompany($id, $request->validated());

            return ApiResponse::success($company->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified company.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->companyService->deleteCompany($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Company not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Company successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting company', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
