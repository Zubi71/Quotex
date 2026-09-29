'use client';

import React, { useState, useEffect } from 'react';
import { BrokerSelector } from '@/components/market/BrokerSelector';
import { PairSelector } from '@/components/market/PairSelector';
import { TimeframeSelector } from '@/components/market/TimeframeSelector';
import { ExpirySelector } from '@/components/market/ExpirySelector';
import { Button } from '@/components/ui/button';
import { LoadingSteps } from './LoadingSteps';
import { SignalResultCard } from './SignalResultCard';
import { NoTradeCard } from './NoTradeCard';
import { Zap, Clock, RefreshCw } from 'lucide-react';
import { useSignalGeneration } from '@/hooks/useSignalGeneration';
import { useMarketData } from '@/hooks/useMarketData';
import { Asset } from '@/types/market';

interface SignalGeneratorProps {
  asset?: string;
  onAssetChange?: (asset: string) => void;
  onSignalGenerated?: (signal: any) => void;
}

const defaultAssets: Asset[] = [
  { id: 1, symbol: 'EUR/USD', displayName: 'EUR/USD (OTC)', assetType: 'OTC', isOtc: true, isActive: true, supportedTimeframes: ['S5', 'S15', 'S30', 'M1', 'M5'], supportedExpiries: [5, 10, 30, 60, 120, 300], payout: 82, marketStatus: 'open' },
  { id: 2, symbol: 'GBP/USD', displayName: 'GBP/USD (OTC)', assetType: 'OTC', isOtc: true, isActive: true, supportedTimeframes: ['S5', 'S15', 'S30', 'M1', 'M5'], supportedExpiries: [5, 10, 30, 60, 120, 300], payout: 85, marketStatus: 'open' },
  { id: 3, symbol: 'USD/JPY', displayName: 'USD/JPY (OTC)', assetType: 'OTC', isOtc: true, isActive: true, supportedTimeframes: ['S5', 'S15', 'S30', 'M1', 'M5'], supportedExpiries: [5, 10, 30, 60, 120, 300], payout: 80, marketStatus: 'open' },
  { id: 4, symbol: 'EUR/GBP', displayName: 'EUR/GBP (OTC)', assetType: 'OTC', isOtc: true, isActive: true, supportedTimeframes: ['S5', 'S15', 'S30', 'M1', 'M5'], supportedExpiries: [5, 10, 30, 60, 120, 300], payout: 78, marketStatus: 'open' },
  { id: 5, symbol: 'AUD/USD', displayName: 'AUD/USD (OTC)', assetType: 'OTC', isOtc: true, isActive: true, supportedTimeframes: ['S5', 'S15', 'S30', 'M1', 'M5'], supportedExpiries: [5, 10, 30, 60, 120, 300], payout: 80, marketStatus: 'open' },
];

export function SignalGenerator({
  asset: externalAsset,
  onAssetChange,
  onSignalGenerated,
}: SignalGeneratorProps) {
  const [broker, setBroker] = useState('mock');
  const [internalAsset, setInternalAsset] = useState('EUR/USD');
  const [timeframe, setTimeframe] = useState('M1');
  const [expiry, setExpiry] = useState(30);

  const asset = externalAsset || internalAsset;
  const setAsset = (val: string) => {
    setInternalAsset(val);
    onAssetChange?.(val);
  };

  const { generate, isLoading, signal, error } = useSignalGeneration();
  const [isProcessingSteps, setIsProcessingSteps] = useState(false);
  const [candleCountdown, setCandleCountdown] = useState(18);

  // Live Candle Countdown Timer
  useEffect(() => {
    const timer = setInterval(() => {
      setCandleCountdown((prev) => (prev > 0 ? prev - 1 : 59));
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  useEffect(() => {
    if (signal) {
      onSignalGenerated?.(signal);
    }
  }, [signal, onSignalGenerated]);

  const handleGenerate = () => {
    setIsProcessingSteps(true);
    generate(
      {
        asset,
        broker,
        timeframe,
        expiry_seconds: expiry,
      },
      {
        onSettled: () => {
          setTimeout(() => {
            setIsProcessingSteps(false);
          }, 300);
        },
      }
    );
  };

  const isBusy = isLoading || isProcessingSteps;

  return (
    <div className="space-y-5">
      {/* Top Configuration Card */}
      <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-5 space-y-4">
        <div className="flex items-center justify-between pb-3 border-b border-[#243149]">
          <span className="font-bold text-sm text-[#F5F7FB] flex items-center gap-2">
            <Zap className="w-4 h-4 text-[#4165FF]" />
            SIGNAL PARAMETERS
          </span>

          {/* Candle countdown timer */}
          <div className="flex items-center gap-1.5 font-mono text-[11px] bg-[#070B14] px-2.5 py-1 rounded border border-[#243149] text-[#98A4B8]">
            <Clock className="w-3.5 h-3.5 text-[#F5B942]" />
            <span>NEXT CANDLE:</span>
            <span className="text-[#F5F7FB] font-bold">
              00:{candleCountdown < 10 ? `0${candleCountdown}` : candleCountdown}
            </span>
          </div>
        </div>

        {/* Input Selectors */}
        <div className="space-y-3.5">
          <BrokerSelector value={broker} onChange={setBroker} />
          <PairSelector assets={defaultAssets} value={asset} onChange={setAsset} />
          <TimeframeSelector value={timeframe} onChange={setTimeframe} />
          <ExpirySelector value={expiry} onChange={setExpiry} />
        </div>

        {/* Generate Primary Button */}
        <Button
          onClick={handleGenerate}
          disabled={isBusy}
          className="w-full h-11 bg-[#4165FF] hover:bg-[#3454db] text-white font-bold font-mono text-xs tracking-wider transition-all duration-150 shadow-md shadow-[#4165FF]/20"
        >
          {isBusy ? (
            <span className="flex items-center gap-2">
              <RefreshCw className="w-4 h-4 animate-spin" />
              ANALYSING MARKET DATA...
            </span>
          ) : (
            <span className="flex items-center gap-2">
              <Zap className="w-4 h-4 fill-current" />
              GENERATE SIGNAL
            </span>
          )}
        </Button>
      </div>

      {/* Loading Steps during calculation */}
      {isBusy && (
        <LoadingSteps onComplete={() => {}} />
      )}

      {/* Result Display: Result Card or No Trade Card */}
      {!isBusy && signal && (
        signal.direction === 'NO_TRADE' ? (
          <NoTradeCard signal={signal} />
        ) : (
          <SignalResultCard signal={signal} />
        )
      )}

      {/* Error state */}
      {error && !isBusy && (
        <div className="p-4 bg-[#FF4D6D]/10 border border-[#FF4D6D]/30 rounded-xl text-xs text-[#FF4D6D]">
          {error.message || 'An unexpected error occurred during signal generation.'}
        </div>
      )}
    </div>
  );
}
