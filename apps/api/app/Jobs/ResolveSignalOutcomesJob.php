<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Signal;
use App\Services\CandleDataService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ResolveSignalOutcomesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $pendingSignals = Signal::where('result', 'PENDING')
            ->where('expiry_time', '<=', now())
            ->take(50)
            ->get();

        if ($pendingSignals->isEmpty()) {
            return;
        }

        $candleService = new CandleDataService();

        foreach ($pendingSignals as $signal) {
            try {
                $broker = $candleService->getBroker($signal->broker_slug);
                $latestPrice = $broker->getLatestPrice($signal->asset_symbol);
                $closePrice = $latestPrice->mid;

                $result = 'VOID';
                $pnl = 0.0;
                $stake = 10.0;
                $payout = 80.0 / 100.0;

                if ($signal->direction === 'CALL') {
                    if ($closePrice > $signal->entry_price) {
                        $result = 'WIN';
                        $pnl = $stake * $payout;
                    } elseif ($closePrice < $signal->entry_price) {
                        $result = 'LOSS';
                        $pnl = -$stake;
                    }
                } elseif ($signal->direction === 'PUT') {
                    if ($closePrice < $signal->entry_price) {
                        $result = 'WIN';
                        $pnl = $stake * $payout;
                    } elseif ($closePrice > $signal->entry_price) {
                        $result = 'LOSS';
                        $pnl = -$stake;
                    }
                }

                $signal->update([
                    'close_price' => $closePrice,
                    'result' => $result,
                    'profit_loss' => $pnl,
                    'result_time' => now(),
                ]);
            } catch (\Throwable) {
                // Ignore transient lookup errors
            }
        }
    }
}
