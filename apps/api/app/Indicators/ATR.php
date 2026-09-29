<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * Average True Range (ATR)
 *
 * True Range = max(High - Low, |High - PrevClose|, |Low - PrevClose|)
 * Seed ATR   = SMA of first `period` TR values
 * Subsequent = Wilder smoothing: ATR[i] = (ATR[i-1] * (period - 1) + TR[i]) / period
 */
final class ATR implements IndicatorInterface
{
    public function __construct(private readonly int $period = 14) {}

    public function getName(): string
    {
        return "ATR({$this->period})";
    }

    /**
     * @param  array<int, array{open: float, high: float, low: float, close: float, volume: float, timestamp: int}> $candles
     * @return array<int, float|null>
     */
    public function calculate(array $candles): array
    {
        $count  = count($candles);
        $result = array_fill(0, $count, null);

        if ($count < $this->period + 1) {
            return $result;
        }

        // Calculate all True Range values
        $tr = [null]; // index 0 has no previous close
        for ($i = 1; $i < $count; $i++) {
            $high      = (float) $candles[$i]['high'];
            $low       = (float) $candles[$i]['low'];
            $prevClose = (float) $candles[$i - 1]['close'];

            $tr[$i] = max(
                $high - $low,
                abs($high - $prevClose),
                abs($low  - $prevClose),
            );
        }

        // Seed: SMA of first `period` TR values (indices 1..period)
        $seedSlice = array_slice($tr, 1, $this->period);
        $seedAtr   = array_sum($seedSlice) / $this->period;
        $result[$this->period] = $seedAtr;
        $prev = $seedAtr;

        // Wilder smooth
        for ($i = $this->period + 1; $i < $count; $i++) {
            $atr        = ($prev * ($this->period - 1) + $tr[$i]) / $this->period;
            $result[$i] = $atr;
            $prev       = $atr;
        }

        return $result;
    }

    /**
     * Calculate ATR over raw candle arrays and return the result array.
     *
     * @param  array<int, array{open: float, high: float, low: float, close: float}> $candles
     * @return float[]|null[]
     */
    public static function over(array $candles, int $period = 14): array
    {
        return (new self($period))->calculate($candles);
    }
}
