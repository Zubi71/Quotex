'use client';

import React, { useState, useMemo } from 'react';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-react';
import { Asset } from '@/types/market';

interface PairSelectorProps {
  assets: Asset[];
  value: string;
  onChange: (value: string) => void;
  isLoading?: boolean;
}

export function PairSelector({ assets, value, onChange, isLoading }: PairSelectorProps) {
  const [search, setSearch] = useState('');

  const filteredAssets = useMemo(() => {
    if (!search.trim()) return assets;
    const q = search.toLowerCase();
    return assets.filter(
      (a) =>
        a.symbol.toLowerCase().includes(q) ||
        a.displayName.toLowerCase().includes(q)
    );
  }, [assets, search]);

  const selectedAsset = assets.find((a) => a.symbol === value);

  return (
    <div className="space-y-1.5">
      <div className="flex items-center justify-between text-[11px] font-mono text-[#98A4B8] tracking-wider uppercase">
        <span>PAIR (OTC INSTRUMENT)</span>
        {selectedAsset?.payout && (
          <span className="text-[#18C78E] font-bold font-mono">
            PAYOUT {selectedAsset.payout}%
          </span>
        )}
      </div>

      <Select value={value} onValueChange={onChange} disabled={isLoading}>
        <SelectTrigger className="w-full bg-[#111A2A] border-[#243149] text-[#F5F7FB] text-xs h-10">
          <SelectValue placeholder={isLoading ? 'Loading assets...' : 'Select OTC Pair'}>
            {selectedAsset ? (
              <div className="flex items-center justify-between w-full">
                <span className="font-semibold">{selectedAsset.displayName}</span>
                <Badge variant="outline" className="text-[9px] px-1 py-0 border-[#243149] text-[#4165FF] bg-[#4165FF]/10 font-mono">
                  OTC
                </Badge>
              </div>
            ) : (
              'Select OTC Pair'
            )}
          </SelectValue>
        </SelectTrigger>
        <SelectContent className="bg-[#111A2A] border-[#243149] text-[#F5F7FB] max-h-60">
          <div className="p-2 border-b border-[#243149]">
            <div className="relative">
              <Search className="w-3.5 h-3.5 absolute left-2.5 top-2.5 text-[#98A4B8]" />
              <Input
                placeholder="Search OTC pairs..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="h-8 pl-8 text-xs bg-[#070B14] border-[#243149] text-[#F5F7FB]"
              />
            </div>
          </div>

          {filteredAssets.length === 0 ? (
            <div className="py-4 text-center text-xs text-[#98A4B8]">
              No instruments found
            </div>
          ) : (
            filteredAssets.map((asset) => (
              <SelectItem
                key={asset.symbol}
                value={asset.symbol}
                className="text-xs focus:bg-[#4165FF]/20 focus:text-white"
              >
                <div className="flex items-center justify-between w-full gap-4">
                  <div className="flex items-center gap-2">
                    <span className="font-medium">{asset.displayName}</span>
                    <span className="text-[9px] px-1 rounded bg-[#243149] text-[#98A4B8] font-mono">
                      OTC
                    </span>
                  </div>
                  <div className="flex items-center gap-2">
                    {asset.payout && (
                      <span className="text-[10px] text-[#18C78E] font-mono font-bold">
                        {asset.payout}%
                      </span>
                    )}
                    <span className="w-1.5 h-1.5 rounded-full bg-[#18C78E]" />
                  </div>
                </div>
              </SelectItem>
            ))
          )}
        </SelectContent>
      </Select>
    </div>
  );
}
