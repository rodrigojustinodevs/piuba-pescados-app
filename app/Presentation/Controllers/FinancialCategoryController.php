<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\FinancialCategoryDTO;
use App\Application\UseCases\FinancialCategory\CreateFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\DeleteFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\ListFinancialCategoriesUseCase;
use App\Application\UseCases\FinancialCategory\ShowFinancialCategoryUseCase;
use App\Application\UseCases\FinancialCategory\UpdateFinancialCategoryUseCase;
use App\Presentation\Requests\FinancialCategory\FinancialCategoryStoreRequest;
use App\Presentation\Requests\FinancialCategory\FinancialCategoryUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class FinancialCategoryController
{
    /**
     * Display a listing of financial categories.
     */
    public function index(ListFinancialCategoriesUseCase $useCase): JsonResponse
    {
        try {
            $categories = $useCase->execute();
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
    public function show(string $id, ShowFinancialCategoryUseCase $useCase): JsonResponse
    {
        try {
            $category = $useCase->execute($id);

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
    public function store(FinancialCategoryStoreRequest $request, CreateFinancialCategoryUseCase $useCase): JsonResponse
    {
        try {
            $category = $useCase->execute($request->validated());

            return ApiResponse::created($category->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified financial category.
     */
    public function update(
        FinancialCategoryUpdateRequest $request,
        string $id,
        UpdateFinancialCategoryUseCase $useCase
    ): JsonResponse {
        try {
            $category = $useCase->execute($id, $request->validated());

            return ApiResponse::success($category->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified financial category.
     */
    public function destroy(string $id, DeleteFinancialCategoryUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

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
