'use client';

import React from 'react';
import { cn } from '@/lib/utils';

interface TimeframeSelectorProps {
  timeframes?: string[];
  value: string;
  onChange: (value: string) => void;
}

const defaultTimeframes = ['S5', 'S15', 'S30', 'M1', 'M5', 'M15'];

export function TimeframeSelector({
  timeframes = defaultTimeframes,
  value,
  onChange,
}: TimeframeSelectorProps) {
  return (
    <div className="space-y-1.5">
      <label className="text-[11px] font-mono text-[#98A4B8] tracking-wider uppercase block">
        TIMEFRAME (EXECUTION)
      </label>
      <div className="grid grid-cols-3 sm:grid-cols-6 gap-1.5 bg-[#111A2A] p-1.5 rounded-lg border border-[#243149]">
        {timeframes.map((tf) => {
          const isSelected = value === tf;
          return (
            <button
              key={tf}
              type="button"
              onClick={() => onChange(tf)}
              className={cn(
                'py-1.5 px-1 rounded text-xs font-mono font-medium transition-all duration-150 text-center',
                isSelected
                  ? 'bg-[#4165FF] text-white font-bold shadow-sm ring-1 ring-[#4165FF]/50'
                  : 'text-[#98A4B8] hover:text-[#F5F7FB] hover:bg-[#243149]/50'
              )}
            >
              {tf}
            </button>
          );
        })}
      </div>
    </div>
  );
}
