'use client';

import React from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Slider } from '@/components/ui/slider';
import { FlaskConical, Play } from 'lucide-react';
import { BacktestConfig as IBacktestConfig } from '@/types/backtest';

interface BacktestConfigProps {
  config: IBacktestConfig;
  onChange: (config: IBacktestConfig) => void;
  onRun: () => void;
  isRunning?: boolean;
}

export function BacktestConfig({
  config,
  onChange,
  onRun,
  isRunning = false,
}: BacktestConfigProps) {
  return (
    <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-5 space-y-4">
      <div className="flex items-center gap-2 pb-3 border-b border-[#243149] text-xs font-mono font-bold text-[#F5F7FB]">
        <FlaskConical className="w-4 h-4 text-[#4165FF]" />
        <span>BACKTEST ENGINE CONFIGURATION</span>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-xs">
        {/* Pair */}
        <div className="space-y-1.5">
          <label className="text-[10px] font-mono text-[#98A4B8] uppercase">OTC ASSET</label>
          <Select
            value={config.asset}
            onValueChange={(val) => onChange({ ...config, asset: val })}
          >
            <SelectTrigger className="bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]">
              <SelectValue />
            </SelectTrigger>
            <SelectContent className="bg-[#111A2A] border-[#243149] text-[#F5F7FB]">
              <SelectItem value="EUR/USD">EUR/USD (OTC)</SelectItem>
              <SelectItem value="GBP/USD">GBP/USD (OTC)</SelectItem>
              <SelectItem value="USD/JPY">USD/JPY (OTC)</SelectItem>
              <SelectItem value="EUR/GBP">EUR/GBP (OTC)</SelectItem>
              <SelectItem value="AUD/USD">AUD/USD (OTC)</SelectItem>
            </SelectContent>
          </Select>
        </div>

        {/* Timeframe */}
        <div className="space-y-1.5">
          <label className="text-[10px] font-mono text-[#98A4B8] uppercase">TIMEFRAME</label>
          <Select
            value={config.timeframe}
            onValueChange={(val) => onChange({ ...config, timeframe: val })}
          >
            <SelectTrigger className="bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]">
              <SelectValue />
            </SelectTrigger>
            <SelectContent className="bg-[#111A2A] border-[#243149] text-[#F5F7FB]">
              <SelectItem value="M1">M1 (1 Minute)</SelectItem>
              <SelectItem value="M5">M5 (5 Minutes)</SelectItem>
              <SelectItem value="M15">M15 (15 Minutes)</SelectItem>
            </SelectContent>
          </Select>
        </div>

        {/* Expiry */}
        <div className="space-y-1.5">
          <label className="text-[10px] font-mono text-[#98A4B8] uppercase">EXPIRY (SECONDS)</label>
          <Select
            value={String(config.expiry_seconds)}
            onValueChange={(val) => onChange({ ...config, expiry_seconds: Number(val) })}
          >
            <SelectTrigger className="bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]">
              <SelectValue />
            </SelectTrigger>
            <SelectContent className="bg-[#111A2A] border-[#243149] text-[#F5F7FB]">
              <SelectItem value="60">60s (1 MIN)</SelectItem>
              <SelectItem value="120">120s (2 MIN)</SelectItem>
              <SelectItem value="180">180s (3 MIN)</SelectItem>
              <SelectItem value="300">300s (5 MIN)</SelectItem>
            </SelectContent>
          </Select>
        </div>

        {/* Initial Capital */}
        <div className="space-y-1.5">
          <label className="text-[10px] font-mono text-[#98A4B8] uppercase">STARTING BALANCE ($)</label>
          <Input
            type="number"
            value={config.initial_balance}
            onChange={(e) => onChange({ ...config, initial_balance: Number(e.target.value) })}
            className="bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]"
          />
        </div>

        {/* Fixed Stake */}
        <div className="space-y-1.5">
          <label className="text-[10px] font-mono text-[#98A4B8] uppercase">STAKE PER TRADE ($)</label>
          <Input
            type="number"
            value={config.stake}
            onChange={(e) => onChange({ ...config, stake: Number(e.target.value) })}
            className="bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]"
          />
        </div>

        {/* Payout Rate */}
        <div className="space-y-1.5">
          <label className="text-[10px] font-mono text-[#98A4B8] uppercase">PAYOUT ASSUMPTION (%)</label>
          <Input
            type="number"
            value={config.payout_rate}
            onChange={(e) => onChange({ ...config, payout_rate: Number(e.target.value) })}
            className="bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]"
          />
        </div>
      </div>

      {/* Confidence Filter Slider */}
      <div className="pt-2 space-y-2">
        <div className="flex items-center justify-between text-xs font-mono">
          <span className="text-[#98A4B8]">CONFIDENCE FILTER THRESHOLD:</span>
          <span className="text-[#4165FF] font-bold">{config.confidence_threshold}%</span>
        </div>
        <Slider
          value={[config.confidence_threshold]}
          min={55}
          max={90}
          step={1}
          onValueChange={([val]) => onChange({ ...config, confidence_threshold: val })}
          className="py-1"
        />
        <div className="flex items-center justify-between text-[10px] font-mono text-[#98A4B8]/60">
          <span>55% (More Trades)</span>
          <span>90% (Strict High Confidence Only)</span>
        </div>
      </div>

      {/* Run Action Button */}
      <Button
        onClick={onRun}
        disabled={isRunning}
        className="w-full h-10 bg-[#4165FF] hover:bg-[#3454db] text-white font-mono font-bold text-xs"
      >
        <Play className="w-4 h-4 fill-current mr-2" />
        {isRunning ? 'SIMULATING CANDLE REPLAY...' : 'EXECUTE SEQUENTIAL BACKTEST'}
      </Button>
    </div>
  );
}
