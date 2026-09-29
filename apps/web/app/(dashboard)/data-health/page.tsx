'use client';

import React from 'react';
import { Activity, CheckCircle2, AlertTriangle, ShieldCheck, Database, Server, Clock } from 'lucide-react';
import { Badge } from '@/components/ui/badge';

const assetHealth = [
  { asset: 'EUR/USD (OTC)', score: 98, latency: '0.4ms', continuity: '100%', candles: 10080, gaps: 0, status: 'OPTIMAL' },
  { asset: 'GBP/USD (OTC)', score: 96, latency: '0.5ms', continuity: '99.9%', candles: 10080, gaps: 1, status: 'OPTIMAL' },
  { asset: 'USD/JPY (OTC)', score: 97, latency: '0.4ms', continuity: '100%', candles: 10080, gaps: 0, status: 'OPTIMAL' },
  { asset: 'EUR/GBP (OTC)', score: 94, latency: '0.6ms', continuity: '99.8%', candles: 10080, gaps: 2, status: 'OPTIMAL' },
  { asset: 'AUD/USD (OTC)', score: 96, latency: '0.4ms', continuity: '100%', candles: 10080, gaps: 0, status: 'OPTIMAL' },
];

export default function DataHealthPage() {
  return (
    <div className="space-y-6">
      <div className="pb-3 border-b border-[#243149]">
        <h1 className="text-lg font-bold font-mono text-[#F5F7FB]">
          MARKET DATA PIPELINE HEALTH & INTEGRITY
        </h1>
        <p className="text-xs text-[#98A4B8]">
          Real-time metrics on candle continuity, timestamp gaps, latency, and quality gates
        </p>
      </div>

      {/* Main KPI Card */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div className="bg-[#111A2A] border border-[#243149] p-5 rounded-xl space-y-2">
          <div className="flex items-center justify-between text-xs font-mono text-[#98A4B8]">
            <span>OVERALL DATA QUALITY</span>
            <Activity className="w-4 h-4 text-[#18C78E]" />
          </div>
          <div className="text-3xl font-bold font-mono text-[#18C78E]">
            96.2 / 100
          </div>
          <div className="text-[11px] text-[#98A4B8]">
            All active instruments satisfy &gt;80% quality threshold
          </div>
        </div>

        <div className="bg-[#111A2A] border border-[#243149] p-5 rounded-xl space-y-2">
          <div className="flex items-center justify-between text-xs font-mono text-[#98A4B8]">
            <span>INGESTION LATENCY</span>
            <Server className="w-4 h-4 text-[#4165FF]" />
          </div>
          <div className="text-3xl font-bold font-mono text-[#F5F7FB]">
            0.46 ms
          </div>
          <div className="text-[11px] text-[#98A4B8]">
            Realtime in-memory rolling candle buffer
          </div>
        </div>

        <div className="bg-[#111A2A] border border-[#243149] p-5 rounded-xl space-y-2">
          <div className="flex items-center justify-between text-xs font-mono text-[#98A4B8]">
            <span>QUALITY GATE STATUS</span>
            <ShieldCheck className="w-4 h-4 text-[#18C78E]" />
          </div>
          <div className="text-3xl font-bold font-mono text-[#18C78E]">
            ACTIVE
          </div>
          <div className="text-[11px] text-[#98A4B8]">
            Blocks signals if data staleness exceeds 2x timeframe
          </div>
        </div>
      </div>

      {/* Per-Asset Data Health Table */}
      <div className="bg-[#111A2A] border border-[#243149] rounded-xl overflow-hidden p-5 space-y-4">
        <span className="font-bold text-xs font-mono text-[#F5F7FB] block">
          PER-INSTRUMENT DATA INTEGRITY BREAKDOWN
        </span>

        <div className="space-y-3">
          {assetHealth.map((item) => (
            <div
              key={item.asset}
              className="flex flex-col sm:flex-row sm:items-center justify-between p-3.5 bg-[#070B14] rounded-lg border border-[#243149] gap-3 text-xs font-mono"
            >
              <div className="space-y-0.5">
                <span className="font-bold text-[#F5F7FB] block">{item.asset}</span>
                <span className="text-[10px] text-[#98A4B8]">
                  History: {item.candles.toLocaleString()} closed bars
                </span>
              </div>

              <div className="flex flex-wrap items-center gap-4 text-[11px]">
                <div>
                  <span className="text-[#98A4B8] block text-[9px]">CONTINUITY</span>
                  <span className="text-[#F5F7FB] font-bold">{item.continuity}</span>
                </div>
                <div>
                  <span className="text-[#98A4B8] block text-[9px]">LATENCY</span>
                  <span className="text-[#F5F7FB] font-bold">{item.latency}</span>
                </div>
                <div>
                  <span className="text-[#98A4B8] block text-[9px]">GAPS</span>
                  <span className="text-[#18C78E] font-bold">{item.gaps}</span>
                </div>
                <div>
                  <span className="text-[#98A4B8] block text-[9px]">SCORE</span>
                  <span className="text-[#18C78E] font-bold">{item.score}%</span>
                </div>
                <Badge className="bg-[#18C78E]/15 text-[#18C78E] border border-[#18C78E]/30 text-[10px]">
                  {item.status}
                </Badge>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
