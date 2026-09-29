<?php

declare(strict_types=1);

namespace App\Services;

use App\MarketData\Adapters\MockBrokerAdapter;
use App\MarketData\Contracts\BrokerInterface;
use App\Models\Broker;
use App\Models\Candle;

final class CandleDataService
{
    private MockBrokerAdapter $mockAdapter;

    public function __construct()
    {
        $this->mockAdapter = new MockBrokerAdapter();
    }

    /**
     * Get broker instance by slug.
     */
    public function getBroker(string $brokerSlug): BrokerInterface
    {
        if ($brokerSlug === 'mock') {
            return $this->mockAdapter;
        }

        // Future or Quotex lookup
        return $this->mockAdapter;
    }

    /**
     * Fetch candles for symbol, timeframe, and count.
     *
     * @param string $symbol
     * @param string $brokerSlug
     * @param string $timeframe
     * @param int $count
     * @param int|null $endTimestamp
     * @return array
     */
    public function getCandles(
        string $symbol,
        string $brokerSlug,
        string $timeframe = 'M1',
        int $count = 250,
        ?int $endTimestamp = null
    ): array {
        // Try to query database first
        try {
            $query = Candle::where('asset_symbol', $symbol)
                ->where('broker_slug', $brokerSlug)
                ->where('timeframe', $timeframe);

            if ($endTimestamp !== null) {
                $query->where('candle_time', '<=', date('Y-m-d H:i:s', $endTimestamp));
            }

            $dbCandles = $query->orderBy('candle_time', 'desc')->take($count)->get()->reverse()->values();

            if ($dbCandles->count() >= min(50, $count)) {
                return $dbCandles->map(function ($c) {
                    return [
                        'timestamp' => strtotime($c->candle_time),
                        'open' => (float)$c->open,
                        'high' => (float)$c->high,
                        'low' => (float)$c->low,
                        'close' => (float)$c->close,
                        'volume' => (float)$c->volume,
                        'is_closed' => (bool)$c->is_closed,
                    ];
                })->toArray();
            }
        } catch (\Throwable) {
            // Fall back to adapter when database connection is not initialized
        }

        // Fallback to adapter
        $broker = $this->getBroker($brokerSlug);
        $dtos = $broker->getCandles($symbol, $timeframe, $count, $endTimestamp);

        return array_map(function ($dto) {
            return [
                'timestamp' => $dto->timestamp,
                'open' => $dto->open,
                'high' => $dto->high,
                'low' => $dto->low,
                'close' => $dto->close,
                'volume' => $dto->volume,
                'is_closed' => $dto->isClosed,
            ];
        }, $dtos);
    }

    /**
     * Compute data quality score (0 - 100).
     */
    public function calculateQualityScore(array $candles, string $timeframe = 'M1'): int
    {
        $n = count($candles);
        if ($n < 50) {
            return 40;
        }

        $score = 100;

        // Check freshness of last candle
        $lastTs = $candles[$n - 1]['timestamp'] ?? 0;
        $now = time();
        $tfSecs = match ($timeframe) {
            'M5' => 300,
            'M15' => 900,
            'H1' => 3600,
            default => 60,
        };

        $lag = $now - $lastTs;
        if ($lag > ($tfSecs * 5)) {
            $score -= 15;
        } elseif ($lag > ($tfSecs * 2)) {
            $score -= 5;
        }

        // Check for missing gaps
        $gaps = 0;
        for ($i = 1; $i < $n; $i++) {
            $diff = $candles[$i]['timestamp'] - $candles[$i - 1]['timestamp'];
            if ($diff > ($tfSecs * 1.5)) {
                $gaps++;
            }
        }

        $gapRatio = $gaps / $n;
        if ($gapRatio > 0.05) {
            $score -= 25;
        } elseif ($gapRatio > 0.02) {
            $score -= 10;
        }

        // Check for zero volume or flat candles
        $flatCandles = 0;
        foreach ($candles as $c) {
            if ($c['high'] == $c['low']) {
                $flatCandles++;
            }
        }
        if ($flatCandles > 2) {
            $score -= 10;
        }

        return max(10, min(100, $score));
    }
}
