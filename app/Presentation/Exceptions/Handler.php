<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

use App\Presentation\Response\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [];

    protected $dontReport = [];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    #[\Override]
    public function register(): void
    {
        $this->renderable(
            fn (NotFoundHttpException $exception): JsonResponse => $this->handleException($exception, 'Route not found', JsonResponse::HTTP_NOT_FOUND)
        );

        $this->renderable(
            fn (MethodNotAllowedHttpException $exception): JsonResponse => $this->handleException($exception, 'Method not allowed', JsonResponse::HTTP_METHOD_NOT_ALLOWED)
        );

        $this->renderable(
            fn (AuthenticationException $exception): JsonResponse => $this->handleException($exception, 'User not authenticated', JsonResponse::HTTP_UNAUTHORIZED)
        );

        $this->renderable(
            fn (AccessDeniedHttpException $exception): JsonResponse => $this->handleException($exception, 'Access denied', JsonResponse::HTTP_FORBIDDEN)
        );

        $this->renderable(
            fn (ValidationException $exception): JsonResponse => $this->handleValidationException($exception)
        );

        $this->renderable(
            fn (Throwable $exception, Request $request): JsonResponse => $this->handleException($exception, 'Internal server error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $request)
        );
    }

    /**
     * Método genérico para tratamento de exceções
     */
    private function handleException(Throwable $exception, string $defaultMessage, int $statusCode, ?Request $request = null): JsonResponse
    {
        $debug = config('app.debug') ? [
            'message' => $exception->getMessage(),
            'line'    => $exception->getLine(),
            'code'    => $exception->getCode(),
            'trace'   => $exception->getTrace(),
        ] : [];

        return ApiResponse::error(
            [
                'exception' => $exception->getMessage(),
                'request'   => $request?->fullUrl(),
                'debug'     => $debug,
            ],
            $defaultMessage,
            $statusCode
        );
    }

    /**
     * Tratamento específico para erros de validação
     */
    private function handleValidationException(ValidationException $exception): JsonResponse
    {
        $errors     = $exception->errors();
        $firstError = reset($errors)[0] ?? 'Validation error';

        return ApiResponse::error(
            [
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ],
            $firstError,
            JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            true
        );
    }
}
