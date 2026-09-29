'use client';

import React from 'react';
import { Check, AlertTriangle, XCircle } from 'lucide-react';

interface SignalReasonsProps {
  reasons?: string[];
  warnings?: string[];
  rejectionReasons?: string[];
}

export function SignalReasons({
  reasons = [],
  warnings = [],
  rejectionReasons = [],
}: SignalReasonsProps) {
  return (
    <div className="space-y-4 text-xs font-sans">
      {/* Rejection Reasons (if NO_TRADE) */}
      {rejectionReasons.length > 0 && (
        <div className="space-y-1.5 bg-[#FF4D6D]/10 border border-[#FF4D6D]/30 p-3 rounded-lg">
          <span className="font-semibold font-mono text-[11px] text-[#FF4D6D] uppercase tracking-wider block">
            REJECTION REASONS
          </span>
          <div className="space-y-1">
            {rejectionReasons.map((rej, i) => (
              <div key={i} className="flex items-start gap-2 text-[#F5F7FB]">
                <XCircle className="w-3.5 h-3.5 text-[#FF4D6D] shrink-0 mt-0.5" />
                <span className="leading-snug">{rej}</span>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Confirming Technical Reasons */}
      {reasons.length > 0 && (
        <div className="space-y-1.5">
          <span className="font-semibold font-mono text-[11px] text-[#98A4B8] uppercase tracking-wider block">
            TECHNICAL CONFIRMATIONS
          </span>
          <div className="space-y-1.5">
            {reasons.map((reason, i) => (
              <div key={i} className="flex items-start gap-2 text-[#F5F7FB]">
                <Check className="w-3.5 h-3.5 text-[#18C78E] shrink-0 mt-0.5" />
                <span className="leading-snug">{reason}</span>
              </div>
            ))}
          </div>
        </div>
      )}

      {/* Warnings & Risk Factors */}
      {warnings.length > 0 && (
        <div className="space-y-1.5 pt-1">
          <span className="font-semibold font-mono text-[11px] text-[#F5B942] uppercase tracking-wider block">
            CAUTIONARY WARNINGS
          </span>
          <div className="space-y-1">
            {warnings.map((warn, i) => (
              <div key={i} className="flex items-start gap-2 text-[#98A4B8]">
                <AlertTriangle className="w-3.5 h-3.5 text-[#F5B942] shrink-0 mt-0.5" />
                <span className="leading-snug">{warn}</span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
