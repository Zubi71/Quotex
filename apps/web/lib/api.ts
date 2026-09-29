import axios, { AxiosError, AxiosInstance, AxiosResponse, InternalAxiosRequestConfig } from 'axios';
import type { ApiError, ApiResponse, LoginRequest, LoginResponse, PaginatedResponse } from '@/types/api';
import type { GeneratedSignal, SignalHistoryItem } from '@/types/signal';
import type { Asset, BrokerInfo, Candle, MarketScannerItem, PriceUpdate } from '@/types/market';
import type { BacktestConfig, BacktestResult } from '@/types/backtest';
import type { PerformanceMetrics, PeriodOption } from '@/types/performance';

const API_BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000';

// Create axios instance
const apiClient: AxiosInstance = axios.create({
  baseURL: API_BASE_URL,
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

// Request interceptor: add auth token
apiClient.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    if (typeof window !== 'undefined') {
      const token = localStorage.getItem('otc_auth_token') || localStorage.getItem('auth_token');
      if (token && config.headers) {
        config.headers.Authorization = `Bearer ${token}`;
      }
    }
    return config;
  },
  (error) => Promise.reject(error)
);

// Response interceptor: handle errors
apiClient.interceptors.response.use(
  (response: AxiosResponse) => response,
  async (error: AxiosError<ApiError>) => {
    if (error.response?.status === 401) {
      if (typeof window !== 'undefined') {
        localStorage.removeItem('otc_auth_token');
        window.location.href = '/login';
      }
    }

    const apiError: ApiError = {
      message: error.response?.data?.message || error.message || 'An unexpected error occurred',
      errors: error.response?.data?.errors,
      code: error.response?.data?.code,
      status: error.response?.status,
    };

    return Promise.reject(apiError);
  }
);

// ─── Helper ──────────────────────────────────────────────────────────────────
async function get<T>(url: string, params?: Record<string, unknown>): Promise<T> {
  const res = await apiClient.get<ApiResponse<T>>(url, { params });
  return res.data.data;
}

async function post<T, D = Record<string, unknown>>(url: string, data?: D): Promise<T> {
  const res = await apiClient.post<ApiResponse<T>>(url, data);
  return res.data.data;
}

// ─── Auth ─────────────────────────────────────────────────────────────────────
export const authApi = {
  login: (credentials: LoginRequest) =>
    post<LoginResponse>('/api/v1/auth/login', credentials),
  logout: () => post<void>('/api/v1/auth/logout'),
  me: () => get<LoginResponse['user']>('/api/v1/auth/me'),
};

// ─── Signals ──────────────────────────────────────────────────────────────────
export interface GenerateSignalRequest {
  asset: string;
  broker: string;
  timeframe: string;
  expiry_seconds: number;
}

export const signalsApi = {
  generate: (payload: GenerateSignalRequest) =>
    post<GeneratedSignal>('/api/v1/signals/generate', payload),

  getHistory: (params?: {
    asset?: string;
    direction?: string;
    result?: string;
    status?: string;
    from_date?: string;
    to_date?: string;
    min_confidence?: number;
    max_confidence?: number;
    page?: number;
    per_page?: number;
  }) => apiClient.get<PaginatedResponse<SignalHistoryItem>>('/api/v1/signals/history', { params }).then((r) => r.data),

  getById: (id: number) => get<SignalHistoryItem>(`/api/v1/signals/${id}`),

  updateResult: (id: number, result: 'WIN' | 'LOSS' | 'VOID', closePrice?: number) =>
    post<SignalHistoryItem>(`/api/v1/signals/${id}/result`, { result, close_price: closePrice }),

  exportCsv: (params?: Record<string, unknown>) =>
    apiClient.get('/api/v1/signals/export', { params, responseType: 'blob' }),
};

// ─── Market ───────────────────────────────────────────────────────────────────
export const marketApi = {
  getBrokers: () => get<BrokerInfo[]>('/api/v1/brokers'),

  getBrokerAssets: (broker: string) => get<Asset[]>(`/api/v1/brokers/${broker}/assets`),

  getCandles: (asset: string, timeframe: string, limit?: number) =>
    get<Candle[]>(`/api/v1/market/${encodeURIComponent(asset)}/candles`, { timeframe, limit }),

  getPrice: (asset: string) => get<PriceUpdate>(`/api/v1/market/${encodeURIComponent(asset)}/price`),

  getScannerData: (params?: { min_confidence?: number; sort?: string }) =>
    get<MarketScannerItem[]>('/api/v1/market/scanner', params),
};

// ─── Backtest ─────────────────────────────────────────────────────────────────
export const backtestApi = {
  run: (config: BacktestConfig) => post<BacktestResult>('/api/v1/backtests', config),
  getStatus: (id: string) => get<BacktestResult>(`/api/v1/backtests/${id}`),
  getList: () => get<BacktestResult[]>('/api/v1/backtests'),
};

// ─── Performance ──────────────────────────────────────────────────────────────
export const performanceApi = {
  getMetrics: (period: PeriodOption, asset?: string) =>
    get<PerformanceMetrics>('/api/v1/performance', { period, asset }),
};

// ─── Settings ─────────────────────────────────────────────────────────────────
export interface SettingsPayload {
  confidence_threshold?: number;
  min_data_quality?: number;
  min_candle_history?: number;
  max_signals_per_hour?: number;
  cooldown_seconds?: number;
  use_mock_data?: boolean;
  enable_auto_scan?: boolean;
  auto_scan_interval?: number;
  indicator_weights?: Record<string, number>;
}

export const settingsApi = {
  get: () => get<SettingsPayload>('/api/v1/settings'),
  update: (payload: SettingsPayload) => post<SettingsPayload>('/api/v1/settings', payload),
  getAuditLog: () => get<Array<{ id: number; user: string; action: string; changes: Record<string, unknown>; created_at: string }>>('/api/v1/settings/audit'),
};

// ─── Data Health ──────────────────────────────────────────────────────────────
export interface AssetHealthStatus {
  asset: string;
  quality_score: number;
  candle_count: number;
  gap_count: number;
  freshness_minutes: number;
  last_candle_time: string;
  status: 'HEALTHY' | 'DEGRADED' | 'STALE' | 'MISSING';
}

export const dataHealthApi = {
  getOverall: () => get<{ overall_score: number; assets: AssetHealthStatus[]; last_check: string }>('/api/v1/data/health'),
  getLogs: (limit?: number) => get<Array<{ id: number; level: string; asset: string; message: string; created_at: string }>>('/api/v1/data/health/logs', { limit }),
};

// ─── System Logs ──────────────────────────────────────────────────────────────
export const systemLogsApi = {
  getLogs: (params?: { level?: string; search?: string; page?: number; per_page?: number }) =>
    apiClient.get<PaginatedResponse<{ id: number; level: string; channel: string; message: string; context: Record<string, unknown>; created_at: string }>>('/api/v1/logs', { params }).then((r) => r.data),
};

// ─── Strategies ───────────────────────────────────────────────────────────────
export interface StrategyInfo {
  id: number;
  name: string;
  display_name: string;
  version: string;
  is_active: boolean;
  weight: number;
  description: string;
  parameters: Record<string, unknown>;
  version_history: Array<{ version: string; created_at: string; changes: string }>;
}

export const strategiesApi = {
  getAll: () => get<StrategyInfo[]>('/api/v1/strategies'),
  update: (id: number, payload: Partial<StrategyInfo>) => post<StrategyInfo>(`/api/v1/strategies/${id}`, payload),
};

export const api = apiClient;
export default apiClient;
