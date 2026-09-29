'use client';

import React, { useState } from 'react';
import { SignalHistoryTable } from '@/components/history/SignalHistoryTable';
import { SignalFilters } from '@/components/history/SignalFilters';
import { SignalHistoryItem } from '@/types/signal';
import { Button } from '@/components/ui/button';
import { Download } from 'lucide-react';

const mockHistoryData: SignalHistoryItem[] = [
  {
    id: 101,
    asset: 'EUR/USD (OTC)',
    broker: 'mock',
    direction: 'CALL',
    confidence: 88,
    status: 'HIGH_CONFIDENCE',
    expiry_seconds: 60,
    signal_time: new Date(Date.now() - 1000 * 60 * 12).toISOString(),
    candle_time: new Date(Date.now() - 1000 * 60 * 13).toISOString(),
    market_regime: 'TRENDING_BULLISH',
    data_quality: 98,
    strategy_version: '1.0.0',
    entry_price: 1.08520,
    close_price: 1.08542,
    result: 'WIN',
    profit_loss: 8.0,
    timeframe: 'M1',
    factors: [],
    reasons: ['Price above EMA 20 & 50', 'MACD positive expansion'],
    warnings: [],
    is_mock: true,
  },
  {
    id: 102,
    asset: 'USD/JPY (OTC)',
    broker: 'mock',
    direction: 'PUT',
    confidence: 84,
    status: 'HIGH_CONFIDENCE',
    expiry_seconds: 60,
    signal_time: new Date(Date.now() - 1000 * 60 * 25).toISOString(),
    candle_time: new Date(Date.now() - 1000 * 60 * 26).toISOString(),
    market_regime: 'TRENDING_BEARISH',
    data_quality: 95,
    strategy_version: '1.0.0',
    entry_price: 149.610,
    close_price: 149.575,
    result: 'WIN',
    profit_loss: 8.0,
    timeframe: 'M1',
    factors: [],
    reasons: ['EMA 20 < EMA 50', 'Bearish engulfing pattern'],
    warnings: [],
    is_mock: true,
  },
  {
    id: 103,
    asset: 'GBP/USD (OTC)',
    broker: 'mock',
    direction: 'CALL',
    confidence: 78,
    status: 'MODERATE',
    expiry_seconds: 120,
    signal_time: new Date(Date.now() - 1000 * 60 * 45).toISOString(),
    candle_time: new Date(Date.now() - 1000 * 60 * 46).toISOString(),
    market_regime: 'RANGING',
    data_quality: 94,
    strategy_version: '1.0.0',
    entry_price: 1.27085,
    close_price: 1.27060,
    result: 'LOSS',
    profit_loss: -10.0,
    timeframe: 'M1',
    factors: [],
    reasons: ['Support bounce', 'RSI oversold exit'],
    warnings: ['Market ranging without strong trend momentum'],
    is_mock: true,
  },
  {
    id: 104,
    asset: 'EUR/USD (OTC)',
    broker: 'mock',
    direction: 'NO_TRADE',
    confidence: 56,
    status: 'NO_TRADE',
    expiry_seconds: 60,
    signal_time: new Date(Date.now() - 1000 * 60 * 65).toISOString(),
    candle_time: new Date(Date.now() - 1000 * 60 * 66).toISOString(),
    market_regime: 'UNCERTAIN',
    data_quality: 91,
    strategy_version: '1.0.0',
    entry_price: 1.08490,
    result: 'NO_TRADE',
    timeframe: 'M1',
    factors: [],
    reasons: [],
    warnings: ['Conflicting indicators'],
    rejection_reasons: ['Confidence score (56%) below minimum threshold (70%)'],
    is_mock: true,
  },
  {
    id: 105,
    asset: 'AUD/USD (OTC)',
    broker: 'mock',
    direction: 'CALL',
    confidence: 86,
    status: 'HIGH_CONFIDENCE',
    expiry_seconds: 60,
    signal_time: new Date(Date.now() - 1000 * 60 * 85).toISOString(),
    candle_time: new Date(Date.now() - 1000 * 60 * 86).toISOString(),
    market_regime: 'TRENDING_BULLISH',
    data_quality: 97,
    strategy_version: '1.0.0',
    entry_price: 0.65120,
    close_price: 0.65145,
    result: 'WIN',
    profit_loss: 8.4,
    timeframe: 'M1',
    factors: [],
    reasons: ['Higher timeframe confluence', 'ADX > 28'],
    warnings: [],
    is_mock: true,
  },
];

export default function HistoryPage() {
  const [assetFilter, setAssetFilter] = useState('ALL');
  const [directionFilter, setDirectionFilter] = useState('ALL');
  const [resultFilter, setResultFilter] = useState('ALL');

  const filtered = mockHistoryData.filter((item) => {
    if (assetFilter !== 'ALL' && !item.asset.includes(assetFilter)) return false;
    if (directionFilter !== 'ALL' && item.direction !== directionFilter) return false;
    if (resultFilter !== 'ALL' && item.result !== resultFilter) return false;
    return true;
  });

  const exportCSV = () => {
    const headers = 'ID,Time,Asset,Direction,Confidence,Regime,EntryPrice,ClosePrice,Result,PL\n';
    const rows = filtered
      .map(
        (r) =>
          `${r.id},${r.signal_time},${r.asset},${r.direction},${r.confidence}%,${r.market_regime},${r.entry_price},${r.close_price || ''},${r.result},${r.profit_loss || 0}`
      )
      .join('\n');
    const blob = new Blob([headers + rows], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `signal-history-${Date.now()}.csv`;
    a.click();
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between pb-3 border-b border-[#243149]">
        <div>
          <h1 className="text-lg font-bold font-mono text-[#F5F7FB]">
            IMMUTABLE SIGNAL HISTORY & AUDIT LOG
          </h1>
          <p className="text-xs text-[#98A4B8]">
            Complete chronological record of all generated setups, confirmations, and actual outcomes
          </p>
        </div>

        <Button
          variant="outline"
          size="sm"
          onClick={exportCSV}
          className="h-8 text-xs font-mono border-[#243149] text-[#98A4B8] hover:text-[#F5F7FB]"
        >
          <Download className="w-3.5 h-3.5 mr-1.5" /> Export CSV
        </Button>
      </div>

      {/* Filter Controls */}
      <SignalFilters
        asset={assetFilter}
        onAssetChange={setAssetFilter}
        direction={directionFilter}
        onDirectionChange={setDirectionFilter}
        result={resultFilter}
        onResultChange={setResultFilter}
        onReset={() => {
          setAssetFilter('ALL');
          setDirectionFilter('ALL');
          setResultFilter('ALL');
        }}
      />

      {/* Table */}
      <SignalHistoryTable signals={filtered} />
    </div>
  );
}
