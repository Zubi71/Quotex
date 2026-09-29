<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Rate of Change (ROC) Calculator.
 * ROC = ((Close[i] - Close[i - period]) / Close[i - period]) * 100
 */
final class ROC
{
    public function __construct(private readonly int $period = 12) {}

    public function calculate(array $candles): array
    {
        $n = count($candles);
        $result = array_fill(0, $n, null);

        if ($n <= $this->period) {
            return $result;
        }

        for ($i = $this->period; $i < $n; $i++) {
            $prevClose = (float)$candles[$i - $this->period]['close'];
            $currClose = (float)$candles[$i]['close'];

            if ($prevClose != 0.0) {
                $result[$i] = round((($currClose - $prevClose) / $prevClose) * 100, 3);
            } else {
                $result[$i] = 0.0;
            }
        }

        return $result;
    }
}
