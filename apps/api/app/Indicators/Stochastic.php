<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Stochastic Oscillator
 *
 * Raw %K = (Close - Lowest Low of period) / (Highest High of period - Lowest Low) * 100
 * Slow %K = SMA(Raw %K, smooth) — also called %K in most charting software
 * %D       = SMA(Slow %K, dPeriod)
 */
final class Stochastic implements IndicatorInterface
{
    public function __construct(
        private readonly int $kPeriod  = 14,
        private readonly int $dPeriod  = 3,
        private readonly int $smooth   = 3,
    ) {}

    public function getName(): string
    {
        return "Stoch({$this->kPeriod},{$this->dPeriod},{$this->smooth})";
    }

    /**
     * @param  array<int, array{open: float, high: float, low: float, close: float, volume: float, timestamp: int}> $candles
     * @return array{k: float[]|null[], d: float[]|null[]}
     */
    public function calculate(array $candles): array
    {
        $count = count($candles);
        $rawK  = array_fill(0, $count, null);
        $kLine = array_fill(0, $count, null);
        $dLine = array_fill(0, $count, null);

        if ($count < $this->kPeriod) {
            return ['k' => $kLine, 'd' => $dLine];
        }

        // Step 1: Raw %K
        for ($i = $this->kPeriod - 1; $i < $count; $i++) {
            $slice = array_slice($candles, $i - $this->kPeriod + 1, $this->kPeriod);
            $highs = array_column($slice, 'high');
            $lows  = array_column($slice, 'low');

            $hh    = max($highs);
            $ll    = min($lows);
            $range = $hh - $ll;

            $rawK[$i] = ($range > 1e-10)
                ? (((float) $candles[$i]['close'] - $ll) / $range) * 100.0
                : 50.0;
        }

        // Step 2: Slow %K = SMA(rawK, smooth)
        $rawKFiltered = array_filter($rawK, fn($v) => $v !== null);
        $rawKValues   = array_values($rawKFiltered);
        $slowK        = SMA::over($rawKValues, $this->smooth);

        // Map back to full array
        $firstValidK = $this->kPeriod - 1;
        foreach ($slowK as $j => $val) {
            if ($val !== null) {
                $kLine[$firstValidK + $j] = $val;
            }
        }

        // Step 3: %D = SMA(slowK, dPeriod)
        $slowKValues  = array_values(array_filter($kLine, fn($v) => $v !== null));
        $dValues      = SMA::over($slowKValues, $this->dPeriod);

        $kValidIndices = array_keys(array_filter($kLine, fn($v) => $v !== null));
        $firstValidD   = reset($kValidIndices);

        foreach ($dValues as $j => $val) {
            if ($val !== null) {
                $dLine[$firstValidD + $j] = $val;
            }
        }

        return ['k' => $kLine, 'd' => $dLine];
    }
}
