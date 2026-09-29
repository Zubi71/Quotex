'use client';

import React from 'react';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Button } from '@/components/ui/button';
import { Filter, RotateCcw } from 'lucide-react';

interface SignalFiltersProps {
  asset: string;
  onAssetChange: (val: string) => void;
  direction: string;
  onDirectionChange: (val: string) => void;
  result: string;
  onResultChange: (val: string) => void;
  onReset: () => void;
}

export function SignalFilters({
  asset,
  onAssetChange,
  direction,
  onDirectionChange,
  result,
  onResultChange,
  onReset,
}: SignalFiltersProps) {
  return (
    <div className="flex flex-wrap items-center gap-3 bg-[#111A2A] p-3.5 rounded-xl border border-[#243149] text-xs">
      <div className="flex items-center gap-2 text-[#98A4B8] font-mono text-[11px] pr-2 border-r border-[#243149]">
        <Filter className="w-3.5 h-3.5 text-[#4165FF]" />
        <span>FILTERS:</span>
      </div>

      {/* Asset Filter */}
      <Select value={asset} onValueChange={onAssetChange}>
        <SelectTrigger className="w-36 h-9 bg-[#070B14] border-[#243149] text-xs text-[#F5F7FB]">
          <SelectValue placeholder="All Assets" />
        </SelectTrigger>
        <SelectContent className="bg-[#111A2A] border-[#243149] text-[#F5F7FB]">
          <SelectItem value="ALL">All Assets</SelectItem>
          <SelectItem value="EUR/USD">EUR/USD (OTC)</SelectItem>
          <SelectItem value="GBP/USD">GBP/USD (OTC)</SelectItem>
          <SelectItem value="USD/JPY">USD/JPY (OTC)</SelectItem>
          <SelectItem value="EUR/GBP">EUR/GBP (OTC)</SelectItem>
          <SelectItem value="AUD/USD">AUD/USD (OTC)</SelectItem>
        </SelectContent>
      </Select>

      {/* Direction Filter */}
      <Select value={direction} onValueChange={onDirectionChange}>
        <SelectTrigger className="w-32 h-9 bg-[#070B14] border-[#243149] text-xs text-[#F5F7FB]">
          <SelectValue placeholder="All Directions" />
        </SelectTrigger>
        <SelectContent className="bg-[#111A2A] border-[#243149] text-[#F5F7FB]">
          <SelectItem value="ALL">All Directions</SelectItem>
          <SelectItem value="CALL">CALL</SelectItem>
          <SelectItem value="PUT">PUT</SelectItem>
          <SelectItem value="NO_TRADE">NO TRADE</SelectItem>
        </SelectContent>
      </Select>

      {/* Result Filter */}
      <Select value={result} onValueChange={onResultChange}>
        <SelectTrigger className="w-32 h-9 bg-[#070B14] border-[#243149] text-xs text-[#F5F7FB]">
          <SelectValue placeholder="All Results" />
        </SelectTrigger>
        <SelectContent className="bg-[#111A2A] border-[#243149] text-[#F5F7FB]">
          <SelectItem value="ALL">All Results</SelectItem>
          <SelectItem value="WIN">WIN</SelectItem>
          <SelectItem value="LOSS">LOSS</SelectItem>
          <SelectItem value="PENDING">PENDING</SelectItem>
        </SelectContent>
      </Select>

      <Button
        variant="ghost"
        size="sm"
        onClick={onReset}
        className="h-9 text-[#98A4B8] hover:text-[#F5F7FB] ml-auto text-xs"
      >
        <RotateCcw className="w-3.5 h-3.5 mr-1.5" /> Reset Filters
      </Button>
    </div>
  );
}
