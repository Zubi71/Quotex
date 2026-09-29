export type SignalDirection = 'CALL' | 'PUT' | 'NO_TRADE';
export type SignalStatus = 'HIGH_CONFIDENCE' | 'MODERATE' | 'WEAK' | 'NO_TRADE';
export type SignalResult = 'WIN' | 'LOSS' | 'VOID' | 'PENDING';
export type MarketRegime =
  | 'TRENDING_BULLISH'
  | 'TRENDING_BEARISH'
  | 'RANGING'
  | 'HIGH_VOLATILITY'
  | 'LOW_VOLATILITY'
  | 'UNCERTAIN';

export interface SignalFactor {
  name: string;
  score: number;
  weight: number;
  contribution: number;
  direction: SignalDirection;
}

export interface GeneratedSignal {
  id?: number;
  signal_uuid?: string;
  asset: string;
  broker: string;
  direction: SignalDirection;
  confidence: number;
  status: SignalStatus;
  expiry_seconds: number;
  signal_time: string;
  candle_time: string;
  market_regime: MarketRegime;
  data_quality: number;
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
}
