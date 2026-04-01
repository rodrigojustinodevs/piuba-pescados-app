<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\Client\AnonymizeClientUseCase;
use App\Application\UseCases\Client\CreateClientUseCase;
use App\Application\UseCases\Client\DeleteClientUseCase;
use App\Application\UseCases\Client\ListClientsUseCase;
use App\Application\UseCases\Client\ShowClientUseCase;
use App\Application\UseCases\Client\UpdateClientUseCase;
use App\Presentation\Requests\Client\ClientStoreRequest;
use App\Presentation\Requests\Client\ClientUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * @OA\Tag(name="Clients", description="Clientes")
 */
final class ClientController
{
    /**
     * @OA\Get(
     *     path="/company/clients",
     *     summary="List all clients",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer", example=1)),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", example=15)),
     *     @OA\Response(response=200, description="List of clients"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(ListClientsUseCase $useCase): JsonResponse
    {
        $clients    = $useCase->execute();
        $data       = $clients->toArray(request());
        $pagination = $clients->additional['pagination'] ?? null;

        return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
    }

    /**
     * @OA\Get(
     *     path="/company/client/{id}",
     *     summary="Get a specific client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Client details"),
     *     @OA\Response(response=404, description="Client not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function show(string $id, ShowClientUseCase $useCase): JsonResponse
    {
        $client = $useCase->execute($id);

        return ApiResponse::success($client->toArray(), Response::HTTP_OK, 'Success');
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
     *             required={"companyId", "name", "personType"},
     *             @OA\Property(property="companyId", type="string", format="uuid"),
     *             @OA\Property(property="name", type="string", maxLength=255, example="João Silva"),
     *             @OA\Property(property="personType", type="string", enum={"individual", "company"}),
     *             @OA\Property(property="documentNumber", type="string", nullable=true, example="12345678901"),
     *             @OA\Property(property="email", type="string", format="email", nullable=true),
     *             @OA\Property(property="phone", type="string", nullable=true, maxLength=20),
     *             @OA\Property(property="contact", type="string", nullable=true, maxLength=255),
     *             @OA\Property(property="address", type="string", nullable=true, maxLength=255),
     *             @OA\Property(property="creditLimit", type="number", format="float", nullable=true),
     *             @OA\Property(
     *                 property="priceGroup",
     *                 type="string",
     *                 enum={"wholesale","retail","consumer"},
     *                 nullable=true
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Client created successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(ClientStoreRequest $request, CreateClientUseCase $useCase): JsonResponse
    {
        $client = $useCase->execute($request->validated());

        return ApiResponse::created($client->toArray());
    }

    /**
     * @OA\Put(
     *     path="/company/client/{id}",
     *     summary="Update a client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", maxLength=255),
     *             @OA\Property(property="personType", type="string", enum={"individual", "company"}),
     *             @OA\Property(property="documentNumber", type="string", nullable=true),
     *             @OA\Property(property="email", type="string", format="email", nullable=true),
     *             @OA\Property(property="phone", type="string", nullable=true),
     *             @OA\Property(property="contact", type="string", nullable=true),
     *             @OA\Property(property="address", type="string", nullable=true),
     *             @OA\Property(property="creditLimit", type="number", format="float", nullable=true),
     *             @OA\Property(
     *                 property="priceGroup",
     *                 type="string",
     *                 enum={"wholesale","retail","consumer"},
     *                 nullable=true
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Client updated successfully"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=404, description="Client not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(ClientUpdateRequest $request, string $id, UpdateClientUseCase $useCase): JsonResponse
    {
        $client = $useCase->execute($id, $request->validated());

        return ApiResponse::success($client->toArray(), Response::HTTP_OK, 'Client updated successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/company/client/{id}",
     *     summary="Soft-delete a client",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Client deleted successfully"),
     *     @OA\Response(response=422, description="Client has pending obligations"),
     *     @OA\Response(response=404, description="Client not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy(string $id, DeleteClientUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Client successfully deleted.');
    }

    /**
     * @OA\Delete(
     *     path="/company/client/{id}/anonymize",
     *     summary="Anonymize and soft-delete a client (LGPD)",
     *     tags={"Clients"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Client anonymized and deleted"),
     *     @OA\Response(response=404, description="Client not found"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function anonymize(string $id, AnonymizeClientUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Client anonymized and deleted successfully.');
    }
}
