'use client';

import React, { useState } from 'react';
import { Switch } from '@/components/ui/switch';
import { Slider } from '@/components/ui/slider';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Sliders, CheckCircle2, History, ShieldAlert } from 'lucide-react';
import { useToast } from '@/hooks/useToast';

interface StrategyItem {
  id: number;
  name: string;
  slug: string;
  description: string;
  weight: number;
  isActive: boolean;
  version: string;
}

const initialStrategies: StrategyItem[] = [
  { id: 1, name: 'Trend Following Confluence', slug: 'trend_following', description: 'Multi-period EMA stack (9, 20, 50, 200) alignment and ADX trend strength evaluation', weight: 20, isActive: true, version: 'v1.0.0' },
  { id: 2, name: 'Multi-Oscillator Momentum', slug: 'momentum', description: 'RSI momentum positioning, MACD histogram directional expansion, and Stochastic crossovers', weight: 15, isActive: true, version: 'v1.0.0' },
  { id: 3, name: 'Bollinger & Mean Reversion', slug: 'mean_reversion', description: 'Bollinger Band envelope extremes (%B) coupled with CCI/Williams %R exhaustion signals', weight: 12, isActive: true, version: 'v1.0.0' },
  { id: 4, name: 'Key Level Support & Resistance', slug: 'support_resistance', description: 'Clustered fractal swing highs and lows with price rejection reactions at key zones', weight: 14, isActive: true, version: 'v1.0.0' },
  { id: 5, name: 'Volatility Squeeze & Breakout', slug: 'breakout', description: 'Bollinger Band compression bandwidth squeeze followed by confirmed ATR expansion', weight: 12, isActive: true, version: 'v1.0.0' },
  { id: 6, name: 'Trend Pullback & Continuation', slug: 'pullback', description: 'Dynamic 20/50 EMA touch in prevailing trend with RSI re-entry stabilization', weight: 14, isActive: true, version: 'v1.0.0' },
  { id: 7, name: 'Candlestick Pattern Confirmation', slug: 'candlestick_confirmation', description: '15 price action patterns: Engulfing, Hammers, Pin Bars, Stars, and Rejection wicks', weight: 12, isActive: true, version: 'v1.0.0' },
  { id: 8, name: 'Multi-Timeframe Trend Confluence', slug: 'mtf_confluence', description: 'Higher-timeframe (M5/M15/H1) directional alignment for lower-timeframe execution', weight: 15, isActive: true, version: 'v1.0.0' },
];

export default function StrategiesPage() {
  const [strategies, setStrategies] = useState<StrategyItem[]>(initialStrategies);
  const { toast } = useToast();

  const handleToggle = (id: number) => {
    setStrategies((prev) =>
      prev.map((s) => (s.id === id ? { ...s, isActive: !s.isActive } : s))
    );
  };

  const handleWeightChange = (id: number, val: number) => {
    setStrategies((prev) =>
      prev.map((s) => (s.id === id ? { ...s, weight: val } : s))
    );
  };

  const handleSave = () => {
    toast({
      title: 'Strategy Configuration Updated',
      description: 'New strategy version v1.0.1 published and logged to audit trail.',
    });
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between pb-3 border-b border-[#243149]">
        <div>
          <h1 className="text-lg font-bold font-mono text-[#F5F7FB]">
            STRATEGY ENSEMBLE CONFIGURATION
          </h1>
          <p className="text-xs text-[#98A4B8]">
            Configure weights, parameters, and active modules in the quantitative signal engine
          </p>
        </div>

        <Button
          onClick={handleSave}
          className="bg-[#4165FF] hover:bg-[#3454db] text-xs font-mono text-white font-bold h-9"
        >
          Publish Version Changes
        </Button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {strategies.map((s) => (
          <div
            key={s.id}
            className="bg-[#111A2A] border border-[#243149] rounded-xl p-5 space-y-4"
          >
            <div className="flex items-center justify-between">
              <div className="space-y-0.5">
                <span className="font-bold text-sm text-[#F5F7FB] block">
                  {s.name}
                </span>
                <span className="text-[10px] text-[#4165FF] font-mono">
                  {s.version} • {s.slug}
                </span>
              </div>

              <div className="flex items-center gap-2">
                <Badge
                  variant="outline"
                  className={
                    s.isActive
                      ? 'border-[#18C78E]/30 bg-[#18C78E]/10 text-[#18C78E] text-[10px]'
                      : 'border-[#243149] text-[#98A4B8] text-[10px]'
                  }
                >
                  {s.isActive ? 'ACTIVE' : 'DISABLED'}
                </Badge>
                <Switch
                  checked={s.isActive}
                  onCheckedChange={() => handleToggle(s.id)}
                />
              </div>
            </div>

            <p className="text-xs text-[#98A4B8] leading-relaxed">
              {s.description}
            </p>

            {/* Weight Slider */}
            <div className="space-y-1.5 pt-2 border-t border-[#243149]">
              <div className="flex items-center justify-between text-xs font-mono">
                <span className="text-[#98A4B8]">Ensemble Weight Contribution:</span>
                <span className="text-[#F5F7FB] font-bold">{s.weight}%</span>
              </div>
              <Slider
                value={[s.weight]}
                min={5}
                max={35}
                step={1}
                disabled={!s.isActive}
                onValueChange={([val]) => handleWeightChange(s.id, val)}
              />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
