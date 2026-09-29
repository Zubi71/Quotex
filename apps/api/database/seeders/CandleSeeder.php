<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\MarketData\Adapters\MockBrokerAdapter;
use App\Models\Candle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class CandleSeeder extends Seeder
{
    public function run(): void
    {
        $mock = new MockBrokerAdapter(seed: 42, daysOfHistory: 5);
        $assets = ['EUR/USD', 'GBP/USD', 'USD/JPY', 'EUR/GBP', 'AUD/USD'];

        foreach ($assets as $symbol) {
            // Check if candles already exist
            if (Candle::where('asset_symbol', $symbol)->exists()) {
                continue;
            }

            $candles = $mock->getCandles($symbol, 'M1', 2500);
            $chunks = array_chunk($candles, 500);

            foreach ($chunks as $chunk) {
                $rows = array_map(function ($c) use ($symbol) {
                    return [
                        'asset_symbol' => $symbol,
                        'broker_slug' => 'mock',
                        'timeframe' => 'M1',
                        'candle_time' => date('Y-m-d H:i:s', $c->timestamp),
                        'open' => $c->open,
                        'high' => $c->high,
                        'low' => $c->low,
                        'close' => $c->close,
                        'volume' => $c->volume,
                        'is_closed' => $c->isClosed,
                        'source' => 'mock',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }, $chunk);

                DB::table('candles')->insertOrIgnore($rows);
            }
        }
    }
}
