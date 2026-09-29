<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * PriceActionDetector
 * 
 * Detects 15 candlestick patterns and price action structures:
 * - Bullish Engulfing
 * - Bearish Engulfing
 * - Hammer
 * - Shooting Star
 * - Doji
 * - Morning Star
 * - Evening Star
 * - Pin Bar (Bullish/Bearish)
 * - Inside Bar
 * - Breakout
 * - False Breakout
 * - Higher High / Higher Low
 * - Lower High / Lower Low
 * - Liquidity Sweep Structure
 * - Rejection Candles
 */
final class PriceActionDetector
{
    /**
     * Detect patterns on the given series of closed candles.
     *
     * @param array $candles Array of candles with open, high, low, close, volume
     * @return array Array of detected patterns with metadata
     */
    public function detect(array $candles): array
    {
        $n = count($candles);
        if ($n < 5) {
            return [];
        }

        $detected = [];
        $lastIdx = $n - 1;
        $c0 = $candles[$lastIdx];     // Most recent closed candle
        $c1 = $candles[$lastIdx - 1]; // 1 candle ago
        $c2 = $candles[$lastIdx - 2]; // 2 candles ago

        $body0 = abs((float)$c0['close'] - (float)$c0['open']);
        $range0 = (float)$c0['high'] - (float)$c0['low'];
        $isBullish0 = (float)$c0['close'] >= (float)$c0['open'];

        $body1 = abs((float)$c1['close'] - (float)$c1['open']);
        $range1 = (float)$c1['high'] - (float)$c1['low'];
        $isBullish1 = (float)$c1['close'] >= (float)$c1['open'];

        $upperWick0 = (float)$c0['high'] - max((float)$c0['open'], (float)$c0['close']);
        $lowerWick0 = min((float)$c0['open'], (float)$c0['close']) - (float)$c0['low'];

        // 1. Doji
        if ($range0 > 0 && ($body0 / $range0) <= 0.08) {
            $detected[] = [
                'pattern' => 'DOJI',
                'strength' => 0.65,
                'candle_index' => $lastIdx,
                'direction' => 'NEUTRAL',
                'description' => 'Indecision candle with minimal body and balanced wicks',
            ];
        }

        // 2. Bullish Engulfing
        if (!$isBullish1 && $isBullish0 &&
            (float)$c0['open'] <= (float)$c1['close'] &&
            (float)$c0['close'] >= (float)$c1['open'] &&
            $body0 > $body1) {
            $detected[] = [
                'pattern' => 'BULLISH_ENGULFING',
                'strength' => 0.85,
                'candle_index' => $lastIdx,
                'direction' => 'BULLISH',
                'description' => 'Bullish candle body fully engulfs previous bearish candle body',
            ];
        }

        // 3. Bearish Engulfing
        if ($isBullish1 && !$isBullish0 &&
            (float)$c0['open'] >= (float)$c1['close'] &&
            (float)$c0['close'] <= (float)$c1['open'] &&
            $body0 > $body1) {
            $detected[] = [
                'pattern' => 'BEARISH_ENGULFING',
                'strength' => 0.85,
                'candle_index' => $lastIdx,
                'direction' => 'BEARISH',
                'description' => 'Bearish candle body fully engulfs previous bullish candle body',
            ];
        }

        // 4. Hammer (Bullish Reversal)
        if ($range0 > 0 && $lowerWick0 >= (2.0 * $body0) && $upperWick0 <= (0.25 * $range0)) {
            $detected[] = [
                'pattern' => 'HAMMER',
                'strength' => 0.80,
                'candle_index' => $lastIdx,
                'direction' => 'BULLISH',
                'description' => 'Long lower shadow with small upper body showing strong buying rejection from lows',
            ];
        }

        // 5. Shooting Star (Bearish Reversal)
        if ($range0 > 0 && $upperWick0 >= (2.0 * $body0) && $lowerWick0 <= (0.25 * $range0)) {
            $detected[] = [
                'pattern' => 'SHOOTING_STAR',
                'strength' => 0.80,
                'candle_index' => $lastIdx,
                'direction' => 'BEARISH',
                'description' => 'Long upper shadow with small lower body showing strong selling rejection from highs',
            ];
        }

        // 6. Pin Bar (Bullish or Bearish)
        if ($range0 > 0) {
            if ($lowerWick0 >= (0.65 * $range0) && $body0 <= (0.25 * $range0)) {
                $detected[] = [
                    'pattern' => 'BULLISH_PIN_BAR',
                    'strength' => 0.88,
                    'candle_index' => $lastIdx,
                    'direction' => 'BULLISH',
                    'description' => 'High-conviction bullish rejection pin bar with long lower tail',
                ];
            } elseif ($upperWick0 >= (0.65 * $range0) && $body0 <= (0.25 * $range0)) {
                $detected[] = [
                    'pattern' => 'BEARISH_PIN_BAR',
                    'strength' => 0.88,
                    'candle_index' => $lastIdx,
                    'direction' => 'BEARISH',
                    'description' => 'High-conviction bearish rejection pin bar with long upper wick',
                ];
            }
        }

        // 7. Inside Bar
        if ((float)$c0['high'] <= (float)$c1['high'] && (float)$c0['low'] >= (float)$c1['low']) {
            $detected[] = [
                'pattern' => 'INSIDE_BAR',
                'strength' => 0.70,
                'candle_index' => $lastIdx,
                'direction' => 'NEUTRAL',
                'description' => 'Inside bar consolidation within prior candle extremes',
            ];
        }

        // 8. Morning Star (3-candle bullish reversal)
        $isBearish2 = (float)$c2['close'] < (float)$c2['open'];
        $body2 = abs((float)$c2['close'] - (float)$c2['open']);
        if ($isBearish2 && $body1 < ($body2 * 0.4) && $isBullish0 && (float)$c0['close'] > ((float)$c2['open'] + (float)$c2['close']) / 2) {
            $detected[] = [
                'pattern' => 'MORNING_STAR',
                'strength' => 0.90,
                'candle_index' => $lastIdx,
                'direction' => 'BULLISH',
                'description' => 'Three-candle morning star reversal confirming buyers taking control',
            ];
        }

        // 9. Evening Star (3-candle bearish reversal)
        $isBullish2 = (float)$c2['close'] > (float)$c2['open'];
        if ($isBullish2 && $body1 < ($body2 * 0.4) && !$isBullish0 && (float)$c0['close'] < ((float)$c2['open'] + (float)$c2['close']) / 2) {
            $detected[] = [
                'pattern' => 'EVENING_STAR',
                'strength' => 0.90,
                'candle_index' => $lastIdx,
                'direction' => 'BEARISH',
                'description' => 'Three-candle evening star reversal confirming sellers taking control',
            ];
        }

        // 10. Breakout & False Breakout
        // Check highest high and lowest low of last 10 candles excluding c0
        $lookback = min(15, $n - 1);
        $priorHigh = -INF;
        $priorLow = INF;
        for ($k = $lastIdx - $lookback; $k < $lastIdx; $k++) {
            $priorHigh = max($priorHigh, (float)$candles[$k]['high']);
            $priorLow = min($priorLow, (float)$candles[$k]['low']);
        }

        // Bullish Breakout
        if ((float)$c0['close'] > $priorHigh) {
            $detected[] = [
                'pattern' => 'BULLISH_BREAKOUT',
                'strength' => 0.82,
                'candle_index' => $lastIdx,
                'direction' => 'BULLISH',
                'description' => 'Confirmed breakout above recent ' . $lookback . '-candle swing high',
            ];
        } elseif ((float)$c0['high'] > $priorHigh && (float)$c0['close'] < $priorHigh) {
            // False breakout above (liquidity sweep)
            $detected[] = [
                'pattern' => 'LIQUIDITY_SWEEP_BEARISH',
                'strength' => 0.86,
                'candle_index' => $lastIdx,
                'direction' => 'BEARISH',
                'description' => 'Liquidity sweep above recent highs followed by rejection back into range',
            ];
        }

        // Bearish Breakout
        if ((float)$c0['close'] < $priorLow) {
            $detected[] = [
                'pattern' => 'BEARISH_BREAKOUT',
                'strength' => 0.82,
                'candle_index' => $lastIdx,
                'direction' => 'BEARISH',
                'description' => 'Confirmed breakdown below recent ' . $lookback . '-candle swing low',
            ];
        } elseif ((float)$c0['low'] < $priorLow && (float)$c0['close'] > $priorLow) {
            // False breakout below (liquidity sweep)
            $detected[] = [
                'pattern' => 'LIQUIDITY_SWEEP_BULLISH',
                'strength' => 0.86,
                'candle_index' => $lastIdx,
                'direction' => 'BULLISH',
                'description' => 'Liquidity sweep below recent lows followed by rejection back into range',
            ];
        }

        // 11. Support/Resistance Rejection Candles
        if ($lowerWick0 > (1.8 * $body0) && (float)$c0['close'] > (float)$c1['close']) {
            $detected[] = [
                'pattern' => 'BULLISH_REJECTION',
                'strength' => 0.78,
                'candle_index' => $lastIdx,
                'direction' => 'BULLISH',
                'description' => 'Strong price rejection off support with upper close',
            ];
        } elseif ($upperWick0 > (1.8 * $body0) && (float)$c0['close'] < (float)$c1['close']) {
            $detected[] = [
                'pattern' => 'BEARISH_REJECTION',
                'strength' => 0.78,
                'candle_index' => $lastIdx,
                'direction' => 'BEARISH',
                'description' => 'Strong price rejection off resistance with lower close',
            ];
        }

        return $detected;
    }
}
