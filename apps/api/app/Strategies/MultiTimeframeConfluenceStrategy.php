<?php

declare(strict_types=1);

namespace App\Strategies;

use App\Strategies\Contracts\StrategyInterface;
use App\Strategies\Contracts\StrategyResult;

final class MultiTimeframeConfluenceStrategy implements StrategyInterface
{
    public function getName(): string
    {
        return 'Multi-Timeframe Trend Confluence';
    }

    public function getSlug(): string
    {
        return 'mtf_confluence';
    }

    public function getDefaultWeight(): float
    {
        return 15.0;
    }

    public function analyse(array $candles, array $indicators, array $context): StrategyResult
    {
        $higherTfCandles = $context['higher_tf_candles'] ?? [];
        $higherTfIndicators = $context['higher_tf_indicators'] ?? [];

        if (empty($higherTfCandles) || count($higherTfCandles) < 20) {
            return new StrategyResult('NEUTRAL', 50.0, $this->getDefaultWeight(), [], ['Higher timeframe data currently unavailable or insufficient'], false);
        }

        $htfN = count($higherTfCandles);
        $htfClose = (float)$higherTfCandles[$htfN - 1]['close'];
        $htfEma20 = $higherTfIndicators['ema20'][$htfN - 1] ?? null;
        $htfEma50 = $higherTfIndicators['ema50'][$htfN - 1] ?? null;
        $htfRsi = $higherTfIndicators['rsi'][$htfN - 1] ?? null;

        $reasons = [];
        $warnings = [];
        $direction = 'NEUTRAL';
        $score = 50.0;

        if ($htfEma20 !== null && $htfEma50 !== null) {
            if ($htfClose > $htfEma20 && $htfEma20 > $htfEma50) {
                $direction = 'CALL';
                $score = 80.0;
                $reasons[] = 'Higher timeframe structure is strongly bullish (Close > EMA20 > EMA50)';

                if ($htfRsi !== null && $htfRsi > 50 && $htfRsi < 70) {
                    $score += 10.0;
                    $reasons[] = 'Higher timeframe RSI confirms upward momentum (' . round($htfRsi, 1) . ')';
                }
            } elseif ($htfClose < $htfEma20 && $htfEma20 < $htfEma50) {
                $direction = 'PUT';
                $score = 80.0;
                $reasons[] = 'Higher timeframe structure is strongly bearish (Close < EMA20 < EMA50)';

                if ($htfRsi !== null && $htfRsi < 50 && $htfRsi > 30) {
                    $score += 10.0;
                    $reasons[] = 'Higher timeframe RSI confirms downward momentum (' . round($htfRsi, 1) . ')';
                }
            } else {
                $score = 45.0;
                $warnings[] = 'Higher timeframe is consolidating without clear trend alignment';
            }
        }

        return new StrategyResult($direction, $score, $this->getDefaultWeight(), $reasons, $warnings, true);
    }
}
