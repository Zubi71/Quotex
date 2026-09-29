<?php

declare(strict_types=1);

namespace App\Jobs;

use App\MarketData\Adapters\MockBrokerAdapter;
use App\Models\Candle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class UpdateCandleHistoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $mock = new MockBrokerAdapter();
        $assets = ['EUR/USD', 'GBP/USD', 'USD/JPY', 'EUR/GBP', 'AUD/USD'];

        foreach ($assets as $symbol) {
            try {
                $candles = $mock->getCandles($symbol, 'M1', 5);
                foreach ($candles as $c) {
                    if ($c->isClosed) {
                        Candle::updateOrCreate(
                            [
                                'asset_symbol' => $symbol,
                                'broker_slug' => 'mock',
                                'timeframe' => 'M1',
                                'candle_time' => date('Y-m-d H:i:s', $c->timestamp),
                            ],
                            [
                                'open' => $c->open,
                                'high' => $c->high,
                                'low' => $c->low,
                                'close' => $c->close,
                                'volume' => $c->volume,
                                'is_closed' => true,
                                'source' => 'mock',
                            ]
                        );
                    }
                }
            } catch (\Throwable) {
                // Ignore transient errors
            }
        }
    }
}
