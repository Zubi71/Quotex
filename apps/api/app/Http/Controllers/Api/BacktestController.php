<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Backtesting\BacktestRunner;
use App\Models\Backtest;
use App\Services\CandleDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BacktestController
{
    private BacktestRunner $runner;
    private CandleDataService $candleService;

    public function __construct()
    {
        $this->runner = new BacktestRunner();
        $this->candleService = new CandleDataService();
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'asset' => 'required|string',
            'broker' => 'nullable|string',
            'timeframe' => 'nullable|string',
            'expiry_seconds' => 'nullable|integer',
            'confidence_threshold' => 'nullable|integer',
            'initial_balance' => 'nullable|numeric',
            'stake' => 'nullable|numeric',
            'payout_rate' => 'nullable|numeric',
            'candle_count' => 'nullable|integer',
        ]);

        $asset = $validated['asset'];
        $broker = $validated['broker'] ?? 'mock';
        $timeframe = $validated['timeframe'] ?? 'M1';
        $candleCount = (int)($validated['candle_count'] ?? 500);

        // Fetch candles for backtest period
        $candles = $this->candleService->getCandles($asset, $broker, $timeframe, $candleCount);

        $config = [
            'initial_balance' => (float)($validated['initial_balance'] ?? 1000.0),
            'stake' => (float)($validated['stake'] ?? 10.0),
            'payout_rate' => (float)($validated['payout_rate'] ?? 80.0),
            'confidence_threshold' => (int)($validated['confidence_threshold'] ?? 75),
            'timeframe' => $timeframe,
            'expiry_seconds' => (int)($validated['expiry_seconds'] ?? 60),
        ];

        // Execute sequential backtest (strictly no look-ahead bias)
        $results = $this->runner->run($candles, $config);

        return response()->json([
            'status' => 'completed',
            'config' => array_merge($config, ['asset' => $asset, 'broker' => $broker]),
            'results' => $results,
        ]);
    }

    public function index(): JsonResponse
    {
        $backtests = Backtest::orderBy('created_at', 'desc')->take(20)->get();

        return response()->json(['data' => $backtests]);
    }

    public function show(int $id): JsonResponse
    {
        $backtest = Backtest::findOrFail($id);

        return response()->json(['data' => $backtest]);
    }

    public function results(int $id): JsonResponse
    {
        $backtest = Backtest::findOrFail($id);
        $results = $backtest->results ?? [];

        return response()->json(['data' => $results]);
    }
}
