<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Williams %R Calculator.
 * %R = ((Highest High - Close) / (Highest High - Lowest Low)) * -100
 * Values range between 0 and -100.
 * Above -20: Overbought
 * Below -80: Oversold
 */
final class WilliamsR
{
    public function __construct(private readonly int $period = 14) {}

    public function calculate(array $candles): array
    {
        $n = count($candles);
        $result = array_fill(0, $n, null);

        if ($n < $this->period) {
            return $result;
        }

        for ($i = $this->period - 1; $i < $n; $i++) {
            $highestHigh = -INF;
            $lowestLow = INF;

            for ($j = $i - $this->period + 1; $j <= $i; $j++) {
                $high = (float)$candles[$j]['high'];
                $low = (float)$candles[$j]['low'];
                if ($high > $highestHigh) $highestHigh = $high;
                if ($low < $lowestLow) $lowestLow = $low;
            }

            $close = (float)$candles[$i]['close'];
            $range = $highestHigh - $lowestLow;

            if ($range == 0.0) {
                $result[$i] = -50.0;
            } else {
                $result[$i] = round((($highestHigh - $close) / $range) * -100.0, 2);
            }
        }

        return $result;
    }
}
