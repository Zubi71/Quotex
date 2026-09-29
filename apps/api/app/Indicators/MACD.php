<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * MACD – Moving Average Convergence Divergence
 *
 * MACD Line  = EMA(fastPeriod) - EMA(slowPeriod)
 * Signal Line = EMA(signalPeriod) of MACD Line
 * Histogram  = MACD Line - Signal Line
 */
final class MACD implements IndicatorInterface
{
    public function __construct(
        private readonly int $fastPeriod   = 12,
        private readonly int $slowPeriod   = 26,
        private readonly int $signalPeriod = 9,
    ) {}

    public function getName(): string
    {
        return "MACD({$this->fastPeriod},{$this->slowPeriod},{$this->signalPeriod})";
    }

    /**
     * @param  array<int, array{open: float, high: float, low: float, close: float, volume: float, timestamp: int}> $candles
     * @return array{macd: float[]|null[], signal: float[]|null[], histogram: float[]|null[]}
     */
    public function calculate(array $candles): array
    {
        $count  = count($candles);
        $closes = array_column($candles, 'close');

        $fastEma = EMA::over($closes, $this->fastPeriod);
        $slowEma = EMA::over($closes, $this->slowPeriod);

        // MACD Line = Fast EMA - Slow EMA (null where either is null)
        $macdLine = [];
        for ($i = 0; $i < $count; $i++) {
            $macdLine[$i] = ($fastEma[$i] !== null && $slowEma[$i] !== null)
                ? $fastEma[$i] - $slowEma[$i]
                : null;
        }

        // Signal line = EMA of macd line values (only non-null ones)
        // Build a contiguous slice of macd values starting from first non-null
        $firstNonNull = $this->slowPeriod - 1; // First place slow EMA is valid
        $macdValues   = [];
        for ($i = $firstNonNull; $i < $count; $i++) {
            $macdValues[] = $macdLine[$i] ?? 0.0;
        }

        $signalValues = EMA::over($macdValues, $this->signalPeriod);

        // Map signal values back to full array
        $signalLine = array_fill(0, $count, null);
        $offset     = $firstNonNull;
        foreach ($signalValues as $j => $val) {
            $signalLine[$offset + $j] = $val;
        }

        // Histogram
        $histogram = [];
        for ($i = 0; $i < $count; $i++) {
            $histogram[$i] = ($macdLine[$i] !== null && $signalLine[$i] !== null)
                ? $macdLine[$i] - $signalLine[$i]
                : null;
        }

        return [
            'macd'      => $macdLine,
            'signal'    => $signalLine,
            'histogram' => $histogram,
        ];
    }
}
