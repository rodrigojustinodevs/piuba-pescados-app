<?php

declare(strict_types=1);


namespace App\Presentation\Controllers;

use App\Application\Services\CompanyService;
use App\Presentation\Requests\CompanyStoreRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController
{

    public function __construct(protected CompanyService $companyService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        dd("sdasd");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyStoreRequest $request): JsonResponse
    {
        dd('ssd');
        try {
            $company = $this->companyService->createCompany($request->validated());

            return ApiResponse::created($company?->toArray());
        } catch (\Exception $exception) {
            return ApiResponse::error(
                [
                    'message' => $exception->getMessage(),
                    'code'    => $exception->getCode(),
                    'file'    => $exception->getFile(),
                    'line'    => $exception->getLine(),
                ],
                $exception->getMessage(),
                $exception->getCode()
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
