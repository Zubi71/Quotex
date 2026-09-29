import type { Metadata } from 'next';
import { Inter } from 'next/font/google';
import '@/styles/globals.css';
import { Toaster } from '@/components/ui/toaster';
import { Providers } from '@/components/providers/Providers';

const inter = Inter({ subsets: ['latin'], variable: '--font-sans' });

export const metadata: Metadata = {
  title: 'OTC Signal Intelligence — High-Confidence Quantitative Terminal',
  description: 'Multi-factor quantitative trading signal intelligence platform for Quotex OTC markets with non-repainting verification.',
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="en" className="dark">
      <body className={`${inter.variable} font-sans bg-[#070B14] text-[#F5F7FB] antialiased`}>
        <Providers>
          {children}
        </Providers>
        <Toaster />
      </body>
    </html>
  );
}
