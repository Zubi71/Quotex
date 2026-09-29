<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\CandleDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MarketController
{
    private CandleDataService $candleService;

    public function __construct()
    {
        $this->candleService = new CandleDataService();
    }

    public function candles(string $asset, Request $request): JsonResponse
    {
        $broker = $request->query('broker', 'mock');
        $timeframe = $request->query('timeframe', 'M1');
        $count = (int)$request->query('count', 250);
        $end = $request->has('end') ? (int)$request->query('end') : null;

        $candles = $this->candleService->getCandles($asset, $broker, $timeframe, $count, $end);
        $qualityScore = $this->candleService->calculateQualityScore($candles, $timeframe);

        return response()->json([
            'asset' => $asset,
            'broker' => $broker,
            'timeframe' => $timeframe,
            'quality_score' => $qualityScore,
            'count' => count($candles),
            'data' => $candles,
        ]);
    }

    public function price(string $asset, Request $request): JsonResponse
    {
        $brokerSlug = $request->query('broker', 'mock');
        $broker = $this->candleService->getBroker($brokerSlug);
        $price = $broker->getLatestPrice($asset);

        return response()->json([
            'data' => $price->toArray(),
        ]);
    }

    public function status(string $asset, Request $request): JsonResponse
    {
        $brokerSlug = $request->query('broker', 'mock');
        $broker = $this->candleService->getBroker($brokerSlug);
        $status = $broker->getAssetStatus($asset);

        return response()->json([
            'data' => $status,
        ]);
    }

    public function payout(string $asset, Request $request): JsonResponse
    {
        $brokerSlug = $request->query('broker', 'mock');
        $broker = $this->candleService->getBroker($brokerSlug);
        $payout = $broker->getPayout($asset);

        return response()->json([
            'asset' => $asset,
            'payout' => $payout,
        ]);
    }
}
