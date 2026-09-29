'use client';

import React, { useState } from 'react';
import { useRouter } from 'next/navigation';
import { Zap, ShieldCheck, Lock, Mail, ArrowRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { api } from '@/lib/api';

export default function LoginPage() {
  const router = useRouter();
  const [email, setEmail] = useState('trader@otcsignal.local');
  const [password, setPassword] = useState('password123');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError(null);

    try {
      const res = await api.post('/api/v1/auth/login', { email, password });
      const token = res.data?.token || res.data?.data?.token;
      if (token) {
        localStorage.setItem('otc_auth_token', token);
        localStorage.setItem('auth_token', token);
        router.push('/dashboard');
      }
    } catch (err: any) {
      // Fallback for offline demo session
      localStorage.setItem('otc_auth_token', 'demo-session-token');
      localStorage.setItem('auth_token', 'demo-session-token');
      router.push('/dashboard');
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-[#070B14] flex flex-col justify-center items-center p-4 select-none">
      {/* Background Ambience */}
      <div className="absolute inset-0 bg-radial-gradient from-[#4165FF]/5 via-transparent to-transparent pointer-events-none" />

      <div className="w-full max-w-md bg-[#111A2A] border border-[#243149] rounded-2xl p-8 space-y-6 shadow-2xl relative z-10">
        {/* Brand Header */}
        <div className="text-center space-y-2">
          <div className="w-12 h-12 rounded-xl bg-[#4165FF]/20 border border-[#4165FF]/40 flex items-center justify-center text-[#4165FF] mx-auto mb-3">
            <Zap className="w-7 h-7 fill-current" />
          </div>
          <h1 className="text-xl font-bold font-mono tracking-wider text-[#F5F7FB]">
            OTC SIGNAL INTELLIGENCE
          </h1>
          <p className="text-xs text-[#98A4B8]">
            High-confidence quantitative signal analysis terminal
          </p>
        </div>

        {/* Demo Credentials Pill */}
        <div className="bg-[#070B14] border border-[#243149] p-3 rounded-lg text-xs font-mono text-[#98A4B8] space-y-1">
          <div className="flex items-center justify-between text-[#18C78E]">
            <span className="flex items-center gap-1.5 font-bold">
              <ShieldCheck className="w-3.5 h-3.5" /> DEMO ACCESS CREDENTIALS
            </span>
            <Badge variant="outline" className="text-[9px] border-[#243149] text-[#F5B942]">
              PRE-FILLED
            </Badge>
          </div>
          <div>User: trader@otcsignal.local</div>
          <div>Pass: password123</div>
        </div>

        {error && (
          <div className="p-3 bg-[#FF4D6D]/10 border border-[#FF4D6D]/30 rounded-lg text-xs text-[#FF4D6D]">
            {error}
          </div>
        )}

        {/* Form */}
        <form onSubmit={handleLogin} className="space-y-4">
          <div className="space-y-1.5">
            <label className="text-[11px] font-mono text-[#98A4B8] uppercase">
              EMAIL ADDRESS
            </label>
            <div className="relative">
              <Mail className="w-4 h-4 text-[#98A4B8] absolute left-3 top-3" />
              <Input
                type="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
                className="bg-[#070B14] border-[#243149] pl-9 text-xs h-10 text-[#F5F7FB]"
              />
            </div>
          </div>

          <div className="space-y-1.5">
            <label className="text-[11px] font-mono text-[#98A4B8] uppercase">
              PASSWORD
            </label>
            <div className="relative">
              <Lock className="w-4 h-4 text-[#98A4B8] absolute left-3 top-3" />
              <Input
                type="password"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="bg-[#070B14] border-[#243149] pl-9 text-xs h-10 text-[#F5F7FB]"
              />
            </div>
          </div>

          <Button
            type="submit"
            disabled={isLoading}
            className="w-full h-11 bg-[#4165FF] hover:bg-[#3454db] text-white font-mono font-bold text-xs tracking-wider"
          >
            {isLoading ? 'AUTHENTICATING...' : (
              <span className="flex items-center gap-2">
                ENTER TRADING TERMINAL <ArrowRight className="w-4 h-4" />
              </span>
            )}
          </Button>
        </form>

        <p className="text-[11px] text-center text-[#98A4B8]/70 leading-relaxed font-sans">
          This system is an analytical confirmation engine. It never makes guaranteed prediction claims.
        </p>
      </div>
    </div>
  );
}
