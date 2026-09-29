'use client';

import React, { useState } from 'react';
import { SignalGenerator } from '@/components/signal/SignalGenerator';
import { CandlestickChart } from '@/components/charts/CandlestickChart';
import { useMarketData } from '@/hooks/useMarketData';

export default function SignalsPage() {
  const [selectedAsset, setSelectedAsset] = useState('EUR/USD');
  const [signalMarker, setSignalMarker] = useState<any>(null);
  const { candles } = useMarketData(selectedAsset);

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between pb-3 border-b border-[#243149]">
        <div>
          <h1 className="text-lg font-bold font-mono text-[#F5F7FB]">
            HIGH-CONFIDENCE SIGNAL GENERATOR
          </h1>
          <p className="text-xs text-[#98A4B8]">
            Multi-factor technical confirmation engine for binary OTC contracts
          </p>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        <div className="lg:col-span-5">
          <SignalGenerator
            asset={selectedAsset}
            onAssetChange={setSelectedAsset}
            onSignalGenerated={(sig) => {
              if (sig.direction === 'CALL' || sig.direction === 'PUT') {
                setSignalMarker({
                  direction: sig.direction,
                  price: sig.entry_price,
                  time: Math.floor(new Date(sig.candle_time).getTime() / 1000),
                });
              }
            }}
          />
        </div>

        <div className="lg:col-span-7">
          <CandlestickChart
            candles={candles}
            symbol={`${selectedAsset} (OTC)`}
            signalMarker={signalMarker}
            height={560}
          />
        </div>
      </div>
    </div>
  );
}
