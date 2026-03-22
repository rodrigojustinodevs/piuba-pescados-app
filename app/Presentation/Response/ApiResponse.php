<?php

declare(strict_types=1);

namespace App\Presentation\Response;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Throwable;

final class ApiResponse
{
    /**
     * 201 — recurso criado com sucesso.
     *
     * @param JsonResource|ResourceCollection|array<string, mixed>|null $data
     */
    public static function created(
        JsonResource|ResourceCollection|array|null $data = null,
        string $message = 'Successfully created',
    ): JsonResponse {
        return response()->json([
            'status'   => true,
            'message'  => $message,
            'response' => self::resolveData($data),
        ], JsonResponse::HTTP_CREATED);
    }

    /**
     * 200 — operação bem-sucedida com dados opcionais e paginação opcional.
     *
     * @param JsonResource|ResourceCollection|array<string, mixed>|null $data
     * @param array<string, mixed>|null                                  $pagination
     */
    public static function success(
        JsonResource|ResourceCollection|array|null $data = null,
        string $message = 'Success',
        ?array $pagination = null,
        int $status = JsonResponse::HTTP_OK,
    ): JsonResponse {
        $body = [
            'status'   => true,
            'message'  => $message,
            'response' => self::resolveData($data),
        ];

        if ($pagination !== null) {
            $body['pagination'] = $pagination;
        }

        return response()->json($body, $status);
    }

    /**
     * Erros de domínio conhecidos (ex: InvalidPurchaseStatusTransitionException).
     * A mensagem da exceção vai direto ao cliente — sem debug, sem stack trace.
     */
    public static function domainError(
        string $message,
        int $status = JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
    ): JsonResponse {
        return response()->json([
            'status'     => false,
            'message'    => $message,
            'response'   => null,
            'paramError' => false,
        ], $status);
    }

    /**
     * Erros genéricos de infraestrutura, HTTP ou inesperados.
     *
     * @param array<string, mixed>|null $handleError Dados extras (errors, request URL, debug).
     *                                               Montado pelo Handler — nunca pelo Controller.
     */
    public static function error(
        ?Throwable $exception = null,
        string $message = 'Error',
        int $status = JsonResponse::HTTP_BAD_REQUEST,
        bool $paramError = false,
        ?array $handleError = null,
    ): JsonResponse {
        $status = $status > 0 ? $status : JsonResponse::HTTP_BAD_REQUEST;

        // Detalhes internos da exceção apenas em debug
        // ✅ file e line nunca chegam ao cliente em produção
        $exceptionData = ($exception instanceof Throwable && config('app.debug'))
            ? [
                'exception' => get_class($exception),
                'message'   => $exception->getMessage(),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
            ]
            : [];

        $response = array_merge($exceptionData, $handleError ?? []);

        return response()->json([
            'status'     => false,
            'message'    => $message,
            'response'   => $response ?: null,
            'paramError' => $paramError,
        ], $status);
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    private static function resolveData(
        JsonResource|ResourceCollection|array|null $data,
    ): mixed {
        if ($data instanceof JsonResource || $data instanceof ResourceCollection) {
            return $data->resolve();
        }

        return $data;
    }
}
