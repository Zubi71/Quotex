<?php

declare(strict_types=1);

namespace App\Strategies;

use App\Strategies\Contracts\StrategyInterface;
use App\Strategies\Contracts\StrategyResult;

final class BreakoutStrategy implements StrategyInterface
{
    public function getName(): string
    {
        return 'Volatility Squeeze & Breakout';
    }

    public function getSlug(): string
    {
        return 'breakout';
    }

    public function getDefaultWeight(): float
    {
        return 12.0;
    }

    public function analyse(array $candles, array $indicators, array $context): StrategyResult
    {
        $n = count($candles);
        if ($n < 25) {
            return new StrategyResult('NEUTRAL', 0, $this->getDefaultWeight(), [], ['Insufficient data for breakout'], false);
        }

        $bandwidth = $indicators['bollinger']['bandwidth'][$n - 1] ?? null;
        $prevBandwidth = $indicators['bollinger']['bandwidth'][$n - 2] ?? null;
        $atr = $indicators['atr'][$n - 1] ?? null;
        $prevAtr = $indicators['atr'][$n - 2] ?? null;

        $c0 = $candles[$n - 1];
        $close0 = (float)$c0['close'];

        // Range of prior 10 candles
        $priorHigh = -INF;
        $priorLow = INF;
        for ($i = $n - 11; $i < $n - 1; $i++) {
            if ($i >= 0) {
                $priorHigh = max($priorHigh, (float)$candles[$i]['high']);
                $priorLow = min($priorLow, (float)$candles[$i]['low']);
            }
        }

        $reasons = [];
        $warnings = [];
        $direction = 'NEUTRAL';
        $score = 50.0;

        $isSqueezing = ($bandwidth !== null && $prevBandwidth !== null && $bandwidth > $prevBandwidth);
        $isAtrExpanding = ($atr !== null && $prevAtr !== null && $atr > $prevAtr);

        if ($close0 > $priorHigh) {
            $direction = 'CALL';
            $score = 75.0;
            $reasons[] = 'Bullish range expansion breaking above prior 10-candle consolidation';

            if ($isAtrExpanding) {
                $score += 10.0;
                $reasons[] = 'ATR expansion confirms breakout momentum';
            }
        } elseif ($close0 < $priorLow) {
            $direction = 'PUT';
            $score = 75.0;
            $reasons[] = 'Bearish range breakdown breaking below prior 10-candle consolidation';

            if ($isAtrExpanding) {
                $score += 10.0;
                $reasons[] = 'ATR expansion confirms breakdown momentum';
            }
        } else {
            $score = 40.0;
            $warnings[] = 'Market remains compressed inside consolidation range';
        }

        $score = min(95.0, max(20.0, $score));
        return new StrategyResult($direction, $score, $this->getDefaultWeight(), $reasons, $warnings, true);
    }
}
