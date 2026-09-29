'use client';

import React from 'react';

interface ConfidenceRingProps {
  confidence: number;
  size?: number;
  strokeWidth?: number;
}

export function ConfidenceRing({
  confidence,
  size = 120,
  strokeWidth = 10,
}: ConfidenceRingProps) {
  const radius = (size - strokeWidth) / 2;
  const circumference = 2 * Math.PI * radius;
  const safeConfidence = Math.min(100, Math.max(0, confidence));
  const strokeDashoffset = circumference - (safeConfidence / 100) * circumference;

  let strokeColor = '#FF4D6D'; // < 60 red
  if (safeConfidence >= 80) {
    strokeColor = '#18C78E'; // >= 80 green
  } else if (safeConfidence >= 60) {
    strokeColor = '#F5B942'; // 60-79 yellow
  }

  return (
    <div className="relative inline-flex items-center justify-center select-none" style={{ width: size, height: size }}>
      <svg width={size} height={size} className="transform -rotate-90">
        {/* Background Track */}
        <circle
          cx={size / 2}
          cy={size / 2}
          r={radius}
          stroke="#243149"
          strokeWidth={strokeWidth}
          fill="transparent"
        />
        {/* Animated Confidence Arc */}
        <circle
          cx={size / 2}
          cy={size / 2}
          r={radius}
          stroke={strokeColor}
          strokeWidth={strokeWidth}
          strokeDasharray={circumference}
          strokeDashoffset={strokeDashoffset}
          strokeLinecap="round"
          fill="transparent"
          className="transition-all duration-700 ease-out"
        />
      </svg>
      {/* Center Value */}
      <div className="absolute inset-0 flex flex-col items-center justify-center text-center">
        <span className="text-2xl font-bold font-mono tracking-tight text-[#F5F7FB]">
          {safeConfidence}%
        </span>
        <span className="text-[9px] uppercase tracking-widest text-[#98A4B8] font-mono mt-0.5">
          CONFIDENCE
        </span>
      </div>
    </div>
  );
}
