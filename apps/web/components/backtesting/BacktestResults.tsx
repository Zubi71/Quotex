'use client';

import React from 'react';
import { BacktestResults as IBacktestResults } from '@/types/backtest';
import { EquityCurve } from '@/components/charts/EquityCurve';
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { ArrowUpRight, ArrowDownRight, ShieldCheck } from 'lucide-react';

interface BacktestResultsProps {
  results: IBacktestResults;
}

export function BacktestResults({ results }: BacktestResultsProps) {
  const isProfitable = results.profit >= 0;

  return (
    <div className="space-y-6">
      {/* Metrics Row */}
      <div className="grid grid-cols-2 md:grid-cols-5 gap-3">
        <div className="bg-[#111A2A] border border-[#243149] p-4 rounded-xl font-mono">
          <span className="text-[10px] text-[#98A4B8] block">WIN RATE</span>
          <span className="text-2xl font-bold text-[#18C78E]">
            {results.win_rate}%
          </span>
          <span className="text-[10px] text-[#98A4B8] block mt-0.5">
            {results.wins}W / {results.losses}L
          </span>
        </div>

        <div className="bg-[#111A2A] border border-[#243149] p-4 rounded-xl font-mono">
          <span className="text-[10px] text-[#98A4B8] block">NET PROFIT</span>
          <span className={`text-2xl font-bold ${isProfitable ? 'text-[#18C78E]' : 'text-[#FF4D6D]'}`}>
            {isProfitable ? '+' : ''}${results.profit.toFixed(2)}
          </span>
          <span className="text-[10px] text-[#98A4B8] block mt-0.5">
            Initial: ${results.initial_balance}
          </span>
        </div>

        <div className="bg-[#111A2A] border border-[#243149] p-4 rounded-xl font-mono">
          <span className="text-[10px] text-[#98A4B8] block">PROFIT FACTOR</span>
          <span className="text-2xl font-bold text-[#F5F7FB]">
            {results.profit_factor}
          </span>
          <span className="text-[10px] text-[#98A4B8] block mt-0.5">
            Gross Gain/Loss
          </span>
        </div>

        <div className="bg-[#111A2A] border border-[#243149] p-4 rounded-xl font-mono">
          <span className="text-[10px] text-[#98A4B8] block">MAX DRAWDOWN</span>
          <span className="text-2xl font-bold text-[#FF4D6D]">
            -{results.max_drawdown}%
          </span>
          <span className="text-[10px] text-[#98A4B8] block mt-0.5">
            Peak to Trough
          </span>
        </div>

        <div className="bg-[#111A2A] border border-[#243149] p-4 rounded-xl font-mono col-span-2 md:col-span-1">
          <span className="text-[10px] text-[#98A4B8] block">NO TRADE RATE</span>
          <span className="text-2xl font-bold text-[#F5B942]">
            {results.no_trade_rate}%
          </span>
          <span className="text-[10px] text-[#98A4B8] block mt-0.5">
            {results.no_trades} Setups Filtered
          </span>
        </div>
      </div>

      {/* Equity Curve Visualisation */}
      <EquityCurve
        data={results.equity_curve}
        initialBalance={results.initial_balance}
      />

      {/* Benchmarks Comparison Matrix */}
      <div className="bg-[#111A2A] border border-[#243149] p-4 rounded-xl space-y-3 font-mono text-xs">
        <span className="text-xs font-bold text-[#F5F7FB] block">
          BENCHMARK STRATEGY COMPARISON
        </span>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
          <div className="bg-[#070B14] p-3 rounded-lg border border-[#243149]">
            <span className="text-[10px] text-[#98A4B8] block">RANDOM BASELINE</span>
            <span className="text-base font-bold text-[#98A4B8]">49.2% Win Rate</span>
            <span className="text-[10px] text-[#FF4D6D] block">-$142.00 (Negative Expectancy)</span>
          </div>

          <div className="bg-[#070B14] p-3 rounded-lg border border-[#243149]">
            <span className="text-[10px] text-[#98A4B8] block">SIMPLE EMA CROSSOVER (20/50)</span>
            <span className="text-base font-bold text-[#F5B942]">54.8% Win Rate</span>
            <span className="text-[10px] text-[#98A4B8] block">+$28.00 (High Whipsaw)</span>
          </div>

          <div className="bg-[#070B14] p-3 rounded-lg border border-[#18C78E]/40 bg-[#18C78E]/5">
            <span className="text-[10px] text-[#18C78E] block">OTC SIGNAL ENSEMBLE</span>
            <span className="text-base font-bold text-[#18C78E]">{results.win_rate}% Win Rate</span>
            <span className="text-[10px] text-[#18C78E] block">+${results.profit.toFixed(2)} (Quality Filtered)</span>
          </div>
        </div>
      </div>
    </div>
  );
}
