<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Relative Strength Index (RSI)
 *
 * Uses Wilder smoothing method (also called Modified Moving Average):
 *   Initial avg_gain / avg_loss = SMA of first `period` up/down moves
 *   Subsequent: smoothed_avg = (prev_avg * (period - 1) + current_value) / period
 *   RS  = smoothed_avg_gain / smoothed_avg_loss
 *   RSI = 100 - (100 / (1 + RS))
 */
final class RSI implements IndicatorInterface
{
    public function __construct(private readonly int $period = 14) {}

    public function getName(): string
    {
        return "RSI({$this->period})";
    }

    /**
     * @param  array<int, array{open: float, high: float, low: float, close: float, volume: float, timestamp: int}> $candles
     * @return array<int, float|null>
     */
    public function calculate(array $candles): array
    {
        $closes = array_column($candles, 'close');
        return self::over($closes, $this->period);
    }

    /**
     * Calculate RSI over a flat array of close prices.
     *
     * @param  float[] $closes
     * @return float[]|null[]
     */
    public static function over(array $closes, int $period = 14): array
    {
        $count  = count($closes);
        $result = array_fill(0, $count, null);

        if ($count <= $period) {
            return $result;
        }

        // Calculate initial gains/losses
        $gains  = [];
        $losses = [];
        for ($i = 1; $i <= $period; $i++) {
            $change = $closes[$i] - $closes[$i - 1];
            $gains[]  = max(0.0, $change);
            $losses[] = max(0.0, -$change);
        }

        // Seed with SMA of first period
        $avgGain = array_sum($gains) / $period;
        $avgLoss = array_sum($losses) / $period;

        $result[$period] = self::rsiFromAvg($avgGain, $avgLoss);

        // Apply Wilder smoothing for subsequent values
        for ($i = $period + 1; $i < $count; $i++) {
            $change  = $closes[$i] - $closes[$i - 1];
            $gain    = max(0.0, $change);
            $loss    = max(0.0, -$change);

            $avgGain = ($avgGain * ($period - 1) + $gain) / $period;
            $avgLoss = ($avgLoss * ($period - 1) + $loss) / $period;

            $result[$i] = self::rsiFromAvg($avgGain, $avgLoss);
        }

        return $result;
    }

    private static function rsiFromAvg(float $avgGain, float $avgLoss): float
    {
        if ($avgLoss < 1e-10) {
            return 100.0;
        }
        $rs = $avgGain / $avgLoss;
        return 100.0 - (100.0 / (1.0 + $rs));
    }
}
