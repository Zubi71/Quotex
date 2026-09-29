'use client';

import React, { useState } from 'react';
import { CandlestickChart } from '@/components/charts/CandlestickChart';
import { SignalGenerator } from '@/components/signal/SignalGenerator';
import { SignalHistoryTable } from '@/components/history/SignalHistoryTable';
import { PerformanceCards } from '@/components/performance/PerformanceCards';
import { useMarketData } from '@/hooks/useMarketData';
import { usePerformance } from '@/hooks/usePerformance';
import { GeneratedSignal, SignalHistoryItem } from '@/types/signal';
import { Zap, Radio, Activity, ShieldCheck, ArrowRight } from 'lucide-react';
import Link from 'next/link';

export default function DashboardPage() {
  const [selectedAsset, setSelectedAsset] = useState('EUR/USD');
  const [signalMarker, setSignalMarker] = useState<{ direction: 'CALL' | 'PUT'; price: number; time: number } | null>(null);

  const { candles, isLoading: isCandlesLoading } = useMarketData(selectedAsset);
  const { data: metrics } = usePerformance('all');

  // Dynamic recent signals list
  const [recentSignals, setRecentSignals] = useState<SignalHistoryItem[]>([
    {
      id: 1,
      asset: 'EUR/USD (OTC)',
      broker: 'mock',
      direction: 'CALL',
      confidence: 86,
      status: 'HIGH_CONFIDENCE',
      expiry_seconds: 60,
      signal_time: new Date(Date.now() - 1000 * 60 * 3).toISOString(),
      candle_time: new Date(Date.now() - 1000 * 60 * 4).toISOString(),
      market_regime: 'TRENDING_BULLISH',
      data_quality: 96,
      strategy_version: '1.0.0',
      entry_price: 1.08515,
      close_price: 1.08535,
      result: 'WIN',
      profit_loss: 8.0,
      timeframe: 'M1',
      factors: [],
      reasons: ['Price above EMA 20 & 50', 'Bullish momentum alignment', 'Key support bounce'],
      warnings: [],
      is_mock: true,
    },
    {
      id: 2,
      asset: 'USD/JPY (OTC)',
      broker: 'mock',
      direction: 'PUT',
      confidence: 82,
      status: 'HIGH_CONFIDENCE',
      expiry_seconds: 60,
      signal_time: new Date(Date.now() - 1000 * 60 * 8).toISOString(),
      candle_time: new Date(Date.now() - 1000 * 60 * 9).toISOString(),
      market_regime: 'TRENDING_BEARISH',
      data_quality: 95,
      strategy_version: '1.0.0',
      entry_price: 149.620,
      close_price: 149.585,
      result: 'WIN',
      profit_loss: 8.0,
      timeframe: 'M1',
      factors: [],
      reasons: ['EMA alignment downward', 'MACD bearish expansion', 'Resistance rejected'],
      warnings: [],
      is_mock: true,
    },
    {
      id: 3,
      asset: 'GBP/USD (OTC)',
      broker: 'mock',
      direction: 'NO_TRADE',
      confidence: 58,
      status: 'NO_TRADE',
      expiry_seconds: 60,
      signal_time: new Date(Date.now() - 1000 * 60 * 15).toISOString(),
      candle_time: new Date(Date.now() - 1000 * 60 * 16).toISOString(),
      market_regime: 'UNCERTAIN',
      data_quality: 92,
      strategy_version: '1.0.0',
      entry_price: 1.27110,
      result: 'NO_TRADE',
      timeframe: 'M1',
      factors: [],
      reasons: [],
      warnings: ['Choppy consolidation'],
      rejection_reasons: ['Conflicting multi-oscillator signals', 'Confidence below 70% threshold'],
      is_mock: true,
    },
  ]);

  const handleSignalGenerated = (newSignal: GeneratedSignal) => {
    // Add to chart markers
    if (newSignal.direction === 'CALL' || newSignal.direction === 'PUT') {
      setSignalMarker({
        direction: newSignal.direction,
        price: newSignal.entry_price,
        time: Math.floor(new Date(newSignal.candle_time).getTime() / 1000),
      });
    }

    // Prepend to recent signals table
    const historyItem: SignalHistoryItem = {
      ...newSignal,
      result: 'PENDING',
      timeframe: 'M1',
    };
    setRecentSignals((prev) => [historyItem, ...prev.slice(0, 9)]);
  };

  return (
    <div className="space-y-6">
      {/* Top Telemetry Performance Cards */}
      <PerformanceCards metrics={metrics} />

      {/* Main Terminal Viewport (2-column layout) */}
      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {/* Left Column: Candlestick Chart (7/12 on large screens) */}
        <div className="lg:col-span-8 space-y-4">
          <CandlestickChart
            candles={candles}
            symbol={`${selectedAsset} (OTC)`}
            signalMarker={signalMarker}
            height={500}
          />
        </div>

        <div className="lg:col-span-4">
          <SignalGenerator
            asset={selectedAsset}
            onAssetChange={setSelectedAsset}
            onSignalGenerated={handleSignalGenerated}
          />
        </div>
      </div>

      {/* Bottom Section: Recent Recorded Signals */}
      <div className="space-y-3 pt-2">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <span className="font-bold text-xs font-mono text-[#F5F7FB]">
              RECENT VERIFIED SIGNAL STREAM
            </span>
            <span className="text-[10px] text-[#98A4B8] font-mono px-2 py-0.5 rounded bg-[#111A2A] border border-[#243149]">
              NON-REPAINTING LOG
            </span>
          </div>

          <Link
            href="/history"
            className="text-xs text-[#4165FF] hover:underline flex items-center gap-1 font-mono"
          >
            Full Signal History <ArrowRight className="w-3.5 h-3.5" />
          </Link>
        </div>

        <SignalHistoryTable signals={recentSignals} />
      </div>
    </div>
  );
}
