<?php

declare(strict_types=1);

namespace App\Strategies;

use App\Strategies\Contracts\StrategyInterface;
use App\Strategies\Contracts\StrategyResult;

final class PullbackStrategy implements StrategyInterface
{
    public function getName(): string
    {
        return 'Trend Pullback & Continuation';
    }

    public function getSlug(): string
    {
        return 'pullback';
    }

    public function getDefaultWeight(): float
    {
        return 14.0;
    }

    public function analyse(array $candles, array $indicators, array $context): StrategyResult
    {
        $n = count($candles);
        if ($n < 30) {
            return new StrategyResult('NEUTRAL', 0, $this->getDefaultWeight(), [], ['Insufficient data for pullback'], false);
        }

        $c0 = $candles[$n - 1];
        $close = (float)$c0['close'];
        $low = (float)$c0['low'];
        $high = (float)$c0['high'];

        $ema20 = $indicators['ema20'][$n - 1] ?? null;
        $ema50 = $indicators['ema50'][$n - 1] ?? null;
        $rsi = $indicators['rsi'][$n - 1] ?? null;

        if ($ema20 === null || $ema50 === null) {
            return new StrategyResult('NEUTRAL', 0, $this->getDefaultWeight(), [], ['Missing EMA values'], false);
        }

        $reasons = [];
        $warnings = [];
        $direction = 'NEUTRAL';
        $score = 50.0;

        // Uptrend pullback: EMA20 > EMA50, price touched EMA20 and closed above it
        $isUptrend = $ema20 > $ema50;
        $isDowntrend = $ema20 < $ema50;

        if ($isUptrend && $low <= ($ema20 * 1.0005) && $close >= $ema20) {
            $direction = 'CALL';
            $score = 76.0;
            $reasons[] = 'Healthy pullback to rising 20 EMA dynamic support in prevailing uptrend';

            if ($rsi !== null && $rsi >= 40 && $rsi <= 58) {
                $score += 10.0;
                $reasons[] = 'RSI pullback reset in ideal re-entry zone (' . round($rsi, 1) . ')';
            }
        } elseif ($isDowntrend && $high >= ($ema20 * 0.9995) && $close <= $ema20) {
            $direction = 'PUT';
            $score = 76.0;
            $reasons[] = 'Healthy pullback to falling 20 EMA dynamic resistance in prevailing downtrend';

            if ($rsi !== null && $rsi >= 42 && $rsi <= 60) {
                $score += 10.0;
                $reasons[] = 'RSI pullback reset in ideal re-entry zone (' . round($rsi, 1) . ')';
            }
        } else {
            $score = 35.0;
            $warnings[] = 'Price is not in an optimal dynamic pullback location';
        }

        return new StrategyResult($direction, $score, $this->getDefaultWeight(), $reasons, $warnings, true);
    }
}
