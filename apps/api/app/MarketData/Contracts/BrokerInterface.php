<?php

declare(strict_types=1);

namespace App\MarketData\Contracts;

use App\MarketData\DTOs\AssetDTO;
use App\MarketData\DTOs\CandleDTO;
use App\MarketData\DTOs\PriceDTO;

/**
 * BrokerInterface — All broker adapters must implement this contract.
 *
 * The system is designed so that the rest of the application never
 * knows which broker is being used. All broker-specific code lives
 * inside the adapter that implements this interface.
 */
interface BrokerInterface
{
    /**
     * Human-readable broker name.
     */
    public function getName(): string;

    /**
     * URL-safe broker slug (e.g., 'quotex', 'mock').
     */
    public function getSlug(): string;

    /**
     * Returns true if this adapter generates synthetic/demo data.
     * Mock adapters must always return true.
     * Live broker adapters must always return false.
     */
    public function isMock(): bool;

    /**
     * Establish connection to the broker data source.
     * Returns true on success.
     */
    public function connect(): bool;

    /**
     * Disconnect from the broker data source.
     */
    public function disconnect(): void;

    /**
     * Check broker connection health.
     *
     * @return array{connected: bool, latency_ms: float|null, message: string, timestamp: int}
     */
    public function healthCheck(): array;

    /**
     * Get all currently available trading assets from this broker.
     *
     * @return AssetDTO[]
     */
    public function getAvailableAssets(): array;

    /**
     * Get the current status of a specific asset.
     *
     * @return array{is_available: bool, market_status: string, payout: float|null, spread: float|null}
     */
    public function getAssetStatus(string $symbol): array;

    /**
     * Get historical OHLCV candles for an asset.
     *
     * @param  string   $symbol      e.g., 'EUR/USD'
     * @param  string   $timeframe   e.g., 'M1', 'M5', 'H1'
     * @param  int      $count       Number of candles to fetch
     * @param  int|null $endTimestamp Unix timestamp — fetch candles ending at this time (null = now)
     * @return CandleDTO[]
     */
    public function getCandles(
        string $symbol,
        string $timeframe,
        int $count,
        ?int $endTimestamp = null
    ): array;

    /**
     * Get the latest price for an asset.
     */
    public function getLatestPrice(string $symbol): PriceDTO;

    /**
     * Get the current payout percentage for an asset.
     * Returns null if payout is not available from this broker.
     */
    public function getPayout(string $symbol): ?float;

    /**
     * Subscribe to a real-time market data stream for an asset.
     * The callback receives a CandleDTO whenever a new candle closes.
     *
     * @param  callable(CandleDTO): void $callback
     */
    public function subscribeMarketData(string $symbol, callable $callback): void;
}
