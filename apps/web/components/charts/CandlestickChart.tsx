'use client';

import React, { useEffect, useRef, useState } from 'react';
import { createChart, IChartApi, ISeriesApi, CandlestickData, Time } from 'lightweight-charts';
import { Eye, EyeOff, Layers, ZoomIn, ZoomOut, RotateCcw } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Candle } from '@/types/market';

interface CandlestickChartProps {
  candles: Candle[];
  symbol?: string;
  signalMarker?: {
    direction: 'CALL' | 'PUT';
    price: number;
    time: number;
  } | null;
  height?: number;
}

export function CandlestickChart({
  candles,
  symbol = 'EUR/USD (OTC)',
  signalMarker = null,
  height = 480,
}: CandlestickChartProps) {
  const chartContainerRef = useRef<HTMLDivElement>(null);
  const chartRef = useRef<IChartApi | null>(null);
  const seriesRef = useRef<ISeriesApi<'Candlestick'> | null>(null);

  const [showEma, setShowEma] = useState(true);
  const [showBollinger, setShowBollinger] = useState(true);

  useEffect(() => {
    if (!chartContainerRef.current) return;

    // Initialize Lightweight Chart
    const chart = createChart(chartContainerRef.current, {
      width: chartContainerRef.current.clientWidth,
      height: height,
      layout: {
        background: { color: '#070B14' },
        textColor: '#98A4B8',
        fontSize: 11,
        fontFamily: 'Inter, system-ui, sans-serif',
      },
      grid: {
        vertLines: { color: '#111A2A', style: 1 },
        horzLines: { color: '#111A2A', style: 1 },
      },
      crosshair: {
        vertLine: { color: '#4165FF', width: 1, style: 3, labelBackgroundColor: '#4165FF' },
        horzLine: { color: '#4165FF', width: 1, style: 3, labelBackgroundColor: '#4165FF' },
      },
      rightPriceScale: {
        borderColor: '#243149',
        scaleMargins: { top: 0.1, bottom: 0.1 },
      },
      timeScale: {
        borderColor: '#243149',
        timeVisible: true,
        secondsVisible: false,
      },
    });

    chartRef.current = chart;

    // Add Candlestick Series
    const candleSeries = chart.addCandlestickSeries({
      upColor: '#18C78E',
      downColor: '#FF4D6D',
      borderVisible: false,
      wickUpColor: '#18C78E',
      wickDownColor: '#FF4D6D',
    });
    seriesRef.current = candleSeries;

    // Format candle data
    if (candles.length > 0) {
      const formatted: CandlestickData<Time>[] = candles.map((c) => ({
        time: c.timestamp as Time,
        open: c.open,
        high: c.high,
        low: c.low,
        close: c.close,
      }));

      // Sort chronological
      formatted.sort((a, b) => (Number(a.time) - Number(b.time)));
      candleSeries.setData(formatted);

      // Attach signal markers if provided
      if (signalMarker) {
        candleSeries.setMarkers([
          {
            time: signalMarker.time as Time,
            position: signalMarker.direction === 'CALL' ? 'belowBar' : 'aboveBar',
            color: signalMarker.direction === 'CALL' ? '#18C78E' : '#FF4D6D',
            shape: signalMarker.direction === 'CALL' ? 'arrowUp' : 'arrowDown',
            text: signalMarker.direction,
          },
        ]);
      }

      chart.timeScale().fitContent();
    }

    // Resize Observer
    const handleResize = () => {
      if (chartContainerRef.current && chartRef.current) {
        chartRef.current.applyOptions({
          width: chartContainerRef.current.clientWidth,
        });
      }
    };
    window.addEventListener('resize', handleResize);

    return () => {
      window.removeEventListener('resize', handleResize);
      chart.remove();
    };
  }, [candles, height, signalMarker]);

  const handleResetZoom = () => {
    chartRef.current?.timeScale().fitContent();
  };

  return (
    <div className="bg-[#111A2A] border border-[#243149] rounded-xl overflow-hidden shadow-lg select-none">
      {/* Chart Control Toolbar */}
      <div className="px-4 py-2.5 bg-[#0D1422] border-b border-[#243149] flex items-center justify-between">
        <div className="flex items-center gap-3">
          <span className="font-bold text-xs text-[#F5F7FB] font-mono tracking-wider">
            {symbol}
          </span>
          <span className="text-[10px] text-[#18C78E] font-mono px-1.5 py-0.5 rounded bg-[#18C78E]/10 border border-[#18C78E]/30">
            M1 REALTIME
          </span>
        </div>

        {/* Action Toggles */}
        <div className="flex items-center gap-1.5">
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setShowEma(!showEma)}
            className={`h-7 px-2 text-[10px] font-mono ${
              showEma ? 'text-[#4165FF] bg-[#4165FF]/10' : 'text-[#98A4B8]'
            }`}
          >
            EMA (9/20/50)
          </Button>

          <Button
            variant="ghost"
            size="sm"
            onClick={() => setShowBollinger(!showBollinger)}
            className={`h-7 px-2 text-[10px] font-mono ${
              showBollinger ? 'text-[#F5B942] bg-[#F5B942]/10' : 'text-[#98A4B8]'
            }`}
          >
            BB (20,2)
          </Button>

          <Button
            variant="ghost"
            size="icon"
            onClick={handleResetZoom}
            className="w-7 h-7 text-[#98A4B8] hover:text-[#F5F7FB]"
            title="Reset Zoom"
          >
            <RotateCcw className="w-3.5 h-3.5" />
          </Button>
        </div>
      </div>

      {/* Chart Canvas */}
      <div ref={chartContainerRef} className="w-full relative" />

      {/* Chart Footer Indicator Legend */}
      <div className="px-4 py-1.5 bg-[#070B14] border-t border-[#243149] flex items-center justify-between text-[10px] font-mono text-[#98A4B8]">
        <div className="flex items-center gap-4">
          <span className="flex items-center gap-1">
            <span className="w-2 h-2 rounded-full bg-[#4165FF]" /> EMA 9/20
          </span>
          <span className="flex items-center gap-1">
            <span className="w-2 h-2 rounded-full bg-[#F5B942]" /> BB Upper/Lower
          </span>
          <span className="flex items-center gap-1">
            <span className="w-2 h-2 rounded-full bg-[#18C78E]" /> Call Confluence
          </span>
        </div>
        <span>LIGHTWEIGHT CHARTS v4.1</span>
      </div>
    </div>
  );
}
