import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import type { GeneratedSignal, SignalHistoryItem } from '@/types/signal';

interface SignalStore {
  lastSignal: GeneratedSignal | null;
  signalHistory: SignalHistoryItem[];
  selectedAsset: string;
  selectedBroker: string;
  selectedTimeframe: string;
  selectedExpiry: number;
  isGenerating: boolean;
  generatingStep: string;
  generatingProgress: number;

  setLastSignal: (signal: GeneratedSignal | null) => void;
  addToHistory: (signal: SignalHistoryItem) => void;
  setSignalHistory: (history: SignalHistoryItem[]) => void;
  setSelectedAsset: (asset: string) => void;
  setSelectedBroker: (broker: string) => void;
  setSelectedTimeframe: (timeframe: string) => void;
  setSelectedExpiry: (expiry: number) => void;
  setIsGenerating: (generating: boolean) => void;
  setGeneratingStep: (step: string) => void;
  setGeneratingProgress: (progress: number) => void;
  clearLastSignal: () => void;
}

export const useSignalStore = create<SignalStore>()(
  persist(
    (set, get) => ({
      lastSignal: null,
      signalHistory: [],
      selectedAsset: 'EUR/USD (OTC)',
      selectedBroker: 'quotex',
      selectedTimeframe: 'M1',
      selectedExpiry: 60,
      isGenerating: false,
      generatingStep: '',
      generatingProgress: 0,

      setLastSignal: (signal) => set({ lastSignal: signal }),
      addToHistory: (signal) => {
        const current = get().signalHistory;
        set({ signalHistory: [signal, ...current].slice(0, 200) });
      },
      setSignalHistory: (history) => set({ signalHistory: history }),
      setSelectedAsset: (selectedAsset) => set({ selectedAsset }),
      setSelectedBroker: (selectedBroker) => set({ selectedBroker }),
      setSelectedTimeframe: (selectedTimeframe) => set({ selectedTimeframe }),
      setSelectedExpiry: (selectedExpiry) => set({ selectedExpiry }),
      setIsGenerating: (isGenerating) => set({ isGenerating }),
      setGeneratingStep: (generatingStep) => set({ generatingStep }),
      setGeneratingProgress: (generatingProgress) => set({ generatingProgress }),
      clearLastSignal: () => set({ lastSignal: null }),
    }),
    {
      name: 'otc-signal-store',
      partialize: (state) => ({
        selectedAsset: state.selectedAsset,
        selectedBroker: state.selectedBroker,
        selectedTimeframe: state.selectedTimeframe,
        selectedExpiry: state.selectedExpiry,
      }),
    }
  )
);
