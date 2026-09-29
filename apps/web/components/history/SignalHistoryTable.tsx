'use client';

import React from 'react';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { SignalHistoryItem } from '@/types/signal';
import { ArrowUpRight, ArrowDownRight, MinusCircle } from 'lucide-react';
import { cn } from '@/lib/utils';

interface SignalHistoryTableProps {
  signals: SignalHistoryItem[];
  isLoading?: boolean;
}

export function SignalHistoryTable({ signals, isLoading }: SignalHistoryTableProps) {
  if (isLoading) {
    return (
      <div className="py-12 text-center text-xs font-mono text-[#98A4B8]">
        Loading verified signal records...
      </div>
    );
  }

  if (signals.length === 0) {
    return (
      <div className="py-12 text-center text-xs font-mono text-[#98A4B8]">
        No historical signals recorded matching current filter parameters.
      </div>
    );
  }

  return (
    <div className="rounded-xl border border-[#243149] bg-[#111A2A] overflow-hidden">
      <Table>
        <TableHeader className="bg-[#0D1422] border-b border-[#243149]">
          <TableRow className="border-[#243149] hover:bg-transparent">
            <TableHead className="text-[11px] font-mono text-[#98A4B8] py-3">TIME</TableHead>
            <TableHead className="text-[11px] font-mono text-[#98A4B8]">ASSET</TableHead>
            <TableHead className="text-[11px] font-mono text-[#98A4B8]">DIRECTION</TableHead>
            <TableHead className="text-[11px] font-mono text-[#98A4B8]">CONFIDENCE</TableHead>
            <TableHead className="text-[11px] font-mono text-[#98A4B8]">EXPIRY</TableHead>
            <TableHead className="text-[11px] font-mono text-[#98A4B8]">REGIME</TableHead>
            <TableHead className="text-[11px] font-mono text-[#98A4B8]">ENTRY</TableHead>
            <TableHead className="text-[11px] font-mono text-[#98A4B8]">OUTCOME</TableHead>
            <TableHead className="text-[11px] font-mono text-[#98A4B8]">RESULT</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          {signals.map((sig, idx) => {
            const isCall = sig.direction === 'CALL';
            const isPut = sig.direction === 'PUT';
            const isNoTrade = sig.direction === 'NO_TRADE';

            return (
              <TableRow
                key={sig.id || sig.signal_uuid || idx}
                className="border-b border-[#243149]/60 hover:bg-[#152238] transition-colors text-xs font-mono"
              >
                {/* Time */}
                <TableCell className="text-[#98A4B8] whitespace-nowrap">
                  {new Date(sig.signal_time).toLocaleTimeString()}
                </TableCell>

                {/* Asset */}
                <TableCell className="font-semibold text-[#F5F7FB]">
                  {sig.asset}
                </TableCell>

                {/* Direction */}
                <TableCell>
                  {isCall && (
                    <span className="flex items-center gap-1 text-[#18C78E] font-bold">
                      <ArrowUpRight className="w-3.5 h-3.5" /> CALL
                    </span>
                  )}
                  {isPut && (
                    <span className="flex items-center gap-1 text-[#FF4D6D] font-bold">
                      <ArrowDownRight className="w-3.5 h-3.5" /> PUT
                    </span>
                  )}
                  {isNoTrade && (
                    <span className="flex items-center gap-1 text-[#F5B942]">
                      <MinusCircle className="w-3.5 h-3.5" /> NO TRADE
                    </span>
                  )}
                </TableCell>

                {/* Confidence */}
                <TableCell>
                  <span
                    className={cn(
                      'font-bold',
                      sig.confidence >= 80
                        ? 'text-[#18C78E]'
                        : sig.confidence >= 65
                        ? 'text-[#F5B942]'
                        : 'text-[#FF4D6D]'
                    )}
                  >
                    {sig.confidence}%
                  </span>
                </TableCell>

                {/* Expiry */}
                <TableCell className="text-[#98A4B8]">
                  {sig.expiry_seconds}s
                </TableCell>

                {/* Regime */}
                <TableCell className="text-[#98A4B8] text-[10px] uppercase">
                  {sig.market_regime?.replace('_', ' ') || 'RANGING'}
                </TableCell>

                {/* Entry Price */}
                <TableCell className="text-[#F5F7FB]">
                  {sig.entry_price?.toFixed(5) || '—'}
                </TableCell>

                {/* Close Price */}
                <TableCell className="text-[#98A4B8]">
                  {sig.close_price ? sig.close_price.toFixed(5) : '—'}
                </TableCell>

                {/* Result Badge */}
                <TableCell>
                  {sig.result === 'WIN' && (
                    <Badge className="bg-[#18C78E]/15 text-[#18C78E] border border-[#18C78E]/30 text-[10px]">
                      WIN
                    </Badge>
                  )}
                  {sig.result === 'LOSS' && (
                    <Badge className="bg-[#FF4D6D]/15 text-[#FF4D6D] border border-[#FF4D6D]/30 text-[10px]">
                      LOSS
                    </Badge>
                  )}
                  {sig.result === 'PENDING' && (
                    <Badge variant="outline" className="border-[#243149] text-[#4165FF] text-[10px]">
                      PENDING
                    </Badge>
                  )}
                  {sig.result === 'VOID' && (
                    <Badge variant="outline" className="border-[#243149] text-[#98A4B8] text-[10px]">
                      VOID
                    </Badge>
                  )}
                  {(!sig.result || sig.result === 'NO_TRADE') && (
                    <span className="text-[#98A4B8] text-[10px]">—</span>
                  )}
                </TableCell>
              </TableRow>
            );
          })}
        </TableBody>
      </Table>
    </div>
  );
}
