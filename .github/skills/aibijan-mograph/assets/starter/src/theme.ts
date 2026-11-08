import {Easing, interpolate} from 'remotion';
import brandFile from './brand.json';

// Brand kit (npm run brand -- aibijan-brand.json) fills these slots after contrast checks; defaults otherwise.
type BrandFile = {name: string | null; handle: string | null; cta: string | null; logo: string | null;
  colors: Partial<Record<'navy' | 'ink' | 'yellow' | 'paper' | 'blue', string>>};
export const BRAND = brandFile as unknown as BrandFile;
const B = BRAND.colors ?? {};

export const C = {
  navy: B.navy ?? '#0D1233', ink: B.ink ?? '#070B22', cream: '#F8F1DD', paper: B.paper ?? '#FFFDF6',
  yellow: B.yellow ?? '#FFC83D', sky: '#CDEBFC', blue: B.blue ?? '#398CE6', coral: '#FF775A',
  green: '#9AD6AC', haze: '#B9AA95', smoke: '#6E655D', ember: '#FF6B2C',
  orange: '#F28C38', rust: '#C4502A', red: '#E5484D', muted: '#9AA3BF',
} as const;

// Plus Jakarta Sans (OFL, public/fonts/OFL-plus-jakarta-sans.txt). Glyphs it lacks (arrows, symbols) fall back to the
// bundled DejaVu Sans, so every OS renders the same text.
export const FONT = "'Mograph Sans', 'Mograph Fallback', sans-serif";      // headlines: Bold, 800/900 = ExtraBold
export const TEXT = "'Mograph Text', 'Mograph Fallback', sans-serif";      // body text / captions: Medium
export const MONO = "'Mograph Mono', monospace";
export const lerp = (a: number, b: number, p: number) => a + (b - a) * p;
export const clamp = (n: number) => Math.max(0, Math.min(1, n));
export const ramp = (t: number, start: number, end: number) =>
  interpolate(t, [start, end], [0, 1], {extrapolateLeft: 'clamp', extrapolateRight: 'clamp', easing: Easing.bezier(0.22, 1, 0.36, 1)});

const parse = (value: string) => {
  const n = parseInt(value.slice(1), 16);
  return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
};
export const mix = (a: string, b: string, p: number) => {
  const A = parse(a), B = parse(b);
  return `#${A.map((v, i) => Math.round(v + (B[i] - v) * clamp(p)).toString(16).padStart(2, '0')).join('')}`;
};
export const shade = (c: string, amount: number) => amount >= 0 ? mix(c, '#FFFFFF', amount) : mix(c, '#000000', -amount);
export const rand = (seed: number) => {
  const x = Math.sin(seed * 127.1 + 311.7) * 43758.5453;
  return x - Math.floor(x);
};
