<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\Services\SubscriptionService;
use App\Presentation\Requests\Subscription\SubscriptionStoreRequest;
use App\Presentation\Requests\Subscription\SubscriptionUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SubscriptionController
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {
    }

    /**
     * Display a listing of subscriptions.
     */
    public function index(): JsonResponse
    {
        try {
            $subscriptions = $this->subscriptionService->showAllSubscriptions();
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
    public function show(string $id): JsonResponse
    {
        try {
            $subscription = $this->subscriptionService->showSubscription($id);

            if (! $subscription instanceof \App\Application\DTOs\SubscriptionDTO || $subscription->isEmpty()) {
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
    public function store(SubscriptionStoreRequest $request): JsonResponse
    {
        try {
            $subscription = $this->subscriptionService->create($request->validated());

            return ApiResponse::created($subscription->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified subscription.
     */
    public function update(SubscriptionUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $subscription = $this->subscriptionService->updateSubscription($id, $request->validated());

            return ApiResponse::success($subscription->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified subscription.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->subscriptionService->deleteSubscription($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Subscription not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Subscription successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting subscription', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
