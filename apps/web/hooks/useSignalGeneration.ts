'use client';

import { useMutation } from '@tanstack/react-query';
import { useState, useCallback } from 'react';
import { signalsApi, type GenerateSignalRequest } from '@/lib/api';
import { useSignalStore } from '@/store/signalStore';
import type { GeneratedSignal } from '@/types/signal';
import type { ApiError } from '@/types/api';

const GENERATION_STEPS = [
  'Validating market connection...',
  'Fetching latest candles...',
  'Calculating indicators...',
  'Analysing market regime...',
  'Running strategy ensemble...',
  'Validating signal quality...',
  'Calculating confidence...',
];

export function useSignalGeneration() {
  const [currentStepIndex, setCurrentStepIndex] = useState<number>(-1);
  const [completedSteps, setCompletedSteps] = useState<number[]>([]);
  const [signal, setSignal] = useState<GeneratedSignal | null>(null);
  const [error, setError] = useState<ApiError | null>(null);

  const {
    setIsGenerating,
    setGeneratingStep,
    setGeneratingProgress,
    setLastSignal,
  } = useSignalStore();

  const simulateProgress = useCallback(async () => {
    setCompletedSteps([]);
    for (let i = 0; i < GENERATION_STEPS.length; i++) {
      setCurrentStepIndex(i);
      setGeneratingStep(GENERATION_STEPS[i]);
      setGeneratingProgress(Math.round(((i + 1) / GENERATION_STEPS.length) * 85));
      await new Promise((r) => setTimeout(r, 350 + Math.random() * 250));
      setCompletedSteps((prev) => [...prev, i]);
    }
  }, [setGeneratingStep, setGeneratingProgress]);

  const mutation = useMutation({
    mutationFn: async (payload: GenerateSignalRequest) => {
      setIsGenerating(true);
      setSignal(null);
      setError(null);

      // Run API call with responsive 600ms visual buffer
      const [result] = await Promise.all([
        signalsApi.generate(payload),
        new Promise((r) => setTimeout(r, 600)),
      ]);

      return result;
    },
    onSuccess: (data) => {
      setGeneratingProgress(100);
      setSignal(data);
      setLastSignal(data);
      setIsGenerating(false);
    },
    onError: (err: ApiError) => {
      setError(err);
      setIsGenerating(false);
      setGeneratingProgress(0);
    },
  });

  const generate = useCallback(
    (payload: GenerateSignalRequest, options?: { onSuccess?: (data: GeneratedSignal) => void; onSettled?: () => void }) => {
      mutation.mutate(payload, options);
    },
    [mutation]
  );

  const reset = useCallback(() => {
    setSignal(null);
    setError(null);
    setCurrentStepIndex(-1);
    setCompletedSteps([]);
    setGeneratingProgress(0);
    setGeneratingStep('');
    setIsGenerating(false);
  }, [setGeneratingStep, setGeneratingProgress, setIsGenerating]);

  return {
    generate,
    reset,
    isLoading: mutation.isPending,
    currentStepIndex,
    completedSteps,
    steps: GENERATION_STEPS,
    signal,
    error,
  };
}
