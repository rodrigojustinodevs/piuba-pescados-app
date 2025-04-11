<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\FinancialCategoryDTO;
use App\Application\Services\FinancialCategoryService;
use App\Presentation\Requests\FinancialCategory\FinancialCategoryStoreRequest;
use App\Presentation\Requests\FinancialCategory\FinancialCategoryUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class FinancialCategoryController
{
    public function __construct(
        protected FinancialCategoryService $financialCategoryService
    ) {
    }

    /**
     * Display a listing of financial categories.
     */
    public function index(): JsonResponse
    {
        try {
            $categories = $this->financialCategoryService->showAllFinancialCategories();
            $data       = $categories->toArray(request());
            $pagination = $categories->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified financial category.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $category = $this->financialCategoryService->showFinancialCategory($id);

            if (! $category instanceof FinancialCategoryDTO || $category->isEmpty()) {
                return ApiResponse::error(
                    null,
                    'Financial category not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            return ApiResponse::success($category->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                'Financial category not found',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Store a newly created financial category.
     */
    public function store(FinancialCategoryStoreRequest $request): JsonResponse
    {
        try {
            $category = $this->financialCategoryService->create($request->validated());

            return ApiResponse::created($category->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified financial category.
     */
    public function update(FinancialCategoryUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $category = $this->financialCategoryService->updateFinancialCategory($id, $request->validated());

            return ApiResponse::success($category->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified financial category.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->financialCategoryService->deleteFinancialCategory($id);

            if (! $deleted) {
                return ApiResponse::error(
                    null,
                    'Financial category not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            return ApiResponse::success(
                null,
                Response::HTTP_OK,
                'Financial category successfully deleted'
            );
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                'Error deleting financial category',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
