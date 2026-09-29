<?php

declare(strict_types=1);

namespace App\Indicators;

/**
 * SupportResistanceDetector
 * Identifies key horizontal support and resistance levels from price swing clustering
 * and pivot point calculations.
 */
final class SupportResistanceDetector
{
    /**
     * Detect support and resistance levels.
     *
     * @param array $candles
     * @param float $clusterTolerancePercent e.g. 0.1% tolerance
     * @return array
     */
    public function detect(array $candles, float $clusterTolerancePercent = 0.15): array
    {
        $n = count($candles);
        if ($n < 20) {
            return [
                'supports' => [],
                'resistances' => [],
                'nearest_support' => null,
                'nearest_resistance' => null,
                'current_price' => $n > 0 ? (float)$candles[$n - 1]['close'] : null,
            ];
        }

        $currentPrice = (float)$candles[$n - 1]['close'];
        $swingAnalyser = new SwingStructureAnalyser(2);
        $swing = $swingAnalyser->analyse($candles);

        $highs = array_column($swing['swing_highs'], 'price');
        $lows = array_column($swing['swing_lows'], 'price');

        $supports = $this->clusterLevels(array_filter($lows, fn($p) => $p < $currentPrice), $clusterTolerancePercent);
        $resistances = $this->clusterLevels(array_filter($highs, fn($p) => $p > $currentPrice), $clusterTolerancePercent);

        // Sort supports descending (closest first)
        rsort($supports);
        // Sort resistances ascending (closest first)
        sort($resistances);

        return [
            'supports' => $supports,
            'resistances' => $resistances,
            'nearest_support' => $supports[0] ?? null,
            'nearest_resistance' => $resistances[0] ?? null,
            'current_price' => $currentPrice,
        ];
    }

    private function clusterLevels(array $prices, float $tolerancePercent): array
    {
        if (empty($prices)) {
            return [];
        }

        sort($prices);
        $clusters = [];
        $currentCluster = [$prices[0]];

        for ($i = 1; $i < count($prices); $i++) {
            $prev = end($currentCluster);
            $diffPercent = abs($prices[$i] - $prev) / $prev * 100.0;

            if ($diffPercent <= $tolerancePercent) {
                $currentCluster[] = $prices[$i];
            } else {
                $clusters[] = round(array_sum($currentCluster) / count($currentCluster), 5);
                $currentCluster = [$prices[$i]];
            }
        }

        if (!empty($currentCluster)) {
            $clusters[] = round(array_sum($currentCluster) / count($currentCluster), 5);
        }

        return $clusters;
    }
}
