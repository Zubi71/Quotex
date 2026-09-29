'use client';

import { useMutation, useQuery } from '@tanstack/react-query';
import { useState } from 'react';
import { backtestApi } from '@/lib/api';
import { QUERY_KEYS } from '@/lib/constants';
import type { BacktestConfig, BacktestResult } from '@/types/backtest';

export function useBacktest() {
  const [backtestId, setBacktestId] = useState<string | null>(null);
  const [isPolling, setIsPolling] = useState(false);

  const mutation = useMutation({
    mutationFn: (config: BacktestConfig) => backtestApi.run(config),
    onSuccess: (data) => {
      if (data.id) {
        setBacktestId(data.id);
        setIsPolling(data.status === 'PENDING' || data.status === 'RUNNING');
      }
    },
  });

  const statusQuery = useQuery({
    queryKey: QUERY_KEYS.BACKTEST(backtestId ?? ''),
    queryFn: () => backtestApi.getStatus(backtestId!),
    enabled: !!backtestId && isPolling,
    refetchInterval: (query) => {
      const data = query.state.data;
      if (!data) return 2_000;
      if (data.status === 'COMPLETED' || data.status === 'FAILED') {
        setIsPolling(false);
        return false;
      }
      return 2_000;
    },
    staleTime: 0,
  });

  const results: BacktestResult | null =
    statusQuery.data ?? (mutation.data ?? null);

  return {
    runBacktest: mutation.mutate,
    isRunning: mutation.isPending || isPolling,
    status: results?.status ?? null,
    results,
    error: mutation.error,
    reset: () => {
      setBacktestId(null);
      setIsPolling(false);
      mutation.reset();
    },
  };
}
