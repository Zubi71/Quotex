import { api } from '@/lib/api';
import { GeneratedSignal, SignalHistoryItem } from '@/types/signal';

export interface GenerateSignalParams {
  asset: string;
  broker?: string;
  timeframe?: string;
  expiry_seconds?: number;
  confidence_threshold?: number;
}

export const signalService = {
  async generate(params: GenerateSignalParams): Promise<GeneratedSignal> {
    const res = await api.post('/signals/generate', params);
    return res.data.data;
  },

  async getHistory(params?: Record<string, any>): Promise<SignalHistoryItem[]> {
    const res = await api.get('/signals', { params });
    return res.data.data || res.data;
  },

  async getSignalById(id: number): Promise<GeneratedSignal> {
    const res = await api.get(`/signals/${id}`);
    return res.data.data;
  },
};
