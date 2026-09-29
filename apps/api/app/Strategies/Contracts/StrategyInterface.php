<?php

declare(strict_types=1);

namespace App\Strategies\Contracts;

/**
 * StrategyResult DTO
 */
final class StrategyResult
{
    /**
     * @param string $direction 'CALL', 'PUT', or 'NEUTRAL'
     * @param float $score 0.0 to 100.0
     * @param float $weight Relative weight in ensemble
     * @param string[] $reasons Key technical reasons
     * @param string[] $warnings Cautions or risk factors
     * @param bool $isReliable False if data or market requirements not met
     */
    public function __construct(
        public string $direction,
        public float $score,
        public float $weight,
        public array $reasons = [],
        public array $warnings = [],
        public bool $isReliable = true
    ) {}
}

/**
 * StrategyInterface
 * All strategy modules must implement this interface.
 */
interface StrategyInterface
{
    public function getName(): string;
    public function getSlug(): string;
    public function getDefaultWeight(): float;

    /**
     * Execute strategy analysis on candle history and indicator snapshot.
     *
     * @param array $candles Closed candle array
     * @param array $indicators Precomputed indicators map
     * @param array $context Additional context (regime, timeframe, higherTfData, etc.)
     * @return StrategyResult
     */
    public function analyse(array $candles, array $indicators, array $context): StrategyResult;
}
