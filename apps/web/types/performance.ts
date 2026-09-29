export type PeriodOption = 'today' | '7d' | '30d' | 'all' | 'last50' | 'last100';
export type PerformancePeriod = PeriodOption | 'all_time' | '7_days' | '30_days' | 'last_50' | 'last_100';

export interface PerformanceMetrics {
  period: PeriodOption;
  total_signals: number;
  total_trades: number;
  wins: number;
  losses: number;
  voids: number;
  no_trades: number;
  win_rate: number;
  loss_rate: number;
  void_rate: number;
  no_trade_pct: number;
  profit: number;
  profit_pct: number;
  profit_factor: number;
  avg_confidence: number;
  avg_win_confidence: number;
  avg_loss_confidence: number;
  max_drawdown: number;
  max_drawdown_pct: number;
  max_win_streak: number;
  max_loss_streak: number;
  current_streak: number;
  current_streak_type: 'WIN' | 'LOSS' | null;
  high_confidence_count: number;
  high_confidence_win_rate: number;
  by_asset: AssetPerformance[];
  by_regime: RegimePerformance[];
  confidence_distribution: ConfidenceBucket[];
  equity_points: PerformanceEquityPoint[];
}

export interface AssetPerformance {
  asset: string;
  total_trades: number;
  wins: number;
  losses: number;
  win_rate: number;
  profit: number;
  avg_confidence: number;
}

export interface RegimePerformance {
  regime: string;
  total_trades: number;
  wins: number;
  win_rate: number;
  avg_confidence: number;
}

export interface ConfidenceBucket {
  range: string;
  min: number;
  max: number;
  count: number;
  win_rate: number;
}

export interface PerformanceEquityPoint {
  time: string;
  balance: number;
  cumulative_profit: number;
}

export interface WinRateData {
  wins: number;
  losses: number;
  voids: number;
  no_trades: number;
}
