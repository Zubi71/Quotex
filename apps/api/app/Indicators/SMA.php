<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Simple Moving Average (SMA)
 *
 * Algorithm: average of the last N close prices.
 */
final class SMA implements IndicatorInterface
{
    public function __construct(private readonly int $period = 20) {}

    public function getName(): string
    {
        return "SMA({$this->period})";
    }

    /**
     * @param  array<int, array{open: float, high: float, low: float, close: float, volume: float, timestamp: int}> $candles
     * @return array<int, float|null>
     */
    public function calculate(array $candles): array
    {
        $count  = count($candles);
        $result = array_fill(0, $count, null);

        if ($count < $this->period) {
            return $result;
        }

        // Extract closes
        $closes = array_column($candles, 'close');

        for ($i = $this->period - 1; $i < $count; $i++) {
            $slice       = array_slice($closes, $i - $this->period + 1, $this->period);
            $result[$i]  = array_sum($slice) / $this->period;
        }

        return $result;
    }

    /**
     * Helper: calculate SMA over a flat array of float values.
     *
     * @param  float[] $values
     * @return float[]|null[]
     */
    public static function over(array $values, int $period): array
    {
        $count  = count($values);
        $result = array_fill(0, $count, null);

        for ($i = $period - 1; $i < $count; $i++) {
            $slice      = array_slice($values, $i - $period + 1, $period);
            $result[$i] = array_sum($slice) / $period;
        }

        return $result;
    }
}
