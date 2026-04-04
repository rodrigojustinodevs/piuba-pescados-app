<?php

declare(strict_types=1);

namespace App\Presentation\Exceptions;

use App\Application\Exceptions\CompanyNotFoundException;
use App\Domain\Exceptions\AllocationAmountMismatchException;
use App\Domain\Exceptions\BatchAlreadyFinishedException;
use App\Domain\Exceptions\BiometryAverageWeightInvalidException;
use App\Domain\Exceptions\BiometryDuplicateDateException;
use App\Domain\Exceptions\BiometryNoFeedingsException;
use App\Domain\Exceptions\BiometryNotFoundException;
use App\Domain\Exceptions\CategoryTypeMismatchException;
use App\Domain\Exceptions\ClientCreditLimitExceededException;
use App\Domain\Exceptions\ClientDocumentAlreadyExistsException;
use App\Domain\Exceptions\ClientHasPendingObligationsException;
use App\Domain\Exceptions\ClientMissingFiscalDataException;
use App\Domain\Exceptions\ClosedStockingException;
use App\Domain\Exceptions\DuplicateStockException;
use App\Domain\Exceptions\FeedInventoryNotFoundException;
use App\Domain\Exceptions\FinancialCategoryHasTransactionsException;
use App\Domain\Exceptions\InactiveStockingException;
use App\Domain\Exceptions\InsufficientBiomassException;
use App\Domain\Exceptions\InsufficientStockException;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Exceptions\InvalidPurchaseStatusTransitionException;
use App\Domain\Exceptions\MortalityExceedsSurvivorsException;
use App\Domain\Exceptions\MortalityNotFoundException;
use App\Domain\Exceptions\SaleFinanciallyLockedException;
use App\Domain\Exceptions\StockNotFoundException;
use App\Domain\Exceptions\TankAlreadyHasActiveBatchException;
use App\Domain\Exceptions\TransactionAlreadyAllocatedException;
use App\Domain\Exceptions\TransactionAmountImmutableException;
use App\Domain\Exceptions\TransferBatchOriginMismatchException;
use App\Domain\Exceptions\TransferSameTankException;
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
        // Client
        ClientHasPendingObligationsException::class,
        ClientMissingFiscalDataException::class,
        ClientCreditLimitExceededException::class,
        ClientDocumentAlreadyExistsException::class,
        // Batch
        BatchAlreadyFinishedException::class,
        TankAlreadyHasActiveBatchException::class,
        // Mortality
        MortalityNotFoundException::class,
        MortalityExceedsSurvivorsException::class,
        // Biometry
        BiometryNotFoundException::class,
        BiometryAverageWeightInvalidException::class,
        BiometryNoFeedingsException::class,
        BiometryDuplicateDateException::class,
        // FeedInventory
        FeedInventoryNotFoundException::class,
        // Stock
        DuplicateStockException::class,
        InsufficientStockException::class,
        StockNotFoundException::class,
        // FinancialCategory
        FinancialCategoryHasTransactionsException::class,
        // FinancialTransaction
        TransactionAmountImmutableException::class,
        CategoryTypeMismatchException::class,
        // Sale
        InsufficientBiomassException::class,
        ClosedStockingException::class,
        SaleFinanciallyLockedException::class,
        // CostAllocation
        TransactionAlreadyAllocatedException::class,
        AllocationAmountMismatchException::class,
        InactiveStockingException::class,
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
            fn (InvalidCredentialsException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNAUTHORIZED,
            )
        );
        $this->renderable(
            fn (UnauthorizedException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNAUTHORIZED,
            )
        );

        // -----------------------------------------------------------------------
        // Client
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (ClientHasPendingObligationsException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (ClientMissingFiscalDataException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (ClientCreditLimitExceededException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (ClientDocumentAlreadyExistsException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        // -----------------------------------------------------------------------
        // Purchase
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (InvalidPurchaseStatusTransitionException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (CompanyNotFoundException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        // -----------------------------------------------------------------------
        // Batch
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (BatchAlreadyFinishedException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (TankAlreadyHasActiveBatchException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (TransferBatchOriginMismatchException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (TransferSameTankException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        // -----------------------------------------------------------------------
        // Mortality
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (MortalityNotFoundException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_NOT_FOUND,
            )
        );
        $this->renderable(
            fn (MortalityExceedsSurvivorsException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        // -----------------------------------------------------------------------
        // Biometry
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (BiometryNotFoundException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_NOT_FOUND,
            )
        );
        $this->renderable(
            fn (BiometryAverageWeightInvalidException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (BiometryNoFeedingsException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (BiometryDuplicateDateException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        // -----------------------------------------------------------------------
        // FeedInventory
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (FeedInventoryNotFoundException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_NOT_FOUND,
            )
        );

        // -----------------------------------------------------------------------
        // Stock
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (DuplicateStockException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (InsufficientStockException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (StockNotFoundException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_NOT_FOUND,
            )
        );

        // -----------------------------------------------------------------------
        // FinancialCategory
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (FinancialCategoryHasTransactionsException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        // -----------------------------------------------------------------------
        // FinancialTransaction
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (TransactionAmountImmutableException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (CategoryTypeMismatchException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        // -----------------------------------------------------------------------
        // Sale
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (InsufficientBiomassException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (ClosedStockingException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );
        $this->renderable(
            fn (SaleFinanciallyLockedException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        // -----------------------------------------------------------------------
        // CostAllocation
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (TransactionAlreadyAllocatedException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        $this->renderable(
            fn (AllocationAmountMismatchException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        $this->renderable(
            fn (InactiveStockingException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
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
            fn (NotFoundHttpException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Route not found.',
                JsonResponse::HTTP_NOT_FOUND,
                $r,
            )
        );
        $this->renderable(
            fn (MethodNotAllowedHttpException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Method not allowed.',
                JsonResponse::HTTP_METHOD_NOT_ALLOWED,
                $r,
            )
        );
        $this->renderable(
            fn (AuthenticationException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'User not authenticated.',
                JsonResponse::HTTP_UNAUTHORIZED,
                $r,
            )
        );
        $this->renderable(
            fn (AccessDeniedHttpException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Access denied.',
                JsonResponse::HTTP_FORBIDDEN,
                $r,
            )
        );
        $this->renderable(
            fn (ValidationException $e, Request $r): JsonResponse => $this->handleValidationException($e, $r)
        );
        $this->renderable(
            fn (ZeroDeltaException $e, Request $r): JsonResponse => $this->handleDomainException(
                $e,
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
            )
        );

        // -----------------------------------------------------------------------
        // JWT
        // -----------------------------------------------------------------------
        $this->renderable(
            fn (TokenExpiredException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Token expired.',
                JsonResponse::HTTP_UNAUTHORIZED,
                $r,
            )
        );
        $this->renderable(
            fn (TokenInvalidException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'Token invalid.',
                JsonResponse::HTTP_UNAUTHORIZED,
                $r,
            )
        );
        $this->renderable(
            fn (JWTException $e, Request $r): JsonResponse => $this->handleException(
                $e,
                'JWT error.',
                JsonResponse::HTTP_UNAUTHORIZED,
                $r,
            )
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
