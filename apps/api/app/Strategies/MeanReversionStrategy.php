<?php

declare(strict_types=1);

namespace App\Strategies;

use App\Strategies\Contracts\StrategyInterface;
use App\Strategies\Contracts\StrategyResult;

final class MeanReversionStrategy implements StrategyInterface
{
    public function getName(): string
    {
        return 'Bollinger & Oscillator Mean Reversion';
    }

    public function getSlug(): string
    {
        return 'mean_reversion';
    }

    public function getDefaultWeight(): float
    {
        return 12.0;
    }

    public function analyse(array $candles, array $indicators, array $context): StrategyResult
    {
        $n = count($candles);
        if ($n < 25) {
            return new StrategyResult('NEUTRAL', 0, $this->getDefaultWeight(), [], ['Insufficient data for mean reversion'], false);
        }

        $lastClose = (float)$candles[$n - 1]['close'];
        $lastLow = (float)$candles[$n - 1]['low'];
        $lastHigh = (float)$candles[$n - 1]['high'];

        $bbUpper = $indicators['bollinger']['upper'][$n - 1] ?? null;
        $bbLower = $indicators['bollinger']['lower'][$n - 1] ?? null;
        $bbMiddle = $indicators['bollinger']['middle'][$n - 1] ?? null;
        $percentB = $indicators['bollinger']['percent_b'][$n - 1] ?? null;
        $rsi = $indicators['rsi'][$n - 1] ?? null;
        $cci = $indicators['cci'][$n - 1] ?? null;
        $williamsR = $indicators['williams_r'][$n - 1] ?? null;

        $reasons = [];
        $warnings = [];
        $direction = 'NEUTRAL';
        $score = 50.0;

        // Bullish mean reversion: oversold extremes with rejection
        $isOversold = ($percentB !== null && $percentB <= 0.05) ||
                      ($lastLow <= ($bbLower ?? -INF)) ||
                      ($rsi !== null && $rsi <= 28) ||
                      ($cci !== null && $cci <= -120) ||
                      ($williamsR !== null && $williamsR <= -85);

        // Bearish mean reversion: overbought extremes with rejection
        $isOverbought = ($percentB !== null && $percentB >= 0.95) ||
                        ($lastHigh >= ($bbUpper ?? INF)) ||
                        ($rsi !== null && $rsi >= 72) ||
                        ($cci !== null && $cci >= 120) ||
                        ($williamsR !== null && $williamsR >= -15);

        if ($isOversold && !$isOverbought) {
            $direction = 'CALL';
            $score = 70.0;
            $reasons[] = 'Price stretched to extreme oversold Bollinger band / CCI channel';

            if ($rsi !== null && $rsi < 30) {
                $score += 10.0;
                $reasons[] = 'RSI confirmed oversold exhaustion (' . round($rsi, 1) . ')';
            }
            if ($lastClose > $lastLow) {
                $score += 8.0;
                $reasons[] = 'Lower shadow indicates buying reaction at band extreme';
            }
        } elseif ($isOverbought && !$isOversold) {
            $direction = 'PUT';
            $score = 70.0;
            $reasons[] = 'Price stretched to extreme overbought Bollinger band / CCI channel';

            if ($rsi !== null && $rsi > 70) {
                $score += 10.0;
                $reasons[] = 'RSI confirmed overbought exhaustion (' . round($rsi, 1) . ')';
            }
            if ($lastClose < $lastHigh) {
                $score += 8.0;
                $reasons[] = 'Upper shadow indicates selling reaction at band extreme';
            }
        } else {
            $score = 40.0;
        }

        $regime = $context['market_regime'] ?? 'RANGING';
        if (str_contains($regime, 'TRENDING')) {
            $score -= 15.0;
            $warnings[] = 'Caution: mean reversion counter-trading against a trending market carries higher risk';
        }

        $score = min(95.0, max(20.0, $score));
        return new StrategyResult($direction, $score, $this->getDefaultWeight(), $reasons, $warnings, true);
    }
}
