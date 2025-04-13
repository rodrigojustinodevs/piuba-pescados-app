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

    public function store(ClientStoreRequest $request): JsonResponse
    {
        try {
            $client = $this->clientService->create($request->validated());

            return ApiResponse::created($client->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function update(ClientUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $client = $this->clientService->updateClient($id, $request->validated());

            return ApiResponse::success($client->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

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
