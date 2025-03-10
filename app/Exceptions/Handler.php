<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

use App\Presentation\Response\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\{
    AccessDeniedHttpException,
    MethodNotAllowedHttpException,
    NotFoundHttpException
};
use Throwable;

class Handler extends ExceptionHandler
{
    protected array $levels = [];
    protected array $dontReport = [];
    protected array $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->renderable(fn (NotFoundHttpException $exception) =>
            $this->handleException($exception, 'Route not found', JsonResponse::HTTP_NOT_FOUND)
        );

        $this->renderable(fn (MethodNotAllowedHttpException $exception) =>
            $this->handleException($exception, 'Method not allowed', JsonResponse::HTTP_METHOD_NOT_ALLOWED)
        );

        $this->renderable(fn (AuthenticationException $exception) =>
            $this->handleException($exception, 'User not authenticated', JsonResponse::HTTP_UNAUTHORIZED)
        );

        $this->renderable(fn (AccessDeniedHttpException $exception) =>
            $this->handleException($exception, 'Access denied', JsonResponse::HTTP_FORBIDDEN)
        );

        $this->renderable(fn (ValidationException $exception) =>
            $this->handleValidationException($exception)
        );

        $this->renderable(fn (Throwable $exception, Request $request) =>
            $this->handleException($exception, 'Internal server error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR, $request)
        );
    }

    /**
     * Método genérico para tratamento de exceções
     *
     * @param Throwable $exception
     * @param string $defaultMessage
     * @param int $statusCode
     * @param Request|null $request
     * @return JsonResponse
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
     *
     * @param ValidationException $exception
     * @return JsonResponse
     */
    private function handleValidationException(ValidationException $exception): JsonResponse
    {
        $errors = $exception->errors();
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
