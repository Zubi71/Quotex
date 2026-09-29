# OTC Signal Intelligence — Market Data Pipeline

## 1. Data Ingestion Architecture

```
[ Broker Adapter: Mock or Quotex ]
               ↓
    [ CandleDTO Normalization ]
               ↓
   [ Timestamp & Continuity Audit ]
               ↓
 [ Redis Cache Buffer (TTL by TF) ]
               ↓
  [ MySQL Persistent Time-Series ]
               ↓
[ Event: CandleClosed → WebSockets ]
```

---

## 2. Broker Adapter Abstraction Layer

The application enforces strict inversion of control. Core services interact exclusively with `BrokerInterface`:

```php
interface BrokerInterface {
    public function getName(): string;
    public function getSlug(): string;
    public function isMock(): bool;
    public function getAvailableAssets(): array;
    public function getCandles(string $symbol, string $timeframe, int $count, ?int $endTimestamp = null): array;
    public function getLatestPrice(string $symbol): PriceDTO;
    public function getPayout(string $symbol): ?float;
}
```

### Adapters Implemented
1. **`MockBrokerAdapter` (Active / Default)**:
   - High-fidelity synthetic OTC market generator.
   - Uses seeded Geometric Brownian Motion (GBM) with mean reversion and sinusoidal volatility cycles.
   - Fully deterministic: identical seeds produce identical historical market replay.
   - Aggregates M1 candles dynamically into M5, M15, and H1 timeframes.
2. **`QuotexAdapter` (Stub with Integration Points)**:
   - Contains clean architectural hooks for Quotex WebSocket ingestion.
   - Protected with configuration guards (`ENABLE_QUOTEX_ADAPTER=false`).
   - Clearly documented integration points for WebSocket connection, session handshake, asset subscriptions, and binary/JSON payload parsing.

---

## 3. Data Quality Gate & Gap Detection

Before any technical indicators are computed, the `CandleDataService` evaluates four data health parameters:

1. **Timestamp Freshness**:
   - Compares the timestamp of the latest closed candle against server epoch time.
   - If age exceeds $2 \times \text{timeframe}$, a latency warning is logged.
   - If age exceeds $5 \times \text{timeframe}$, signal generation is halted (`DATA_STALE`).
2. **Continuity & Gap Ratio**:
   - Compares sequential candle intervals ($\Delta t = t_i - t_{i-1}$).
   - Missing candles indicate connection dropouts or broker downtime.
   - If missing bar ratio exceeds $5\%$, the Data Quality Score drops below $80\%$ and **triggers automatic trade rejection**.
3. **Price Anomaly Filtering**:
   - Detects erroneous spikes or bad ticks ($>2\%$ change in a single 60s bar).
4. **Volume Health**:
   - Verifies positive non-zero volume ticks to eliminate flatline periods.

---

## 4. Redis Caching Strategy

To achieve sub-millisecond API response times and eliminate redundant database queries:
- **M1 Candles**: Cached in Redis key `market:{broker}:{symbol}:M1` with a **60-second TTL**.
- **M5 Candles**: Cached with a **300-second TTL**.
- **Asset Status & Payouts**: Cached with a **30-second TTL**.
- **Indicator Snapshots**: Recomputed incrementally on candle close and cached in memory.
