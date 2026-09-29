<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Commodity Channel Index (CCI) Calculator.
 * Typical Price (TP) = (High + Low + Close) / 3
 * SMA_TP = SMA of TP over period
 * Mean Deviation = average of |TP - SMA_TP|
 * CCI = (TP - SMA_TP) / (0.015 * Mean Deviation)
 */
final class CCI
{
    public function __construct(private readonly int $period = 20) {}

    public function calculate(array $candles): array
    {
        $n = count($candles);
        $result = array_fill(0, $n, null);

        if ($n < $this->period) {
            return $result;
        }

        $tp = [];
        for ($i = 0; $i < $n; $i++) {
            $tp[$i] = ((float)$candles[$i]['high'] + (float)$candles[$i]['low'] + (float)$candles[$i]['close']) / 3.0;
        }

        for ($i = $this->period - 1; $i < $n; $i++) {
            $sum = 0.0;
            for ($j = $i - $this->period + 1; $j <= $i; $j++) {
                $sum += $tp[$j];
            }
            $smaTp = $sum / $this->period;

            $devSum = 0.0;
            for ($j = $i - $this->period + 1; $j <= $i; $j++) {
                $devSum += abs($tp[$j] - $smaTp);
            }
            $meanDev = $devSum / $this->period;

            if ($meanDev == 0.0) {
                $result[$i] = 0.0;
            } else {
                $result[$i] = round(($tp[$i] - $smaTp) / (0.015 * $meanDev), 2);
            }
        }

        return $result;
    }
}
