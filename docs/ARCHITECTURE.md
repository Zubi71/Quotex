# Architecture — OTC Signal Intelligence

## Table of Contents

1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [Component Descriptions](#component-descriptions)
4. [Data Flow Diagrams](#data-flow-diagrams)
5. [Broker Adapter Pattern](#broker-adapter-pattern)
6. [Signal Generation Pipeline](#signal-generation-pipeline)
7. [Database Architecture](#database-architecture)
8. [Caching Strategy](#caching-strategy)
9. [Queue Architecture](#queue-architecture)
10. [WebSocket Architecture](#websocket-architecture)

---

## System Overview

OTC Signal Intelligence is a service-oriented monorepo. Each concern is isolated into its own service (web server, PHP-FPM, WebSocket, queue worker, scheduler) communicating over a shared internal Docker network with MySQL and Redis as data stores.

```
+==============================================================+
|                        CLIENT BROWSER                        |
|            Next.js 14 SPA / React 18 / TypeScript           |
+========================+=====================================+
                         |
          +──────────────+──────────────+
          |  HTTP/REST (port 80/8000)   |  WebSocket (port 8080)
          v                             v
+─────────────────────+   +──────────────────────────────────+
|       NGINX         |   |      Laravel Reverb              |
|  (Reverse Proxy)    |   |  (WebSocket server, Pusher API)  |
|  port 80  -> web:3000   |  Broadcasts: SignalGenerated,    |
|  port 8000 -> api:9000  |  PriceUpdated, BacktestComplete  |
+─────────┬───────────+   +──────────────────────────────────+
          |
          v
+─────────────────────────────────────────────────────────────+
|              Laravel 11 API  (PHP 8.3 FPM)                  |
|                                                             |
|  ┌──────────────────────────────────────────────────────┐  |
|  │                   HTTP Layer                          │  |
|  │  Routes (api.php) → Middleware → Controllers         │  |
|  │  Laravel Sanctum (token + session auth)              │  |
|  └──────────────────────────────────────────────────────┘  |
|                                                             |
|  ┌─────────────────────┐  ┌─────────────────────────────┐  |
|  │   Signal Engine     │  │     Backtesting Engine      │  |
|  │  MultiStrategyPipe  │  │  WalkForwardBacktester      │  |
|  │  MarketRegimeDetect │  │  PerformanceMetricCalc      │  |
|  │  ConfidenceScorer   │  │  LookAheadBiasGuard         │  |
|  └─────────────────────┘  └─────────────────────────────┘  |
|                                                             |
|  ┌─────────────────────────────────────────────────────┐   |
|  │                Data Pipeline                         │   |
|  │  CandleIngestionService                             │   |
|  │  DataQualityValidator                               │   |
|  │  GapDetector                                        │   |
|  │  TimestampNormaliser                                │   |
|  └─────────────────────────────────────────────────────┘   |
|                                                             |
|  ┌─────────────────────────────────────────────────────┐   |
|  │              Broker Adapter Layer                    │   |
|  │                                                     │   |
|  │  BrokerFactory ──> BrokerAdapterInterface            │   |
|  │       │                                             │   |
|  │  ┌────┴────┐  ┌──────────┐  ┌──────────────────┐   │   |
|  │  │  Mock   │  │  Quotex  │  │   Future Broker  │   │   |
|  │  │ Adapter │  │  Adapter │  │   (extensible)   │   │   |
|  │  └─────────┘  └──────────┘  └──────────────────┘   │   |
|  └─────────────────────────────────────────────────────┘   |
+─────────────────────┬───────────────────────────────────────+
                      |
            +---------+---------+
            v                   v
+─────────────────────+ +─────────────────────+
│     MySQL 8.0       │ │     Redis 7         │
│  (Primary storage)  │ │  Cache / Queues /   │
│                     │ │  Sessions /         │
│  Tables:            │ │  Pub-Sub            │
│  - candles          │ +─────────────────────+
│  - signals          │
│  - backtests        │
│  - backtest_results │
│  - assets           │
│  - brokers          │
│  - users            │
│  - personal_access  │
│    _tokens          │
│  - settings         │
│  - audit_logs       │
+─────────────────────+
```

---

## Technology Stack

| Layer | Technology | Version | Role |
|---|---|---|---|
| Backend API | PHP | 8.3 | Runtime |
| Backend Framework | Laravel | 11 | API, ORM, Queue, Scheduler |
| Frontend Framework | Next.js | 14 | SSR/SPA UI |
| UI Library | React | 18 | Component model |
| Frontend Language | TypeScript | 5 | Type safety |
| Database | MySQL | 8.0 | Primary persistence |
| Cache / Queue | Redis | 7 | Caching, queue backend, pub-sub |
| WebSocket | Laravel Reverb | 1.x | Real-time events |
| Auth | Laravel Sanctum | 4.x | Token + session authentication |
| Web Server | Nginx | 1.25 (Alpine) | Reverse proxy, SSL termination |
| PHP Container | PHP-FPM | 8.3-Alpine | FastCGI process manager |
| Node Container | Node.js | 20 (Alpine) | Next.js runtime |
| Containerisation | Docker | 24+ | Service isolation |
| Orchestration | Docker Compose | v2 | Local + production deployment |
| PHP Package Mgr | Composer | 2.7 | PHP dependency management |
| JS Package Mgr | pnpm | 9 | Fast Node.js package management |
| CSS Framework | Tailwind CSS | 3 | Utility-first styling |
| State Management | Zustand | 4 | Lightweight React state |
| Chart Library | Lightweight Charts | 4 | TradingView-style candlestick charts |
| HTTP Client (FE) | Axios | 1 | API communication |
| Testing (PHP) | PHPUnit + Pest | 11 / 2 | PHP test suite |
| Testing (JS) | Jest + Testing Library | 29 / 14 | JS unit tests |
| E2E Testing | Playwright | 1 | Browser automation |
| Static Analysis | PHPStan | 1 | PHP type analysis (level 8) |
| Code Style | PHP-CS-Fixer | 3 | PHP formatting |
| Code Style (JS) | ESLint + Prettier | 8 / 3 | JS formatting |

---

## Component Descriptions

### API (Laravel 11)

The core backend service running as PHP-FPM. Responsibilities:
- RESTful API for all frontend interactions
- Signal generation via queue jobs
- Backtest execution via queue jobs
- Event broadcasting via Reverb
- Scheduled tasks (auto-scan, cleanup)

### Signal Engine

Located in `app/Services/Signals/`. The pipeline:
1. Fetches candles via the active broker adapter
2. Validates data quality
3. Detects the current market regime
4. Runs each registered strategy
5. Aggregates votes into a confidence score
6. Applies quality gates
7. Persists and broadcasts the result

### Backtesting Engine

Located in `app/Services/Backtesting/`. Key guarantee: **no look-ahead bias**. Implements walk-forward testing across historical candle slices.

### Broker Adapter Layer

Located in `app/Services/Brokers/`. Implements the **Strategy** design pattern. The `BrokerFactory` resolves the correct adapter based on `DEFAULT_BROKER` env. Each adapter implements `BrokerAdapterInterface`.

### Data Pipeline

Located in `app/Services/DataPipeline/`. Responsible for:
- Candle ingestion from broker adapters
- Timestamp normalisation to UTC
- OHLCV validation (open ≤ high, low ≤ close, etc.)
- Gap detection
- Storage in MySQL + caching in Redis

### Queue Workers

Multiple queue workers process jobs from Redis queues. Queues (priority order):
1. `signals` — signal generation jobs
2. `backtests` — long-running backtest jobs
3. `default` — all other jobs

### Scheduler

Runs every 60 seconds in Docker or every minute via host cron. Scheduled tasks:
- Auto-scan assets (if `ENABLE_AUTO_SCAN=true`)
- Expire stale signals
- Clean up old candle cache entries
- Broadcast system health events

### Reverb (WebSocket)

Standalone process running `php artisan reverb:start`. Clients subscribe to:
- `private-signals.{userId}` — personal signal feed
- `public-market.{asset}` — live price updates
- `private-backtests.{userId}` — backtest progress

### Nginx

Reverse proxy with two server blocks:
- Port 80 → proxies to Next.js (web:3000)
- Port 8000 → FastCGI proxies to PHP-FPM (api:9000)

Provides: SSL termination, rate limiting, security headers, gzip, static caching.

---

## Data Flow Diagrams

### Signal Generation Flow

```
User Request (POST /api/signals/generate)
         │
         v
  SignalController::generate()
         │
         v
  Dispatches: GenerateSignalJob (queue: signals)
         │
         v
  [Queue Worker picks up job]
         │
         v
  CandleIngestionService::fetch(asset, timeframe, count)
         │
         ├── Check Redis cache (TTL: 30s for live, 5m for historical)
         │       └── Cache HIT → return cached candles
         │       └── Cache MISS → fetch from BrokerAdapter
         │
         v
  DataQualityValidator::validate(candles)
         │
         ├── Quality score < threshold → Abort, return NO_TRADE
         │
         v
  MarketRegimeDetector::detect(candles)
         │   Returns: TRENDING | RANGING | CHOPPY
         │
         v
  [For each registered Strategy]
  Strategy::analyze(candles, regime) → StrategyVote {direction, confidence}
         │
         v
  ConsensusAggregator::aggregate(votes, regime) → AggregatedSignal
         │
         ├── Confidence < threshold → NO_TRADE
         │
         v
  QualityGate::check(signal, candles) → pass | fail
         │
         v
  Signal::persist() → MySQL
         │
         v
  SignalGenerated event → Reverb → Client WebSocket
         │
         v
  Return signal via HTTP or WebSocket
```

### Backtest Flow

```
User Request (POST /api/backtests)
         │
         v
  BacktestController::create()
         │
         v
  Dispatches: RunBacktestJob (queue: backtests)
         │
         v
  [Queue Worker picks up job]
         │
         v
  HistoricalCandleFetcher::fetch(asset, from, to)
         │
         v
  WalkForwardPartitioner::partition(candles, inSampleRatio=0.7)
         │   Returns: [inSampleWindow, outOfSampleWindow]
         │
         v
  StrategyOptimiser::optimise(inSampleWindow)
         │   Grid search over parameter space
         │   Returns: bestParameters
         │
         v
  BacktestRunner::run(outOfSampleWindow, bestParameters)
         │   Iterates candle-by-candle, CLOSED candles only
         │   Simulates entries at next candle open
         │   Returns: tradeList
         │
         v
  PerformanceMetricCalculator::calculate(tradeList)
         │   Computes: winRate, profitFactor, expectancy,
         │   maxDrawdown, sharpeRatio, totalTrades
         │
         v
  BacktestResult::persist() → MySQL
         │
         v
  BacktestComplete event → Reverb → Client WebSocket
```

---

## Broker Adapter Pattern

The broker adapter layer uses the **Strategy** and **Factory Method** design patterns.

### Interface Contract

```php
interface BrokerAdapterInterface
{
    public function getName(): string;
    public function getAssets(): AssetCollection;
    public function getCandles(
        string $asset,
        int    $timeframeSeconds,
        int    $count,
        ?int   $toTimestamp = null
    ): CandleCollection;
    public function getPrice(string $asset): PriceQuote;
    public function getPayout(string $asset, int $expirySeconds): float;
    public function getStatus(string $asset): AssetStatus;
    public function isAvailable(): bool;
}
```

### Factory Resolution

```php
class BrokerFactory
{
    public function make(string $broker): BrokerAdapterInterface
    {
        return match ($broker) {
            'mock'   => new MockBrokerAdapter(config('signal.mock_seed')),
            'quotex' => new QuotexBrokerAdapter(
                             config('quotex.ws_url'),
                             config('quotex.session_token'),
                             config('quotex.account_type')
                         ),
            default  => throw new UnsupportedBrokerException($broker),
        };
    }
}
```

### Adding a New Broker

1. Create `app/Services/Brokers/{Name}/{Name}Adapter.php` implementing `BrokerAdapterInterface`
2. Add a case to `BrokerFactory::make()`
3. Add config entries in `config/brokers.php`
4. Add env vars to `.env.example`
5. Write `tests/Unit/Brokers/{Name}AdapterTest.php`

---

## Signal Generation Pipeline

See [SIGNAL_ENGINE.md](SIGNAL_ENGINE.md) for full detail. Summary:

```
Candles → DataQuality → MarketRegime → Strategies × N → Consensus → QualityGate → Signal
```

Each strategy produces a `StrategyVote`:
- `direction`: `CALL` | `PUT` | `NO_TRADE`
- `confidence`: 0–100
- `reasoning`: array of contributing factors

The `ConsensusAggregator` computes a weighted average considering:
1. Strategy agreement (unanimous = higher weight)
2. Market regime compatibility (trending strategies weighted down in choppy markets)
3. Historical strategy accuracy on this asset (optional)

---

## Database Architecture

### Key Tables

| Table | Description |
|---|---|
| `users` | Operator accounts (Sanctum auth) |
| `personal_access_tokens` | API tokens (Sanctum) |
| `brokers` | Registered broker configurations |
| `assets` | Tradeable asset metadata (symbol, category, decimal places) |
| `candles` | OHLCV candle data (asset_id, timeframe, timestamp, OHLCV) |
| `signals` | Generated signals (asset_id, direction, confidence, strategy_breakdown JSON, status) |
| `backtests` | Backtest run metadata (asset_id, strategy, params, from/to dates, status) |
| `backtest_results` | Aggregated backtest performance metrics |
| `backtest_trades` | Individual simulated trades from backtests |
| `settings` | Key-value application settings |
| `audit_logs` | Security audit trail (user, action, ip, created_at) |

### Indexing Strategy

- `candles`: composite index on `(asset_id, timeframe, timestamp)` — primary query pattern
- `signals`: index on `(asset_id, status, created_at)` — dashboard queries
- `backtests`: index on `(user_id, status, created_at)` — user-specific queries

### Candle Storage Consideration

High-frequency candle data grows quickly. For production:
- Set a retention policy (e.g., keep only 90 days of M1 candles)
- Run `php artisan candles:purge --older-than=90` via scheduler

---

## Caching Strategy

| Data | Cache Key Pattern | TTL | Reason |
|---|---|---|---|
| Live candles (last 30s) | `candles:{asset}:{tf}:live` | 30s | Rate-limit broker calls |
| Historical candles | `candles:{asset}:{tf}:{from}:{to}` | 5 min | Reuse across users |
| Asset list | `broker:{name}:assets` | 10 min | Rarely changes |
| Asset payout | `broker:{name}:payout:{asset}:{expiry}` | 60s | Changes with market hours |
| Signal (last generated) | `signal:last:{asset}:{tf}` | Cooldown period | Honour cooldown |
| System health | `system:health` | 30s | Avoid repeated DB checks |
| Market regime | `regime:{asset}:{tf}` | 60s | Expensive computation |

All cache operations use Laravel's `Cache` facade backed by the Redis driver.

---

## Queue Architecture

### Queues (priority order)

| Queue | Purpose | Max Tries | Timeout |
|---|---|---|---|
| `signals` | Signal generation jobs | 3 | 60s |
| `backtests` | Backtest execution jobs | 1 | 3600s |
| `default` | All other jobs | 3 | 90s |

### Job Classes

| Job | Queue | Description |
|---|---|---|
| `GenerateSignalJob` | signals | Runs the full signal pipeline for one asset |
| `RunBacktestJob` | backtests | Executes a full backtest and computes metrics |
| `ScanAllAssetsJob` | default | Dispatches `GenerateSignalJob` for each active asset |
| `IngestCandlesJob` | default | Fetches and stores new candles from broker |
| `SendSignalNotificationJob` | default | Dispatches email/webhook notifications |

### Failed Jobs

Failed jobs are stored in `failed_jobs` table. Inspect with:
```bash
php artisan queue:failed
php artisan queue:retry all
php artisan queue:flush
```

---

## WebSocket Architecture

### Technology

Laravel Reverb implements the Pusher protocol, allowing use of any Pusher-compatible client (`laravel-echo` + `pusher-js`).

### Channel Types

| Channel | Pattern | Access | Events |
|---|---|---|---|
| Public market | `public-market.{asset}` | Anyone | `PriceUpdated` |
| Private signals | `private-signals.{userId}` | Auth user | `SignalGenerated` |
| Private backtests | `private-backtests.{userId}` | Auth user | `BacktestProgress`, `BacktestComplete` |
| Private system | `private-system` | Admin | `SystemHealthChanged` |

### Client Connection (Next.js)

```typescript
// lib/ws.ts
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

export const echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.NEXT_PUBLIC_WS_KEY,
    wsHost: process.env.NEXT_PUBLIC_WS_HOST,
    wsPort: Number(process.env.NEXT_PUBLIC_WS_PORT),
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

// Subscribe to market prices
echo.channel(`public-market.${asset}`)
    .listen('PriceUpdated', (e: PriceUpdatedEvent) => {
        updatePrice(e.price);
    });

// Subscribe to private signals
echo.private(`signals.${userId}`)
    .listen('SignalGenerated', (e: SignalGeneratedEvent) => {
        addSignal(e.signal);
    });
```
