import type { Config } from 'tailwindcss';

const config: Config = {
  darkMode: ['class'],
  content: [
    './pages/**/*.{ts,tsx}',
    './components/**/*.{ts,tsx}',
    './app/**/*.{ts,tsx}',
    './src/**/*.{ts,tsx}',
  ],
  prefix: '',
  theme: {
    container: {
      center: true,
      padding: '2rem',
      screens: {
        '2xl': '1400px',
      },
    },
    extend: {
      colors: {
        'bg-primary': '#070B14',
        'bg-secondary': '#0D1422',
        'bg-card': '#111A2A',
        'border-card': '#243149',
        'text-primary': '#F5F7FB',
        'text-secondary': '#98A4B8',
        primary: {
          DEFAULT: '#4165FF',
          foreground: '#F5F7FB',
        },
        positive: '#18C78E',
        negative: '#FF4D6D',
        warning: '#F5B942',
        border: '#243149',
        input: '#243149',
        ring: '#4165FF',
        background: '#070B14',
        foreground: '#F5F7FB',
        card: {
          DEFAULT: '#111A2A',
          foreground: '#F5F7FB',
        },
        popover: {
          DEFAULT: '#0D1422',
          foreground: '#F5F7FB',
        },
        secondary: {
          DEFAULT: '#0D1422',
          foreground: '#98A4B8',
        },
        muted: {
          DEFAULT: '#111A2A',
          foreground: '#98A4B8',
        },
        accent: {
          DEFAULT: '#243149',
          foreground: '#F5F7FB',
        },
        destructive: {
          DEFAULT: '#FF4D6D',
          foreground: '#F5F7FB',
        },
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
        mono: ['JetBrains Mono', 'Fira Code', 'monospace'],
      },
      borderRadius: {
        lg: '0.5rem',
        md: 'calc(0.5rem - 2px)',
        sm: 'calc(0.5rem - 4px)',
      },
      keyframes: {
        'accordion-down': {
          from: { height: '0' },
          to: { height: 'var(--radix-accordion-content-height)' },
        },
        'accordion-up': {
          from: { height: 'var(--radix-accordion-content-height)' },
          to: { height: '0' },
        },
        'pulse-slow': {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0.4' },
        },
        'confidence-fill': {
          '0%': { 'stroke-dashoffset': '251.2' },
          '100%': { 'stroke-dashoffset': 'var(--target-offset)' },
        },
        'skeleton-wave': {
          '0%': { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0' },
        },
        'fade-in': {
          from: { opacity: '0', transform: 'translateY(4px)' },
          to: { opacity: '1', transform: 'translateY(0)' },
        },
        'slide-in-right': {
          from: { opacity: '0', transform: 'translateX(20px)' },
          to: { opacity: '1', transform: 'translateX(0)' },
        },
        'scale-in': {
          from: { opacity: '0', transform: 'scale(0.95)' },
          to: { opacity: '1', transform: 'scale(1)' },
        },
        blink: {
          '0%, 100%': { opacity: '1' },
          '50%': { opacity: '0' },
        },
      },
      animation: {
        'accordion-down': 'accordion-down 0.2s ease-out',
        'accordion-up': 'accordion-up 0.2s ease-out',
        'pulse-slow': 'pulse-slow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite',
        'confidence-fill': 'confidence-fill 1.2s ease-out forwards',
        'skeleton-wave': 'skeleton-wave 1.5s linear infinite',
        'fade-in': 'fade-in 0.3s ease-out',
        'slide-in-right': 'slide-in-right 0.3s ease-out',
        'scale-in': 'scale-in 0.2s ease-out',
        blink: 'blink 1s step-end infinite',
      },
      boxShadow: {
        card: '0 4px 24px 0 rgba(0, 0, 0, 0.4)',
        'card-hover': '0 8px 32px 0 rgba(0, 0, 0, 0.5)',
        glow: '0 0 20px rgba(65, 101, 255, 0.3)',
        'glow-positive': '0 0 20px rgba(24, 199, 142, 0.3)',
        'glow-negative': '0 0 20px rgba(255, 77, 109, 0.3)',
      },
      backgroundImage: {
        'gradient-card': 'linear-gradient(135deg, #111A2A 0%, #0D1422 100%)',
        'gradient-primary': 'linear-gradient(135deg, #4165FF 0%, #2948CC 100%)',
        'gradient-positive': 'linear-gradient(135deg, #18C78E 0%, #0F9E6F 100%)',
        'gradient-negative': 'linear-gradient(135deg, #FF4D6D 0%, #CC2D4A 100%)',
      },
    },
  },
  plugins: [],
};

export default config;
