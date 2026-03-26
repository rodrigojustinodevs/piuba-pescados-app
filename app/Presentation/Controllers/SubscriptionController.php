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

/**
 * @OA\Schema(
 *     schema="Subscription",
 *     type="object",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="plan", type="string", enum={"basic","premium","enterprise"}),
 *     @OA\Property(property="status", type="string", enum={"active","canceled"}),
 *     @OA\Property(property="startDate", type="string", format="date"),
 *     @OA\Property(property="endDate", type="string", format="date"),
 *     @OA\Property(
 *         property="company",
 *         type="object",
 *         nullable=true,
 *         @OA\Property(property="name", type="string")
 *     ),
 *     @OA\Property(property="createdAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="updatedAt", type="string", format="date-time", nullable=true)
 * )
 */
class SubscriptionController
{
    /**
     * @OA\Get(
     *     path="/company/subscriptions",
     *     summary="List subscriptions",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer", example=25)),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of subscriptions",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/Subscription")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="pagination",
     *                 type="object",
     *                 @OA\Property(property="total", type="integer", example=1),
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=1),
     *                 @OA\Property(property="first_page", type="integer", example=1),
     *                 @OA\Property(property="per_page", type="integer", example=25)
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
     * @OA\Get(
     *     path="/company/subscription/{id}",
     *     summary="Get subscription by ID",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Subscription")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Subscription not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
     * @OA\Post(
     *     path="/company/subscription",
     *     summary="Create subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company_id","plan","start_date","end_date","status"},
     *             @OA\Property(property="company_id", type="string", format="uuid"),
     *             @OA\Property(property="plan", type="string", enum={"basic","premium","enterprise"}),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date"),
     *             @OA\Property(property="status", type="string", enum={"active","canceled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subscription created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully created"),
     *             @OA\Property(property="response", ref="#/components/schemas/Subscription")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
     * @OA\Put(
     *     path="/company/subscription/{id}",
     *     summary="Update subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="company_id", type="string", format="uuid"),
     *             @OA\Property(property="plan", type="string", enum={"basic","premium","enterprise"}),
     *             @OA\Property(property="start_date", type="string", format="date"),
     *             @OA\Property(property="end_date", type="string", format="date"),
     *             @OA\Property(property="status", type="string", enum={"active","canceled"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="response", ref="#/components/schemas/Subscription")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Subscription not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(
        SubscriptionUpdateRequest $request,
        string $id,
        UpdateSubscriptionUseCase $useCase
    ): JsonResponse {
        try {
            $subscription = $useCase->execute($id, $request->validated());

            return ApiResponse::success($subscription->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/subscription/{id}",
     *     summary="Delete subscription",
     *     tags={"Subscriptions"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription deleted",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Subscription successfully deleted"),
     *             @OA\Property(property="response", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=404, description="Subscription not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
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
