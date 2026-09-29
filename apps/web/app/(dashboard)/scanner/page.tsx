'use client';

import React from 'react';
import { PairScannerTable } from '@/components/scanner/PairScannerTable';
import { useRouter } from 'next/navigation';

export default function ScannerPage() {
  const router = useRouter();

  const handleSelectAsset = (asset: string) => {
    router.push(`/dashboard?asset=${encodeURIComponent(asset)}`);
  };

  return (
    <div className="space-y-6">
      <div className="pb-3 border-b border-[#243149]">
        <h1 className="text-lg font-bold font-mono text-[#F5F7FB]">
          MARKET WATCH & CONFLUENCE SCANNER
        </h1>
        <p className="text-xs text-[#98A4B8]">
          Real-time cross-asset scanning across all available OTC broker instruments
        </p>
      </div>

      <PairScannerTable onSelectAsset={handleSelectAsset} />
    </div>
  );
}
