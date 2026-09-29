'use client';

import React from 'react';
import { cn } from '@/lib/utils';

interface ExpirySelectorProps {
  expiries?: number[]; // in seconds
  value: number;
  onChange: (value: number) => void;
}

const defaultExpiries = [
  { seconds: 5, label: '5 SEC' },
  { seconds: 10, label: '10 SEC' },
  { seconds: 30, label: '30 SEC' },
  { seconds: 60, label: '1 MIN' },
  { seconds: 120, label: '2 MIN' },
  { seconds: 300, label: '5 MIN' },
];

export function ExpirySelector({ value, onChange }: ExpirySelectorProps) {
  return (
    <div className="space-y-1.5">
      <div className="flex items-center justify-between">
        <label className="text-[11px] font-mono text-[#98A4B8] tracking-wider uppercase block">
          EXPIRY / TRADE DURATION
        </label>
        <span className="text-[10px] font-mono text-[#4165FF] font-semibold">
          {value < 60 ? `${value}s Turbo` : `${Math.round(value / 60)}m Classic`}
        </span>
      </div>
      <div className="grid grid-cols-3 sm:grid-cols-6 gap-1.5 bg-[#111A2A] p-1 rounded-lg border border-[#243149]">
        {defaultExpiries.map((exp) => {
          const isSelected = value === exp.seconds;
          return (
            <button
              key={exp.seconds}
              type="button"
              onClick={() => onChange(exp.seconds)}
              className={cn(
                'py-1.5 px-1 rounded text-xs font-mono font-medium transition-all duration-150 text-center',
                isSelected
                  ? 'bg-[#4165FF] text-white font-bold shadow-sm'
                  : 'text-[#98A4B8] hover:text-[#F5F7FB] hover:bg-[#243149]/50'
              )}
            >
              {exp.label}
            </button>
          );
        })}
      </div>
    </div>
  );
}
