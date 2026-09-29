<?php

declare(strict_types=1);

namespace App\Backtesting;

final class WalkForwardTester
{
    private BacktestRunner $runner;

    public function __construct()
    {
        $this->runner = new BacktestRunner();
    }

    /**
     * Run walk-forward analysis with in-sample and out-of-sample splits.
     *
     * @param array $allCandles
     * @param array $config
     * @param float $trainSplitRatio e.g. 0.70 (70% in-sample, 30% out-of-sample)
     * @return array
     */
    public function run(array $allCandles, array $config, float $trainSplitRatio = 0.70): array
    {
        $n = count($allCandles);
        if ($n < 200) {
            throw new \InvalidArgumentException('Walk-forward testing requires at least 200 candles to prevent statistical bias.');
        }

        $splitIndex = (int)floor($n * $trainSplitRatio);
        $trainCandles = array_slice($allCandles, 0, $splitIndex);
        $testCandles = array_slice($allCandles, $splitIndex);

        // Run training backtest (in-sample)
        $inSample = $this->runner->run($trainCandles, $config);

        // Run test backtest (out-of-sample)
        $outOfSample = $this->runner->run($testCandles, $config);

        $winRateDegradation = $inSample['win_rate'] - $outOfSample['win_rate'];
        $isOverfitted = $winRateDegradation > 12.0 || ($outOfSample['win_rate'] < 52.0 && $inSample['win_rate'] > 65.0);

        return [
            'train_sample_size' => count($trainCandles),
            'test_sample_size' => count($testCandles),
            'train_split_ratio' => $trainSplitRatio,
            'in_sample' => $inSample,
            'out_of_sample' => $outOfSample,
            'win_rate_degradation' => round($winRateDegradation, 2),
            'is_overfitted' => $isOverfitted,
            'robustness_rating' => $isOverfitted ? 'LOW_ROBUSTNESS_OVERFITTING_DETECTED' : 'HEALTHY_OUT_OF_SAMPLE_PERSISTENCE',
        ];
    }
}
