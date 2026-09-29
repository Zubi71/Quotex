<?php

declare(strict_types=1);

namespace App\Backtesting;

use App\Services\ConfidenceEngine;
use App\Services\IndicatorService;
use App\Services\MarketRegimeEngine;
use App\Services\StrategyOrchestrationService;

final class BacktestRunner
{
    private IndicatorService $indicatorService;
    private MarketRegimeEngine $regimeEngine;
    private StrategyOrchestrationService $orchestrationService;
    private ConfidenceEngine $confidenceEngine;

    public function __construct()
    {
        $this->indicatorService = new IndicatorService();
        $this->regimeEngine = new MarketRegimeEngine();
        $this->orchestrationService = new StrategyOrchestrationService();
        $this->confidenceEngine = new ConfidenceEngine();
    }

    /**
     * Run sequential backtest strictly preventing look-ahead bias.
     *
     * @param array $allCandles Chronological candle array
     * @param array $config Backtest configuration parameters
     * @return array Backtest summary results and equity curve
     */
    public function run(array $allCandles, array $config): array
    {
        $initialBalance = (float)($config['initial_balance'] ?? 1000.0);
        $stake = (float)($config['stake'] ?? 10.0);
        $payoutRate = (float)($config['payout_rate'] ?? 80.0) / 100.0;
        $threshold = (int)($config['confidence_threshold'] ?? 75);
        $timeframe = $config['timeframe'] ?? 'M1';
        $expirySeconds = (int)($config['expiry_seconds'] ?? 60);

        $tfSeconds = match ($timeframe) {
            'M5' => 300,
            'M15' => 900,
            'H1' => 3600,
            default => 60,
        };
        $expiryCandleOffset = max(1, (int)round($expirySeconds / $tfSeconds));

        $totalCandles = count($allCandles);
        $minHistory = 60; // Warmup candles for EMA, MACD, etc.

        $balance = $initialBalance;
        $peakBalance = $initialBalance;
        $maxDrawdown = 0.0;
        $maxDrawdownAmount = 0.0;

        $wins = 0;
        $losses = 0;
        $voidTrades = 0;
        $noTrades = 0;
        $trades = [];
        $equityCurve = [
            [
                'timestamp' => $totalCandles > 0 ? date('c', $allCandles[0]['timestamp']) : date('c'),
                'balance' => $initialBalance,
                'trade_result' => null,
                'profit_loss' => 0.0,
            ]
        ];

        $currentStreak = 0;
        $longestWinStreak = 0;
        $longestLossStreak = 0;
        $grossProfit = 0.0;
        $grossLoss = 0.0;

        // Iterate sequentially through each closed candle
        for ($i = $minHistory; $i < $totalCandles - $expiryCandleOffset; $i++) {
            // STRICT NO LOOK-AHEAD BIAS: Slice data strictly up to index $i
            $availableCandles = array_slice($allCandles, 0, $i + 1);

            // Compute indicators only on available data
            $indicators = $this->indicatorService->computeAll($availableCandles);
            $regime = $this->regimeEngine->detect($availableCandles, $indicators);

            $context = [
                'market_regime' => $regime,
                'timeframe' => $timeframe,
                'expiry_seconds' => $expirySeconds,
            ];

            $strategyResults = $this->orchestrationService->evaluateAll($availableCandles, $indicators, $context);
            $confResult = $this->confidenceEngine->calculate($strategyResults, $regime, 100, [
                'confidence_threshold' => $threshold,
                'min_data_quality' => 50,
            ]);

            if ($confResult->direction === 'NO_TRADE') {
                $noTrades++;
                continue;
            }

            // Signal generated: evaluate outcome at future candle (i + expiryCandleOffset)
            $entryCandle = $allCandles[$i];
            $outcomeCandle = $allCandles[$i + $expiryCandleOffset];

            $entryPrice = (float)$entryCandle['close'];
            $exitPrice = (float)$outcomeCandle['close'];
            $direction = $confResult->direction;

            $tradeResult = 'VOID';
            $pnl = 0.0;

            if ($direction === 'CALL') {
                if ($exitPrice > $entryPrice) {
                    $tradeResult = 'WIN';
                    $pnl = $stake * $payoutRate;
                    $wins++;
                    $grossProfit += $pnl;
                    $currentStreak = ($currentStreak > 0) ? $currentStreak + 1 : 1;
                    $longestWinStreak = max($longestWinStreak, $currentStreak);
                } elseif ($exitPrice < $entryPrice) {
                    $tradeResult = 'LOSS';
                    $pnl = -$stake;
                    $losses++;
                    $grossLoss += $stake;
                    $currentStreak = ($currentStreak < 0) ? $currentStreak - 1 : -1;
                    $longestLossStreak = max($longestLossStreak, abs($currentStreak));
                } else {
                    $tradeResult = 'VOID';
                    $voidTrades++;
                }
            } elseif ($direction === 'PUT') {
                if ($exitPrice < $entryPrice) {
                    $tradeResult = 'WIN';
                    $pnl = $stake * $payoutRate;
                    $wins++;
                    $grossProfit += $pnl;
                    $currentStreak = ($currentStreak > 0) ? $currentStreak + 1 : 1;
                    $longestWinStreak = max($longestWinStreak, $currentStreak);
                } elseif ($exitPrice > $entryPrice) {
                    $tradeResult = 'LOSS';
                    $pnl = -$stake;
                    $losses++;
                    $grossLoss += $stake;
                    $currentStreak = ($currentStreak < 0) ? $currentStreak - 1 : -1;
                    $longestLossStreak = max($longestLossStreak, abs($currentStreak));
                } else {
                    $tradeResult = 'VOID';
                    $voidTrades++;
                }
            }

            $balance += $pnl;
            if ($balance > $peakBalance) {
                $peakBalance = $balance;
            }
            $ddAmount = $peakBalance - $balance;
            $ddPct = ($peakBalance > 0) ? ($ddAmount / $peakBalance) * 100.0 : 0.0;
            if ($ddPct > $maxDrawdown) {
                $maxDrawdown = $ddPct;
                $maxDrawdownAmount = $ddAmount;
            }

            $equityCurve[] = [
                'timestamp' => date('c', $outcomeCandle['timestamp']),
                'balance' => round($balance, 2),
                'trade_result' => $tradeResult,
                'profit_loss' => round($pnl, 2),
            ];

            $trades[] = [
                'candle_time' => date('c', $entryCandle['timestamp']),
                'direction' => $direction,
                'confidence' => $confResult->confidence,
                'entry_price' => $entryPrice,
                'close_price' => $exitPrice,
                'result' => $tradeResult,
                'profit_loss' => round($pnl, 2),
                'balance_after' => round($balance, 2),
                'reasons' => array_slice($confResult->reasons, 0, 3),
            ];
        }

        $totalTrades = $wins + $losses + $voidTrades;
        $decidedTrades = $wins + $losses;
        $winRate = ($decidedTrades > 0) ? round(($wins / $decidedTrades) * 100.0, 2) : 0.0;
        $netProfit = $balance - $initialBalance;
        $profitFactor = ($grossLoss > 0) ? round($grossProfit / $grossLoss, 2) : ($grossProfit > 0 ? 99.0 : 1.0);

        $avgWin = ($wins > 0) ? $grossProfit / $wins : 0.0;
        $avgLoss = ($losses > 0) ? $grossLoss / $losses : 0.0;
        $pWin = ($decidedTrades > 0) ? $wins / $decidedTrades : 0.0;
        $pLoss = ($decidedTrades > 0) ? $losses / $decidedTrades : 0.0;
        $expectancy = round(($pWin * $avgWin) - ($pLoss * $avgLoss), 2);

        $totalSignalsEvaluated = $totalTrades + $noTrades;
        $noTradeRate = ($totalSignalsEvaluated > 0) ? round(($noTrades / $totalSignalsEvaluated) * 100.0, 2) : 0.0;

        return [
            'total_signals' => $totalSignalsEvaluated,
            'total_trades' => $totalTrades,
            'wins' => $wins,
            'losses' => $losses,
            'void_trades' => $voidTrades,
            'no_trades' => $noTrades,
            'win_rate' => $winRate,
            'profit' => round($netProfit, 2),
            'max_drawdown' => round($maxDrawdown, 2),
            'max_drawdown_amount' => round($maxDrawdownAmount, 2),
            'profit_factor' => $profitFactor,
            'expectancy' => $expectancy,
            'longest_win_streak' => $longestWinStreak,
            'longest_loss_streak' => $longestLossStreak,
            'no_trade_rate' => $noTradeRate,
            'initial_balance' => $initialBalance,
            'final_balance' => round($balance, 2),
            'equity_curve' => $equityCurve,
            'recent_trades' => array_slice($trades, -50),
        ];
    }
}
