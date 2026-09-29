<?php

declare(strict_types=1);

namespace App\Strategies;

use App\Strategies\Contracts\StrategyInterface;
use App\Strategies\Contracts\StrategyResult;

final class MomentumStrategy implements StrategyInterface
{
    public function getName(): string
    {
        return 'Multi-Oscillator Momentum';
    }

    public function getSlug(): string
    {
        return 'momentum';
    }

    public function getDefaultWeight(): float
    {
        return 15.0;
    }

    public function analyse(array $candles, array $indicators, array $context): StrategyResult
    {
        $n = count($candles);
        if ($n < 30) {
            return new StrategyResult('NEUTRAL', 0, $this->getDefaultWeight(), [], ['Insufficient data for momentum'], false);
        }

        $rsi = $indicators['rsi'][$n - 1] ?? null;
        $macdHist = $indicators['macd']['histogram'][$n - 1] ?? null;
        $prevMacdHist = $indicators['macd']['histogram'][$n - 2] ?? null;
        $stochK = $indicators['stochastic']['k'][$n - 1] ?? null;
        $stochD = $indicators['stochastic']['d'][$n - 1] ?? null;
        $roc = $indicators['roc'][$n - 1] ?? null;

        $reasons = [];
        $warnings = [];
        $bullishPoints = 0;
        $bearishPoints = 0;

        // RSI Momentum
        if ($rsi !== null) {
            if ($rsi > 52 && $rsi < 70) {
                $bullishPoints += 2;
                $reasons[] = 'RSI in healthy bullish momentum zone (' . round($rsi, 1) . ')';
            } elseif ($rsi < 48 && $rsi > 30) {
                $bearishPoints += 2;
                $reasons[] = 'RSI in healthy bearish momentum zone (' . round($rsi, 1) . ')';
            } elseif ($rsi >= 70) {
                $warnings[] = 'RSI in overbought territory (' . round($rsi, 1) . ')';
            } elseif ($rsi <= 30) {
                $warnings[] = 'RSI in oversold territory (' . round($rsi, 1) . ')';
            }
        }

        // MACD Histogram
        if ($macdHist !== null) {
            if ($macdHist > 0 && ($prevMacdHist === null || $macdHist >= $prevMacdHist)) {
                $bullishPoints += 2;
                $reasons[] = 'MACD histogram positive and expanding';
            } elseif ($macdHist < 0 && ($prevMacdHist === null || $macdHist <= $prevMacdHist)) {
                $bearishPoints += 2;
                $reasons[] = 'MACD histogram negative and expanding';
            }
        }

        // Stochastic K/D
        if ($stochK !== null && $stochD !== null) {
            if ($stochK > $stochD && $stochK < 80) {
                $bullishPoints += 1;
                $reasons[] = 'Stochastic %K crossed above %D with room to run';
            } elseif ($stochK < $stochD && $stochK > 20) {
                $bearishPoints += 1;
                $reasons[] = 'Stochastic %K crossed below %D with room to decline';
            }
        }

        // ROC
        if ($roc !== null) {
            if ($roc > 0.05) {
                $bullishPoints += 1;
            } elseif ($roc < -0.05) {
                $bearishPoints += 1;
            }
        }

        $direction = 'NEUTRAL';
        $score = 50.0;

        if ($bullishPoints >= 4 && $bullishPoints > $bearishPoints) {
            $direction = 'CALL';
            $score = 65.0 + ($bullishPoints * 5.0);
        } elseif ($bearishPoints >= 4 && $bearishPoints > $bullishPoints) {
            $direction = 'PUT';
            $score = 65.0 + ($bearishPoints * 5.0);
        } else {
            $warnings[] = 'Oscillators show conflicting or muted momentum';
            $score = 45.0;
        }

        $score = min(95.0, max(20.0, $score));
        return new StrategyResult($direction, $score, $this->getDefaultWeight(), $reasons, $warnings, true);
    }
}
