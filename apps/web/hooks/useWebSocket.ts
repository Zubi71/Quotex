'use client';

import { useEffect, useRef, useCallback, useState } from 'react';
import { useConnectionStore } from '@/store/connectionStore';
import type { ConnectionState } from '@/types/market';
import type { PriceUpdate } from '@/types/market';
import type { GeneratedSignal } from '@/types/signal';

interface UseWebSocketOptions {
  asset?: string;
  onPriceUpdate?: (data: PriceUpdate) => void;
  onSignal?: (data: GeneratedSignal) => void;
  onHeartbeat?: () => void;
}

const WS_URL = process.env.NEXT_PUBLIC_WS_URL || 'ws://localhost:6001';
const HEARTBEAT_INTERVAL = 30_000;
const RECONNECT_BASE_DELAY = 1_000;
const MAX_RECONNECT_DELAY = 30_000;
const MAX_RECONNECT_ATTEMPTS = 10;

export function useWebSocket(options: UseWebSocketOptions = {}) {
  const wsRef = useRef<WebSocket | null>(null);
  const heartbeatRef = useRef<ReturnType<typeof setInterval> | null>(null);
  const reconnectTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const reconnectAttemptsRef = useRef(0);
  const mountedRef = useRef(true);
  const [isConnected, setIsConnected] = useState(false);

  const { setWsState, setLastUpdateTime, setLatencyMs } = useConnectionStore();

  const clearTimers = useCallback(() => {
    if (heartbeatRef.current) {
      clearInterval(heartbeatRef.current);
      heartbeatRef.current = null;
    }
    if (reconnectTimerRef.current) {
      clearTimeout(reconnectTimerRef.current);
      reconnectTimerRef.current = null;
    }
  }, []);

  const disconnect = useCallback(() => {
    clearTimers();
    if (wsRef.current) {
      wsRef.current.close(1000, 'Component unmounted');
      wsRef.current = null;
    }
    setIsConnected(false);
  }, [clearTimers]);

  const connect = useCallback(() => {
    if (!mountedRef.current) return;
    if (wsRef.current?.readyState === WebSocket.OPEN) return;

    // Build Reverb-compatible URL
    const params = new URLSearchParams({ protocol: 'pusher' });
    const url = `${WS_URL}/app/otc-signal?${params.toString()}`;

    let ws: WebSocket;
    try {
      ws = new WebSocket(url);
    } catch {
      setWsState('OFFLINE');
      return;
    }

    wsRef.current = ws;
    setWsState('RECONNECTING');

    ws.onopen = () => {
      if (!mountedRef.current) return;
      reconnectAttemptsRef.current = 0;
      setIsConnected(true);
      setWsState('LIVE');
      setLastUpdateTime(new Date());

      // Subscribe to channels
      if (options.asset) {
        ws.send(
          JSON.stringify({
            event: 'pusher:subscribe',
            data: { channel: `market.${options.asset}` },
          })
        );
      }
      ws.send(
        JSON.stringify({
          event: 'pusher:subscribe',
          data: { channel: 'signals' },
        })
      );

      // Heartbeat
      heartbeatRef.current = setInterval(() => {
        if (ws.readyState === WebSocket.OPEN) {
          const t0 = Date.now();
          ws.send(JSON.stringify({ event: 'pusher:ping', data: {} }));
          // Pong latency approximation
          setTimeout(() => {
            setLatencyMs(Date.now() - t0);
          }, 100);
        }
      }, HEARTBEAT_INTERVAL);
    };

    ws.onmessage = (event) => {
      if (!mountedRef.current) return;
      try {
        const msg = JSON.parse(event.data as string) as {
          event: string;
          data: unknown;
          channel?: string;
        };

        setLastUpdateTime(new Date());

        if (msg.event === 'pusher:pong') {
          setWsState('LIVE');
          return;
        }

        if (msg.event === 'App\\Events\\PriceUpdated' && options.onPriceUpdate) {
          options.onPriceUpdate(msg.data as PriceUpdate);
        }

        if (msg.event === 'App\\Events\\SignalGenerated' && options.onSignal) {
          options.onSignal(msg.data as GeneratedSignal);
        }

        if (msg.event === 'heartbeat' && options.onHeartbeat) {
          options.onHeartbeat();
        }
      } catch {
        // Ignore parse errors
      }
    };

    ws.onclose = (event) => {
      if (!mountedRef.current) return;
      clearTimers();
      setIsConnected(false);

      if (event.code === 1000) {
        setWsState('OFFLINE');
        return;
      }

      // Exponential backoff reconnect
      if (reconnectAttemptsRef.current < MAX_RECONNECT_ATTEMPTS) {
        setWsState('RECONNECTING');
        const delay = Math.min(
          RECONNECT_BASE_DELAY * Math.pow(2, reconnectAttemptsRef.current),
          MAX_RECONNECT_DELAY
        );
        reconnectAttemptsRef.current++;
        reconnectTimerRef.current = setTimeout(connect, delay);
      } else {
        setWsState('OFFLINE');
      }
    };

    ws.onerror = () => {
      if (!mountedRef.current) return;
      setWsState('RECONNECTING');
    };
  }, [options, clearTimers, setWsState, setLastUpdateTime, setLatencyMs]);

  // Stale detection: if no update in 60s, mark as STALE
  useEffect(() => {
    const staleTimer = setInterval(() => {
      const { lastUpdateTime, wsState } = useConnectionStore.getState();
      if (wsState === 'LIVE' && lastUpdateTime) {
        const staleSecs = (Date.now() - lastUpdateTime.getTime()) / 1000;
        if (staleSecs > 60) {
          setWsState('STALE');
        }
      }
    }, 10_000);
    return () => clearInterval(staleTimer);
  }, [setWsState]);

  useEffect(() => {
    mountedRef.current = true;
    connect();
    return () => {
      mountedRef.current = false;
      disconnect();
    };
  }, [connect, disconnect]);

  // Re-subscribe when asset changes
  useEffect(() => {
    if (wsRef.current?.readyState === WebSocket.OPEN && options.asset) {
      wsRef.current.send(
        JSON.stringify({
          event: 'pusher:subscribe',
          data: { channel: `market.${options.asset}` },
        })
      );
    }
  }, [options.asset]);

  return { isConnected, reconnect: connect, disconnect };
}
