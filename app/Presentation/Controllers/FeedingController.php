<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\FeedingDTO;
use App\Application\UseCases\Feeding\CreateFeedingUseCase;
use App\Application\UseCases\Feeding\DeleteFeedingUseCase;
use App\Application\UseCases\Feeding\ListFeedingsUseCase;
use App\Application\UseCases\Feeding\ShowFeedingUseCase;
use App\Application\UseCases\Feeding\UpdateFeedingUseCase;
use App\Presentation\Requests\Feeding\FeedingStoreRequest;
use App\Presentation\Requests\Feeding\FeedingUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class FeedingController
{
    /**
     * Display a listing of feedings.
     */
    public function index(ListFeedingsUseCase $useCase): JsonResponse
    {
        try {
            $feedings   = $useCase->execute();
            $data       = $feedings->toArray(request());
            $pagination = $feedings->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified feeding.
     */
    public function show(string $id, ShowFeedingUseCase $useCase): JsonResponse
    {
        try {
            $feeding = $useCase->execute($id);

            if (! $feeding instanceof FeedingDTO || $feeding->isEmpty()) {
                return ApiResponse::error(null, 'Feeding not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($feeding->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Feeding not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created feeding.
     */
    public function store(FeedingStoreRequest $request, CreateFeedingUseCase $useCase): JsonResponse
    {
        try {
            $validated = $request->validated();
            $dto       = FeedingDTO::fromArray([
                'id'                       => '',
                'batch_id'                 => $validated['batchId'],
                'feeding_date'             => $validated['feedingDate'],
                'quantity_provided'        => (float) $validated['quantityProvided'],
                'feed_type'                => $validated['feedType'],
                'stock_reduction_quantity' => (float) $validated['stockReductionQuantity'],
                'created_at'               => null,
                'updated_at'               => null,
            ]);
            $feeding = $useCase->execute($dto);

            return ApiResponse::created($feeding->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified feeding.
     */
    public function update(FeedingUpdateRequest $request, string $id, UpdateFeedingUseCase $useCase): JsonResponse
    {
        try {
            $feeding = $useCase->execute($id, $request->validated());

            return ApiResponse::success($feeding->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified feeding.
     */
    public function destroy(string $id, DeleteFeedingUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Feeding not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Feeding successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting feeding', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
