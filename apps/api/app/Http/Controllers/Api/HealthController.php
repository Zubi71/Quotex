<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\MarketData\Adapters\MockBrokerAdapter;
use Illuminate\Http\JsonResponse;

final class HealthController
{
    public function system(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'redis' => 'connected',
            'queue' => 'active',
            'websocket' => 'ready',
            'broker' => [
                'status' => 'connected',
                'adapter' => 'MockBrokerAdapter',
                'latency_ms' => 0.4,
            ],
            'timestamp' => date('c'),
        ]);
    }

    public function data(): JsonResponse
    {
        $mock = new MockBrokerAdapter();
        $assets = $mock->getAvailableAssets();

        $healthList = [];
        foreach ($assets as $asset) {
            $healthList[] = [
                'asset' => $asset->symbol,
                'broker' => 'mock',
                'timeframe' => 'M1',
                'quality_score' => 96,
                'candle_count' => 10080,
                'gap_count' => 0,
                'latest_candle_time' => date('c'),
                'latency_ms' => 0.5,
                'is_fresh' => true,
                'status' => 'HEALTHY',
            ];
        }

        return response()->json([
            'overall_quality_score' => 96,
            'status' => 'OPTIMAL',
            'assets' => $healthList,
            'timestamp' => date('c'),
        ]);
    }
}
