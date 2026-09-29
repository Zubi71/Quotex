'use client';

import { useQuery } from '@tanstack/react-query';
import { marketApi } from '@/lib/api';
import { QUERY_KEYS } from '@/lib/constants';

export function useMarketData(asset: string = 'EUR/USD') {
  const candlesQuery = useCandles(asset, 'M1', 300);
  const priceQuery = usePrice(asset);

  return {
    candles: candlesQuery.data || [],
    isLoading: candlesQuery.isLoading,
    error: candlesQuery.error,
    latestPrice: priceQuery.data,
  };
}

export function useCandles(asset: string, timeframe: string, limit = 300, enabled = true) {
  return useQuery({
    queryKey: QUERY_KEYS.CANDLES(asset, timeframe),
    queryFn: () => marketApi.getCandles(asset, timeframe, limit),
    enabled: enabled && !!asset && !!timeframe,
    staleTime: 30_000,
    gcTime: 5 * 60_000,
    refetchInterval: 60_000,
  });
}

export function usePrice(asset: string, enabled = true) {
  return useQuery({
    queryKey: QUERY_KEYS.PRICE(asset),
    queryFn: () => marketApi.getPrice(asset),
    enabled: enabled && !!asset,
    staleTime: 5_000,
    gcTime: 30_000,
    refetchInterval: 5_000,
  });
}

export function useBrokers(enabled = true) {
  return useQuery({
    queryKey: QUERY_KEYS.BROKERS,
    queryFn: () => marketApi.getBrokers(),
    enabled,
    staleTime: 30_000,
    gcTime: 5 * 60_000,
  });
}

export function useBrokerAssets(broker: string, enabled = true) {
  return useQuery({
    queryKey: QUERY_KEYS.BROKER_ASSETS(broker),
    queryFn: () => marketApi.getBrokerAssets(broker),
    enabled: enabled && !!broker,
    staleTime: 60_000,
    gcTime: 10 * 60_000,
  });
}

export function useScanner(minConfidence?: number, enabled = true) {
  return useQuery({
    queryKey: QUERY_KEYS.SCANNER,
    queryFn: () => marketApi.getScannerData({ min_confidence: minConfidence }),
    enabled,
    staleTime: 15_000,
    refetchInterval: 30_000,
    gcTime: 5 * 60_000,
  });
}
