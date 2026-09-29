import { api } from '@/lib/api';

export const settingsService = {
  async getSettings() {
    const res = await api.get('/settings');
    return res.data.data;
  },

  async updateSettings(settings: Record<string, any>) {
    const res = await api.put('/settings', settings);
    return res.data.data;
  },

  async getAuditLogs() {
    const res = await api.get('/settings/audit');
    return res.data.data;
  },
};
