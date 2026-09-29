<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\MarketData\Adapters\MockBrokerAdapter;
use App\Models\Broker;
use Illuminate\Http\JsonResponse;

final class BrokerController
{
    private MockBrokerAdapter $mockAdapter;

    public function __construct()
    {
        $this->mockAdapter = new MockBrokerAdapter();
    }

    public function index(): JsonResponse
    {
        $brokers = [
            [
                'id' => 1,
                'name' => 'Mock Broker (Demo)',
                'slug' => 'mock',
                'is_mock' => true,
                'is_active' => true,
                'connection_status' => 'connected',
            ],
            [
                'id' => 2,
                'name' => 'Quotex',
                'slug' => 'quotex',
                'is_mock' => false,
                'is_active' => (bool)config('brokers.quotex.enabled', false),
                'connection_status' => config('brokers.quotex.enabled', false) ? 'connected' : 'disconnected',
            ],
        ];

        return response()->json(['data' => $brokers]);
    }

    public function assets(string $brokerSlug): JsonResponse
    {
        $adapter = $this->mockAdapter;
        $assets = $adapter->getAvailableAssets();

        $data = array_map(function ($dto) use ($adapter) {
            $status = $adapter->getAssetStatus($dto->symbol);
            return [
                'symbol' => $dto->symbol,
                'display_name' => $dto->displayName,
                'asset_type' => $dto->assetType,
                'is_otc' => $dto->isOtc,
                'is_active' => $dto->isActive,
                'supported_timeframes' => $dto->supportedTimeframes,
                'supported_expiries' => $dto->supportedExpiries,
                'payout' => $status['payout'] ?? $dto->payout,
                'market_status' => $status['market_status'] ?? $dto->marketStatus,
                'source' => $dto->source,
                'last_update' => date('c'),
            ];
        }, $assets);

        return response()->json(['data' => $data]);
    }
}
