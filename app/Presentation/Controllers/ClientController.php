<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\ClientDTO;
use App\Application\Services\ClientService;
use App\Presentation\Requests\Client\ClientStoreRequest;
use App\Presentation\Requests\Client\ClientUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class ClientController
{
    public function __construct(
        protected ClientService $clientService
    ) {
    }

    /**
     * @OA\Get(
     *     path="/company/clients",
     *     summary="List all clients",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of clients",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $clients    = $this->clientService->showAllClients();
            $data       = $clients->toArray(request());
            $pagination = $clients->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * @OA\Get(
     *     path="/company/client/{id}",
     *     summary="Get a specific client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client details",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Client not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id): JsonResponse
    {
        try {
            $client = $this->clientService->showClient($id);

            if (! $client instanceof ClientDTO || $client->isEmpty()) {
                return ApiResponse::error(
                    null,
                    'Client not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            return ApiResponse::success($client->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Client not found');
        }
    }

    /**
     * @OA\Post(
     *     path="/company/client",
     *     summary="Create a new client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"company_id", "name", "person_type"},
     *             @OA\Property(
     *                 property="company_id",
     *                 type="string",
     *                 format="uuid",
     *                 example="550e8400-e29b-41d4-a716-446655440000"
     *             ),
     *             @OA\Property(property="name", type="string", maxLength=255, example="João Silva"),
     *             @OA\Property(
     *                 property="contact",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=255,
     *                 example="Maria Silva"
     *             ),
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=20,
     *                 example="(85) 99999-9999"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 nullable=true,
     *                 maxLength=255,
     *                 example="joao@example.com"
     *             ),
     *             @OA\Property(
     *                 property="person_type",
     *                 type="string",
     *                 enum={"individual", "company"},
     *                 example="individual"
     *             ),
     *             @OA\Property(
     *                 property="document_number",
     *                 type="string",
     *                 nullable=true,
     *                 pattern="^\\d{11}|\\d{14}$",
     *                 example="12345678901"
     *             ),
     *             @OA\Property(
     *                 property="address",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=255,
     *                 example="Rua Exemplo, 123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client created successfully"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(ClientStoreRequest $request): JsonResponse
    {
        try {
            $client = $this->clientService->create($request->validated());

            return ApiResponse::created($client->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Put(
     *     path="/company/client/{id}",
     *     summary="Update a client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="company_id",
     *                 type="string",
     *                 format="uuid",
     *                 example="550e8400-e29b-41d4-a716-446655440000"
     *             ),
     *             @OA\Property(property="name", type="string", maxLength=255, example="João Silva"),
     *             @OA\Property(
     *                 property="contact",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=255,
     *                 example="Maria Silva"
     *             ),
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=20,
     *                 example="(85) 99999-9999"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 nullable=true,
     *                 maxLength=255,
     *                 example="joao@example.com"
     *             ),
     *             @OA\Property(
     *                 property="person_type",
     *                 type="string",
     *                 enum={"individual", "company"},
     *                 example="individual"
     *             ),
     *             @OA\Property(
     *                 property="document_number",
     *                 type="string",
     *                 nullable=true,
     *                 pattern="^\\d{11}|\\d{14}$",
     *                 example="12345678901"
     *             ),
     *             @OA\Property(
     *                 property="address",
     *                 type="string",
     *                 nullable=true,
     *                 maxLength=255,
     *                 example="Rua Exemplo, 123"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Validation error"),
     *     @OA\Response(response=404, description="Client not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(ClientUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $client = $this->clientService->updateClient($id, $request->validated());

            return ApiResponse::success($client->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Delete(
     *     path="/company/client/{id}",
     *     summary="Delete a client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Client ID",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Client deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Client successfully deleted")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Client not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->clientService->deleteClient($id);

            if (! $deleted) {
                return ApiResponse::error(null, 'Client not found', Response::HTTP_NOT_FOUND);
            }

            return ApiResponse::success(null, Response::HTTP_OK, 'Client successfully deleted');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, 'Error deleting client');
        }
    }
}
