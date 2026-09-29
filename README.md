# OTC Signal Intelligence

> **A research-grade, open-architecture signal intelligence platform for OTC (Over-The-Counter) binary/digital options markets. Built with Laravel 11 (PHP 8.3) + Next.js 14, containerised with Docker, and designed for transparency, extensibility, and rigorous methodology.**

---

## ⚠️ Disclaimer

This software is provided **for educational and research purposes only**. Trading binary/digital options involves substantial risk of loss. OTC instruments carry additional counterparty risk. **This platform does not guarantee profits.** All signals are probabilistic estimates derived from historical price behaviour. Past performance is not indicative of future results. Do not risk money you cannot afford to lose.

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Quick Start (Docker)](#quick-start-docker)
3. [Manual Setup](#manual-setup)
4. [Environment Variables](#environment-variables)
5. [NPM & Composer Commands](#npm--composer-commands)
6. [Directory Structure](#directory-structure)
7. [Architecture Overview](#architecture-overview)
8. [Broker Integration Notes](#broker-integration-notes)
9. [Signal Engine Methodology](#signal-engine-methodology)
10. [Backtesting Methodology](#backtesting-methodology)
11. [Testing Commands](#testing-commands)
12. [Production Deployment](#production-deployment)
13. [Known Limitations](#known-limitations)

---

## Project Overview

OTC Signal Intelligence is a full-stack monorepo that provides:

| Feature | Description |
|---|---|
| **Signal Engine** | Multi-strategy technical analysis engine generating directional (CALL/PUT/NO TRADE) signals |
| **Broker Adapter Layer** | Pluggable broker adapter pattern (Mock, Quotex, extensible to others) |
| **Data Pipeline** | Candle ingestion, validation, normalisation, gap-detection, and caching |
| **Backtesting Engine** | Walk-forward, look-ahead-bias-free strategy evaluation |
| **Real-time Dashboard** | Next.js frontend with WebSocket live updates via Laravel Reverb |
| **API** | RESTful JSON API with Laravel Sanctum authentication |
| **Queue System** | Redis-backed Laravel queue for async signal generation and backtests |

### Technology Stack

| Layer | Technology |
|---|---|
| Backend API | PHP 8.3 / Laravel 11 |
| Frontend | Next.js 14 / React 18 / TypeScript |
| Database | MySQL 8.0 |
| Cache / Queue | Redis 7 |
| WebSockets | Laravel Reverb |
| Containerisation | Docker / Docker Compose |
| Web Server | Nginx (Alpine) |
| Package Manager (PHP) | Composer 2 |
| Package Manager (JS) | pnpm 9 |

---

## Quick Start (Docker)

### Prerequisites

- Docker Desktop >= 4.25 (with Compose v2)
- Git

### Steps

```bash
# 1. Clone the repository
git clone <your-repo-url> otc-signal-intelligence
cd otc-signal-intelligence

# 2. Copy environment file
cp .env.example .env
# Edit .env as needed — step 4 will generate APP_KEY

# 3. Build and start all services
docker compose up -d --build

# 4. Generate Laravel application key
docker compose exec api php artisan key:generate

# 5. Run database migrations and seed
docker compose exec api php artisan migrate --seed

# 6. Verify all services are running
docker compose ps
docker compose exec api php artisan system:health-check
```

After startup:

| Service | URL |
|---|---|
| Frontend (Next.js) | http://localhost |
| API | http://localhost:8000/api |
| WebSocket | ws://localhost:8080 |
| API Health | http://localhost:8000/api/system/health |

### Stopping

```bash
docker compose down          # Stop containers, keep volumes
docker compose down -v       # Stop containers AND delete volumes (data loss!)
```

---

## Manual Setup

### Prerequisites

- PHP 8.3 with extensions: `pdo_mysql`, `redis`, `bcmath`, `mbstring`, `xml`, `zip`, `pcntl`, `sockets`
- Composer 2.x
- Node.js 20.x
- pnpm 9.x (`npm install -g pnpm`)
- MySQL 8.0
- Redis 7.x

### Backend (Laravel API)

```bash
cd apps/api

# Install PHP dependencies
composer install

# Copy and configure environment
cp ../../.env.example .env
# Edit .env with your local DB/Redis credentials

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Start development server
php artisan serve --port=8000

# In a separate terminal: start queue worker
php artisan queue:work redis --sleep=1 --tries=3

# In a separate terminal: start scheduler (development)
php artisan schedule:work

# In a separate terminal: start WebSocket server
php artisan reverb:start --port=8080
```

### Frontend (Next.js)

```bash
cd apps/web

# Install JS dependencies
pnpm install

# Copy environment
cp .env.local.example .env.local
# Set NEXT_PUBLIC_API_URL=http://localhost:8000/api
# Set NEXT_PUBLIC_WS_URL=ws://localhost:8080

# Start development server
pnpm dev
```

The frontend will be available at http://localhost:3000.

---

## Environment Variables

All variables are defined in `.env.example` at the repository root. A detailed explanation of each group follows.

### Application

| Variable | Default | Description |
|---|---|---|
| `APP_NAME` | `OTC Signal Intelligence` | Application name used in UI and notifications |
| `APP_ENV` | `local` | Environment: `local`, `staging`, `production` |
| `APP_KEY` | *(empty)* | 32-byte base64 encryption key — **generate with `php artisan key:generate`** |
| `APP_DEBUG` | `true` | Enable detailed error pages. **Must be `false` in production** |
| `APP_URL` | `http://localhost` | Canonical URL used for link generation |
| `APP_PORT` | `8000` | Port the API is accessible on |

### Database

| Variable | Default | Description |
|---|---|---|
| `DB_CONNECTION` | `mysql` | Laravel database driver |
| `DB_HOST` | `mysql` | Hostname of MySQL server |
| `DB_PORT` | `3306` | MySQL port |
| `DB_DATABASE` | `otc_signal_intelligence` | Database name |
| `DB_USERNAME` | `otc_user` | Database user |
| `DB_PASSWORD` | `secret` | Database password — **change in production** |
| `DB_ROOT_PASSWORD` | `rootsecret` | MySQL root password — **change in production** |

### Redis

| Variable | Default | Description |
|---|---|---|
| `REDIS_HOST` | `redis` | Redis hostname |
| `REDIS_PASSWORD` | `null` | Redis AUTH password — set in production |
| `REDIS_PORT` | `6379` | Redis port |
| `QUEUE_CONNECTION` | `redis` | Laravel queue driver |
| `CACHE_DRIVER` | `redis` | Laravel cache driver |
| `SESSION_DRIVER` | `redis` | Laravel session driver |

### Broadcasting (Reverb WebSocket)

| Variable | Default | Description |
|---|---|---|
| `BROADCAST_DRIVER` | `reverb` | Laravel broadcast driver |
| `REVERB_APP_ID` | `otc-signal-app` | Reverb application identifier |
| `REVERB_APP_KEY` | `otc-signal-key` | Pusher-compatible app key — **change in production** |
| `REVERB_APP_SECRET` | `otc-signal-secret` | Pusher-compatible app secret — **change in production** |
| `REVERB_HOST` | `localhost` | WebSocket server hostname |
| `REVERB_PORT` | `8080` | WebSocket server port |
| `REVERB_SCHEME` | `http` | `http` or `https` |

### Quotex Adapter

| Variable | Default | Description |
|---|---|---|
| `QUOTEX_ADAPTER_ENABLED` | `false` | Enable Quotex WebSocket adapter |
| `QUOTEX_WS_URL` | *(empty)* | Quotex WebSocket endpoint |
| `QUOTEX_SESSION_TOKEN` | *(empty)* | Session cookie/token from authenticated Quotex browser session |
| `QUOTEX_ACCOUNT_TYPE` | `demo` | `demo` or `real` |

### Broker

| Variable | Default | Description |
|---|---|---|
| `DEFAULT_BROKER` | `mock` | Default broker adapter: `mock`, `quotex` |
| `MOCK_BROKER_SEED` | `42` | Random seed for deterministic mock data |

### Feature Flags

| Variable | Default | Description |
|---|---|---|
| `USE_MOCK_DATA` | `true` | Use generated mock candle data |
| `ENABLE_QUOTEX_ADAPTER` | `false` | Activate Quotex WebSocket connection |
| `ENABLE_AUTO_SCAN` | `false` | Automatically scan all assets on schedule |
| `ENABLE_NOTIFICATIONS` | `false` | Send signal notifications |
| `ENABLE_REAL_ACCOUNT_ACTIONS` | `false` | Allow trade execution on real accounts — **dangerous** |

### Signal Engine

| Variable | Default | Description |
|---|---|---|
| `DEFAULT_CONFIDENCE_THRESHOLD` | `70` | Minimum confidence score (0–100) to emit a signal |
| `DEFAULT_MIN_DATA_QUALITY` | `80` | Minimum data quality score to proceed |
| `DEFAULT_MIN_CANDLE_HISTORY` | `200` | Minimum candle count before running strategies |
| `MAX_SIGNALS_PER_HOUR` | `10` | Rate limit per asset per hour |
| `SIGNAL_COOLDOWN_SECONDS` | `60` | Minimum seconds between signals on same asset |

### Frontend

| Variable | Default | Description |
|---|---|---|
| `NEXT_PUBLIC_API_URL` | `http://localhost:8000/api` | Backend API base URL (exposed to browser) |
| `NEXT_PUBLIC_WS_URL` | `ws://localhost:8080` | WebSocket server URL (exposed to browser) |
| `NEXT_PUBLIC_APP_NAME` | `OTC Signal Intelligence` | App name in UI |
| `NEXT_PUBLIC_DEMO_MODE` | `true` | Show demo/mock data banner |

### Encryption

| Variable | Default | Description |
|---|---|---|
| `ENCRYPTION_KEY` | *(empty)* | Additional key for encrypting broker credentials at rest |

---

## NPM & Composer Commands

### Composer (from `apps/api`)

```bash
composer install                              # Install all dependencies
composer install --no-dev --optimize-autoloader  # Production install
composer update                               # Update dependencies
composer dump-autoload -o                     # Regenerate autoloader
composer audit                                # Security vulnerability check
```

### Laravel Artisan (from `apps/api`)

```bash
php artisan key:generate                      # Generate APP_KEY
php artisan migrate                           # Run migrations
php artisan migrate:fresh --seed              # Fresh DB + seed
php artisan db:seed --class=MockBrokerSeeder  # Run specific seeder
php artisan queue:work redis --sleep=1 --tries=3 --queue=default,signals,backtests
php artisan schedule:run                      # Run scheduler (production cron)
php artisan schedule:work                     # Run scheduler loop (development)
php artisan reverb:start --host=0.0.0.0 --port=8080
php artisan signals:scan-all                  # Scan all assets for signals
php artisan backtest:run --strategy=multi_strategy --asset=EURUSD_otc --from=2024-01-01 --to=2024-06-30
php artisan system:health-check               # System health verification
php artisan optimize:clear                    # Clear all caches
php artisan optimize                          # Cache config/routes for production
php artisan test                              # Run test suite
php artisan test --coverage                   # With coverage report
```

### pnpm (from `apps/web`)

```bash
pnpm install          # Install dependencies
pnpm dev              # Development server (port 3000)
pnpm build            # Production build
pnpm start            # Start production server
pnpm type-check       # TypeScript type checking
pnpm lint             # ESLint
pnpm lint:fix         # ESLint with auto-fix
pnpm format           # Prettier formatting
pnpm test             # Jest unit tests
pnpm test:coverage    # Tests with coverage
pnpm e2e              # Playwright e2e tests
```

---

## Directory Structure

```
otc-signal-intelligence/
├── .env.example                    # Environment variable template
├── .gitignore
├── README.md                       # This file
├── docker-compose.yml              # Development Docker Compose
├── docker-compose.prod.yml         # Production Docker Compose
│
├── apps/
│   ├── api/                        # Laravel 11 backend
│   │   ├── app/
│   │   │   ├── Console/Commands/   # Artisan commands
│   │   │   ├── Http/Controllers/   # API controllers
│   │   │   ├── Http/Middleware/    # Custom middleware
│   │   │   ├── Http/Requests/      # Form request validation
│   │   │   ├── Models/             # Eloquent models
│   │   │   ├── Services/
│   │   │   │   ├── Brokers/        # Broker adapter layer
│   │   │   │   │   ├── Contracts/  # BrokerAdapterInterface
│   │   │   │   │   ├── Mock/       # Mock broker implementation
│   │   │   │   │   └── Quotex/     # Quotex adapter stub
│   │   │   │   ├── DataPipeline/   # Candle ingestion & validation
│   │   │   │   ├── Indicators/     # Technical indicator calculators
│   │   │   │   ├── Signals/        # Signal generation engine
│   │   │   │   ├── Backtesting/    # Backtesting engine
│   │   │   │   └── MarketRegime/   # Market regime detection
│   │   │   ├── Jobs/               # Queue jobs
│   │   │   ├── Events/             # Broadcast events
│   │   │   └── Exceptions/         # Custom exception handlers
│   │   ├── config/
│   │   ├── database/migrations/
│   │   ├── database/seeders/
│   │   ├── routes/api.php
│   │   ├── routes/channels.php
│   │   ├── tests/Feature/
│   │   ├── tests/Unit/
│   │   ├── composer.json
│   │   └── phpunit.xml
│   │
│   └── web/                        # Next.js 14 frontend
│       ├── app/                    # App router pages
│       │   ├── dashboard/
│       │   ├── assets/
│       │   ├── signals/
│       │   ├── backtests/
│       │   └── settings/
│       ├── components/
│       │   ├── charts/             # Candlestick charts
│       │   ├── signals/            # Signal cards & history
│       │   ├── broker/             # Broker/asset selectors
│       │   └── ui/                 # Shared UI primitives
│       ├── hooks/                  # Custom React hooks
│       ├── lib/api.ts              # API client
│       ├── lib/ws.ts               # WebSocket client
│       ├── store/                  # Zustand global state
│       ├── types/                  # TypeScript definitions
│       ├── next.config.ts
│       ├── tailwind.config.ts
│       ├── tsconfig.json
│       └── package.json
│
├── infrastructure/
│   └── docker/
│       ├── nginx/
│       │   ├── nginx.conf          # Main Nginx config
│       │   └── default.conf        # Server blocks
│       └── php/
│           ├── Dockerfile          # PHP 8.3-FPM image
│           └── php.ini             # PHP production settings
│
├── docs/
│   ├── ARCHITECTURE.md
│   ├── API.md
│   ├── SIGNAL_ENGINE.md
│   ├── BACKTESTING.md
│   ├── DATA_PIPELINE.md
│   ├── DEPLOYMENT.md
│   ├── SECURITY.md
│   └── TESTING.md
│
└── scripts/
    ├── dev-setup.sh
    └── prod-deploy.sh
```

---

## Architecture Overview

```
+----------------------------------------------------------+
|                     CLIENT BROWSER                       |
|           Next.js 14 (React 18 / TypeScript)            |
+-----------------------------+----------------------------+
                              |
              +---------------+---------------+
              | HTTP/REST                     | WebSocket
              v                               v
+------------------------+    +------------------------------+
|        Nginx           |    |   Laravel Reverb (WS)        |
|    (Reverse Proxy)     |    |   port 8080                  |
+------------------------+    +------------------------------+
              |
              v
+----------------------------------------------------------+
|            Laravel 11 API (PHP-FPM 8.3)                  |
|                                                          |
|  +----------------+  +--------------+  +-------------+  |
|  |  HTTP Layer    |  | Signal Engine|  | Backtesting |  |
|  |  (Sanctum)     |  |  Pipeline    |  |   Engine    |  |
|  +----------------+  +--------------+  +-------------+  |
|                                                          |
|  +----------------------------------------------------+  |
|  |             Broker Adapter Layer                   |  |
|  |  +----------+  +----------+  +----------------+   |  |
|  |  |   Mock   |  |  Quotex  |  | Future Broker  |   |  |
|  |  | Adapter  |  |  Adapter |  |    Adapter     |   |  |
|  |  +----------+  +----------+  +----------------+   |  |
|  +----------------------------------------------------+  |
+---------------------------+------------------------------+
                            |
              +-------------+-------------+
              v                           v
+-------------------------+   +-------------------------+
|       MySQL 8.0         |   |        Redis 7          |
|     (Persistence)       |   |   (Cache/Queue/Session) |
+-------------------------+   +-------------------------+
```

For detailed architecture documentation, see [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md).

---

## Broker Integration Notes

### Adapter Pattern

Every broker implements `BrokerAdapterInterface`, which defines:

- `getAssets(): AssetCollection`
- `getCandles(string $asset, int $timeframe, int $count): CandleCollection`
- `getPrice(string $asset): PriceQuote`
- `getPayout(string $asset, int $expiry): float`
- `getStatus(string $asset): AssetStatus`

### Mock Broker

The built-in `MockBrokerAdapter` generates deterministic OHLCV candle data using a seeded random walk with:
- Configurable volatility per asset class
- Simulated spread
- Realistic payout tiers (72%–92%) varying by time-of-day
- Deterministic output for reproducible backtests (controlled by `MOCK_BROKER_SEED`)

### Quotex Adapter (Integration Point)

The Quotex adapter is a **stub** requiring:
1. A valid `QUOTEX_SESSION_TOKEN` (from an authenticated browser session)
2. The WebSocket URL (`QUOTEX_WS_URL`)
3. `QUOTEX_ADAPTER_ENABLED=true` and `ENABLE_QUOTEX_ADAPTER=true`

> **Important**: Reverse-engineering or scraping broker APIs may violate their Terms of Service. Ensure you have appropriate authorisation before connecting to live broker endpoints.

### Adding a New Broker

1. Create `app/Services/Brokers/{BrokerName}/{BrokerName}Adapter.php` implementing `BrokerAdapterInterface`
2. Register it in `app/Services/Brokers/BrokerFactory.php`
3. Add environment variables to `.env.example`
4. Write unit tests in `tests/Unit/Brokers/`

---

## Signal Engine Methodology

The signal engine uses a **multi-strategy consensus model**:

### Strategies

| Strategy | Description |
|---|---|
| `TrendFollowing` | EMA crossover + ADX trend strength confirmation |
| `MeanReversion` | RSI + Bollinger Band squeeze/expansion |
| `MomentumBreakout` | MACD + volume surge detection |
| `CandlestickPattern` | Pin bars, engulfing, doji at key levels |
| `SupportResistance` | Pivot-based level detection with price reaction scoring |

### Confidence Scoring

Each strategy votes CALL, PUT, or NO_TRADE with a confidence 0–100. The engine:

1. Applies **market regime detection** (trending/ranging/choppy) to weight strategies
2. Computes a **weighted average confidence** across agreeing strategies
3. Applies **quality gates** (data quality, candle count, spread check)
4. Emits a signal only if confidence >= `DEFAULT_CONFIDENCE_THRESHOLD`

Full methodology: [docs/SIGNAL_ENGINE.md](docs/SIGNAL_ENGINE.md)

---

## Backtesting Methodology

- **Look-ahead bias prevention**: All indicators computed on closed candles only; open candle always excluded
- **Walk-forward validation**: Optimisation on in-sample data, validation on out-of-sample data
- **Metrics**: Win rate, profit factor, expectancy, maximum drawdown, Sharpe-like ratio
- **Overfitting warning**: Parameters with fewer than 300 trades flagged

Full methodology: [docs/BACKTESTING.md](docs/BACKTESTING.md)

---

## Testing Commands

```bash
# Backend
cd apps/api
php artisan test                                              # All tests
php artisan test --coverage --min=80                         # With coverage
php artisan test --testsuite=Unit                            # Unit only
php artisan test --testsuite=Feature                         # Feature only
php artisan test --filter=candles_never_include_open_candle  # Specific test
./vendor/bin/phpstan analyse --level=8                       # Static analysis
./vendor/bin/php-cs-fixer fix --dry-run --diff               # Code style check
./vendor/bin/php-cs-fixer fix                                # Code style apply

# Frontend
cd apps/web
pnpm test              # Jest unit tests
pnpm test:coverage     # With coverage
pnpm type-check        # TypeScript check
pnpm lint              # ESLint
pnpm e2e               # Playwright e2e
```

---

## Production Deployment

See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) for the full guide.

```bash
# 1. Configure .env for production (APP_DEBUG=false, APP_ENV=production)
# 2. Build and deploy
docker compose -f docker-compose.prod.yml up -d --build
# 3. Migrate
docker compose -f docker-compose.prod.yml exec api php artisan migrate --force
# 4. Cache configuration
docker compose -f docker-compose.prod.yml exec api php artisan optimize
# 5. Verify
curl https://yourdomain.com/api/system/health
```

### Critical Production Checklist

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` generated and backed up securely
- [ ] Strong `DB_PASSWORD` and `DB_ROOT_PASSWORD`
- [ ] Strong `REVERB_APP_KEY` and `REVERB_APP_SECRET`
- [ ] `REDIS_PASSWORD` set
- [ ] SSL certificate configured in Nginx
- [ ] `ENABLE_REAL_ACCOUNT_ACTIONS=false` (unless explicitly intended)
- [ ] Log level set to `error` or `warning`
- [ ] MySQL volume backups configured
- [ ] Rate limiting verified

---

## Known Limitations

| Limitation | Details |
|---|---|
| **Quotex adapter is a stub** | Real WebSocket integration requires careful analysis and carries ToS risk |
| **OTC market hours** | OTC assets trade 24/7 but spreads and payouts vary widely; the engine does not model all payout tiers dynamically |
| **No execution engine** | This platform generates signals only; it does NOT place trades automatically unless a future execution adapter is built |
| **1-minute timeframe focus** | Strategies are tuned for M1–M5; higher timeframes may require re-tuning |
| **Mock data limitations** | Mock broker uses a random walk — it does not replicate real market microstructure |
| **Single-timezone assumption** | All timestamps are stored and processed in UTC |
| **No multi-user isolation** | Assumes single operator; multi-tenant isolation not implemented |
| **Backtests are not Monte Carlo** | Walk-forward validation used but Monte Carlo simulation of trade order is not implemented |
| **PHP scheduler in Docker** | Uses a shell loop in the scheduler service; in production prefer a proper cron daemon |
