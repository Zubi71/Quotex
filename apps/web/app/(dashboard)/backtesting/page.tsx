'use client';

import React, { useState } from 'react';
import { BacktestConfig } from '@/components/backtesting/BacktestConfig';
import { BacktestResults } from '@/components/backtesting/BacktestResults';
import { BacktestConfig as IBacktestConfig, BacktestResults as IBacktestResults } from '@/types/backtest';
import { useBacktest } from '@/hooks/useBacktest';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ShieldCheck, AlertCircle } from 'lucide-react';

const initialConfig: IBacktestConfig = {
  asset: 'EUR/USD',
  broker: 'mock',
  timeframe: 'M1',
  expiry_seconds: 60,
  date_from: '2024-01-01',
  date_to: '2024-01-07',
  confidence_threshold: 75,
  initial_balance: 1000,
  stake: 10,
  payout_rate: 80,
};

export default function BacktestingPage() {
  const [config, setConfig] = useState<IBacktestConfig>(initialConfig);
  const { runBacktest, results, isRunning, error } = useBacktest();

  const handleRun = async () => {
    await runBacktest(config);
  };

  return (
    <div className="space-y-6">
      <div className="pb-3 border-b border-[#243149]">
        <h1 className="text-lg font-bold font-mono text-[#F5F7FB]">
          QUANTITATIVE BACKTESTING & SIMULATION ENGINE
        </h1>
        <p className="text-xs text-[#98A4B8]">
          Sequential tick-by-tick candle replay with zero look-ahead bias and capital preservation filtering
        </p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        {/* Left Column: Configuration Form */}
        <div className="lg:col-span-4">
          <BacktestConfig
            config={config}
            onChange={setConfig}
            onRun={handleRun}
            isRunning={isRunning}
          />
        </div>

        {/* Right Column: Simulation Output */}
        <div className="lg:col-span-8 space-y-4">
          {error && (
            <div className="p-4 bg-[#FF4D6D]/10 border border-[#FF4D6D]/30 rounded-xl text-xs text-[#FF4D6D] flex items-center gap-2">
              <AlertCircle className="w-4 h-4 shrink-0" />
              <span>{error}</span>
            </div>
          )}

          {results ? (
            <BacktestResults results={results} />
          ) : (
            <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-12 text-center space-y-3">
              <ShieldCheck className="w-10 h-10 text-[#4165FF] mx-auto opacity-70" />
              <div className="text-sm font-bold font-mono text-[#F5F7FB]">
                READY FOR STRATEGY SIMULATION
              </div>
              <p className="text-xs text-[#98A4B8] max-w-md mx-auto leading-relaxed">
                Click &quot;EXECUTE SEQUENTIAL BACKTEST&quot; to replay historical candles through the multi-factor confidence engine.
                Signals are evaluated only with information available at candle close.
              </p>
            </div>
          )}
        </div>
      </div>
    </div>
  );
}
