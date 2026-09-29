import { create } from 'zustand';
import type { ConnectionState } from '@/types/market';

interface ConnectionState_ {
  wsState: ConnectionState;
  brokerConnected: boolean;
  lastUpdateTime: Date | null;
  latencyMs: number | null;
  activeBroker: string | null;
  setWsState: (state: ConnectionState) => void;
  setBrokerConnected: (connected: boolean) => void;
  setLastUpdateTime: (time: Date) => void;
  setLatencyMs: (ms: number) => void;
  setActiveBroker: (broker: string) => void;
  reset: () => void;
}

export const useConnectionStore = create<ConnectionState_>((set) => ({
  wsState: 'OFFLINE',
  brokerConnected: false,
  lastUpdateTime: null,
  latencyMs: null,
  activeBroker: null,

  setWsState: (wsState) => set({ wsState }),
  setBrokerConnected: (brokerConnected) => set({ brokerConnected }),
  setLastUpdateTime: (lastUpdateTime) => set({ lastUpdateTime }),
  setLatencyMs: (latencyMs) => set({ latencyMs }),
  setActiveBroker: (activeBroker) => set({ activeBroker }),

  reset: () =>
    set({
      wsState: 'OFFLINE',
      brokerConnected: false,
      lastUpdateTime: null,
      latencyMs: null,
      activeBroker: null,
    }),
}));
