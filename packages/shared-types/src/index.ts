/**
 * OTC Signal Intelligence — Shared TypeScript Types
 * Shared between web app and any future packages.
 */

// ─── Signal Types ─────────────────────────────────────────────────────────────

export type SignalDirection = 'CALL' | 'PUT' | 'NO_TRADE';
export type SignalStatus = 'HIGH_CONFIDENCE' | 'MODERATE' | 'WEAK' | 'NO_TRADE';
export type SignalResult = 'WIN' | 'LOSS' | 'VOID' | 'PENDING' | 'NO_TRADE';
export type MarketRegime =
  | 'TRENDING_BULLISH'
  | 'TRENDING_BEARISH'
  | 'RANGING'
  | 'HIGH_VOLATILITY'
  | 'LOW_VOLATILITY'
  | 'UNCERTAIN';

export interface SignalFactor {
  name: string;
  score: number;       // 0–100
  weight: number;      // percentage weight in total score
  contribution: number; // score * weight / 100
  direction: SignalDirection;
}

export interface GeneratedSignal {
  id?: number;
  signal_uuid?: string;
  asset: string;
  broker: string;
  direction: SignalDirection;
  confidence: number;         // 0–100
  status: SignalStatus;
  expiry_seconds: number;
  signal_time: string;        // ISO 8601
  candle_time: string;        // ISO 8601 — the last closed candle used
  market_regime: MarketRegime;
  data_quality: number;       // 0–100
  strategy_version: string;
  entry_price: number;
  factors: SignalFactor[];
  reasons: string[];
  warnings: string[];
  rejection_reasons?: string[];
  is_mock: boolean;
}

export interface SignalHistoryItem extends GeneratedSignal {
  result: SignalResult;
  close_price?: number;
  result_time?: string;
  profit_loss?: number;
  timeframe: string;
}

// ─── Market Types ──────────────────────────────────────────────────────────────

export interface Candle {
  timestamp: number;  // Unix seconds
  open: number;
  high: number;
  low: number;
  close: number;
  volume: number;
  isClosed: boolean;
}

export interface Asset {
  id: number;
  symbol: string;
  displayName: string;
  assetType: string;
  isOtc: boolean;
  isActive: boolean;
  supportedTimeframes: string[];
  supportedExpiries: number[];
  payout?: number;
  marketStatus: 'open' | 'closed' | 'suspended';
}

export interface BrokerInfo {
  id: number;
  name: string;
  slug: string;
  isMock: boolean;
  isActive: boolean;
  connectionStatus: 'connected' | 'disconnected' | 'error' | 'unknown';
  lastConnectedAt?: string;
}

export type ConnectionState = 'LIVE' | 'RECONNECTING' | 'OFFLINE' | 'STALE';

export interface MarketStatus {
  state: ConnectionState;
  broker: string;
  lastUpdate: string | null;
  latencyMs: number | null;
}

// ─── Backtest Types ────────────────────────────────────────────────────────────

export interface BacktestConfig {
  asset: string;
  broker: string;
  timeframe: string;
  expiry_seconds: number;
  date_from: string;
  date_to: string;
  confidence_threshold: number;
  initial_balance: number;
  stake: number;
  payout_rate: number;
}

export interface EquityPoint {
  timestamp: string;
  balance: number;
  trade_result?: SignalResult;
  profit_loss?: number;
}

export interface BacktestResults {
  total_signals: number;
  total_trades: number;
  wins: number;
  losses: number;
  void_trades: number;
  no_trades: number;
  win_rate: number;
  profit: number;
  max_drawdown: number;
  profit_factor: number;
  expectancy: number;
  signal_frequency: number;
  no_trade_rate: number;
  equity_curve: EquityPoint[];
  initial_balance: number;
  final_balance: number;
}

export interface BacktestRun {
  id: number;
  status: 'pending' | 'running' | 'completed' | 'failed';
  config: BacktestConfig;
  results?: BacktestResults;
  error_message?: string;
  started_at?: string;
  completed_at?: string;
  created_at: string;
}

// ─── Performance Types ─────────────────────────────────────────────────────────

export type PerformancePeriod = 'today' | '7_days' | '30_days' | 'all_time' | 'last_50' | 'last_100' | 'last_500';

export interface PerformanceMetrics {
  period: PerformancePeriod;
  total_signals: number;
  total_trades: number;
  wins: number;
  losses: number;
  void_trades: number;
  no_trades: number;
  win_rate: number;
  average_confidence: number;
  profit_factor: number | null;
  max_drawdown: number | null;
  longest_win_streak: number;
  longest_loss_streak: number;
  expectancy: number | null;
  no_trade_rate: number;
  calculated_at: string;
}

// ─── API Types ─────────────────────────────────────────────────────────────────

export interface ApiResponse<T> {
  data: T;
  message?: string;
  meta?: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  type?: string;
  code?: number;
}

// ─── Settings Types ────────────────────────────────────────────────────────────

export interface AppSettings {
  confidence_threshold: number;        // 70
  min_data_quality: number;            // 80
  min_candle_history: number;          // 200
  max_signals_per_hour: number;        // 10
  signal_cooldown_seconds: number;     // 60
  min_payout: number;                  // 70
  use_mock_data: boolean;
  enable_auto_scan: boolean;
  enable_notifications: boolean;
  auto_scan_interval_seconds: number;  // 300
  // Factor weights (must sum to 100)
  weight_trend_alignment: number;      // 15
  weight_momentum: number;             // 15
  weight_rsi: number;                  // 10
  weight_macd: number;                 // 10
  weight_ema_alignment: number;        // 10
  weight_bollinger: number;            // 8
  weight_support_resistance: number;   // 10
  weight_candlestick: number;          // 10
  weight_volatility_regime: number;    // 5
  weight_multi_timeframe: number;      // 5
  weight_market_structure: number;     // 2
}

// ─── WebSocket Event Types ─────────────────────────────────────────────────────

export interface WsCandleUpdate {
  event: 'candle.closed';
  data: {
    symbol: string;
    timeframe: string;
    candle: Candle;
  };
}

export interface WsSignalUpdate {
  event: 'signal.generated' | 'signal.resolved';
  data: GeneratedSignal | SignalHistoryItem;
}

export interface WsPriceUpdate {
  event: 'price.update';
  data: {
    symbol: string;
    price: number;
    timestamp: number;
  };
}

export type WsMessage = WsCandleUpdate | WsSignalUpdate | WsPriceUpdate;

// ─── Confidence Threshold Mapping ─────────────────────────────────────────────

export const CONFIDENCE_THRESHOLDS = {
  NO_TRADE: { min: 0, max: 59, label: 'NO TRADE', color: '#FF4D6D' },
  WEAK: { min: 60, max: 69, label: 'WEAK', color: '#F5B942' },
  MODERATE: { min: 70, max: 79, label: 'MODERATE', color: '#F5B942' },
  HIGH_CONFIDENCE: { min: 80, max: 89, label: 'HIGH CONFIDENCE', color: '#18C78E' },
  VERY_HIGH_CONFIDENCE: { min: 90, max: 100, label: 'VERY HIGH CONFIDENCE', color: '#18C78E' },
} as const;

export function getConfidenceStatus(confidence: number): SignalStatus {
  if (confidence >= 90) return 'HIGH_CONFIDENCE';
  if (confidence >= 80) return 'HIGH_CONFIDENCE';
  if (confidence >= 70) return 'MODERATE';
  if (confidence >= 60) return 'WEAK';
  return 'NO_TRADE';
}
