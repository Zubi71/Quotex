'use client';

import React, { useState, useEffect } from 'react';
import { Bell, Wifi, CircleUser, Shield, Menu } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useConnectionStore } from '@/store/connectionStore';

interface HeaderProps {
  onMenuToggle?: () => void;
}

export function Header({ onMenuToggle }: HeaderProps) {
  const { wsState, lastUpdateTime, latencyMs } = useConnectionStore();
  const [secondsAgo, setSecondsAgo] = useState(0);

  useEffect(() => {
    const timer = setInterval(() => {
      setSecondsAgo((prev) => (prev < 59 ? prev + 1 : 0));
    }, 1000);
    return () => clearInterval(timer);
  }, []);

  const formatSeconds = (s: number) => (s < 10 ? `0${s}` : `${s}`);

  return (
    <header className="h-16 bg-[#0D1422] border-b border-[#243149] px-6 flex items-center justify-between shrink-0 select-none">
      {/* Left: Mobile menu toggle + Title */}
      <div className="flex items-center gap-4">
        {onMenuToggle && (
          <Button
            variant="ghost"
            size="icon"
            onClick={onMenuToggle}
            className="md:hidden text-[#98A4B8] hover:text-[#F5F7FB]"
          >
            <Menu className="w-5 h-5" />
          </Button>
        )}
        <div className="flex items-center gap-2">
          <span className="font-semibold text-sm tracking-wider text-[#F5F7FB]">
            OTC SIGNAL INTELLIGENCE
          </span>
          <span className="hidden sm:inline-block text-[#243149]">|</span>
          <span className="hidden sm:inline-block text-xs font-mono text-[#98A4B8]">
            ANALYTICS TERMINAL
          </span>
        </div>
      </div>

      {/* Centre: Live Market Status Indicator */}
      <div className="hidden md:flex items-center gap-3 bg-[#111A2A] border border-[#243149] px-3.5 py-1.5 rounded-full font-mono text-[11px]">
        <div className="flex items-center gap-1.5 text-[#18C78E]">
          <span className="relative flex h-2 w-2">
            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#18C78E] opacity-75"></span>
            <span className="relative inline-flex rounded-full h-2 w-2 bg-[#18C78E]"></span>
          </span>
          <span className="font-bold tracking-wider">CONNECTED</span>
        </div>
        <span className="text-[#243149]">/</span>
        <span className="text-[#F5F7FB] tracking-wider">DATA LIVE</span>
        <span className="text-[#243149]">/</span>
        <span className="text-[#98A4B8]">
          UPDATE 00:{formatSeconds(secondsAgo)} AGO
        </span>
      </div>

      {/* Right: Notifications & User Profile */}
      <div className="flex items-center gap-3">
        <Button
          variant="outline"
          size="icon"
          className="w-9 h-9 border-[#243149] bg-[#111A2A] text-[#98A4B8] hover:text-[#F5F7FB] relative"
        >
          <Bell className="w-4 h-4" />
          <span className="absolute top-2 right-2 w-1.5 h-1.5 rounded-full bg-[#4165FF]" />
        </Button>

        <div className="flex items-center gap-2 pl-2 border-l border-[#243149]">
          <div className="w-8 h-8 rounded-full bg-[#4165FF]/20 border border-[#4165FF]/40 flex items-center justify-center text-[#4165FF]">
            <CircleUser className="w-5 h-5" />
          </div>
          <div className="hidden sm:block text-left">
            <div className="text-xs font-semibold text-[#F5F7FB] leading-none">Trader</div>
            <div className="text-[10px] text-[#18C78E] font-mono leading-none mt-1">Verified</div>
          </div>
        </div>
      </div>
    </header>
  );
}
