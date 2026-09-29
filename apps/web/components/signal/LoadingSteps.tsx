'use client';

import React, { useState, useEffect } from 'react';
import { CheckCircle2, Loader2, Circle } from 'lucide-react';
import { cn } from '@/lib/utils';

const STEPS = [
  'Checking market connection...',
  'Fetching latest closed candles...',
  'Calculating multi-period technical indicators...',
  'Analysing market regime & volatility state...',
  'Running strategy ensemble modules...',
  'Running trade quality gate filters...',
  'Calculating confluence confidence score...',
];

interface LoadingStepsProps {
  onComplete?: () => void;
}

export function LoadingSteps({ onComplete }: LoadingStepsProps) {
  const [currentStepIndex, setCurrentStepIndex] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setCurrentStepIndex((prev) => {
        if (prev < STEPS.length - 1) {
          return prev + 1;
        } else {
          clearInterval(timer);
          onComplete?.();
          return prev;
        }
      });
    }, 280);

    return () => clearInterval(timer);
  }, [onComplete]);

  return (
    <div className="py-6 px-4 bg-[#070B14]/80 rounded-xl border border-[#243149] space-y-3 font-mono text-xs">
      <div className="text-[11px] uppercase tracking-wider text-[#98A4B8] pb-1 border-b border-[#243149] flex items-center justify-between">
        <span>SIGNAL ENGINE PIPELINE</span>
        <span className="text-[#4165FF] animate-pulse">PROCESSING</span>
      </div>

      <div className="space-y-2">
        {STEPS.map((step, idx) => {
          const isDone = idx < currentStepIndex;
          const isCurrent = idx === currentStepIndex;
          const isPending = idx > currentStepIndex;

          return (
            <div
              key={step}
              className={cn(
                'flex items-center gap-2.5 transition-all duration-200',
                isDone && 'text-[#18C78E]',
                isCurrent && 'text-[#4165FF] font-semibold',
                isPending && 'text-[#98A4B8]/40'
              )}
            >
              {isDone && <CheckCircle2 className="w-3.5 h-3.5 shrink-0" />}
              {isCurrent && <Loader2 className="w-3.5 h-3.5 shrink-0 animate-spin text-[#4165FF]" />}
              {isPending && <Circle className="w-3.5 h-3.5 shrink-0 opacity-30" />}
              <span className="truncate">{step}</span>
            </div>
          );
        })}
      </div>
    </div>
  );
}
