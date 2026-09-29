import { api } from '@/lib/api';
import { Asset, BrokerInfo, Candle } from '@/types/market';

export const marketService = {
  async getBrokers(): Promise<BrokerInfo[]> {
    const res = await api.get('/brokers');
    return res.data.data;
  },

  async getAssets(brokerSlug: string = 'mock'): Promise<Asset[]> {
    const res = await api.get(`/brokers/${brokerSlug}/assets`);
    return res.data.data;
  },

  async getCandles(
    asset: string,
    broker: string = 'mock',
    timeframe: string = 'M1',
    count: number = 250
  ): Promise<Candle[]> {
    const res = await api.get(`/market/${encodeURIComponent(asset)}/candles`, {
      params: { broker, timeframe, count },
    });
    return res.data.data;
  },

  async getPrice(asset: string, broker: string = 'mock') {
    const res = await api.get(`/market/${encodeURIComponent(asset)}/price`, {
      params: { broker },
    });
    return res.data.data;
  },

  async getPayout(asset: string, broker: string = 'mock') {
    const res = await api.get(`/market/${encodeURIComponent(asset)}/payout`, {
      params: { broker },
    });
    return res.data.payout;
  },
};
