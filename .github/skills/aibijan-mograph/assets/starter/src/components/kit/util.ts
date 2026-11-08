import {Easing, interpolate} from 'remotion';

// Shared timing helpers for the component kit. Every component is driven by `t` (seconds), never by timers.

export const easeOut = Easing.bezier(0.22, 1, 0.36, 1);
export const easeInOut = Easing.bezier(0.65, 0, 0.35, 1);
export const easeBack = Easing.bezier(0.34, 1.56, 0.64, 1);

/** 0→1 progress of an animation that starts at `start` and lasts `dur` seconds. */
export const prog = (t: number, start: number, dur: number, easing: (n: number) => number = easeOut) =>
  dur <= 0 ? (t >= start ? 1 : 0) : interpolate(t, [start, start + dur], [0, 1], {extrapolateLeft: 'clamp', extrapolateRight: 'clamp', easing});

export const lerp = (a: number, b: number, p: number) => a + (b - a) * p;

/** Indonesian number format: 5000000 → "5.000.000", 2.5 (decimals 1) → "2,5". */
export const formatNumber = (n: number, decimals = 0) => {
  const neg = n < 0;
  const fixed = Math.abs(n).toFixed(decimals);
  const [int, dec] = fixed.split('.');
  const grouped = int.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  return (neg ? '-' : '') + grouped + (dec ? ',' + dec : '');
};

/** 5000000 → "Rp5.000.000" (Indonesian style, no space). */
export const formatRupiah = (n: number, prefix = 'Rp') => (n < 0 ? '-' : '') + prefix + formatNumber(Math.round(Math.abs(n)));

/** Deterministic pseudo-random 0..1 from a seed (no Math.random in renders). */
export const seeded = (seed: number) => {
  const x = Math.sin(seed * 127.1 + 311.7) * 43758.5453;
  return x - Math.floor(x);
};
