<?php

declare(strict_types=1);

namespace App\Signals;

final class QualityGateResult
{
    public function __construct(
        public bool $passed,
        public array $failures = [],
        public array $warnings = []
    ) {}
}

final class TradeQualityGate
{
    /**
     * Run all pre-signal quality filters.
     *
     * @param array $params
     * @return QualityGateResult
     */
    public function check(array $params): QualityGateResult
    {
        $failures = [];
        $warnings = [];

        $candles = $params['candles'] ?? [];
        $dataQualityScore = $params['data_quality_score'] ?? 100;
        $minDataQuality = $params['min_data_quality'] ?? 80;
        $minCandleHistory = $params['min_candle_history'] ?? 200;
        $payout = $params['payout'] ?? null;
        $minPayout = $params['min_payout'] ?? 70.0;
        $isMarketOpen = $params['is_market_open'] ?? true;
        $isBrokerConnected = $params['is_broker_connected'] ?? true;

        // 1. Connection check
        if (!$isBrokerConnected) {
            $failures[] = 'Broker connection unavailable';
        }

        // 2. Market availability
        if (!$isMarketOpen) {
            $failures[] = 'Asset market is currently closed or suspended';
        }

        // 3. Minimum candle count
        $candleCount = count($candles);
        if ($candleCount < $minCandleHistory) {
            $failures[] = "Insufficient candle history: {$candleCount} loaded, minimum required is {$minCandleHistory}";
        }

        // 4. Data Quality Score
        if ($dataQualityScore < $minDataQuality) {
            $failures[] = "Data quality score ({$dataQualityScore}%) below threshold ({$minDataQuality}%)";
        }

        // 5. Payout filter
        if ($payout !== null && $payout < $minPayout) {
            $failures[] = "Asset payout rate ({$payout}%) below configured minimum ({$minPayout}%)";
        }

        // 6. Candle continuity & freshness
        if ($candleCount >= 2) {
            $lastCandle = $candles[$candleCount - 1];
            $prevCandle = $candles[$candleCount - 2];
            $now = time();
            $lastTimestamp = $lastCandle['timestamp'] ?? 0;

            // Freshness: if last candle is older than 5 minutes for M1, flag warning
            $timeframeSecs = 60; // M1 default
            if ($now - $lastTimestamp > ($timeframeSecs * 5)) {
                $warnings[] = 'Last closed candle is older than expected; real-time feed may be lagging';
            }

            // Price anomaly check: abrupt jump > 2% between consecutive candles
            if (!empty($prevCandle['close']) && !empty($lastCandle['close'])) {
                $pctChange = abs((float)$lastCandle['close'] - (float)$prevCandle['close']) / (float)$prevCandle['close'] * 100.0;
                if ($pctChange > 2.0) {
                    $warnings[] = 'Abrupt price gap detected between last two candles (' . round($pctChange, 2) . '%)';
                }
            }
        }

        return new QualityGateResult(
            passed: empty($failures),
            failures: $failures,
            warnings: $warnings
        );
    }
}
