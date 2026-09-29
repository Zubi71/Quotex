<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Asset;
use App\Models\Broker;
use Illuminate\Database\Seeder;

final class AssetSeeder extends Seeder
{
    public function run(): void
    {
        $mockBroker = Broker::where('slug', 'mock')->first();
        if (!$mockBroker) return;

        $assets = [
            [
                'symbol' => 'EUR/USD',
                'display_name' => 'EUR/USD (OTC)',
                'asset_type' => 'OTC',
                'is_otc' => true,
                'is_active' => true,
                'supported_timeframes' => ['M1', 'M5', 'M15', 'H1'],
                'supported_expiries' => [60, 120, 180, 300],
            ],
            [
                'symbol' => 'GBP/USD',
                'display_name' => 'GBP/USD (OTC)',
                'asset_type' => 'OTC',
                'is_otc' => true,
                'is_active' => true,
                'supported_timeframes' => ['M1', 'M5', 'M15', 'H1'],
                'supported_expiries' => [60, 120, 180, 300],
            ],
            [
                'symbol' => 'USD/JPY',
                'display_name' => 'USD/JPY (OTC)',
                'asset_type' => 'OTC',
                'is_otc' => true,
                'is_active' => true,
                'supported_timeframes' => ['M1', 'M5', 'M15', 'H1'],
                'supported_expiries' => [60, 120, 180, 300],
            ],
            [
                'symbol' => 'EUR/GBP',
                'display_name' => 'EUR/GBP (OTC)',
                'asset_type' => 'OTC',
                'is_otc' => true,
                'is_active' => true,
                'supported_timeframes' => ['M1', 'M5', 'M15', 'H1'],
                'supported_expiries' => [60, 120, 180, 300],
            ],
            [
                'symbol' => 'AUD/USD',
                'display_name' => 'AUD/USD (OTC)',
                'asset_type' => 'OTC',
                'is_otc' => true,
                'is_active' => true,
                'supported_timeframes' => ['M1', 'M5', 'M15', 'H1'],
                'supported_expiries' => [60, 120, 180, 300],
            ],
        ];

        foreach ($assets as $asset) {
            Asset::firstOrCreate(
                ['broker_id' => $mockBroker->id, 'symbol' => $asset['symbol']],
                $asset
            );
        }
    }
}
