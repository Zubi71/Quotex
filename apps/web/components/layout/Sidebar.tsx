'use client';

import React from 'react';
import Link from 'next/link';
import { usePathname } from 'next/navigation';
import {
  LayoutDashboard,
  Zap,
  Radar,
  History,
  FlaskConical,
  BarChart3,
  Sliders,
  Activity,
  FileText,
  Settings,
  ShieldCheck,
  Radio,
} from 'lucide-react';
import { cn } from '@/lib/utils';
import { Badge } from '@/components/ui/badge';

const navItems = [
  { href: '/dashboard', label: 'Dashboard', icon: LayoutDashboard },
  { href: '/signals', label: 'Signal Generator', icon: Zap },
  { href: '/scanner', label: 'Market Watch', icon: Radar },
  { href: '/history', label: 'Signal History', icon: History },
  { href: '/backtesting', label: 'Backtesting', icon: FlaskConical },
  { href: '/performance', label: 'Performance', icon: BarChart3 },
  { href: '/strategies', label: 'Strategy Settings', icon: Sliders },
  { href: '/data-health', label: 'Data Health', icon: Activity },
  { href: '/system-logs', label: 'System Logs', icon: FileText },
  { href: '/settings', label: 'Settings', icon: Settings },
];

export function Sidebar() {
  const pathname = usePathname();

  return (
    <aside className="w-64 bg-[#0D1422] border-r border-[#243149] flex flex-col shrink-0 select-none">
      {/* Brand Header */}
      <div className="h-16 flex items-center px-5 border-b border-[#243149] gap-3">
        <div className="w-8 h-8 rounded-lg bg-[#4165FF]/20 border border-[#4165FF]/40 flex items-center justify-center text-[#4165FF]">
          <Zap className="w-5 h-5 fill-current" />
        </div>
        <div>
          <span className="font-bold text-sm tracking-wider text-[#F5F7FB] block">
            OTC INTELLIGENCE
          </span>
          <span className="text-[10px] text-[#98A4B8] tracking-widest uppercase block font-mono">
            High Confidence Terminal
          </span>
        </div>
      </div>

      {/* Navigation Links */}
      <nav className="flex-1 py-4 px-3 space-y-1 overflow-y-auto">
        {navItems.map((item) => {
          const Icon = item.icon;
          const isActive = pathname === item.href;
          return (
            <Link
              key={item.href}
              href={item.href}
              className={cn(
                'flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-medium transition-all duration-150',
                isActive
                  ? 'bg-[#4165FF]/15 text-[#4165FF] border border-[#4165FF]/30 font-semibold'
                  : 'text-[#98A4B8] hover:text-[#F5F7FB] hover:bg-[#111A2A]'
              )}
            >
              <Icon className={cn('w-4 h-4', isActive ? 'text-[#4165FF]' : 'text-[#98A4B8]')} />
              <span>{item.label}</span>
            </Link>
          );
        })}
      </nav>

      {/* Footer Info Badge */}
      <div className="p-4 border-t border-[#243149] bg-[#070B14]/40">
        <div className="flex items-center justify-between text-[11px] text-[#98A4B8] mb-2 font-mono">
          <span className="flex items-center gap-1.5">
            <Radio className="w-3.5 h-3.5 text-[#18C78E] animate-pulse" />
            ENGINE v1.0.0
          </span>
          <Badge variant="outline" className="text-[10px] px-1.5 py-0 border-[#243149] text-[#F5B942] bg-[#F5B942]/10">
            DEMO MODE
          </Badge>
        </div>
        <p className="text-[10px] text-[#98A4B8]/70 leading-relaxed font-sans">
          Historical confidence based on multi-factor confirmations. Never guaranteed.
        </p>
      </div>
    </aside>
  );
}
