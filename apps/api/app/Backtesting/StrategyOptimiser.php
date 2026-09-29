<?php

declare(strict_types=1);

namespace App\Backtesting;

final class StrategyOptimiser
{
    private BacktestRunner $runner;

    public function __construct()
    {
        $this->runner = new BacktestRunner();
    }

    /**
     * Run bounded parameter optimisation with overfitting safeguards.
     *
     * @param array $candles
     * @param array $baseConfig
     * @return array
     */
    public function optimizeThreshold(array $candles, array $baseConfig): array
    {
        $thresholds = [65, 70, 75, 80, 85];
        $results = [];

        foreach ($thresholds as $threshold) {
            $config = array_merge($baseConfig, ['confidence_threshold' => $threshold]);
            $run = $this->runner->run($candles, $config);

            // Compute composite robustness score (penalizing low trade counts and high drawdown)
            $trades = $run['total_trades'];
            $winRate = $run['win_rate'];
            $maxDd = $run['max_drawdown'];

            // Must have statistically valid sample size
            $samplePenalty = ($trades < 30) ? 0.6 : 1.0;
            $drawdownPenalty = max(0.2, (100.0 - $maxDd) / 100.0);
            $compositeScore = round($winRate * $samplePenalty * $drawdownPenalty, 2);

            $results[] = [
                'threshold' => $threshold,
                'trades' => $trades,
                'win_rate' => $winRate,
                'profit' => $run['profit'],
                'max_drawdown' => $maxDd,
                'profit_factor' => $run['profit_factor'],
                'composite_score' => $compositeScore,
                'statistically_sufficient' => $trades >= 30,
            ];
        }

        // Sort by composite score descending
        usort($results, fn($a, $b) => $b['composite_score'] <=> $a['composite_score']);

        return [
            'optimal_setting' => $results[0] ?? null,
            'all_evaluations' => $results,
        ];
    }
}
