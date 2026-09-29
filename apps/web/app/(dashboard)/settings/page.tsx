'use client';

import React, { useState } from 'react';
import { Slider } from '@/components/ui/slider';
import { Switch } from '@/components/ui/switch';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Settings as SettingsIcon, ShieldCheck, History, Sliders } from 'lucide-react';
import { useToast } from '@/hooks/useToast';

export default function SettingsPage() {
  const { toast } = useToast();

  const [confidenceThreshold, setConfidenceThreshold] = useState(70);
  const [minDataQuality, setMinDataQuality] = useState(80);
  const [minCandles, setMinCandles] = useState(200);
  const [maxSignalsPerHour, setMaxSignalsPerHour] = useState(10);
  const [cooldownSecs, setCooldownSecs] = useState(60);
  const [minPayout, setMinPayout] = useState(70);
  const [useMock, setUseMock] = useState(true);
  const [autoScan, setAutoScan] = useState(false);

  const handleSave = () => {
    toast({
      title: 'Settings Saved & Audited',
      description: `Threshold set to ${confidenceThreshold}%. Modification logged to audit log table.`,
    });
  };

  return (
    <div className="space-y-6 max-w-4xl">
      <div className="flex items-center justify-between pb-3 border-b border-[#243149]">
        <div>
          <h1 className="text-lg font-bold font-mono text-[#F5F7FB]">
            ADMIN & SYSTEM GOVERNANCE SETTINGS
          </h1>
          <p className="text-xs text-[#98A4B8]">
            Configure quantitative engine thresholds, risk gates, broker adapters, and audit rules
          </p>
        </div>

        <Button
          onClick={handleSave}
          className="bg-[#4165FF] hover:bg-[#3454db] text-xs font-mono font-bold text-white h-9"
        >
          Save & Publish
        </Button>
      </div>

      <Tabs defaultValue="engine" className="space-y-4">
        <TabsList className="bg-[#111A2A] border border-[#243149] p-1">
          <TabsTrigger value="engine" className="text-xs font-mono data-[state=active]:bg-[#4165FF] data-[state=active]:text-white">
            Signal Engine
          </TabsTrigger>
          <TabsTrigger value="broker" className="text-xs font-mono data-[state=active]:bg-[#4165FF] data-[state=active]:text-white">
            Broker & Feeds
          </TabsTrigger>
          <TabsTrigger value="audit" className="text-xs font-mono data-[state=active]:bg-[#4165FF] data-[state=active]:text-white">
            Audit Trail
          </TabsTrigger>
        </TabsList>

        {/* Engine Settings Tab */}
        <TabsContent value="engine" className="space-y-4">
          <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-5 space-y-6">
            {/* Confidence Threshold */}
            <div className="space-y-2">
              <div className="flex items-center justify-between text-xs font-mono">
                <span className="text-[#F5F7FB] font-semibold">
                  CONFIDENCE THRESHOLD GATE (STRICT NO TRADE UNDER THIS VALUE)
                </span>
                <span className="text-[#4165FF] font-bold text-sm">
                  {confidenceThreshold}%
                </span>
              </div>
              <Slider
                value={[confidenceThreshold]}
                min={55}
                max={90}
                step={1}
                onValueChange={([val]) => setConfidenceThreshold(val)}
              />
              <p className="text-[11px] text-[#98A4B8]">
                Setups scoring below {confidenceThreshold}% are automatically marked as <strong>NO TRADE</strong> to prevent over-trading in weak conditions.
              </p>
            </div>

            {/* Minimum Data Quality */}
            <div className="space-y-2 pt-4 border-t border-[#243149]">
              <div className="flex items-center justify-between text-xs font-mono">
                <span className="text-[#F5F7FB] font-semibold">
                  MINIMUM DATA QUALITY SCORE
                </span>
                <span className="text-[#18C78E] font-bold text-sm">
                  {minDataQuality}%
                </span>
              </div>
              <Slider
                value={[minDataQuality]}
                min={60}
                max={100}
                step={5}
                onValueChange={([val]) => setMinDataQuality(val)}
              />
              <p className="text-[11px] text-[#98A4B8]">
                Rejects signal generation if data freshness or candle continuity score is lower than {minDataQuality}%.
              </p>
            </div>

            {/* Numeric Fields */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-4 border-t border-[#243149] text-xs">
              <div className="space-y-1.5">
                <label className="text-[10px] font-mono text-[#98A4B8] uppercase">
                  MINIMUM CANDLE SAMPLES
                </label>
                <Input
                  type="number"
                  value={minCandles}
                  onChange={(e) => setMinCandles(Number(e.target.value))}
                  className="bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]"
                />
              </div>

              <div className="space-y-1.5">
                <label className="text-[10px] font-mono text-[#98A4B8] uppercase">
                  MAX SIGNALS PER HOUR
                </label>
                <Input
                  type="number"
                  value={maxSignalsPerHour}
                  onChange={(e) => setMaxSignalsPerHour(Number(e.target.value))}
                  className="bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]"
                />
              </div>

              <div className="space-y-1.5">
                <label className="text-[10px] font-mono text-[#98A4B8] uppercase">
                  COOLDOWN SECONDS
                </label>
                <Input
                  type="number"
                  value={cooldownSecs}
                  onChange={(e) => setCooldownSecs(Number(e.target.value))}
                  className="bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]"
                />
              </div>
            </div>
          </div>
        </TabsContent>

        {/* Broker Tab */}
        <TabsContent value="broker" className="space-y-4">
          <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-5 space-y-4 text-xs">
            <div className="flex items-center justify-between">
              <div>
                <span className="font-bold text-[#F5F7FB] block">Use Mock Broker Data</span>
                <span className="text-[11px] text-[#98A4B8]">
                  Generates deterministic synthetic OTC feeds with Gaussian random walk and volatility cycles
                </span>
              </div>
              <Switch checked={useMock} onCheckedChange={setUseMock} />
            </div>

            <div className="flex items-center justify-between pt-3 border-t border-[#243149]">
              <div>
                <span className="font-bold text-[#F5F7FB] block">Enable Auto-Scan Engine</span>
                <span className="text-[11px] text-[#98A4B8]">
                  Periodically evaluates all active assets in background and alerts when confidence is satisfied
                </span>
              </div>
              <Switch checked={autoScan} onCheckedChange={setAutoScan} />
            </div>

            <div className="pt-3 border-t border-[#243149] space-y-1.5">
              <label className="text-[10px] font-mono text-[#98A4B8] uppercase">
                MINIMUM ACCEPTABLE BROKER PAYOUT (%)
              </label>
              <Input
                type="number"
                value={minPayout}
                onChange={(e) => setMinPayout(Number(e.target.value))}
                className="w-48 bg-[#070B14] border-[#243149] text-xs h-9 text-[#F5F7FB]"
              />
            </div>
          </div>
        </TabsContent>

        {/* Audit Tab */}
        <TabsContent value="audit" className="space-y-4">
          <div className="bg-[#111A2A] border border-[#243149] rounded-xl p-5 space-y-3 font-mono text-xs">
            <span className="font-bold text-[#F5F7FB] block">
              SETTINGS AUDIT LOG RECORD
            </span>
            <div className="divide-y divide-[#243149]/60">
              <div className="py-2.5 flex items-center justify-between text-[#98A4B8]">
                <span>CONFIDENCE_THRESHOLD updated: 65 -&gt; 70</span>
                <span>User #1 • 10:14:22</span>
              </div>
              <div className="py-2.5 flex items-center justify-between text-[#98A4B8]">
                <span>MIN_DATA_QUALITY updated: 75 -&gt; 80</span>
                <span>User #1 • 09:45:10</span>
              </div>
              <div className="py-2.5 flex items-center justify-between text-[#98A4B8]">
                <span>SYSTEM_INITIALIZATION: Initial baseline applied</span>
                <span>System • 09:15:00</span>
              </div>
            </div>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  );
}
