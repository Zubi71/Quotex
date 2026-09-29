'use client';

import React from 'react';
import { ShieldAlert, AlertCircle, HelpCircle } from 'lucide-react';
import { GeneratedSignal } from '@/types/signal';
import { ConfidenceRing } from './ConfidenceRing';
import { SignalReasons } from './SignalReasons';

interface NoTradeCardProps {
  signal: GeneratedSignal;
}

export function NoTradeCard({ signal }: NoTradeCardProps) {
  return (
    <div className="bg-[#111A2A] border border-[#F5B942]/40 rounded-xl overflow-hidden shadow-lg select-none">
      {/* Header Banner */}
      <div className="bg-[#F5B942]/10 border-b border-[#F5B942]/30 px-6 py-4 flex items-center justify-between">
        <div>
          <div className="text-[11px] font-mono text-[#98A4B8] tracking-widest uppercase">
            {signal.asset}
          </div>
          <div className="flex items-center gap-2 mt-0.5">
            <ShieldAlert className="w-7 h-7 text-[#F5B942]" />
            <span className="text-3xl font-black font-mono tracking-wider text-[#F5B942]">
              NO TRADE
            </span>
          </div>
        </div>

        {/* Ring */}
        <ConfidenceRing confidence={signal.confidence} size={90} strokeWidth={8} />
      </div>

      {/* Main Content */}
      <div className="p-6 space-y-5">
        <div className="bg-[#070B14]/80 p-3.5 rounded-lg border border-[#243149] text-xs font-mono text-[#98A4B8] space-y-1.5">
          <div className="flex items-center justify-between">
            <span>MARKET REGIME:</span>
            <span className="text-[#F5B942] font-bold">
              {signal.market_regime.replace('_', ' ')}
            </span>
          </div>
          <div className="flex items-center justify-between">
            <span>DATA QUALITY SCORE:</span>
            <span className="text-[#F5F7FB] font-bold">
              {signal.data_quality}%
            </span>
          </div>
          <div className="flex items-center justify-between">
            <span>STATUS:</span>
            <span className="text-[#FF4D6D] font-bold">
              SETUP REJECTED (PRESERVE CAPITAL)
            </span>
          </div>
        </div>

        {/* Rejection Details & Guidance */}
        <SignalReasons
          reasons={signal.reasons}
          warnings={signal.warnings}
          rejectionReasons={
            signal.rejection_reasons && signal.rejection_reasons.length > 0
              ? signal.rejection_reasons
              : ['Insufficient confluence across indicators to warrant an execution']
          }
        />

        {/* Notice */}
        <div className="p-3 bg-[#070B14] rounded-lg border border-[#243149]/60 text-[11px] text-[#98A4B8] leading-relaxed font-sans">
          The system prioritises signal quality over quantity. Rejecting low-confidence, choppy, or contradictory market conditions is a foundational risk protection safeguard.
        </div>
      </div>
    </div>
  );
}
