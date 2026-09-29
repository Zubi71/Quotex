<?php

declare(strict_types=1);

namespace App\Services;

use App\Strategies\BreakoutStrategy;
use App\Strategies\CandlestickConfirmationStrategy;
use App\Strategies\Contracts\StrategyInterface;
use App\Strategies\Contracts\StrategyResult;
use App\Strategies\MeanReversionStrategy;
use App\Strategies\MomentumStrategy;
use App\Strategies\MultiTimeframeConfluenceStrategy;
use App\Strategies\PullbackStrategy;
use App\Strategies\SupportResistanceStrategy;
use App\Strategies\TrendFollowingStrategy;

final class StrategyOrchestrationService
{
    /** @var StrategyInterface[] */
    private array $strategies;

    public function __construct()
    {
        $this->strategies = [
            new TrendFollowingStrategy(),
            new MomentumStrategy(),
            new MeanReversionStrategy(),
            new SupportResistanceStrategy(),
            new BreakoutStrategy(),
            new PullbackStrategy(),
            new CandlestickConfirmationStrategy(),
            new MultiTimeframeConfluenceStrategy(),
        ];
    }

    /**
     * Run all strategies against candle data and precomputed indicators.
     *
     * @param array $candles
     * @param array $indicators
     * @param array $context
     * @return StrategyResult[]
     */
    public function evaluateAll(array $candles, array $indicators, array $context): array
    {
        $results = [];

        foreach ($this->strategies as $strategy) {
            $results[] = $strategy->analyse($candles, $indicators, $context);
        }

        return $results;
    }
}
