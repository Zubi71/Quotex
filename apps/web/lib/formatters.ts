import { format, formatDistanceToNow, parseISO } from 'date-fns';
import type { SignalDirection, SignalStatus, MarketRegime } from '@/types/signal';

export function formatPrice(price: number, decimals = 5): string {
  return price.toFixed(decimals);
}

export function formatPercent(value: number, decimals = 2): string {
  return `${value >= 0 ? '+' : ''}${value.toFixed(decimals)}%`;
}

export function formatConfidence(confidence: number): string {
  return `${Math.round(confidence)}%`;
}

export function formatDuration(seconds: number): string {
  if (seconds < 60) return `${seconds}s`;
  const mins = Math.floor(seconds / 60);
  const secs = seconds % 60;
  if (secs === 0) return `${mins}m`;
  return `${mins}m ${secs}s`;
}

export function formatDateTime(isoString: string): string {
  try {
    return format(parseISO(isoString), 'dd MMM yyyy HH:mm:ss');
  } catch {
    return isoString;
  }
}

export function formatDate(isoString: string): string {
  try {
    return format(parseISO(isoString), 'dd MMM yyyy');
  } catch {
    return isoString;
  }
}

export function formatTime(isoString: string): string {
  try {
    return format(parseISO(isoString), 'HH:mm:ss');
  } catch {
    return isoString;
  }
}

export function formatRelativeTime(isoString: string): string {
  try {
    return formatDistanceToNow(parseISO(isoString), { addSuffix: true });
  } catch {
    return isoString;
  }
}

export function getConfidenceColor(confidence: number): string {
  if (confidence >= 80) return '#18C78E';
  if (confidence >= 65) return '#F5B942';
  return '#FF4D6D';
}

export function getConfidenceTextClass(confidence: number): string {
  if (confidence >= 80) return 'text-positive';
  if (confidence >= 65) return 'text-warning';
  return 'text-negative';
}

export function getDirectionColor(direction: SignalDirection): string {
  switch (direction) {
    case 'CALL':
      return '#18C78E';
    case 'PUT':
      return '#FF4D6D';
    case 'NO_TRADE':
      return '#F5B942';
    default:
      return '#98A4B8';
  }
}

export function getDirectionBgClass(direction: SignalDirection): string {
  switch (direction) {
    case 'CALL':
      return 'direction-call';
    case 'PUT':
      return 'direction-put';
    case 'NO_TRADE':
      return 'direction-no-trade';
    default:
      return '';
  }
}

export function getRegimeLabel(regime: MarketRegime): string {
  const labels: Record<MarketRegime, string> = {
    TRENDING_BULLISH: 'Trending Bullish',
    TRENDING_BEARISH: 'Trending Bearish',
    RANGING: 'Ranging',
    HIGH_VOLATILITY: 'High Volatility',
    LOW_VOLATILITY: 'Low Volatility',
    UNCERTAIN: 'Uncertain',
  };
  return labels[regime] ?? regime;
}

export function getRegimeColor(regime: MarketRegime): string {
  switch (regime) {
    case 'TRENDING_BULLISH':
      return '#18C78E';
    case 'TRENDING_BEARISH':
      return '#FF4D6D';
    case 'HIGH_VOLATILITY':
      return '#F5B942';
    case 'RANGING':
      return '#4165FF';
    case 'LOW_VOLATILITY':
      return '#98A4B8';
    case 'UNCERTAIN':
      return '#F5B942';
    default:
      return '#98A4B8';
  }
}

export function getStatusBadgeVariant(status: SignalStatus): 'default' | 'secondary' | 'destructive' | 'outline' {
  switch (status) {
    case 'HIGH_CONFIDENCE':
      return 'default';
    case 'MODERATE':
      return 'secondary';
    case 'WEAK':
      return 'outline';
    case 'NO_TRADE':
      return 'destructive';
    default:
      return 'outline';
  }
}

export function getStatusLabel(status: SignalStatus): string {
  const labels: Record<SignalStatus, string> = {
    HIGH_CONFIDENCE: 'HIGH CONFIDENCE',
    MODERATE: 'MODERATE',
    WEAK: 'WEAK',
    NO_TRADE: 'NO TRADE',
  };
  return labels[status] ?? status;
}

export function getResultColor(result: string): string {
  switch (result) {
    case 'WIN':
      return '#18C78E';
    case 'LOSS':
      return '#FF4D6D';
    case 'VOID':
      return '#F5B942';
    case 'PENDING':
      return '#98A4B8';
    default:
      return '#98A4B8';
  }
}

export function formatCurrency(value: number, symbol = '$'): string {
  const abs = Math.abs(value);
  const formatted = abs.toFixed(2);
  return value >= 0 ? `${symbol}${formatted}` : `-${symbol}${formatted}`;
}

export function formatLargeNumber(value: number): string {
  if (Math.abs(value) >= 1_000_000) return `${(value / 1_000_000).toFixed(1)}M`;
  if (Math.abs(value) >= 1_000) return `${(value / 1_000).toFixed(1)}K`;
  return value.toString();
}

export function clamp(value: number, min: number, max: number): number {
  return Math.min(Math.max(value, min), max);
}

export function buildConfidenceRingDashoffset(confidence: number, circumference = 251.2): number {
  const pct = clamp(confidence, 0, 100) / 100;
  return circumference * (1 - pct);
}
