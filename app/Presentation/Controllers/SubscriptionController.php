<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\SubscriptionDTO;
use App\Application\UseCases\Subscription\CreateSubscriptionUseCase;
use App\Application\UseCases\Subscription\DeleteSubscriptionUseCase;
use App\Application\UseCases\Subscription\ListSubscriptionsUseCase;
use App\Application\UseCases\Subscription\ShowSubscriptionUseCase;
use App\Application\UseCases\Subscription\UpdateSubscriptionUseCase;
use App\Presentation\Requests\Subscription\SubscriptionStoreRequest;
use App\Presentation\Requests\Subscription\SubscriptionUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SubscriptionController
{
    /**
     * Display a listing of subscriptions.
     */
    public function index(ListSubscriptionsUseCase $useCase): JsonResponse
    {
        try {
            $subscriptions = $useCase->execute();
            $data          = $subscriptions->toArray(request());
            $pagination    = $subscriptions->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified subscription.
     */
    public function show(string $id, ShowSubscriptionUseCase $useCase): JsonResponse
    {
        try {
            $subscription = $useCase->execute($id);

            if (! $subscription instanceof SubscriptionDTO || $subscription->isEmpty()) {
                return ApiResponse::error(null, 'Subscription not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success($subscription->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Subscription not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created subscription.
     */
    public function store(SubscriptionStoreRequest $request, CreateSubscriptionUseCase $useCase): JsonResponse
    {
        try {
            $subscription = $useCase->execute($request->validated());

            return ApiResponse::created($subscription->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified subscription.
     */
    public function update(SubscriptionUpdateRequest $request, string $id, UpdateSubscriptionUseCase $useCase): JsonResponse
    {
        try {
            $subscription = $useCase->execute($id, $request->validated());

            return ApiResponse::success($subscription->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified subscription.
     */
    public function destroy(string $id, DeleteSubscriptionUseCase $useCase): JsonResponse
    {
        try {
            $deleted = $useCase->execute($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Subscription not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Subscription successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting subscription', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
