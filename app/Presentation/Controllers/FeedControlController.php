<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\FeedControlDTO;
use App\Application\UseCases\FeedControl\CreateFeedControlUseCase;
use App\Application\UseCases\FeedControl\DeleteFeedControlUseCase;
use App\Application\UseCases\FeedControl\ListFeedControlsUseCase;
use App\Application\UseCases\FeedControl\ShowFeedControlUseCase;
use App\Application\UseCases\FeedControl\UpdateFeedControlUseCase;
use App\Presentation\Requests\FeedControl\FeedControlStoreRequest;
use App\Presentation\Requests\FeedControl\FeedControlUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class FeedControlController
{
    /**
     * Display a listing of feedControls.
     */
    public function index(ListFeedControlsUseCase $useCase): JsonResponse
    {
        try {
            $feedControls = $useCase->execute();
            $data         = $feedControls->toArray(request());
            $pagination   = $feedControls->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified feedControl.
     */
    public function show(string $id, ShowFeedControlUseCase $useCase): JsonResponse
    {
        try {
            $feedControl = $useCase->execute($id);

            if (! $feedControl instanceof FeedControlDTO || $feedControl->isEmpty()) {
                return ApiResponse::error(null, 'FeedControl not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($feedControl->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'FeedControl not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created feedControl.
     */
    public function store(FeedControlStoreRequest $request, CreateFeedControlUseCase $useCase): JsonResponse
    {
        try {
            $feedControl = $useCase->execute($request->validated());

            return ApiResponse::created($feedControl->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified feedControl.
     */
    public function update(FeedControlUpdateRequest $request, string $id, UpdateFeedControlUseCase $useCase): JsonResponse
    {
        try {
            $feedControl = $useCase->execute($id, $request->validated());

            return ApiResponse::success($feedControl->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified feedControl.
     */
    public function destroy(string $id, DeleteFeedControlUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'FeedControl not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'FeedControl successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting feedcontrol', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
