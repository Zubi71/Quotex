<?php

declare(strict_types=1);

namespace App\Strategies;

use App\Indicators\SupportResistanceDetector;
use App\Strategies\Contracts\StrategyInterface;
use App\Strategies\Contracts\StrategyResult;

final class SupportResistanceStrategy implements StrategyInterface
{
    private SupportResistanceDetector $detector;

    public function __construct()
    {
        $this->detector = new SupportResistanceDetector();
    }

    public function getName(): string
    {
        return 'Key Level Support & Resistance';
    }

    public function getSlug(): string
    {
        return 'support_resistance';
    }

    public function getDefaultWeight(): float
    {
        return 14.0;
    }

    public function analyse(array $candles, array $indicators, array $context): StrategyResult
    {
        $n = count($candles);
        if ($n < 25) {
            return new StrategyResult('NEUTRAL', 0, $this->getDefaultWeight(), [], ['Insufficient data for S/R'], false);
        }

        $sr = $this->detector->detect($candles);
        $currClose = (float)$candles[$n - 1]['close'];
        $currLow = (float)$candles[$n - 1]['low'];
        $currHigh = (float)$candles[$n - 1]['high'];

        $nearestSupport = $sr['nearest_support'];
        $nearestResistance = $sr['nearest_resistance'];

        $reasons = [];
        $warnings = [];
        $direction = 'NEUTRAL';
        $score = 50.0;

        // Check if price bounced near support (within 0.08%)
        $nearSupport = false;
        if ($nearestSupport !== null) {
            $distToSupportPct = abs($currLow - $nearestSupport) / $nearestSupport * 100.0;
            if ($distToSupportPct <= 0.12 && $currClose > $currLow) {
                $nearSupport = true;
            }
        }

        // Check if price reacted near resistance (within 0.08%)
        $nearResistance = false;
        if ($nearestResistance !== null) {
            $distToResistancePct = abs($currHigh - $nearestResistance) / $nearestResistance * 100.0;
            if ($distToResistancePct <= 0.12 && $currClose < $currHigh) {
                $nearResistance = true;
            }
        }

        if ($nearSupport && !$nearResistance) {
            $direction = 'CALL';
            $score = 78.0;
            $reasons[] = 'Price tested and respected key support zone (' . number_format($nearestSupport, 5) . ')';
        } elseif ($nearResistance && !$nearSupport) {
            $direction = 'PUT';
            $score = 78.0;
            $reasons[] = 'Price tested and rejected key resistance zone (' . number_format($nearestResistance, 5) . ')';
        } else {
            $score = 40.0;
            $warnings[] = 'Price is floating between major support and resistance zones';
        }

        return new StrategyResult($direction, $score, $this->getDefaultWeight(), $reasons, $warnings, true);
    }
}
