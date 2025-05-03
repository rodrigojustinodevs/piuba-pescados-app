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
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

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
        $this->reportable(function (Throwable $exception): void {
            $this->handleException($exception, 'User not authenticated', JsonResponse::HTTP_UNAUTHORIZED);
        });

        $this->renderable(
            fn (NotFoundHttpException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Route not found',
                JsonResponse::HTTP_NOT_FOUND,
                $r
            )
        );

        $this->renderable(
            fn (MethodNotAllowedHttpException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Method not allowed',
                JsonResponse::HTTP_METHOD_NOT_ALLOWED,
                $r
            )
        );

        $this->renderable(
            fn (AuthenticationException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'User not authenticated',
                JsonResponse::HTTP_UNAUTHORIZED,
                $r
            )
        );

        $this->renderable(
            fn (AccessDeniedHttpException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Access denied',
                JsonResponse::HTTP_FORBIDDEN,
                $r
            )
        );

        $this->renderable(
            fn (ValidationException $e, Request $r): JsonResponse => $this->handleValidationException(
                $e,
                $r
            )
        );

        $this->renderable(
            fn (TokenExpiredException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Token expired',
                JsonResponse::HTTP_UNAUTHORIZED,
                $r
            )
        );

        $this->renderable(
            fn (TokenInvalidException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Token invalid',
                JsonResponse::HTTP_UNAUTHORIZED,
                $r
            )
        );

        $this->renderable(
            fn (JWTException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'JWT error',
                JsonResponse::HTTP_UNAUTHORIZED,
                $r
            )
        );
    }

    #[\Override]
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse
    {
        return $this->handleException(
            $exception,
            'User not authenticated',
            JsonResponse::HTTP_UNAUTHORIZED,
            $request
        );
    }

    private function handleException(
        Throwable $exception,
        string $defaultMessage,
        int $statusCode,
        ?Request $request = null
    ): JsonResponse {
        $debug = config('app.debug') ? [
            'message' => $exception->getMessage(),
            'line'    => $exception->getLine(),
            'code'    => $exception->getCode(),
            'trace'   => $exception->getTrace(),
        ] : [];

        return ApiResponse::error(
            $exception,
            $defaultMessage,
            $statusCode,
            false,
            [
                'request' => $request?->fullUrl(),
                'debug'   => $debug,
            ]
        );
    }

    private function handleValidationException(ValidationException $exception, ?Request $request = null): JsonResponse
    {
        $errors = $exception->errors();

        $firstError = $errors ? reset($errors) : ['Validation error'];

        $firstErrorMessage = is_array($firstError) && isset($firstError[0])
            ? (string) $firstError[0]
            : 'Validation error';

        return ApiResponse::error(
            $exception,
            $firstErrorMessage,
            JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            true,
            [
                'errors'  => $errors,
                'line'    => $exception->getLine(),
                'request' => $request?->fullUrl(),
            ]
        );
    }
}
