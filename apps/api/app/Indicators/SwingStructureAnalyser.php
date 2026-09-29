<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * SwingStructureAnalyser
 * Identifies swing highs, swing lows, and trend structure (UPTREND, DOWNTREND, RANGING).
 * Uses fractal-based peak and valley detection with configurable lookback.
 */
final class SwingStructureAnalyser
{
    public function __construct(private readonly int $pivotLookback = 3) {}

    public function analyse(array $candles): array
    {
        $n = count($candles);
        if ($n < ($this->pivotLookback * 2 + 1)) {
            return [
                'swing_highs' => [],
                'swing_lows' => [],
                'structure' => 'RANGING',
                'higher_highs' => false,
                'higher_lows' => false,
                'lower_highs' => false,
                'lower_lows' => false,
            ];
        }

        $swingHighs = [];
        $swingLows = [];

        for ($i = $this->pivotLookback; $i < $n - $this->pivotLookback; $i++) {
            $isHigh = true;
            $isLow = true;
            $currHigh = (float)$candles[$i]['high'];
            $currLow = (float)$candles[$i]['low'];

            for ($j = 1; $j <= $this->pivotLookback; $j++) {
                if ((float)$candles[$i - $j]['high'] >= $currHigh || (float)$candles[$i + $j]['high'] >= $currHigh) {
                    $isHigh = false;
                }
                if ((float)$candles[$i - $j]['low'] <= $currLow || (float)$candles[$i + $j]['low'] <= $currLow) {
                    $isLow = false;
                }
            }

            if ($isHigh) {
                $swingHighs[] = [
                    'index' => $i,
                    'price' => $currHigh,
                    'timestamp' => $candles[$i]['timestamp'] ?? null,
                ];
            }
            if ($isLow) {
                $swingLows[] = [
                    'index' => $i,
                    'price' => $currLow,
                    'timestamp' => $candles[$i]['timestamp'] ?? null,
                ];
            }
        }

        $numHighs = count($swingHighs);
        $numLows = count($swingLows);

        $higherHighs = false;
        $higherLows = false;
        $lowerHighs = false;
        $lowerLows = false;

        if ($numHighs >= 2) {
            $lastHigh = $swingHighs[$numHighs - 1]['price'];
            $prevHigh = $swingHighs[$numHighs - 2]['price'];
            $higherHighs = $lastHigh > $prevHigh;
            $lowerHighs = $lastHigh < $prevHigh;
        }

        if ($numLows >= 2) {
            $lastLow = $swingLows[$numLows - 1]['price'];
            $prevLow = $swingLows[$numLows - 2]['price'];
            $higherLows = $lastLow > $prevLow;
            $lowerLows = $lastLow < $prevLow;
        }

        $structure = 'RANGING';
        if ($higherHighs && $higherLows) {
            $structure = 'UPTREND';
        } elseif ($lowerHighs && $lowerLows) {
            $structure = 'DOWNTREND';
        }

        return [
            'swing_highs' => $swingHighs,
            'swing_lows' => $swingLows,
            'structure' => $structure,
            'higher_highs' => $higherHighs,
            'higher_lows' => $higherLows,
            'lower_highs' => $lowerHighs,
            'lower_lows' => $lowerLows,
        ];
    }
}
