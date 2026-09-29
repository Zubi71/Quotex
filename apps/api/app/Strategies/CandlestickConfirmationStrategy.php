<?php

declare(strict_types=1);

namespace App\Strategies;

use App\Indicators\PriceActionDetector;
use App\Strategies\Contracts\StrategyInterface;
use App\Strategies\Contracts\StrategyResult;

final class CandlestickConfirmationStrategy implements StrategyInterface
{
    private PriceActionDetector $detector;

    public function __construct()
    {
        $this->detector = new PriceActionDetector();
    }

    public function getName(): string
    {
        return 'Candlestick Pattern Confirmation';
    }

    public function getSlug(): string
    {
        return 'candlestick_confirmation';
    }

    public function getDefaultWeight(): float
    {
        return 12.0;
    }

    public function analyse(array $candles, array $indicators, array $context): StrategyResult
    {
        $patterns = $this->detector->detect($candles);

        if (empty($patterns)) {
            return new StrategyResult('NEUTRAL', 45.0, $this->getDefaultWeight(), [], ['No definitive candlestick pattern on last closed bar'], true);
        }

        $reasons = [];
        $warnings = [];
        $bullishWeight = 0.0;
        $bearishWeight = 0.0;

        foreach ($patterns as $p) {
            if ($p['direction'] === 'BULLISH') {
                $bullishWeight += $p['strength'];
                $reasons[] = 'Pattern: ' . str_replace('_', ' ', $p['pattern']) . ' — ' . $p['description'];
            } elseif ($p['direction'] === 'BEARISH') {
                $bearishWeight += $p['strength'];
                $reasons[] = 'Pattern: ' . str_replace('_', ' ', $p['pattern']) . ' — ' . $p['description'];
            } elseif ($p['direction'] === 'NEUTRAL') {
                $warnings[] = 'Caution: ' . str_replace('_', ' ', $p['pattern']) . ' indicates market indecision';
            }
        }

        $direction = 'NEUTRAL';
        $score = 50.0;

        if ($bullishWeight > 0.6 && $bullishWeight > $bearishWeight) {
            $direction = 'CALL';
            $score = 65.0 + ($bullishWeight * 20.0);
        } elseif ($bearishWeight > 0.6 && $bearishWeight > $bullishWeight) {
            $direction = 'PUT';
            $score = 65.0 + ($bearishWeight * 20.0);
        }

        $score = min(96.0, max(25.0, $score));
        return new StrategyResult($direction, $score, $this->getDefaultWeight(), $reasons, $warnings, true);
    }
}
