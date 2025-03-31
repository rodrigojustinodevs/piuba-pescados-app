<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\FeedControlService;
use App\Presentation\Requests\FeedControl\FeedControlStoreRequest;
use App\Presentation\Requests\FeedControl\FeedControlUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class FeedControlController
{
    public function __construct(
        protected FeedControlService $feedControlService
    ) {
    }

    /**
     * Display a listing of feedControls.
     */
    public function index(): JsonResponse
    {
        try {
            $feedControls = $this->feedControlService->showAllFeedControls();
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
    public function show(string $id): JsonResponse
    {
        try {
            $feedControl = $this->feedControlService->showFeedControl($id);

            if (! $feedControl instanceof \App\Application\DTOs\FeedControlDTO || $feedControl->isEmpty()) {
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
    public function store(FeedControlStoreRequest $request): JsonResponse
    {
        try {
            $feedControl = $this->feedControlService->create($request->validated());

            return ApiResponse::created($feedControl->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified feedControl.
     */
    public function update(FeedControlUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $feedControl = $this->feedControlService->updateFeedControl($id, $request->validated());

            return ApiResponse::success($feedControl->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified feedControl.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->feedControlService->deleteFeedControl($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'FeedControl not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'FeedControl successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting feedcontrol', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
