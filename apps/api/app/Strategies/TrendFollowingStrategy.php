<?php

declare(strict_types=1);

namespace App\Strategies;

use App\Strategies\Contracts\StrategyInterface;
use App\Strategies\Contracts\StrategyResult;

final class TrendFollowingStrategy implements StrategyInterface
{
    public function getName(): string
    {
        return 'Trend Following Confluence';
    }

    public function getSlug(): string
    {
        return 'trend_following';
    }

    public function getDefaultWeight(): float
    {
        return 20.0;
    }

    public function analyse(array $candles, array $indicators, array $context): StrategyResult
    {
        $n = count($candles);
        if ($n < 50) {
            return new StrategyResult('NEUTRAL', 0, $this->getDefaultWeight(), [], ['Insufficient history for trend analysis'], false);
        }

        $lastClose = (float)$candles[$n - 1]['close'];
        $ema9 = $indicators['ema9'][$n - 1] ?? null;
        $ema20 = $indicators['ema20'][$n - 1] ?? null;
        $ema50 = $indicators['ema50'][$n - 1] ?? null;
        $adx = $indicators['adx']['adx'][$n - 1] ?? null;
        $plusDi = $indicators['adx']['plus_di'][$n - 1] ?? null;
        $minusDi = $indicators['adx']['minus_di'][$n - 1] ?? null;

        if ($ema9 === null || $ema20 === null || $ema50 === null) {
            return new StrategyResult('NEUTRAL', 0, $this->getDefaultWeight(), [], ['EMA values missing'], false);
        }

        $reasons = [];
        $warnings = [];
        $score = 50.0;
        $direction = 'NEUTRAL';

        $isBullishStack = ($lastClose > $ema9) && ($ema9 > $ema20) && ($ema20 > $ema50);
        $isBearishStack = ($lastClose < $ema9) && ($ema9 < $ema20) && ($ema20 < $ema50);

        if ($isBullishStack) {
            $direction = 'CALL';
            $score = 75.0;
            $reasons[] = 'Price aligned above stacked EMAs (Close > EMA9 > EMA20 > EMA50)';

            if ($adx !== null && $adx > 25) {
                $score += 15.0;
                $reasons[] = 'Strong directional trend confirmed by ADX (' . round($adx, 1) . ' > 25)';
            }
            if ($plusDi !== null && $minusDi !== null && $plusDi > $minusDi) {
                $score += 10.0;
                $reasons[] = '+DI dominates -DI indicating bullish pressure';
            }
        } elseif ($isBearishStack) {
            $direction = 'PUT';
            $score = 75.0;
            $reasons[] = 'Price aligned below stacked EMAs (Close < EMA9 < EMA20 < EMA50)';

            if ($adx !== null && $adx > 25) {
                $score += 15.0;
                $reasons[] = 'Strong directional trend confirmed by ADX (' . round($adx, 1) . ' > 25)';
            }
            if ($plusDi !== null && $minusDi !== null && $minusDi > $plusDi) {
                $score += 10.0;
                $reasons[] = '-DI dominates +DI indicating bearish pressure';
            }
        } else {
            $score = 40.0;
            $warnings[] = 'EMAs are entangled or conflicting with price';
        }

        if ($adx !== null && $adx < 20) {
            $score -= 15.0;
            $warnings[] = 'ADX below 20 indicates weak trend or consolidation';
        }

        $finalScore = min(100.0, max(0.0, $score));
        return new StrategyResult($direction, $finalScore, $this->getDefaultWeight(), $reasons, $warnings, true);
    }
}
