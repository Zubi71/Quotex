<?php

declare(strict_types=1);

namespace App\Backtesting\Benchmarks;

use App\Indicators\EMA;

final class SimpleEMAStrategy
{
    public function evaluate(array $candles): string
    {
        $n = count($candles);
        if ($n < 50) return 'NO_TRADE';

        $ema20 = (new EMA(20))->calculate($candles);
        $ema50 = (new EMA(50))->calculate($candles);

        $currEma20 = $ema20[$n - 1] ?? null;
        $prevEma20 = $ema20[$n - 2] ?? null;
        $currEma50 = $ema50[$n - 1] ?? null;
        $prevEma50 = $ema50[$n - 2] ?? null;

        if ($currEma20 === null || $prevEma20 === null || $currEma50 === null || $prevEma50 === null) {
            return 'NO_TRADE';
        }

        if ($prevEma20 <= $prevEma50 && $currEma20 > $currEma50) {
            return 'CALL';
        }
        if ($prevEma20 >= $prevEma50 && $currEma20 < $currEma50) {
            return 'PUT';
        }

        return 'NO_TRADE';
    }
}
