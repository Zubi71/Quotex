<?php

declare(strict_types=1);

namespace App\Backtesting\Benchmarks;

use App\Indicators\RSI;

final class SimpleRSIStrategy
{
    public function evaluate(array $candles): string
    {
        $n = count($candles);
        if ($n < 20) return 'NO_TRADE';

        $rsi = (new RSI(14))->calculate($candles);
        $currRsi = $rsi[$n - 1] ?? null;
        $prevRsi = $rsi[$n - 2] ?? null;

        if ($currRsi === null || $prevRsi === null) {
            return 'NO_TRADE';
        }

        if ($prevRsi < 30 && $currRsi >= 30) {
            return 'CALL';
        }
        if ($prevRsi > 70 && $currRsi <= 70) {
            return 'PUT';
        }

        return 'NO_TRADE';
    }
}
