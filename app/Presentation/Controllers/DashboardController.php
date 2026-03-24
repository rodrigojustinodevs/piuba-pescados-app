<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Application\DTOs\TankAlertDTO;
use App\Application\DTOs\WaterQualityDataPoint;
use App\Application\DTOs\WaterQualityTrendDTO;
use App\Application\UseCases\Dashboard\GetDashboardAlertsUseCase;
use App\Application\UseCases\Dashboard\GetDashboardSummaryUseCase;
use App\Application\UseCases\Dashboard\GetWaterQualityTrendsUseCase;
use App\Domain\ValueObjects\WaterQualityThresholds;
use App\Presentation\Requests\Dashboard\DashboardTrendsRequest;
use App\Presentation\Response\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Schema(
 *     schema="DashboardSummary",
 *     type="object",
 *     @OA\Property(property="totalTanks", type="integer", example=12),
 *     @OA\Property(property="tanksWithAlerts", type="integer", example=4),
 *     @OA\Property(property="criticalAlerts", type="integer", example=2),
 *     @OA\Property(property="readingsLast24h", type="integer", example=180),
 *     @OA\Property(property="stocksBelowMinimum", type="integer", example=3),
 *     @OA\Property(property="inactiveSensors", type="integer", example=1)
 * )
 *
 * @OA\Schema(
 *     schema="DashboardTankAlert",
 *     type="object",
 *     @OA\Property(property="tankId", type="string", format="uuid"),
 *     @OA\Property(property="tankName", type="string"),
 *     @OA\Property(property="waterQualityAlerts", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="stockAlerts", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="sensorAlerts", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="severity", type="string", example="warning"),
 *     @OA\Property(property="lastMeasuredAt", type="string", format="date-time", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="DashboardTrendPoint",
 *     type="object",
 *     @OA\Property(property="timestamp", type="string", format="date-time"),
 *     @OA\Property(property="avg", type="number", format="float", nullable=true),
 *     @OA\Property(property="min", type="number", format="float", nullable=true),
 *     @OA\Property(property="max", type="number", format="float", nullable=true)
 * )
 */
final class DashboardController
{
    /**
     * @OA\Get(
     *     path="/company/dashboard/summary",
     *     summary="Dashboard summary cards",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Summary loaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dashboard summary retrieved successfully."),
     *             @OA\Property(property="response", ref="#/components/schemas/DashboardSummary")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function summary(GetDashboardSummaryUseCase $useCase): JsonResponse
    {
        $summary = $useCase->execute();

        return ApiResponse::success(
            data:    $summary->toArray(),
            message: 'Dashboard summary retrieved successfully.',
        );
    }

    /**
     * @OA\Get(
     *     path="/company/dashboard/alerts",
     *     summary="Dashboard alerts by tank",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Alerts loaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Dashboard alerts retrieved successfully."),
     *             @OA\Property(
     *                 property="response",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/DashboardTankAlert")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function alerts(GetDashboardAlertsUseCase $useCase): JsonResponse
    {
        $alerts = $useCase->execute();

        return ApiResponse::success(
            data: [
                'items' => array_map(
                    static fn (TankAlertDTO $dto): array => $dto->toArray(),
                    $alerts,
                ),
            ],
            message: 'Dashboard alerts retrieved successfully.',
        );
    }

    /**
     * @OA\Get(
     *     path="/company/dashboard/trends",
     *     summary="Water quality trends for dashboard charts",
     *     tags={"Dashboard"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="tank_id", in="query", @OA\Schema(type="string", format="uuid")),
     *     @OA\Parameter(
     *         name="parameter",
     *         in="query",
     *         @OA\Schema(type="string", enum={"temperature","ph","dissolved_oxygen","ammonia","salinity","turbidity"})
     *     ),
     *     @OA\Parameter(name="period", in="query", @OA\Schema(type="string", enum={"24h","7d","30d"})),
     *     @OA\Parameter(name="granularity", in="query", @OA\Schema(type="string", enum={"hour","day"})),
     *     @OA\Response(
     *         response=200,
     *         description="Trends loaded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Success"),
     *             @OA\Property(
     *                 property="response",
     *                 type="object",
     *                 @OA\Property(property="parameter", type="string", example="temperature"),
     *                 @OA\Property(property="period", type="string", example="7d"),
     *                 @OA\Property(
     *                     property="thresholds",
     *                     type="object",
     *                     @OA\Property(property="min", type="number", format="float", nullable=true),
     *                     @OA\Property(property="max", type="number", format="float", nullable=true)
     *                 ),
     *                 @OA\Property(
     *                     property="tanks",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="tankId", type="string", format="uuid"),
     *                         @OA\Property(property="tankName", type="string"),
     *                         @OA\Property(property="unit", type="string", example="°C"),
     *                         @OA\Property(property="currentValue", type="number", format="float", nullable=true),
     *                         @OA\Property(property="minValue", type="number", format="float", nullable=true),
     *                         @OA\Property(property="maxValue", type="number", format="float", nullable=true),
     *                         @OA\Property(property="avgValue", type="number", format="float", nullable=true),
     *                         @OA\Property(
     *                             property="dataPoints",
     *                             type="array",
     *                             @OA\Items(ref="#/components/schemas/DashboardTrendPoint")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function trends(
        DashboardTrendsRequest $request,
        GetWaterQualityTrendsUseCase $useCase,
    ): JsonResponse {
        $trends = $useCase->execute($request->validated());

        // Inclui os limiares no response para o frontend renderizar linhas de referência
        $thresholds = $this->thresholdsForParameter(
            $request->input('parameter', 'temperature'),
        );

        return ApiResponse::success(
            data: [
                'parameter'  => $request->input('parameter', 'temperature'),
                'period'     => $request->input('period', '7d'),
                'thresholds' => $thresholds,
                'tanks'      => array_map(
                    static fn (WaterQualityTrendDTO $dto): array => [
                        'tankId'       => $dto->tankId,
                        'tankName'     => $dto->tankName,
                        'unit'         => $dto->unit,
                        'currentValue' => $dto->currentValue,
                        'minValue'     => $dto->minValue,
                        'maxValue'     => $dto->maxValue,
                        'avgValue'     => $dto->avgValue,
                        'dataPoints'   => array_map(
                            static fn (WaterQualityDataPoint $p): array => [
                                'timestamp' => $p->timestamp,
                                'avg'       => $p->avg,
                                'min'       => $p->min,
                                'max'       => $p->max,
                            ],
                            $dto->dataPoints,
                        ),
                    ],
                    $trends,
                ),
            ],
        );
    }

    /**
     * Retorna os limiares técnicos do parâmetro para o frontend
     * renderizar linhas de referência nos gráficos (linha vermelha de alerta).
     *
     * @return array<string, float|null>
     */
    private function thresholdsForParameter(string $parameter): array
    {
        return match ($parameter) {
            'ph' => [
                'min' => WaterQualityThresholds::PH_MIN,
                'max' => WaterQualityThresholds::PH_MAX,
            ],
            'dissolved_oxygen' => [
                'min' => WaterQualityThresholds::DISSOLVED_OXYGEN_MIN,
                'max' => null,
            ],
            'ammonia' => [
                'min' => null,
                'max' => WaterQualityThresholds::AMMONIA_MAX,
            ],
            'temperature' => [
                'min' => WaterQualityThresholds::TEMPERATURE_MIN,
                'max' => WaterQualityThresholds::TEMPERATURE_MAX,
            ],
            default => ['min' => null, 'max' => null],
        };
    }
}
