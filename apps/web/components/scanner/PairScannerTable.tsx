'use client';

import React, { useState } from 'react';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { ArrowUpRight, ArrowDownRight, MinusCircle, ArrowUpDown, Zap } from 'lucide-react';
import { cn } from '@/lib/utils';

interface ScannerItem {
  asset: string;
  price: number;
  trend: 'BULLISH' | 'BEARISH' | 'RANGING';
  volatility: 'HIGH' | 'NORMAL' | 'LOW';
  payout: number;
  confidence: number;
  signal: 'CALL' | 'PUT' | 'NO_TRADE';
  status: 'OPEN' | 'CLOSED';
}

const mockScannerData: ScannerItem[] = [
  { asset: 'EUR/USD (OTC)', price: 1.08524, trend: 'BULLISH', volatility: 'NORMAL', payout: 85, confidence: 88, signal: 'CALL', status: 'OPEN' },
  { asset: 'USD/JPY (OTC)', price: 149.620, trend: 'BEARISH', volatility: 'NORMAL', payout: 82, confidence: 84, signal: 'PUT', status: 'OPEN' },
  { asset: 'GBP/USD (OTC)', price: 1.27110, trend: 'RANGING', volatility: 'LOW', payout: 80, confidence: 62, signal: 'NO_TRADE', status: 'OPEN' },
  { asset: 'EUR/GBP (OTC)', price: 0.85412, trend: 'BEARISH', volatility: 'HIGH', payout: 80, confidence: 58, signal: 'NO_TRADE', status: 'OPEN' },
  { asset: 'AUD/USD (OTC)', price: 0.65140, trend: 'BULLISH', volatility: 'NORMAL', payout: 84, confidence: 82, signal: 'CALL', status: 'OPEN' },
];

export function PairScannerTable({ onSelectAsset }: { onSelectAsset?: (asset: string) => void }) {
  const [data, setData] = useState<ScannerItem[]>(mockScannerData);
  const [sortBy, setSortBy] = useState<'confidence' | 'payout'>('confidence');

  const handleSort = (field: 'confidence' | 'payout') => {
    setSortBy(field);
    const sorted = [...data].sort((a, b) => b[field] - a[field]);
    setData(sorted);
  };

  return (
    <div className="space-y-4">
      {/* Header Toolbar */}
      <div className="flex items-center justify-between bg-[#111A2A] p-4 rounded-xl border border-[#243149]">
        <div>
          <span className="font-bold text-xs font-mono text-[#F5F7FB] block">
            MARKET WATCH & CONFLUENCE SCANNER
          </span>
          <span className="text-[11px] text-[#98A4B8] font-sans">
            Continuous multi-pair technical scan with real-time confidence ranking
          </span>
        </div>

        <div className="flex items-center gap-2">
          <Button
            variant="outline"
            size="sm"
            onClick={() => handleSort('confidence')}
            className={cn(
              'h-8 text-xs font-mono border-[#243149]',
              sortBy === 'confidence' ? 'bg-[#4165FF]/15 text-[#4165FF] border-[#4165FF]/40' : 'text-[#98A4B8]'
            )}
          >
            <ArrowUpDown className="w-3 h-3 mr-1.5" /> Highest Confidence
          </Button>

          <Button
            variant="outline"
            size="sm"
            onClick={() => handleSort('payout')}
            className={cn(
              'h-8 text-xs font-mono border-[#243149]',
              sortBy === 'payout' ? 'bg-[#4165FF]/15 text-[#4165FF] border-[#4165FF]/40' : 'text-[#98A4B8]'
            )}
          >
            <ArrowUpDown className="w-3 h-3 mr-1.5" /> Highest Payout
          </Button>
        </div>
      </div>

      {/* Scanner Table */}
      <div className="rounded-xl border border-[#243149] bg-[#111A2A] overflow-hidden">
        <Table>
          <TableHeader className="bg-[#0D1422] border-b border-[#243149]">
            <TableRow className="border-[#243149] hover:bg-transparent text-[11px] font-mono text-[#98A4B8]">
              <TableHead>ASSET (OTC)</TableHead>
              <TableHead>LAST PRICE</TableHead>
              <TableHead>TREND</TableHead>
              <TableHead>VOLATILITY</TableHead>
              <TableHead>PAYOUT</TableHead>
              <TableHead>CONFIDENCE</TableHead>
              <TableHead>CURRENT SIGNAL</TableHead>
              <TableHead className="text-right">ACTION</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {data.map((item) => (
              <TableRow
                key={item.asset}
                className="border-b border-[#243149]/60 hover:bg-[#152238] transition-colors text-xs font-mono"
              >
                <TableCell className="font-semibold text-[#F5F7FB]">
                  {item.asset}
                </TableCell>
                <TableCell className="text-[#F5F7FB]">
                  {item.price.toFixed(item.price > 100 ? 3 : 5)}
                </TableCell>
                <TableCell>
                  <span
                    className={cn(
                      'text-[11px] font-bold',
                      item.trend === 'BULLISH' && 'text-[#18C78E]',
                      item.trend === 'BEARISH' && 'text-[#FF4D6D]',
                      item.trend === 'RANGING' && 'text-[#98A4B8]'
                    )}
                  >
                    {item.trend}
                  </span>
                </TableCell>
                <TableCell className="text-[#98A4B8] text-[11px]">
                  {item.volatility}
                </TableCell>
                <TableCell className="text-[#18C78E] font-bold">
                  {item.payout}%
                </TableCell>
                <TableCell>
                  <span
                    className={cn(
                      'font-bold',
                      item.confidence >= 80 ? 'text-[#18C78E]' : item.confidence >= 65 ? 'text-[#F5B942]' : 'text-[#FF4D6D]'
                    )}
                  >
                    {item.confidence}%
                  </span>
                </TableCell>
                <TableCell>
                  {item.signal === 'CALL' && (
                    <Badge className="bg-[#18C78E]/15 text-[#18C78E] border border-[#18C78E]/30 text-[10px]">
                      <ArrowUpRight className="w-3 h-3 mr-1" /> CALL
                    </Badge>
                  )}
                  {item.signal === 'PUT' && (
                    <Badge className="bg-[#FF4D6D]/15 text-[#FF4D6D] border border-[#FF4D6D]/30 text-[10px]">
                      <ArrowDownRight className="w-3 h-3 mr-1" /> PUT
                    </Badge>
                  )}
                  {item.signal === 'NO_TRADE' && (
                    <Badge variant="outline" className="border-[#243149] text-[#98A4B8] text-[10px]">
                      NO TRADE
                    </Badge>
                  )}
                </TableCell>
                <TableCell className="text-right">
                  <Button
                    size="sm"
                    variant="ghost"
                    onClick={() => onSelectAsset?.(item.asset.replace(' (OTC)', ''))}
                    className="h-7 text-xs text-[#4165FF] hover:text-white hover:bg-[#4165FF]/20"
                  >
                    <Zap className="w-3 h-3 mr-1" /> Trade Setup
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </div>

      <div className="p-3 bg-[#070B14] rounded-lg border border-[#243149]/60 text-[11px] text-[#98A4B8] leading-relaxed font-sans">
        Rankings are based strictly on statistical indicator confluence and current data quality. Higher confidence does not eliminate market risk.
      </div>
    </div>
  );
}
