<?php

declare(strict_types=1);

namespace App\MarketData\Adapters;

use App\MarketData\Contracts\BrokerInterface;
use App\MarketData\DTOs\AssetDTO;
use App\MarketData\DTOs\CandleDTO;
use App\MarketData\DTOs\PriceDTO;

/**
 * MockBrokerAdapter — Fully working synthetic market data adapter.
 *
 * This adapter generates deterministic, realistic OTC candle data using
 * a seeded random walk with mean reversion and volatility cycles.
 *
 * ⚠  DEMO DATA — clearly labelled throughout the system.
 * Never used in production when a real broker adapter is configured.
 *
 * All data generated here is synthetic and does not represent real market prices.
 */
final class MockBrokerAdapter implements BrokerInterface
{
    // Supported OTC assets with their base prices and volatility profiles
    private const ASSETS = [
        'EUR/USD' => ['base' => 1.08500, 'volatility' => 0.00015, 'display' => 'EUR/USD (OTC)'],
        'GBP/USD' => ['base' => 1.27000, 'volatility' => 0.00020, 'display' => 'GBP/USD (OTC)'],
        'USD/JPY' => ['base' => 149.500, 'volatility' => 0.0200,  'display' => 'USD/JPY (OTC)'],
        'EUR/GBP' => ['base' => 0.85500, 'volatility' => 0.00012, 'display' => 'EUR/GBP (OTC)'],
        'AUD/USD' => ['base' => 0.65000, 'volatility' => 0.00013, 'display' => 'AUD/USD (OTC)'],
    ];

    private const SUPPORTED_TIMEFRAMES = ['M1', 'M5', 'M15', 'H1'];
    private const SUPPORTED_EXPIRIES   = [60, 120, 180, 300]; // seconds
    private const PAYOUT_RATE          = 80.0;

    /** Timeframe duration in seconds */
    private const TIMEFRAME_SECONDS = [
        'M1'  => 60,
        'M5'  => 300,
        'M15' => 900,
        'H1'  => 3600,
    ];

    /** Cached generated M1 candle series per symbol */
    private array $candleCache = [];

    public function __construct(
        private readonly int $seed = 42,
        private readonly int $daysOfHistory = 7,
    ) {}

    // ─── BrokerInterface Implementation ───────────────────────────────────────

    public function getName(): string
    {
        return 'Mock Broker (Demo)';
    }

    public function getSlug(): string
    {
        return 'mock';
    }

    public function isMock(): bool
    {
        return true;
    }

    public function connect(): bool
    {
        return true; // Always connected
    }

    public function disconnect(): void
    {
        // No-op for mock
    }

    public function healthCheck(): array
    {
        return [
            'connected'  => true,
            'latency_ms' => 0.5,
            'message'    => 'Mock broker always available',
            'timestamp'  => time(),
        ];
    }

    public function getAvailableAssets(): array
    {
        $assets = [];
        foreach (self::ASSETS as $symbol => $config) {
            $assets[] = new AssetDTO(
                symbol:               $symbol,
                displayName:          $config['display'],
                assetType:            'OTC',
                isOtc:                true,
                isActive:             true,
                supportedTimeframes:  self::SUPPORTED_TIMEFRAMES,
                supportedExpiries:    self::SUPPORTED_EXPIRIES,
                payout:               self::PAYOUT_RATE,
                marketStatus:         'open',
                source:               'mock',
            );
        }
        return $assets;
    }

    public function getAssetStatus(string $symbol): array
    {
        if (!isset(self::ASSETS[$symbol])) {
            return [
                'is_available'  => false,
                'market_status' => 'unknown',
                'payout'        => null,
                'spread'        => null,
            ];
        }

        $config = self::ASSETS[$symbol];
        return [
            'is_available'  => true,
            'market_status' => 'open',
            'payout'        => self::PAYOUT_RATE,
            'spread'        => $config['volatility'] * 2,
        ];
    }

    public function getCandles(
        string $symbol,
        string $timeframe,
        int $count,
        ?int $endTimestamp = null
    ): array {
        if (!isset(self::ASSETS[$symbol])) {
            return [];
        }

        $m1Candles = $this->getOrGenerateM1Candles($symbol);

        if ($timeframe === 'M1') {
            $candles = $m1Candles;
        } else {
            $candles = $this->aggregateCandles($m1Candles, $timeframe);
        }

        // Filter by end timestamp if provided
        if ($endTimestamp !== null) {
            $candles = array_filter(
                $candles,
                fn(CandleDTO $c) => $c->timestamp <= $endTimestamp
            );
            $candles = array_values($candles);
        }

        // Return the last $count candles (only closed ones)
        $closedCandles = array_filter($candles, fn(CandleDTO $c) => $c->isClosed);
        $closedCandles = array_values($closedCandles);

        return array_slice($closedCandles, -$count);
    }

    public function getLatestPrice(string $symbol): PriceDTO
    {
        $m1Candles = $this->getOrGenerateM1Candles($symbol);
        $lastCandle = end($m1Candles);

        $basePrice = $lastCandle ? $lastCandle->close : (self::ASSETS[$symbol]['base'] ?? 1.0);
        $spread    = (self::ASSETS[$symbol]['volatility'] ?? 0.0001) * 1.5;

        return new PriceDTO(
            symbol:    $symbol,
            bid:       $basePrice - $spread / 2,
            ask:       $basePrice + $spread / 2,
            mid:       $basePrice,
            timestamp: time(),
            source:    'mock',
        );
    }

    public function getPayout(string $symbol): ?float
    {
        return isset(self::ASSETS[$symbol]) ? self::PAYOUT_RATE : null;
    }

    public function subscribeMarketData(string $symbol, callable $callback): void
    {
        // For the mock adapter, subscription is a no-op.
        // Real-time updates are simulated via the scheduler/jobs.
    }

    // ─── Private: Candle Generation ───────────────────────────────────────────

    /**
     * Get or generate the M1 candle series for a symbol.
     * The series is deterministic based on the seed.
     *
     * @return CandleDTO[]
     */
    private function getOrGenerateM1Candles(string $symbol): array
    {
        if (isset($this->candleCache[$symbol])) {
            return $this->candleCache[$symbol];
        }

        $this->candleCache[$symbol] = $this->generateM1Candles($symbol);
        return $this->candleCache[$symbol];
    }

    /**
     * Generate realistic M1 OHLCV candles using a seeded random walk.
     *
     * Algorithm:
     *   - Seeded MT random for determinism
     *   - Geometric Brownian Motion base
     *   - Mean reversion toward base price (prevents runaway)
     *   - Sinusoidal volatility cycle (simulates session activity)
     *   - Realistic body/wick structure
     *
     * @return CandleDTO[]
     */
    private function generateM1Candles(string $symbol): array
    {
        $config = self::ASSETS[$symbol];
        $basePrice  = $config['base'];
        $baseVol    = $config['volatility'];

        // Seed includes symbol for variety between pairs
        $symbolSeed = $this->seed + crc32($symbol) % 10000;
        mt_srand($symbolSeed);

        $totalMinutes = $this->daysOfHistory * 24 * 60; // e.g. 7 days = 10080 minutes
        $now          = time();
        // Align start to the nearest minute boundary
        $startTime    = $now - ($totalMinutes * 60);
        $startTime    = (int) (floor($startTime / 60) * 60);

        $candles   = [];
        $price     = $basePrice;
        $meanReversionRate = 0.001; // Pull back toward base price slowly

        for ($i = 0; $i < $totalMinutes; $i++) {
            $candleTime = $startTime + ($i * 60);

            // Sinusoidal volatility cycle (peaks at session opens)
            $hourOfDay  = (int) (($candleTime % 86400) / 3600);
            $volMultiplier = 1.0 + 0.6 * sin(($hourOfDay - 8) * M_PI / 12);
            $volMultiplier = max(0.4, $volMultiplier); // Floor at 40% base vol

            $currentVol = $baseVol * $volMultiplier;

            // Mean reversion component
            $meanReversion = $meanReversionRate * ($basePrice - $price);

            // Price change: GBM + mean reversion
            $drift  = $meanReversion;
            $noise  = $this->gaussianRandom(0.0, $currentVol);
            $change = $drift + $noise;

            $open  = $price;
            $close = $open + $change;

            // Generate realistic high and low
            $bodyHigh = max($open, $close);
            $bodyLow  = min($open, $close);

            $upperWickFactor = abs($this->gaussianRandom(0.0, $currentVol * 0.6));
            $lowerWickFactor = abs($this->gaussianRandom(0.0, $currentVol * 0.6));

            $high = $bodyHigh + $upperWickFactor;
            $low  = $bodyLow  - $lowerWickFactor;

            // Ensure OHLC constraints
            $high  = max($high, $open, $close);
            $low   = min($low, $open, $close);
            $close = max($low, min($high, $close));

            // Volume: base + random variation
            $volume = abs($this->gaussianRandom(1000.0, 300.0));

            // Determine if this candle is closed:
            // The current (last) candle might be forming
            $isLastCandle = ($i === $totalMinutes - 1);
            $isClosed     = !$isLastCandle;

            $candles[] = new CandleDTO(
                symbol:    $symbol,
                timeframe: 'M1',
                timestamp: $candleTime,
                open:      round($open,  $this->getPrecision($basePrice)),
                high:      round($high,  $this->getPrecision($basePrice)),
                low:       round($low,   $this->getPrecision($basePrice)),
                close:     round($close, $this->getPrecision($basePrice)),
                volume:    round($volume, 2),
                isClosed:  $isClosed,
                source:    'mock',
            );

            $price = $close;
        }

        return $candles;
    }

    /**
     * Aggregate M1 candles into a higher timeframe.
     *
     * @param  CandleDTO[] $m1Candles
     * @return CandleDTO[]
     */
    private function aggregateCandles(array $m1Candles, string $timeframe): array
    {
        $periodSeconds = self::TIMEFRAME_SECONDS[$timeframe] ?? 60;

        $grouped = [];
        foreach ($m1Candles as $candle) {
            // Align candle timestamp to the timeframe period
            $periodStart = (int) (floor($candle->timestamp / $periodSeconds) * $periodSeconds);
            $grouped[$periodStart][] = $candle;
        }

        $aggregated = [];
        foreach ($grouped as $periodStart => $periodCandles) {
            /** @var CandleDTO[] $periodCandles */
            $open   = $periodCandles[0]->open;
            $close  = end($periodCandles)->close;
            $high   = max(array_map(fn(CandleDTO $c) => $c->high, $periodCandles));
            $low    = min(array_map(fn(CandleDTO $c) => $c->low,  $periodCandles));
            $volume = array_sum(array_map(fn(CandleDTO $c) => $c->volume, $periodCandles));

            // Period is closed unless the last M1 candle in it was open
            $lastM1InPeriod = end($periodCandles);
            $isClosed = $lastM1InPeriod->isClosed;

            $aggregated[] = new CandleDTO(
                symbol:    $periodCandles[0]->symbol,
                timeframe: $timeframe,
                timestamp: $periodStart,
                open:      $open,
                high:      $high,
                low:       $low,
                close:     $close,
                volume:    round($volume, 2),
                isClosed:  $isClosed,
                source:    'mock',
            );
        }

        return $aggregated;
    }

    /**
     * Generate a normally distributed random number using the Box-Muller transform.
     * Uses PHP's mt_rand() for seeded determinism.
     */
    private function gaussianRandom(float $mean, float $stdDev): float
    {
        static $hasSpare    = false;
        static $spare       = 0.0;

        if ($hasSpare) {
            $hasSpare = false;
            return $mean + $stdDev * $spare;
        }

        $u = 0.0;
        $v = 0.0;
        $s = 0.0;

        do {
            $u = (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
            $v = (mt_rand() / mt_getrandmax()) * 2.0 - 1.0;
            $s = $u * $u + $v * $v;
        } while ($s >= 1.0 || $s === 0.0);

        $factor = sqrt(-2.0 * log($s) / $s);
        $spare  = $v * $factor;
        $hasSpare = true;

        return $mean + $stdDev * $u * $factor;
    }

    /**
     * Determine decimal precision based on asset price magnitude.
     */
    private function getPrecision(float $price): int
    {
        if ($price >= 100.0) return 3;  // JPY pairs
        if ($price >= 1.0)   return 5;  // EUR/USD, etc.
        return 6;
    }
}
