<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\FeedInventoryDTO;
use App\Application\UseCases\FeedInventory\CreateFeedInventoryUseCase;
use App\Application\UseCases\FeedInventory\DeleteFeedInventoryUseCase;
use App\Application\UseCases\FeedInventory\ListFeedInventoriesUseCase;
use App\Application\UseCases\FeedInventory\ShowFeedInventoryUseCase;
use App\Application\UseCases\FeedInventory\UpdateFeedInventoryUseCase;
use App\Presentation\Requests\FeedInventory\FeedInventoryStoreRequest;
use App\Presentation\Requests\FeedInventory\FeedInventoryUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class FeedInventoryController
{
    /**
     * Display a listing of feed inventories.
     */
    public function index(ListFeedInventoriesUseCase $useCase): JsonResponse
    {
        try {
            $feedInventories = $useCase->execute();
            $data            = $feedInventories->toArray(request());
            $pagination      = $feedInventories->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified feed inventory.
     */
    public function show(string $id, ShowFeedInventoryUseCase $useCase): JsonResponse
    {
        try {
            $feedInventory = $useCase->execute($id);

            if (! $feedInventory instanceof FeedInventoryDTO || $feedInventory->isEmpty()) {
                return ApiResponse::error(null, 'FeedInventory not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($feedInventory->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'FeedInventory not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created feed inventory.
     */
    public function store(FeedInventoryStoreRequest $request, CreateFeedInventoryUseCase $useCase): JsonResponse
    {
        try {
            $feedInventory = $useCase->execute($request->validated());

            return ApiResponse::created($feedInventory->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified feed inventory.
     */
    public function update(
        FeedInventoryUpdateRequest $request,
        string $id,
        UpdateFeedInventoryUseCase $useCase
    ): JsonResponse {
        try {
            $feedInventory = $useCase->execute($id, $request->validated());

            return ApiResponse::success($feedInventory->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified feed inventory.
     */
    public function destroy(string $id, DeleteFeedInventoryUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'FeedInventory not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(
                null,
                Response::HTTP_OK,
                'FeedInventory successfully deleted'
            );
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                'Error deleting feed inventory',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
