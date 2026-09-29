<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Exponential Moving Average (EMA)
 *
 * Algorithm:
 *   multiplier = 2 / (period + 1)
 *   EMA[0]     = SMA of first `period` candles
 *   EMA[i]     = close[i] * multiplier + EMA[i-1] * (1 - multiplier)
 */
final class EMA implements IndicatorInterface
{
    public function __construct(private readonly int $period = 20) {}

    public function getName(): string
    {
        return "EMA({$this->period})";
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
     * Calculate EMA over a flat array of float values.
     *
     * @param  float[] $values
     * @return float[]|null[]
     */
    public static function over(array $values, int $period): array
    {
        $count  = count($values);
        $result = array_fill(0, $count, null);

        if ($count < $period) {
            return $result;
        }

        $multiplier = 2.0 / ($period + 1);

        // Seed with SMA of first `period` values
        $seed = array_sum(array_slice($values, 0, $period)) / $period;
        $result[$period - 1] = $seed;
        $prev = $seed;

        for ($i = $period; $i < $count; $i++) {
            $ema        = $values[$i] * $multiplier + $prev * (1.0 - $multiplier);
            $result[$i] = $ema;
            $prev       = $ema;
        }

        return $result;
    }
}
