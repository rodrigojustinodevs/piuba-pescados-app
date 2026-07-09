<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use App\Domain\Models\WaterQuality;

final class WaterQualityScore
{
    private int $excellent = 0;

    private int $good = 0;

    private int $warning = 0;

    private int $critical = 0;

    /**
     * @param array<string, int> $counts resultado de countByQuality() do repositório
     */
    public static function fromCounts(array $counts): self
    {
        $instance            = new self();
        $instance->excellent = (int) ($counts['excellent'] ?? 0);
        $instance->good      = (int) ($counts['good'] ?? 0);
        $instance->warning   = (int) ($counts['warning'] ?? 0);
        $instance->critical  = (int) ($counts['critical'] ?? 0);

        return $instance;
    }

    public function add(WaterQuality $record): void
    {
        match ($this->bucketFor($record)) {
            'critical' => $this->critical++,
            'warning'  => $this->warning++,
            'good'     => $this->good++,
            default    => $this->excellent++,
        };
    }

    public function score(): float
    {
        $total = $this->total();

        if ($total === 0) {
            return 0.0;
        }

        return round(
            (($this->excellent * 100) + ($this->good * 75) + ($this->warning * 40) + ($this->critical * 10))
            / $total,
            2
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'score'     => $this->score(),
            'excellent' => $this->excellent,
            'good'      => $this->good,
            'warning'   => $this->warning,
            'critical'  => $this->critical,
        ];
    }

    private function total(): int
    {
        return $this->excellent + $this->good + $this->warning + $this->critical;
    }

    /**
     * Lógica de severidade:
     *
     *   0 alertas                              → excellent
     *   1 alerta qualquer                      → good
     *   2 alertas e nenhum letal              → warning
     *   qualquer alerta letal (ammonia/O2)    → critical (independente da contagem)
     *
     * Diferença do comportamento anterior:
     *   Antes: 'good' e 'warning' só apareciam quando o único problema era ph/temperature
     *   sem nenhum alerta letal — cenário raro na aquicultura real.
     *
     *   Agora: a presença de qualquer alerta letal → critical imediatamente.
     *   Sem alertas letais → gradação por contagem (0 = excellent, 1 = good, 2+ = warning).
     *   Isso torna 'good' e 'warning' alcançáveis para registros com problemas leves
     *   de ph ou temperatura sem comprometimento de O2/ammonia.
     */
    private function bucketFor(WaterQuality $record): string
    {
        $alerts = WaterQualityThresholds::evaluate(
            ph:              $record->ph !== null ? (float) $record->ph : null,
            dissolvedOxygen: $record->dissolved_oxygen !== null ? (float) $record->dissolved_oxygen : null,
            ammonia:         $record->ammonia !== null ? (float) $record->ammonia : null,
            temperature:     $record->temperature !== null ? (float) $record->temperature : null,
        );

        if ($alerts === []) {
            return 'excellent';
        }

        // Parâmetros letais: comprometimento imediato da sobrevivência do peixe
        $hasLethal = in_array('ammonia', $alerts, strict: true)
                  || in_array('dissolved_oxygen', $alerts, strict: true);

        if ($hasLethal) {
            return 'critical';
        }

        // Sem letais: severidade proporcional ao número de parâmetros fora da faixa
        return count($alerts) >= 2 ? 'warning' : 'good';
    }
}
