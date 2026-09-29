import type { TimeframeOption, ExpiryOption } from '@/types/market';

// ─── Timeframes ────────────────────────────────────────────────────────────────
export const TIMEFRAMES: TimeframeOption[] = [
  { value: 'S5', label: '5S', seconds: 5 },
  { value: 'S15', label: '15S', seconds: 15 },
  { value: 'S30', label: '30S', seconds: 30 },
  { value: 'M1', label: '1M', seconds: 60 },
  { value: 'M5', label: '5M', seconds: 300 },
  { value: 'M15', label: '15M', seconds: 900 },
  { value: 'H1', label: '1H', seconds: 3600 },
];

// ─── Expiry Options ───────────────────────────────────────────────────────────
export const EXPIRY_OPTIONS: ExpiryOption[] = [
  { value: 5, label: '5 SEC' },
  { value: 10, label: '10 SEC' },
  { value: 30, label: '30 SEC' },
  { value: 60, label: '1 MIN' },
  { value: 120, label: '2 MIN' },
  { value: 300, label: '5 MIN' },
];

// ─── Confidence Thresholds ────────────────────────────────────────────────────
export const CONFIDENCE_THRESHOLDS = {
  HIGH: 80,
  MODERATE: 65,
  WEAK: 50,
  MINIMUM: 40,
} as const;

// ─── API Endpoints ────────────────────────────────────────────────────────────
export const API_ENDPOINTS = {
  // Auth
  LOGIN: '/api/v1/auth/login',
  LOGOUT: '/api/v1/auth/logout',
  ME: '/api/v1/auth/me',

  // Signals
  SIGNALS_GENERATE: '/api/v1/signals/generate',
  SIGNALS_HISTORY: '/api/v1/signals/history',
  SIGNALS_EXPORT: '/api/v1/signals/export',

  // Market
  BROKERS: '/api/v1/brokers',
  BROKER_ASSETS: (broker: string) => `/api/v1/brokers/${broker}/assets`,
  MARKET_CANDLES: (asset: string) => `/api/v1/market/${encodeURIComponent(asset)}/candles`,
  MARKET_PRICE: (asset: string) => `/api/v1/market/${encodeURIComponent(asset)}/price`,
  MARKET_SCANNER: '/api/v1/market/scanner',

  // Backtests
  BACKTESTS: '/api/v1/backtests',
  BACKTEST_STATUS: (id: string) => `/api/v1/backtests/${id}`,

  // Performance
  PERFORMANCE: '/api/v1/performance',

  // Settings
  SETTINGS: '/api/v1/settings',
  SETTINGS_AUDIT: '/api/v1/settings/audit',

  // Data Health
  DATA_HEALTH: '/api/v1/data/health',
  DATA_HEALTH_LOGS: '/api/v1/data/health/logs',

  // System Logs
  LOGS: '/api/v1/logs',

  // Strategies
  STRATEGIES: '/api/v1/strategies',
} as const;

// ─── WebSocket Channels ───────────────────────────────────────────────────────
export const WS_CHANNELS = {
  MARKET: (asset: string) => `market.${asset}`,
  SIGNALS: 'signals',
  SYSTEM: 'system',
} as const;

// ─── Color Map ────────────────────────────────────────────────────────────────
export const COLOR_MAP = {
  BG_PRIMARY: '#070B14',
  BG_SECONDARY: '#0D1422',
  BG_CARD: '#111A2A',
  BORDER: '#243149',
  PRIMARY: '#4165FF',
  POSITIVE: '#18C78E',
  NEGATIVE: '#FF4D6D',
  WARNING: '#F5B942',
  TEXT_PRIMARY: '#F5F7FB',
  TEXT_SECONDARY: '#98A4B8',
} as const;

// ─── Query Keys ───────────────────────────────────────────────────────────────
export const QUERY_KEYS = {
  BROKERS: ['brokers'] as const,
  BROKER_ASSETS: (broker: string) => ['brokers', broker, 'assets'] as const,
  CANDLES: (asset: string, timeframe: string) => ['candles', asset, timeframe] as const,
  PRICE: (asset: string) => ['price', asset] as const,
  SCANNER: ['scanner'] as const,
  SIGNAL_HISTORY: (filters?: Record<string, unknown>) => ['signal-history', filters] as const,
  PERFORMANCE: (period: string) => ['performance', period] as const,
  BACKTEST: (id: string) => ['backtest', id] as const,
  SETTINGS: ['settings'] as const,
  DATA_HEALTH: ['data-health'] as const,
  STRATEGIES: ['strategies'] as const,
} as const;

// ─── Chart Config ─────────────────────────────────────────────────────────────
export const CHART_COLORS = {
  EMA9: '#4165FF',
  EMA20: '#F5B942',
  EMA50: '#18C78E',
  EMA200: '#FF4D6D',
  BB_UPPER: '#4165FF',
  BB_LOWER: '#4165FF',
  BB_MIDDLE: '#98A4B8',
  VOLUME: '#243149',
} as const;

// ─── Navigation Items ─────────────────────────────────────────────────────────
export const NAV_ITEMS = [
  { label: 'Dashboard', href: '/dashboard', icon: 'LayoutDashboard' },
  { label: 'Signal Generator', href: '/signals', icon: 'Zap' },
  { label: 'Market Scanner', href: '/scanner', icon: 'Search' },
  { label: 'Signal History', href: '/history', icon: 'History' },
  { label: 'Backtesting', href: '/backtesting', icon: 'FlaskConical' },
  { label: 'Performance', href: '/performance', icon: 'TrendingUp' },
  { label: 'Strategies', href: '/strategies', icon: 'BrainCircuit' },
  { label: 'Data Health', href: '/data-health', icon: 'HeartPulse' },
  { label: 'System Logs', href: '/system-logs', icon: 'Terminal' },
  { label: 'Settings', href: '/settings', icon: 'Settings' },
] as const;
