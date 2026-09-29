import { api } from '@/lib/api';
import { PerformanceMetrics, PerformancePeriod } from '@/types/performance';

export const performanceService = {
  async getMetrics(period: PerformancePeriod = 'all_time'): Promise<PerformanceMetrics> {
    const res = await api.get('/performance', {
      params: { period },
    });
    return res.data.data;
  },

  async getSummary(): Promise<PerformanceMetrics> {
    const res = await api.get('/performance/summary');
    return res.data.data;
  },
};
