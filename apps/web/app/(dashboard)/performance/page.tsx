'use client';

import React, { useState } from 'react';
import { PerformanceCards } from '@/components/performance/PerformanceCards';
import { MetricsGrid } from '@/components/performance/MetricsGrid';
import { usePerformance } from '@/hooks/usePerformance';
import { PerformancePeriod } from '@/types/performance';
import { cn } from '@/lib/utils';
import { ShieldCheck, Info } from 'lucide-react';

const periods: { id: PerformancePeriod; label: string }[] = [
  { id: 'all_time', label: 'All Time' },
  { id: 'today', label: 'Today' },
  { id: '7_days', label: 'Last 7 Days' },
  { id: '30_days', label: 'Last 30 Days' },
  { id: 'last_50', label: 'Last 50 Trades' },
  { id: 'last_100', label: 'Last 100 Trades' },
];

export default function PerformancePage() {
  const [selectedPeriod, setSelectedPeriod] = useState<PerformancePeriod>('all_time');
  const { data: metrics, isLoading } = usePerformance(selectedPeriod);

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-[#243149] gap-4">
        <div>
          <h1 className="text-lg font-bold font-mono text-[#F5F7FB]">
            HISTORICAL AUDIT & RECORDED PERFORMANCE
          </h1>
          <p className="text-xs text-[#98A4B8]">
            Empirical statistics calculated strictly from resolved historical signals
          </p>
        </div>

        {/* Period Selector Tabs */}
        <div className="flex items-center gap-1 bg-[#111A2A] p-1 rounded-lg border border-[#243149] overflow-x-auto">
          {periods.map((p) => {
            const isSelected = selectedPeriod === p.id;
            return (
              <button
                key={p.id}
                onClick={() => setSelectedPeriod(p.id)}
                className={cn(
                  'px-3 py-1.5 rounded text-xs font-mono font-medium transition-all whitespace-nowrap',
                  isSelected
                    ? 'bg-[#4165FF] text-white font-bold'
                    : 'text-[#98A4B8] hover:text-[#F5F7FB] hover:bg-[#243149]/40'
                )}
              >
                {p.label}
              </button>
            );
          })}
        </div>
      </div>

      {/* Primary KPI Cards */}
      <PerformanceCards metrics={metrics} />

      {/* In-Depth Statistical Grid */}
      <MetricsGrid metrics={metrics} />

      {/* Compliance & Methodology Notice */}
      <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-4 text-xs font-sans text-[#98A4B8] space-y-2">
        <div className="flex items-center gap-2 font-mono font-bold text-[#F5F7FB]">
          <Info className="w-4 h-4 text-[#4165FF]" />
          <span>STATISTICAL INTEGRITY NOTICE</span>
        </div>
        <p className="leading-relaxed">
          All metrics on this page reflect actual executed signal outcomes logged by the platform.
          Signals below the configured confidence threshold ({(metrics?.avg_confidence ?? metrics?.average_confidence ?? 75) >= 70 ? '70%' : 'configured'})
          are marked as <strong>NO TRADE</strong> to preserve capital. No outcome is ever guaranteed.
        </p>
      </div>
    </div>
  );
}
