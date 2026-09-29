import { api } from '@/lib/api';
import { BacktestConfig, BacktestResults } from '@/types/backtest';

export const backtestService = {
  async run(config: BacktestConfig): Promise<BacktestResults> {
    const res = await api.post('/backtests', config);
    return res.data.results;
  },

  async list() {
    const res = await api.get('/backtests');
    return res.data.data;
  },

  async getById(id: number) {
    const res = await api.get(`/backtests/${id}`);
    return res.data.data;
  },
};
