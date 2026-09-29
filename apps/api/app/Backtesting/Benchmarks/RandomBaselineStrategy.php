<?php

declare(strict_types=1);

namespace App\Backtesting\Benchmarks;

final class RandomBaselineStrategy
{
    public function evaluate(array $candles): string
    {
        $r = mt_rand(0, 100);
        if ($r < 45) return 'CALL';
        if ($r < 90) return 'PUT';
        return 'NO_TRADE';
    }
}
