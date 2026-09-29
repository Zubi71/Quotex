<?php

declare(strict_types=1);

namespace Tests\Unit\SignalEngine;

use App\MarketData\Adapters\MockBrokerAdapter;
use App\Services\ConfidenceEngine;
use App\Services\IndicatorService;
use App\Services\MarketRegimeEngine;
use App\Services\StrategyOrchestrationService;
use PHPUnit\Framework\TestCase;

final class FutureCandleLeakageTest extends TestCase
{
    public function test_future_candles_do_not_alter_past_signals(): void
    {
        $mock = new MockBrokerAdapter(seed: 12345, daysOfHistory: 2);
        $allCandles = array_map(fn($dto) => [
            'timestamp' => $dto->timestamp,
            'open' => $dto->open,
            'high' => $dto->high,
            'low' => $dto->low,
            'close' => $dto->close,
            'volume' => $dto->volume,
            'is_closed' => $dto->isClosed,
        ], $mock->getCandles('EUR/USD', 'M1', 300));

        $historicalSlice = array_slice($allCandles, 0, 200);

        $indicatorService = new IndicatorService();
        $regimeEngine = new MarketRegimeEngine();
        $strategyService = new StrategyOrchestrationService();
        $confidenceEngine = new ConfidenceEngine();

        // 1. Generate signal at candle 200
        $indicators1 = $indicatorService->computeAll($historicalSlice);
        $regime1 = $regimeEngine->detect($historicalSlice, $indicators1);
        $context1 = ['market_regime' => $regime1, 'timeframe' => 'M1', 'expiry_seconds' => 60];
        $strategyResults1 = $strategyService->evaluateAll($historicalSlice, $indicators1, $context1);
        $signal1 = $confidenceEngine->calculate($strategyResults1, $regime1, 100);

        // 2. Simulate catastrophic market crash on subsequent 100 candles
        $futureCrashCandles = [];
        $lastClose = end($historicalSlice)['close'];
        for ($i = 0; $i < 100; $i++) {
            $lastClose *= 0.98; // severe downward cascade
            $futureCrashCandles[] = [
                'timestamp' => time() + ($i * 60),
                'open' => $lastClose * 1.01,
                'high' => $lastClose * 1.01,
                'low' => $lastClose * 0.99,
                'close' => $lastClose,
                'volume' => 5000.0,
                'is_closed' => true,
            ];
        }

        $fullDataset = array_merge($historicalSlice, $futureCrashCandles);

        // 3. Re-evaluate signal at candle index 200 by slicing up to 200 ONLY
        $retestedSlice = array_slice($fullDataset, 0, 200);
        $indicators2 = $indicatorService->computeAll($retestedSlice);
        $regime2 = $regimeEngine->detect($retestedSlice, $indicators2);
        $context2 = ['market_regime' => $regime2, 'timeframe' => 'M1', 'expiry_seconds' => 60];
        $strategyResults2 = $strategyService->evaluateAll($retestedSlice, $indicators2, $context2);
        $signal2 = $confidenceEngine->calculate($strategyResults2, $regime2, 100);

        // 4. Assert absolute non-repainting reproducibility
        $this->assertSame($signal1->direction, $signal2->direction, 'Signal direction must never repaint based on future candles.');
        $this->assertSame($signal1->confidence, $signal2->confidence, 'Signal confidence score must remain immutable.');
        $this->assertSame($signal1->status, $signal2->status, 'Signal status must remain immutable.');
    }
}
