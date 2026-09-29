'use client';

import React from 'react';
import { ArrowUpRight, ArrowDownRight, Clock, ShieldCheck, Activity, BarChart2 } from 'lucide-react';
import { GeneratedSignal } from '@/types/signal';
import { ConfidenceRing } from './ConfidenceRing';
import { SignalReasons } from './SignalReasons';
import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';

interface SignalResultCardProps {
  signal: GeneratedSignal;
}

export function SignalResultCard({ signal }: SignalResultCardProps) {
  const isCall = signal.direction === 'CALL';

  return (
    <div className="bg-[#111A2A] border border-[#243149] rounded-xl overflow-hidden shadow-lg select-none">
      {/* Top Banner */}
      <div
        className={cn(
          'px-6 py-4 flex items-center justify-between border-b',
          isCall
            ? 'bg-[#18C78E]/10 border-[#18C78E]/30'
            : 'bg-[#FF4D6D]/10 border-[#FF4D6D]/30'
        )}
      >
        <div>
          <div className="text-[11px] font-mono text-[#98A4B8] tracking-widest uppercase">
            {signal.asset}
          </div>
          <div className="flex items-center gap-2 mt-0.5">
            <span
              className={cn(
                'text-3xl font-black font-mono tracking-wider flex items-center gap-1',
                isCall ? 'text-[#18C78E]' : 'text-[#FF4D6D]'
              )}
            >
              {isCall ? (
                <>
                  <ArrowUpRight className="w-8 h-8 stroke-[3]" /> CALL
                </>
              ) : (
                <>
                  <ArrowDownRight className="w-8 h-8 stroke-[3]" /> PUT
                </>
              )}
            </span>
          </div>
        </div>

        {/* Circular Confidence Display */}
        <ConfidenceRing confidence={signal.confidence} size={90} strokeWidth={8} />
      </div>

      {/* Primary Key Telemetry Grid */}
      <div className="p-6 space-y-5">
        <div className="grid grid-cols-2 sm:grid-cols-4 gap-3 bg-[#070B14]/60 p-3.5 rounded-lg border border-[#243149] font-mono text-xs">
          <div>
            <span className="text-[10px] text-[#98A4B8] uppercase block">EXPIRY</span>
            <span className="font-bold text-[#F5F7FB]">
              {signal.expiry_seconds / 60} MIN ({signal.expiry_seconds}s)
            </span>
          </div>
          <div>
            <span className="text-[10px] text-[#98A4B8] uppercase block">ENTRY PRICE</span>
            <span className="font-bold text-[#F5F7FB]">
              {signal.entry_price?.toFixed(5) ?? 'Market'}
            </span>
          </div>
          <div>
            <span className="text-[10px] text-[#98A4B8] uppercase block">REGIME</span>
            <span className="font-bold text-[#4165FF] truncate block">
              {signal.market_regime.replace('_', ' ')}
            </span>
          </div>
          <div>
            <span className="text-[10px] text-[#98A4B8] uppercase block">DATA QUALITY</span>
            <span className="font-bold text-[#18C78E]">
              {signal.data_quality}%
            </span>
          </div>
        </div>

        {/* Entry Reference Window */}
        <div className="flex items-center justify-between text-xs bg-[#111A2A] border border-[#243149] px-3.5 py-2 rounded-lg text-[#98A4B8]">
          <span className="flex items-center gap-1.5 font-mono text-[11px]">
            <Clock className="w-3.5 h-3.5 text-[#4165FF]" />
            ENTRY REFERENCE:
          </span>
          <span className="font-mono text-[#F5F7FB] font-semibold text-[11px]">
            Next candle open / next valid window
          </span>
        </div>

        {/* Reasons & Confirmations */}
        <div className="border-t border-[#243149] pt-4">
          <SignalReasons
            reasons={signal.reasons}
            warnings={signal.warnings}
            rejectionReasons={signal.rejection_reasons}
          />
        </div>

        {/* Legal & Risk Disclaimer */}
        <div className="p-3 bg-[#070B14] rounded-lg border border-[#243149]/60 text-[11px] text-[#98A4B8]/80 leading-relaxed font-sans">
          Historical confidence score based on current technical confirmations. Not a guarantee of outcome. Financial markets carry high risk of loss.
        </div>
      </div>
    </div>
  );
}
