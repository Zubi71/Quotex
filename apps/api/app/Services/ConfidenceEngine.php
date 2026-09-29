<?php

declare(strict_types=1);

namespace App\Services;

use App\Strategies\Contracts\StrategyResult;

/**
 * ConfidenceResult DTO
 */
final class ConfidenceResult
{
    public function __construct(
        public string $direction,         // CALL, PUT, NO_TRADE
        public int $confidence,           // 0 to 100
        public string $status,            // HIGH_CONFIDENCE, MODERATE, WEAK, NO_TRADE
        public array $factors,            // [{name, score, weight, contribution, direction}]
        public array $reasons,            // Technical reasons
        public array $warnings,           // Warning points
        public array $rejectionReasons = [] // Reasons why trade was rejected (if NO_TRADE)
    ) {}
}

/**
 * ConfidenceEngine
 * Calculates weighted multi-factor confidence and enforces strict NO TRADE gates.
 */
final class ConfidenceEngine
{
    /**
     * Calculate final confidence and direction.
     *
     * @param StrategyResult[] $strategyResults
     * @param string $marketRegime
     * @param int $dataQualityScore 0-100
     * @param array $config Configuration overrides
     * @return ConfidenceResult
     */
    public function calculate(
        array $strategyResults,
        string $marketRegime,
        int $dataQualityScore,
        array $config = []
    ): ConfidenceResult {
        $threshold = $config['confidence_threshold'] ?? 70;
        $minDataQuality = $config['min_data_quality'] ?? 80;

        $rejectionReasons = [];
        $reasons = [];
        $warnings = [];

        // 1. Quality Check
        if ($dataQualityScore < $minDataQuality) {
            $rejectionReasons[] = "Data quality score ({$dataQualityScore}%) is below minimum threshold ({$minDataQuality}%)";
        }

        // 2. Market Regime Gate
        if ($marketRegime === 'UNCERTAIN') {
            $rejectionReasons[] = "Market regime is UNCERTAIN (conflicting multi-timeframe trend & momentum structure)";
        } elseif ($marketRegime === 'HIGH_VOLATILITY') {
            $warnings[] = "Market experiencing elevated volatility anomaly; wide swings may cause slippage";
        }

        $bullishScore = 0.0;
        $bearishScore = 0.0;
        $bullishWeight = 0.0;
        $bearishWeight = 0.0;
        $totalWeight = 0.0;
        $callCount = 0;
        $putCount = 0;
        $factorList = [];

        foreach ($strategyResults as $res) {
            $effectiveWeight = $res->weight;

            // Regime-based dynamic weight adjustment
            if (str_contains($marketRegime, 'TRENDING')) {
                if (str_contains(strtolower($res->reasons[0] ?? ''), 'trend')) {
                    $effectiveWeight *= 1.3;
                }
            } elseif ($marketRegime === 'RANGING') {
                if (str_contains(strtolower($res->reasons[0] ?? ''), 'mean reversion') ||
                    str_contains(strtolower($res->reasons[0] ?? ''), 'support')) {
                    $effectiveWeight *= 1.3;
                }
            }

            $totalWeight += $effectiveWeight;
            $contribution = ($res->score * $effectiveWeight) / 100.0;

            if ($res->direction === 'CALL') {
                $bullishScore += $contribution;
                $bullishWeight += $effectiveWeight;
                $callCount++;
            } elseif ($res->direction === 'PUT') {
                $bearishScore += $contribution;
                $bearishWeight += $effectiveWeight;
                $putCount++;
            }

            $factorList[] = [
                'name' => $res->reasons[0] ?? 'Strategy factor',
                'score' => round($res->score, 1),
                'weight' => round($effectiveWeight, 1),
                'contribution' => round($contribution, 2),
                'direction' => $res->direction,
            ];

            foreach ($res->reasons as $r) {
                if (!in_array($r, $reasons, true)) {
                    $reasons[] = $r;
                }
            }
            foreach ($res->warnings as $w) {
                if (!in_array($w, $warnings, true)) {
                    $warnings[] = $w;
                }
            }
        }

        if ($totalWeight <= 0) {
            return new ConfidenceResult('NO_TRADE', 0, 'NO_TRADE', [], [], ['No strategies active'], ['No active strategy evaluations']);
        }

        // Active directional weight and average scores
        $directionalWeight = $bullishWeight + $bearishWeight;
        $bullishAvgScore = $bullishWeight > 0 ? ($bullishScore / $bullishWeight) * 100.0 : 0.0;
        $bearishAvgScore = $bearishWeight > 0 ? ($bearishScore / $bearishWeight) * 100.0 : 0.0;

        // Ratio of active consensus
        $bullishRatio = $directionalWeight > 0 ? ($bullishWeight / $directionalWeight) * 100.0 : 0.0;
        $bearishRatio = $directionalWeight > 0 ? ($bearishWeight / $directionalWeight) * 100.0 : 0.0;

        // 4. Direction Decision & Confluence Synthesis
        $direction = 'NO_TRADE';
        $finalConfidence = 0;

        if ($callCount >= 2 && $bullishRatio >= 60.0 && $bullishAvgScore >= 55.0) {
            $direction = 'CALL';
            $finalConfidence = (int)round(($bullishAvgScore * 0.7) + ($bullishRatio * 0.3));

            if ($bearishWeight > 0) {
                $finalConfidence -= (int)round(($bearishRatio * 0.3));
                $warnings[] = "Minor opposing bearish momentum detected; confidence score adjusted";
            }
        } elseif ($putCount >= 2 && $bearishRatio >= 60.0 && $bearishAvgScore >= 55.0) {
            $direction = 'PUT';
            $finalConfidence = (int)round(($bearishAvgScore * 0.7) + ($bearishRatio * 0.3));

            if ($bullishWeight > 0) {
                $finalConfidence -= (int)round(($bullishRatio * 0.3));
                $warnings[] = "Minor opposing bullish momentum detected; confidence score adjusted";
            }
        } elseif ($callCount >= 1 && $putCount === 0 && $bullishAvgScore >= 70.0) {
            $direction = 'CALL';
            $finalConfidence = (int)round($bullishAvgScore * 0.88);
            $warnings[] = "Dominant technical breakout; single confirmation cluster";
        } elseif ($putCount >= 1 && $callCount === 0 && $bearishAvgScore >= 70.0) {
            $direction = 'PUT';
            $finalConfidence = (int)round($bearishAvgScore * 0.88);
            $warnings[] = "Dominant technical breakdown; single confirmation cluster";
        } elseif ($directionalWeight > 0 && abs($bullishRatio - $bearishRatio) < 25.0) {
            $rejectionReasons[] = "Conflicting directional signals (Bullish: " . round($bullishRatio) . "%, Bearish: " . round($bearishRatio) . "%)";
        } else {
            $rejectionReasons[] = "Insufficient technical confluence on current candle (Bullish: " . round($bullishScore) . ", Bearish: " . round($bearishScore) . ")";
        }

        // 5. Data Quality Scaling
        if ($dataQualityScore < 95 && $finalConfidence > 0) {
            $penalty = (int)round((100 - $dataQualityScore) * 0.15);
            $finalConfidence = max(0, $finalConfidence - $penalty);
        }

        $finalConfidence = min(98, max(0, $finalConfidence));

        // 6. Strict NO_TRADE Threshold Enforcement
        if (!empty($rejectionReasons) || $finalConfidence < $threshold) {
            if ($finalConfidence < $threshold && empty($rejectionReasons)) {
                $rejectionReasons[] = "Calculated confidence ({$finalConfidence}%) is below configured minimum threshold ({$threshold}%)";
            }
            $direction = 'NO_TRADE';
        }

        // 7. Status Classification
        $status = 'NO_TRADE';
        if ($direction !== 'NO_TRADE') {
            if ($finalConfidence >= 85) {
                $status = 'HIGH_CONFIDENCE';
            } elseif ($finalConfidence >= 70) {
                $status = 'MODERATE';
            } else {
                $status = 'WEAK';
            }
        }

        return new ConfidenceResult(
            direction: $direction,
            confidence: $finalConfidence,
            status: $status,
            factors: $factorList,
            reasons: $reasons,
            warnings: $warnings,
            rejectionReasons: $rejectionReasons
        );
    }
}
