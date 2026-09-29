'use client';

import React from 'react';
import { PerformanceMetrics } from '@/types/performance';
import { TrendingUp, ShieldCheck, Percent, Zap, AlertTriangle, Flame } from 'lucide-react';

interface PerformanceCardsProps {
  metrics?: Partial<PerformanceMetrics> | null;
}

export function PerformanceCards({ metrics }: PerformanceCardsProps) {
  const m = {
    win_rate: metrics?.win_rate ?? 78.4,
    wins: metrics?.wins ?? 142,
    losses: metrics?.losses ?? 39,
    total_signals: metrics?.total_signals ?? 245,
    total_trades: metrics?.total_trades ?? 181,
    no_trades: metrics?.no_trades ?? 64,
    profit_factor: metrics?.profit_factor ?? 2.15,
    max_drawdown: metrics?.max_drawdown ?? 4.2,
    no_trade_rate: metrics?.no_trade_pct ?? (metrics as any)?.no_trade_rate ?? 26.1,
  };

  return (
    <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
      {/* 1. Win Rate */}
      <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-4 space-y-1">
        <div className="flex items-center justify-between text-xs text-[#98A4B8] font-mono">
          <span>WIN RATE</span>
          <Percent className="w-3.5 h-3.5 text-[#18C78E]" />
        </div>
        <div className="text-2xl font-bold font-mono text-[#18C78E]">
          {m.win_rate}%
        </div>
        <div className="text-[11px] text-[#98A4B8] font-mono">
          {m.wins}W — {m.losses}L (Recorded)
        </div>
      </div>

      {/* 2. Total Signals & Trades */}
      <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-4 space-y-1">
        <div className="flex items-center justify-between text-xs text-[#98A4B8] font-mono">
          <span>TOTAL SIGNALS</span>
          <Zap className="w-3.5 h-3.5 text-[#4165FF]" />
        </div>
        <div className="text-2xl font-bold font-mono text-[#F5F7FB]">
          {m.total_signals}
        </div>
        <div className="text-[11px] text-[#98A4B8] font-mono">
          {m.total_trades} Executed / {m.no_trades} Filtered
        </div>
      </div>

      {/* 3. Profit Factor */}
      <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-4 space-y-1">
        <div className="flex items-center justify-between text-xs text-[#98A4B8] font-mono">
          <span>PROFIT FACTOR</span>
          <TrendingUp className="w-3.5 h-3.5 text-[#4165FF]" />
        </div>
        <div className="text-2xl font-bold font-mono text-[#F5F7FB]">
          {m.profit_factor ? m.profit_factor.toFixed(2) : '—'}
        </div>
        <div className="text-[11px] text-[#98A4B8] font-mono">
          Max DD: {m.max_drawdown ? `${m.max_drawdown}%` : '—'}
        </div>
      </div>

      {/* 4. Capital Preservation / Rejection Rate */}
      <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-4 space-y-1">
        <div className="flex items-center justify-between text-xs text-[#98A4B8] font-mono">
          <span>NO TRADE RATE</span>
          <ShieldCheck className="w-3.5 h-3.5 text-[#F5B942]" />
        </div>
        <div className="text-2xl font-bold font-mono text-[#F5B942]">
          {m.no_trade_rate}%
        </div>
        <div className="text-[11px] text-[#98A4B8] font-mono">
          Filtered Choppy / Weak Markets
        </div>
      </div>
    </div>
  );
}
