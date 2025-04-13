<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\FinancialTransactionDTO;
use App\Application\Services\FinancialTransactionService;
use App\Presentation\Requests\FinancialTransaction\FinancialTransactionStoreRequest;
use App\Presentation\Requests\FinancialTransaction\FinancialTransactionUpdateRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class FinancialTransactionController
{
    public function __construct(
        protected FinancialTransactionService $financialTransactionService
    ) {
    }

    /**
     * Display a listing of financial transactions.
     */
    public function index(): JsonResponse
    {
        try {
            $transactions = $this->financialTransactionService->showAllFinancialTransactions();
            $data         = $transactions->toArray(request());
            $pagination   = $transactions->additional['pagination'] ?? null;

            return ApiResponse::success($data, Response::HTTP_OK, 'Success', $pagination);
        } catch (Throwable $exception) {
            return ApiResponse::error($exception);
        }
    }

    /**
     * Display the specified financial transaction.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $transaction = $this->financialTransactionService->showFinancialTransaction($id);

            if (! $transaction instanceof FinancialTransactionDTO || $transaction->isEmpty()) {
                return ApiResponse::error(
                    null,
                    'Financial transaction not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            return ApiResponse::success($transaction->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                'Financial transaction not found',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Store a newly created financial transaction.
     */
    public function store(FinancialTransactionStoreRequest $request): JsonResponse
    {
        try {
            $transaction = $this->financialTransactionService->create($request->validated());

            return ApiResponse::created($transaction->toArray());
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Update the specified financial transaction.
     */
    public function update(FinancialTransactionUpdateRequest $request, string $id): JsonResponse
    {
        try {
            $transaction = $this->financialTransactionService->updateFinancialTransaction($id, $request->validated());

            return ApiResponse::success($transaction->toArray(), Response::HTTP_OK, 'Success');
        } catch (Throwable $exception) {
            return ApiResponse::error($exception, $exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified financial transaction.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->financialTransactionService->deleteFinancialTransaction($id);

            if (! $deleted) {
                return ApiResponse::error(
                    null,
                    'Financial transaction not found',
                    Response::HTTP_NOT_FOUND
                );
            }

            return ApiResponse::success(
                null,
                Response::HTTP_OK,
                'Financial transaction successfully deleted'
            );
        } catch (Throwable $exception) {
            return ApiResponse::error(
                $exception,
                'Error deleting financial transaction',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
