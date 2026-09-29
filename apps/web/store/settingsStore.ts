import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface SettingsStore {
  confidenceThreshold: number;
  minDataQuality: number;
  autoScanEnabled: boolean;
  autoScanInterval: number;
  notificationsEnabled: boolean;
  demoMode: boolean;
  soundEnabled: boolean;
  chartTheme: 'dark' | 'light';

  setConfidenceThreshold: (v: number) => void;
  setMinDataQuality: (v: number) => void;
  setAutoScanEnabled: (v: boolean) => void;
  setAutoScanInterval: (v: number) => void;
  setNotificationsEnabled: (v: boolean) => void;
  setDemoMode: (v: boolean) => void;
  setSoundEnabled: (v: boolean) => void;
  setChartTheme: (v: 'dark' | 'light') => void;
}

export const useSettingsStore = create<SettingsStore>()(
  persist(
    (set) => ({
      confidenceThreshold: 70,
      minDataQuality: 70,
      autoScanEnabled: false,
      autoScanInterval: 60,
      notificationsEnabled: true,
      demoMode: false,
      soundEnabled: false,
      chartTheme: 'dark',

      setConfidenceThreshold: (confidenceThreshold) => set({ confidenceThreshold }),
      setMinDataQuality: (minDataQuality) => set({ minDataQuality }),
      setAutoScanEnabled: (autoScanEnabled) => set({ autoScanEnabled }),
      setAutoScanInterval: (autoScanInterval) => set({ autoScanInterval }),
      setNotificationsEnabled: (notificationsEnabled) => set({ notificationsEnabled }),
      setDemoMode: (demoMode) => set({ demoMode }),
      setSoundEnabled: (soundEnabled) => set({ soundEnabled }),
      setChartTheme: (chartTheme) => set({ chartTheme }),
    }),
    { name: 'otc-settings-store' }
  )
);
