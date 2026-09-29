<?php

declare(strict_types=1);

namespace App\Indicators;

interface IndicatorInterface
{
    /**
     * Calculate the indicator over the given candles array.
     * Each candle is an associative array with keys: open, high, low, close, volume, timestamp.
     *
     * @param  array<int, array{open: float, high: float, low: float, close: float, volume: float, timestamp: int}> $candles
     * @return array<int, float|null>
     */
    public function calculate(array $candles): array;

    /**
     * Return the human-readable indicator name.
     */
    public function getName(): string;
}
