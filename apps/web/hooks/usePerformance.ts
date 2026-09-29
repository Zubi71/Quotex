'use client';

import { useQuery } from '@tanstack/react-query';
import { performanceApi } from '@/lib/api';
import { QUERY_KEYS } from '@/lib/constants';
import type { PeriodOption } from '@/types/performance';

export function usePerformance(period: PeriodOption | string = 'all', asset?: string) {
  return useQuery({
    queryKey: [...QUERY_KEYS.PERFORMANCE(period as string), asset],
    queryFn: () => performanceApi.getMetrics(period as any, asset),
    staleTime: 60_000,
    gcTime: 5 * 60_000,
  });
}
