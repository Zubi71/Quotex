# OTC Signal Intelligence — REST & WebSocket API Documentation

Version: `1.0.0`  
Base URL: `/api/v1`  
Authentication: Bearer Token via Laravel Sanctum (`Authorization: Bearer <token>`)

---

## 1. Authentication Endpoints

### `POST /api/v1/auth/login`
Authenticate user credentials and retrieve bearer token.

**Request Body:**
```json
{
  "email": "trader@otcsignal.local",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "token": "1|N4c8Q7A1g6B2...",
  "user": {
    "id": 1,
    "name": "Quantitative Trader",
    "email": "trader@otcsignal.local",
    "is_admin": true
  }
}
```

### `POST /api/v1/auth/logout`
Revoke the currently active bearer token.

---

## 2. Broker & Asset Endpoints

### `GET /api/v1/brokers`
Retrieve all registered broker adapters.

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Mock Broker (Demo)",
      "slug": "mock",
      "is_mock": true,
      "is_active": true,
      "connection_status": "connected"
    },
    {
      "id": 2,
      "name": "Quotex",
      "slug": "quotex",
      "is_mock": false,
      "is_active": false,
      "connection_status": "disconnected"
    }
  ]
}
```

### `GET /api/v1/brokers/{broker}/assets`
Retrieve currently available OTC assets for the selected broker adapter.

**Response (200 OK):**
```json
{
  "data": [
    {
      "symbol": "EUR/USD",
      "display_name": "EUR/USD (OTC)",
      "asset_type": "OTC",
      "is_otc": true,
      "is_active": true,
      "supported_timeframes": ["M1", "M5", "M15", "H1"],
      "supported_expiries": [60, 120, 180, 300],
      "payout": 80.0,
      "market_status": "open",
      "source": "mock",
      "last_update": "2026-09-26T09:40:00Z"
    }
  ]
}
```

---

## 3. Market Data Endpoints

### `GET /api/v1/market/{asset}/candles`
Retrieve closed OHLCV candle history for indicator generation and charting.

**Query Parameters:**
- `broker` (string, optional, default: "mock")
- `timeframe` (string, optional, default: "M1")
- `count` (integer, optional, default: 250, max: 1000)
- `end` (integer, unix timestamp, optional)

**Response (200 OK):**
```json
{
  "asset": "EUR/USD",
  "broker": "mock",
  "timeframe": "M1",
  "quality_score": 98,
  "count": 250,
  "data": [
    {
      "timestamp": 1727337600,
      "open": 1.08510,
      "high": 1.08535,
      "low": 1.08505,
      "close": 1.08528,
      "volume": 1240.5,
      "is_closed": true
    }
  ]
}
```

---

## 4. Signal Intelligence Endpoints

### `POST /api/v1/signals/generate`
Evaluate latest market data, run multi-factor strategy ensemble, and generate structured trade signal or NO TRADE rejection.

**Request Body:**
```json
{
  "asset": "EUR/USD",
  "broker": "mock",
  "timeframe": "M1",
  "expiry_seconds": 60,
  "confidence_threshold": 70
}
```

**Response (200 OK — CALL / PUT Signal):**
```json
{
  "status": "success",
  "data": {
    "signal_uuid": "e7b0b65a-5ff7-44bc-87f5-832810f27916",
    "asset": "EUR/USD",
    "broker": "mock",
    "direction": "CALL",
    "confidence": 86,
    "status": "HIGH_CONFIDENCE",
    "expiry_seconds": 60,
    "signal_time": "2026-09-26T09:45:00Z",
    "candle_time": "2026-09-26T09:44:00Z",
    "market_regime": "TRENDING_BULLISH",
    "data_quality": 96,
    "strategy_version": "1.0.0",
    "entry_price": 1.08528,
    "factors": [
      {
        "name": "Price aligned above stacked EMAs",
        "score": 85.0,
        "weight": 26.0,
        "contribution": 22.1,
        "direction": "CALL"
      }
    ],
    "reasons": [
      "Price aligned above stacked EMAs (Close > EMA9 > EMA20 > EMA50)",
      "Strong directional trend confirmed by ADX (28.4 > 25)",
      "RSI in healthy bullish momentum zone (58.2)"
    ],
    "warnings": [],
    "rejection_reasons": [],
    "is_mock": true
  }
}
```

**Response (200 OK — NO TRADE Signal):**
```json
{
  "status": "success",
  "data": {
    "asset": "GBP/USD",
    "direction": "NO_TRADE",
    "confidence": 58,
    "status": "NO_TRADE",
    "rejection_reasons": [
      "Confluence agreement too low (Bullish: 42%, Bearish: 46%)",
      "Calculated confidence (58%) is below configured minimum threshold (70%)"
    ]
  }
}
```

---

## 5. Backtesting Endpoints

### `POST /api/v1/backtests`
Run sequential candle replay simulation with strict look-ahead bias prevention.

**Request Body:**
```json
{
  "asset": "EUR/USD",
  "broker": "mock",
  "timeframe": "M1",
  "expiry_seconds": 60,
  "confidence_threshold": 75,
  "initial_balance": 1000,
  "stake": 10,
  "payout_rate": 80,
  "candle_count": 500
}
```

**Response (200 OK):**
```json
{
  "status": "completed",
  "results": {
    "total_signals": 440,
    "total_trades": 88,
    "wins": 67,
    "losses": 21,
    "win_rate": 76.14,
    "profit": 326.0,
    "max_drawdown": 5.4,
    "profit_factor": 2.55,
    "no_trade_rate": 80.0,
    "equity_curve": [
      {"timestamp": "2026-09-26T00:00:00Z", "balance": 1000.0, "profit_loss": 0}
    ]
  }
}
```

---

## 6. Observability & Health

- `GET /api/v1/system/health`: Checks DB, Redis, Queue, WebSockets, Broker connectivity.
- `GET /api/v1/data/health`: Checks per-asset candle continuity, latency, and quality gates.
- `GET /api/v1/settings`: Fetches current system governance parameters.
- `PUT /api/v1/settings`: Updates governance settings (requires admin access, logged to audit trail).
