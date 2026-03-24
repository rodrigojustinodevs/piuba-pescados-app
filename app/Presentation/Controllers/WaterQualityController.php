<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\UseCases\WaterQuality\CreateWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\DeleteWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\ListWaterQualitiesUseCase;
use App\Application\UseCases\WaterQuality\ShowWaterQualityUseCase;
use App\Application\UseCases\WaterQuality\UpdateWaterQualityUseCase;
use App\Presentation\Requests\WaterQuality\WaterQualityStoreRequest;
use App\Presentation\Requests\WaterQuality\WaterQualityUpdateRequest;
use App\Presentation\Resources\WaterQuality\WaterQualityResource;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WaterQualityController
{
    /**
     * Display a listing of water quality records.
     */
    public function index(
        Request $request,
        ListWaterQualitiesUseCase $useCase,
    ): JsonResponse {
        $paginator = $useCase->execute(
            filters: $request->only(['tank_id', 'date_from', 'date_to', 'per_page', 'page']),
        );

        return ApiResponse::success(
            data:       WaterQualityResource::collection($paginator->items()),
            pagination: [
                'total'        => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'first_page'   => $paginator->firstPage(),
                'per_page'     => $paginator->perPage(),
            ],
        );
    }

    /**
     * Display the specified water quality record.
     */
    public function show(string $id, ShowWaterQualityUseCase $useCase): JsonResponse
    {
        $record = $useCase->execute($id);

        return ApiResponse::success(
            data: new WaterQualityResource($record->loadMissing('tank')),
        );
    }

    /**
     * Store a newly created water quality record.
     */
    public function store(WaterQualityStoreRequest $request, CreateWaterQualityUseCase $useCase): JsonResponse
    {
        $record = $useCase->execute($request->validated());

        return ApiResponse::created(
            data:    new WaterQualityResource($record),
            message: 'Water quality record created successfully.',
        );
    }

    /**
     * Update the specified water quality record.
     */
    public function update(
        WaterQualityUpdateRequest $request,
        string $id,
        UpdateWaterQualityUseCase $useCase
    ): JsonResponse {
        $record = $useCase->execute($id, $request->validated());

        return ApiResponse::success(
            data:    new WaterQualityResource($record),
            message: 'Water quality record updated successfully.',
        );
    }

    /**
     * Remove the specified water quality record.
     */
    public function destroy(string $id, DeleteWaterQualityUseCase $useCase): JsonResponse
    {
        $useCase->execute($id);

        return ApiResponse::success(message: 'Water quality record deleted successfully.');
    }
}
