'use client';

import React, { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FileText, RefreshCw, AlertCircle, Info, ShieldAlert } from 'lucide-react';
import { Button } from '@/components/ui/button';

interface LogEntry {
  id: number;
  time: string;
  level: 'INFO' | 'WARNING' | 'ERROR';
  channel: string;
  message: string;
  context?: string;
}

const mockLogs: LogEntry[] = [
  { id: 1, time: new Date().toLocaleTimeString(), level: 'INFO', channel: 'SIGNAL_ENGINE', message: 'Signal generated: EUR/USD CALL (Confidence 86%)', context: '{"regime":"TRENDING_BULLISH","quality":96}' },
  { id: 2, time: new Date(Date.now() - 30000).toLocaleTimeString(), level: 'INFO', channel: 'DATA_PIPELINE', message: 'M1 candle closed: EUR/USD [1.08520 -> 1.08535]', context: '{"vol":1240}' },
  { id: 3, time: new Date(Date.now() - 65000).toLocaleTimeString(), level: 'WARNING', channel: 'QUALITY_GATE', message: 'Setup rejected: GBP/USD confidence 58% below 70% threshold', context: '{"direction":"NO_TRADE"}' },
  { id: 4, time: new Date(Date.now() - 120000).toLocaleTimeString(), level: 'INFO', channel: 'BACKTEST', message: 'Sequential simulation completed: 500 candles, Win Rate 76.4%', context: '{"profit":142.50}' },
  { id: 5, time: new Date(Date.now() - 180000).toLocaleTimeString(), level: 'INFO', channel: 'BROKER_ADAPTER', message: 'MockBrokerAdapter health check: latency 0.42ms OK', context: '{"connected":true}' },
  { id: 6, time: new Date(Date.now() - 240000).toLocaleTimeString(), level: 'INFO', channel: 'BROADCAST', message: 'Reverb WebSocket dispatched market.EUR/USD price update', context: '{"clients":1}' },
];

export default function SystemLogsPage() {
  const [levelFilter, setLevelFilter] = useState('ALL');

  const filteredLogs = mockLogs.filter((l) => {
    if (levelFilter !== 'ALL' && l.level !== levelFilter) return false;
    return true;
  });

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between pb-3 border-b border-[#243149]">
        <div>
          <h1 className="text-lg font-bold font-mono text-[#F5F7FB]">
            SYSTEM LOGS & AUDIT LOGS
          </h1>
          <p className="text-xs text-[#98A4B8]">
            Structured execution events, indicator cycles, quality rejections, and WebSocket telemetry
          </p>
        </div>

        <div className="flex items-center gap-3">
          <Select value={levelFilter} onValueChange={setLevelFilter}>
            <SelectTrigger className="w-32 h-8 bg-[#111A2A] border-[#243149] text-xs text-[#F5F7FB]">
              <SelectValue placeholder="All Levels" />
            </SelectTrigger>
            <SelectContent className="bg-[#111A2A] border-[#243149] text-[#F5F7FB]">
              <SelectItem value="ALL">All Levels</SelectItem>
              <SelectItem value="INFO">INFO</SelectItem>
              <SelectItem value="WARNING">WARNING</SelectItem>
              <SelectItem value="ERROR">ERROR</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>

      <div className="rounded-xl border border-[#243149] bg-[#111A2A] overflow-hidden">
        <div className="divide-y divide-[#243149]/60 font-mono text-xs">
          {filteredLogs.map((log) => (
            <div key={log.id} className="p-3.5 hover:bg-[#152238] transition-colors flex items-start gap-4">
              <span className="text-[#98A4B8] whitespace-nowrap text-[11px] pt-0.5">
                {log.time}
              </span>

              <div>
                {log.level === 'INFO' && (
                  <Badge className="bg-[#4165FF]/15 text-[#4165FF] border border-[#4165FF]/30 text-[9px] py-0 px-1.5">
                    INFO
                  </Badge>
                )}
                {log.level === 'WARNING' && (
                  <Badge className="bg-[#F5B942]/15 text-[#F5B942] border border-[#F5B942]/30 text-[9px] py-0 px-1.5">
                    WARN
                  </Badge>
                )}
                {log.level === 'ERROR' && (
                  <Badge className="bg-[#FF4D6D]/15 text-[#FF4D6D] border border-[#FF4D6D]/30 text-[9px] py-0 px-1.5">
                    ERROR
                  </Badge>
                )}
              </div>

              <span className="text-[#4165FF] font-semibold text-[11px] shrink-0">
                [{log.channel}]
              </span>

              <div className="flex-1 space-y-1">
                <div className="text-[#F5F7FB]">{log.message}</div>
                {log.context && (
                  <div className="text-[10px] text-[#98A4B8]/70 bg-[#070B14] p-1.5 rounded border border-[#243149]/40 font-mono">
                    {log.context}
                  </div>
                )}
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
