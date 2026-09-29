export interface Asset {
  symbol: string;
  name: string;
  broker: string;
  is_otc: boolean;
  payout_percent: number;
  min_expiry: number;
  max_expiry: number;
  supported_timeframes: string[];
  is_active: boolean;
  last_price?: number;
  last_update?: string;
  spread?: number;
}

export interface Candle {
  time: number; // Unix timestamp
  open: number;
  high: number;
  low: number;
  close: number;
  volume?: number;
}

export interface PriceUpdate {
  asset: string;
  price: number;
  bid: number;
  ask: number;
  timestamp: string;
  change_24h?: number;
  change_pct_24h?: number;
}

export interface BrokerInfo {
  id: string;
  name: string;
  display_name: string;
  is_connected: boolean;
  last_heartbeat?: string;
  active_assets_count: number;
  latency_ms?: number;
  is_demo: boolean;
}

export interface TimeframeOption {
  value: string;
  label: string;
  seconds: number;
}

export interface ExpiryOption {
  value: number;
  label: string;
}

export type MarketStatus = 'OPEN' | 'CLOSED' | 'PRE_MARKET' | 'AFTER_HOURS';

export type ConnectionState = 'LIVE' | 'RECONNECTING' | 'OFFLINE' | 'STALE';

export interface MarketScannerItem {
  asset: Asset;
  current_price: number;
  trend: 'UP' | 'DOWN' | 'SIDEWAYS';
  volatility: number;
  volatility_label: 'LOW' | 'MEDIUM' | 'HIGH' | 'EXTREME';
  confidence: number;
  signal_direction?: 'CALL' | 'PUT' | 'NO_TRADE';
  signal_status?: string;
  last_updated: string;
}
