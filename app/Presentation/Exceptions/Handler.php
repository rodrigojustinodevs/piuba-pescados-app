<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

use App\Application\Exceptions\CompanyNotFoundException;
use App\Domain\Exceptions\DuplicateStockException;
use App\Domain\Exceptions\InsufficientStockException;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\InvalidPurchaseStatusTransitionException;
use App\Domain\Exceptions\StockNotFoundException;
use App\Domain\Exceptions\UnauthorizedException;
use App\Domain\Exceptions\ZeroDeltaException;
use App\Presentation\Response\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    protected $dontReport = [
        // Auth
        InvalidCredentialsException::class,
        UnauthorizedException::class,
        // Purchase
        CompanyNotFoundException::class,
        InvalidPurchaseStatusTransitionException::class,
        // Stock
        DuplicateStockException::class,
        InsufficientStockException::class,
        StockNotFoundException::class,
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    #[\Override]
    public function register(): void
    {
        // -----------------------------------------------------------------------
        // Auth
        // -----------------------------------------------------------------------
        $this->renderable(
            function (InvalidCredentialsException $e, Request $r): JsonResponse {
                return $this->handleDomainException($e, JsonResponse::HTTP_UNAUTHORIZED);
            }
        );
        $this->renderable(
            function (UnauthorizedException $e, Request $r): JsonResponse {
                return $this->handleDomainException($e, JsonResponse::HTTP_UNAUTHORIZED);
            }
        );

        // -----------------------------------------------------------------------
        // Purchase
        // -----------------------------------------------------------------------
        $this->renderable(
            function (InvalidPurchaseStatusTransitionException $e, Request $r): JsonResponse {
                return $this->handleDomainException($e, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        );
        $this->renderable(
            function (CompanyNotFoundException $e, Request $r): JsonResponse {
                return $this->handleDomainException($e, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        );

        // -----------------------------------------------------------------------
        // Stock
        // -----------------------------------------------------------------------
        $this->renderable(
            function (DuplicateStockException $e, Request $r): JsonResponse {
                return $this->handleDomainException($e, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        );
        $this->renderable(
            function (InsufficientStockException $e, Request $r): JsonResponse {
                return $this->handleDomainException($e, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        );
        $this->renderable(
            function (StockNotFoundException $e, Request $r): JsonResponse {
                return $this->handleDomainException($e, JsonResponse::HTTP_NOT_FOUND);
            }
        );

        // -----------------------------------------------------------------------
        // Eloquent
        // -----------------------------------------------------------------------
        $this->renderable(function (ModelNotFoundException $e, Request $r): JsonResponse {
            $model = class_basename($e->getModel());

            return $this->handleException(
                $e,
                "{$model} not found.",
                JsonResponse::HTTP_NOT_FOUND,
                $r,
            );
        });

        // -----------------------------------------------------------------------
        // HTTP / infraestrutura
        // -----------------------------------------------------------------------
        $this->renderable(
            function (NotFoundHttpException $e, Request $r): JsonResponse {
                return $this->handleException($e, 'Route not found.', JsonResponse::HTTP_NOT_FOUND, $r);
            }
        );
        $this->renderable(
            function (MethodNotAllowedHttpException $e, Request $r): JsonResponse {
                return $this->handleException(
                    $e,
                    'Method not allowed.',
                    JsonResponse::HTTP_METHOD_NOT_ALLOWED,
                    $r,
                );
            }
        );
        $this->renderable(
            function (AuthenticationException $e, Request $r): JsonResponse {
                return $this->handleException(
                    $e,
                    'User not authenticated.',
                    JsonResponse::HTTP_UNAUTHORIZED,
                    $r,
                );
            }
        );
        $this->renderable(
            function (AccessDeniedHttpException $e, Request $r): JsonResponse {
                return $this->handleException($e, 'Access denied.', JsonResponse::HTTP_FORBIDDEN, $r);
            }
        );
        $this->renderable(
            function (ValidationException $e, Request $r): JsonResponse {
                return $this->handleValidationException($e, $r);
            }
        );
        $this->renderable(
            function (ZeroDeltaException $e, Request $r): JsonResponse {
                return $this->handleDomainException($e, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }
        );

        // -----------------------------------------------------------------------
        // JWT
        // -----------------------------------------------------------------------
        $this->renderable(
            function (TokenExpiredException $e, Request $r): JsonResponse {
                return $this->handleException($e, 'Token expired.', JsonResponse::HTTP_UNAUTHORIZED, $r);
            }
        );
        $this->renderable(
            function (TokenInvalidException $e, Request $r): JsonResponse {
                return $this->handleException($e, 'Token invalid.', JsonResponse::HTTP_UNAUTHORIZED, $r);
            }
        );
        $this->renderable(
            function (JWTException $e, Request $r): JsonResponse {
                return $this->handleException($e, 'JWT error.', JsonResponse::HTTP_UNAUTHORIZED, $r);
            }
        );
    }

    #[\Override]
    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse
    {
        return $this->handleException(
            $exception,
            'User not authenticated.',
            JsonResponse::HTTP_UNAUTHORIZED,
            $request,
        );
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function handleDomainException(Throwable $exception, int $statusCode): JsonResponse
    {
        return ApiResponse::domainError(
            message: $exception->getMessage(),
            status:  $statusCode,
        );
    }

    private function handleException(
        Throwable $exception,
        string $defaultMessage,
        int $statusCode,
        ?Request $request = null,
    ): JsonResponse {
        $debug = config('app.debug') ? [
            'exception' => $exception::class,
            'message'   => $exception->getMessage(),
            'file'      => $exception->getFile(),
            'line'      => $exception->getLine(),
        ] : [];

        return ApiResponse::error(
            exception:   $exception,
            message:     $defaultMessage,
            status:      $statusCode,
            handleError: array_filter([
                'request' => $request?->fullUrl(),
                'debug'   => $debug ?: null,
            ]),
        );
    }

    private function handleValidationException(
        ValidationException $exception,
        ?Request $request = null,
    ): JsonResponse {
        $errors = $exception->errors();
        $first  = reset($errors);

        $firstMessage = is_array($first) && isset($first[0])
            ? (string) $first[0]
            : 'Validation error';

        return ApiResponse::error(
            exception:   $exception,
            message:     $firstMessage,
            status:      JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            paramError:  true,
            handleError: [
                'errors'  => $errors,
                'request' => $request?->fullUrl(),
            ],
        );
    }
}
