'use client';

import React from 'react';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { ShieldAlert, CheckCircle2 } from 'lucide-react';

interface BrokerSelectorProps {
  value: string;
  onChange: (value: string) => void;
}

export function BrokerSelector({ value, onChange }: BrokerSelectorProps) {
  return (
    <div className="space-y-1.5">
      <label className="text-[11px] font-mono text-[#98A4B8] tracking-wider uppercase flex items-center justify-between">
        <span>BROKER</span>
        <span className="text-[#18C78E] text-[10px] flex items-center gap-1">
          <CheckCircle2 className="w-3 h-3" /> ACTIVE
        </span>
      </label>
      <Select value={value} onValueChange={onChange}>
        <SelectTrigger className="w-full bg-[#111A2A] border-[#243149] text-[#F5F7FB] text-xs h-10">
          <SelectValue placeholder="Select Broker" />
        </SelectTrigger>
        <SelectContent className="bg-[#111A2A] border-[#243149] text-[#F5F7FB]">
          <SelectItem value="quotex" className="text-xs focus:bg-[#4165FF]/20 focus:text-white">
            <div className="flex items-center justify-between w-full gap-2">
              <span className="font-semibold">Quotex</span>
              <span className="text-[10px] text-[#98A4B8] font-mono">(Adapter Ready)</span>
            </div>
          </SelectItem>
          <SelectItem value="mock" className="text-xs focus:bg-[#4165FF]/20 focus:text-white">
            <div className="flex items-center justify-between w-full gap-2">
              <span className="font-semibold">Mock Broker</span>
              <span className="text-[10px] text-[#F5B942] font-mono">(Demo Mode)</span>
            </div>
          </SelectItem>
        </SelectContent>
      </Select>
    </div>
  );
}
