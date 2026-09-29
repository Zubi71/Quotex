'use client';

import React from 'react';
import { EquityPoint } from '@/types/backtest';

interface EquityCurveProps {
  data: EquityPoint[];
  initialBalance?: number;
  height?: number;
}

export function EquityCurve({
  data,
  initialBalance = 1000,
  height = 260,
}: EquityCurveProps) {
  if (!data || data.length === 0) {
    return (
      <div
        className="w-full bg-[#070B14] rounded-lg border border-[#243149] flex items-center justify-center text-xs text-[#98A4B8] font-mono"
        style={{ height }}
      >
        No backtest simulation executed yet
      </div>
    );
  }

  const balances = data.map((d) => d.balance);
  const minBalance = Math.min(...balances, initialBalance * 0.9);
  const maxBalance = Math.max(...balances, initialBalance * 1.1);
  const range = maxBalance - minBalance || 1;

  // Build SVG path
  const width = 800;
  const padding = 20;
  const innerWidth = width - padding * 2;
  const innerHeight = height - padding * 2;

  const points = data.map((d, i) => {
    const x = padding + (i / (data.length - 1 || 1)) * innerWidth;
    const y = height - padding - ((d.balance - minBalance) / range) * innerHeight;
    return `${x},${y}`;
  });

  const pathD = `M ${points.join(' L ')}`;
  const baselineY = height - padding - ((initialBalance - minBalance) / range) * innerHeight;
  const finalBalance = data[data.length - 1].balance;
  const isProfitable = finalBalance >= initialBalance;

  return (
    <div className="w-full bg-[#070B14] rounded-lg border border-[#243149] p-4 space-y-2">
      <div className="flex items-center justify-between text-xs font-mono">
        <span className="text-[#98A4B8]">EQUITY CURVE REPLAY</span>
        <span className={isProfitable ? 'text-[#18C78E] font-bold' : 'text-[#FF4D6D] font-bold'}>
          ${finalBalance.toFixed(2)} ({isProfitable ? '+' : ''}
          {(((finalBalance - initialBalance) / initialBalance) * 100).toFixed(2)}%)
        </span>
      </div>

      <div className="w-full overflow-hidden">
        <svg
          viewBox={`0 0 ${width} ${height}`}
          className="w-full h-auto overflow-visible select-none"
        >
          <defs>
            <linearGradient id="equityGrad" x1="0%" y1="0%" x2="0%" y2="100%">
              <stop offset="0%" stopColor="#4165FF" stopOpacity="0.3" />
              <stop offset="100%" stopColor="#4165FF" stopOpacity="0.0" />
            </linearGradient>
          </defs>

          {/* Baseline starting balance */}
          <line
            x1={padding}
            y1={baselineY}
            x2={width - padding}
            y2={baselineY}
            stroke="#243149"
            strokeDasharray="4 4"
            strokeWidth="1.5"
          />

          {/* Equity Line */}
          <path
            d={pathD}
            fill="none"
            stroke="#4165FF"
            strokeWidth="2.5"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      </div>

      <div className="flex items-center justify-between text-[10px] font-mono text-[#98A4B8]/60 pt-1">
        <span>START: ${initialBalance.toFixed(2)}</span>
        <span>TRADES REPLAYED: {data.length - 1}</span>
      </div>
    </div>
  );
}
