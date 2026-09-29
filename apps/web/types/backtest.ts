export interface BacktestConfig {
  asset: string;
  broker: string;
  date_from: string;
  date_to: string;
  timeframe: string;
  expiry_seconds: number;
  confidence_threshold: number;
  initial_balance: number;
  stake_amount: number;
  payout_rate: number;
  strategy_version?: string;
}

export type BacktestStatus = 'PENDING' | 'RUNNING' | 'COMPLETED' | 'FAILED';

export interface BacktestSignal {
  id: number;
  candle_time: string;
  direction: 'CALL' | 'PUT';
  confidence: number;
  entry_price: number;
  close_price: number;
  result: 'WIN' | 'LOSS' | 'VOID';
  profit_loss: number;
  balance_after: number;
  market_regime: string;
}

export interface EquityPoint {
  time: string;
  balance: number;
  drawdown: number;
  trade_number: number;
}

export interface DrawdownPoint {
  time: string;
  drawdown_pct: number;
  balance: number;
}

export interface WalkForwardResult {
  period: string;
  win_rate: number;
  profit: number;
  signals: number;
}

export interface BenchmarkComparison {
  strategy: string;
  win_rate: number;
  profit: number;
  max_drawdown: number;
  profit_factor: number;
  total_trades: number;
}

export interface BacktestResult {
  id: string;
  status: BacktestStatus;
  config: BacktestConfig;
  created_at: string;
  completed_at?: string;
  error?: string;

  // Summary metrics
  total_signals: number;
  total_trades: number;
  wins: number;
  losses: number;
  voids: number;
  win_rate: number;
  profit: number;
  profit_pct: number;
  max_drawdown: number;
  max_drawdown_pct: number;
  profit_factor: number;
  avg_confidence: number;
  max_win_streak: number;
  max_loss_streak: number;
  no_trade_pct: number;

  // Detailed data
  signals: BacktestSignal[];
  equity_curve: EquityPoint[];
  drawdown_curve: DrawdownPoint[];
  walk_forward?: WalkForwardResult[];
  benchmarks?: BenchmarkComparison[];
}
