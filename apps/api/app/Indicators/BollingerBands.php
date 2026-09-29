<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Bollinger Bands
 *
 * Middle    = SMA(period)
 * StdDev    = rolling population std deviation of close over period
 * Upper     = Middle + (multiplier * StdDev)
 * Lower     = Middle - (multiplier * StdDev)
 * Bandwidth = (Upper - Lower) / Middle
 * %B        = (Close - Lower) / (Upper - Lower)
 */
final class BollingerBands implements IndicatorInterface
{
    public function __construct(
        private readonly int   $period     = 20,
        private readonly float $multiplier = 2.0,
    ) {}

    public function getName(): string
    {
        return "BB({$this->period},{$this->multiplier})";
    }

    /**
     * @param  array<int, array{open: float, high: float, low: float, close: float, volume: float, timestamp: int}> $candles
     * @return array{upper: float[]|null[], middle: float[]|null[], lower: float[]|null[], bandwidth: float[]|null[], percent_b: float[]|null[]}
     */
    public function calculate(array $candles): array
    {
        $count  = count($candles);
        $closes = array_column($candles, 'close');

        $upper     = array_fill(0, $count, null);
        $middle    = array_fill(0, $count, null);
        $lower     = array_fill(0, $count, null);
        $bandwidth = array_fill(0, $count, null);
        $percentB  = array_fill(0, $count, null);

        for ($i = $this->period - 1; $i < $count; $i++) {
            $slice  = array_slice($closes, $i - $this->period + 1, $this->period);
            $sma    = array_sum($slice) / $this->period;
            $stdDev = $this->stdDev($slice, $sma);

            $u = $sma + $this->multiplier * $stdDev;
            $l = $sma - $this->multiplier * $stdDev;

            $middle[$i]    = $sma;
            $upper[$i]     = $u;
            $lower[$i]     = $l;
            $bandwidth[$i] = ($sma > 0.0) ? ($u - $l) / $sma : 0.0;
            $percentB[$i]  = ($u - $l > 1e-10)
                ? ($closes[$i] - $l) / ($u - $l)
                : 0.5;
        }

        return [
            'upper'     => $upper,
            'middle'    => $middle,
            'lower'     => $lower,
            'bandwidth' => $bandwidth,
            'percent_b' => $percentB,
        ];
    }

    /**
     * Population standard deviation.
     *
     * @param float[] $values
     */
    private function stdDev(array $values, float $mean): float
    {
        $n          = count($values);
        $squaredSum = 0.0;
        foreach ($values as $v) {
            $squaredSum += ($v - $mean) ** 2;
        }
        return sqrt($squaredSum / $n);
    }
}
