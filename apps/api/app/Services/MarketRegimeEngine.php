<?php

declare(strict_types=1);

namespace App\Services;

/**
 * MarketRegimeEngine
 * 
 * Determines the current statistical state of the market:
 * - TRENDING_BULLISH
 * - TRENDING_BEARISH
 * - RANGING
 * - HIGH_VOLATILITY
 * - LOW_VOLATILITY
 * - UNCERTAIN
 */
final class MarketRegimeEngine
{
    public function detect(array $candles, array $indicators): string
    {
        $n = count($candles);
        if ($n < 30) {
            return 'UNCERTAIN';
        }

        $adx = $indicators['adx']['adx'][$n - 1] ?? null;
        $plusDi = $indicators['adx']['plus_di'][$n - 1] ?? null;
        $minusDi = $indicators['adx']['minus_di'][$n - 1] ?? null;

        $ema20 = $indicators['ema20'][$n - 1] ?? null;
        $ema50 = $indicators['ema50'][$n - 1] ?? null;
        $ema200 = $indicators['ema200'][$n - 1] ?? null;
        $close = (float)$candles[$n - 1]['close'];

        $atr = $indicators['atr'][$n - 1] ?? null;
        $bbBandwidth = $indicators['bollinger']['bandwidth'][$n - 1] ?? null;

        // Calculate average ATR over last 20 periods if available
        $avgAtr = 0.0;
        $atrCount = 0;
        if (!empty($indicators['atr'])) {
            $atrSlice = array_filter(array_slice($indicators['atr'], -20), fn($v) => $v !== null);
            if (!empty($atrSlice)) {
                $avgAtr = array_sum($atrSlice) / count($atrSlice);
                $atrCount = count($atrSlice);
            }
        }

        // 1. Check for extreme volatility anomaly
        if ($atrCount > 10 && $atr !== null && $avgAtr > 0) {
            if ($atr > ($avgAtr * 2.2)) {
                return 'HIGH_VOLATILITY';
            }
            if ($atr < ($avgAtr * 0.45)) {
                return 'LOW_VOLATILITY';
            }
        }

        // 2. Strong Trend Detection
        if ($adx !== null && $adx >= 24) {
            if ($ema20 !== null && $ema50 !== null) {
                if ($close > $ema20 && $ema20 > $ema50 && ($plusDi === null || $plusDi > $minusDi)) {
                    return 'TRENDING_BULLISH';
                }
                if ($close < $ema20 && $ema20 < $ema50 && ($minusDi === null || $minusDi > $plusDi)) {
                    return 'TRENDING_BEARISH';
                }
            }
        }

        // 3. Ranging Detection
        if (($adx !== null && $adx < 20) || ($bbBandwidth !== null && $bbBandwidth < 0.003)) {
            return 'RANGING';
        }

        // 4. Default to UNCERTAIN if conditions conflict
        return 'UNCERTAIN';
    }
}
