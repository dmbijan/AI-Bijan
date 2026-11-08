import React from 'react';
import {Easing, interpolate, staticFile, useCurrentFrame, useVideoConfig} from 'remotion';
import {words} from '../lib/words';
import {buildPages, CaptionPage} from '../components/WordCaptions';

// Shared plumbing for the named style prototypes (ARSIP, BLUEPRINT, SKETSA).
// Every style uses the same clock, camera, draw-on and caption timing; only the look differs.

export const useTime = () => {
  const frame = useCurrentFrame();
  const {fps} = useVideoConfig();
  return {t: frame / fps, frame, fps};
};

const OUT = Easing.bezier(0.22, 1, 0.36, 1);
const BACK = Easing.bezier(0.34, 1.56, 0.64, 1);
const INOUT = Easing.inOut(Easing.cubic);
const clampOpts = {extrapolateLeft: 'clamp', extrapolateRight: 'clamp'} as const;

/** 0→1 between a and b (seconds), ease-out. */
export const p = (t: number, a: number, b: number, e = OUT) => interpolate(t, [a, b], [0, 1], {...clampOpts, easing: e});
/** 0→1 with a small overshoot. */
export const pb = (t: number, a: number, b: number) => p(t, a, b, BACK);
/** 0→1 ease-in-out (camera moves). */
export const pio = (t: number, a: number, b: number) => p(t, a, b, INOUT);
export const lerp = (a: number, b: number, q: number) => a + (b - a) * q;
export const rnd = (seed: number) => {
  const x = Math.sin(seed * 127.1 + 311.7) * 43758.5453;
  return x - Math.floor(x);
};

/** Props that draw an SVG stroke on from 0 to 1 (works on path/line/rect/circle/polyline). */
export const drawOn = (q: number) => ({
  pathLength: 1,
  strokeDasharray: '1 2',
  strokeDashoffset: 1 - q,
  opacity: q <= 0.002 ? 0 : 1,
});

// ---------- camera ----------
export type Pose = {x: number; y: number; s: number; r: number};
export type Move = {from: number; to: number} & Partial<Pose>;

/** Camera pose at time t: starts at `start`, each move eases toward its target between from/to. */
export const camera = (t: number, start: Pose, moves: Move[]): Pose => {
  let pose = {...start};
  for (const m of moves) {
    const q = pio(t, m.from, m.to);
    pose = {
      x: lerp(pose.x, m.x ?? pose.x, q),
      y: lerp(pose.y, m.y ?? pose.y, q),
      s: lerp(pose.s, m.s ?? pose.s, q),
      r: lerp(pose.r, m.r ?? pose.r, q),
    };
  }
  return pose;
};

/** Decaying deterministic shake after each impact time. */
export const shake = (t: number, impacts: number[], amp = 12, dur = 0.28) => {
  let x = 0, y = 0;
  impacts.forEach((at, i) => {
    const k = t - at;
    if (k < 0 || k > dur) return;
    const fall = 1 - k / dur;
    x += Math.sin(k * 90 + i) * amp * fall * fall;
    y += Math.cos(k * 77 + i * 3) * amp * 0.7 * fall * fall;
  });
  return {x, y};
};

/** World layer: (pose.x, pose.y) in world coordinates lands at the screen centre. */
export const World: React.FC<{pose: Pose; offset?: {x: number; y: number}; children: React.ReactNode; w?: number; h?: number}> = ({
  pose, offset = {x: 0, y: 0}, children, w = 1080, h = 1920,
}) => (
  <div style={{position: 'absolute', left: 0, top: 0, width: w, height: h, transformOrigin: '0 0',
    transform: `translate(${540 + offset.x}px, ${960 + offset.y}px) rotate(${pose.r}deg) scale(${pose.s}) translate(${-pose.x}px, ${-pose.y}px)`}}>
    {children}
  </div>
);

// ---------- captions ----------
export const PAGES: CaptionPage[] = buildPages(words, 4, 24);
export const pageAt = (t: number, pages: CaptionPage[] = PAGES) => pages.find((pg) => t >= pg.start - 0.08 && t < pg.end) ?? null;
/** Caption pages for any episode's word list (ElevenLabs tags never reach words.json, whisper only hears speech). */
export const pagesFor = (ws: Array<{i: number; word: string; start: number; end: number}>, maxWords = 4, maxChars = 24) => buildPages(ws, maxWords, maxChars);
export const display = (w: string) => w.replace(/[.,;:]+(["')\]]*)$/, '$1');

// ---------- fonts for all three styles (all SIL OFL, licences in public/fonts) ----------
export const StyleFonts: React.FC = () => (
  <style>{`
    @font-face {font-family:'Arsip Head';src:url('${staticFile('fonts/PlayfairDisplay-Var.ttf')}') format('truetype');font-weight:400 900;}
    @font-face {font-family:'Arsip Type';src:url('${staticFile('fonts/CourierPrime-Regular.ttf')}') format('truetype');font-weight:400;}
    @font-face {font-family:'Arsip Type';src:url('${staticFile('fonts/CourierPrime-Bold.ttf')}') format('truetype');font-weight:700;}
    @font-face {font-family:'BP Head';src:url('${staticFile('fonts/SpaceGrotesk-Var.ttf')}') format('truetype');font-weight:300 700;}
    @font-face {font-family:'BP Mono';src:url('${staticFile('fonts/IBMPlexMono-Medium.ttf')}') format('truetype');font-weight:500;}
    @font-face {font-family:'BP Mono';src:url('${staticFile('fonts/IBMPlexMono-SemiBold.ttf')}') format('truetype');font-weight:600 700;}
    @font-face {font-family:'Sketsa Head';src:url('${staticFile('fonts/CaveatBrush-Regular.ttf')}') format('truetype');font-weight:400 900;}
    @font-face {font-family:'Sketsa Hand';src:url('${staticFile('fonts/PatrickHand-Regular.ttf')}') format('truetype');font-weight:400 900;}
    @font-face {font-family:'Hik Head';src:url('${staticFile('fonts/Cinzel-Var.ttf')}') format('truetype');font-weight:400 900;}
    @font-face {font-family:'Hik Text';src:url('${staticFile('fonts/CormorantGaramond-Var.ttf')}') format('truetype');font-weight:300 700;}
    @font-face {font-family:'Geo Head';src:url('${staticFile('fonts/Archivo-Var.ttf')}') format('truetype');font-weight:100 900;font-stretch:62% 125%;}
    @font-face {font-family:'Geo Text';src:url('${staticFile('fonts/Inter-Var.ttf')}') format('truetype');font-weight:100 900;}
    @font-face {font-family:'Term Head';src:url('${staticFile('fonts/VT323-Regular.ttf')}') format('truetype');font-weight:400 900;}
    @font-face {font-family:'Term Mono';src:url('${staticFile('fonts/JetBrainsMono-Var.ttf')}') format('truetype');font-weight:100 800;}
  `}</style>
);

/** Tileable SVG noise as a CSS background (rasterised once by the browser, cheap per frame). */
export const noise = (freq: number, octaves = 3, seed = 1, size = 512) => {
  const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='${size}' height='${size}'><filter id='n'><feTurbulence type='fractalNoise' baseFrequency='${freq}' numOctaves='${octaves}' seed='${seed}' stitchTiles='stitch'/><feColorMatrix type='saturate' values='0'/></filter><rect width='100%' height='100%' filter='url(#n)'/></svg>`;
  return `url("data:image/svg+xml;utf8,${encodeURIComponent(svg)}")`;
};

// ---------- hand-drawn geometry (SKETSA uses it; jitter is seeded so frames are deterministic) ----------
type Pt = [number, number];
/** Catmull-Rom spline through points → smooth SVG path. */
export const spline = (pts: Pt[], closed = false) => {
  if (pts.length < 2) return '';
  const P = closed ? [pts[pts.length - 1], ...pts, pts[0], pts[1]] : [pts[0], ...pts, pts[pts.length - 1]];
  let d = `M ${P[1][0].toFixed(1)} ${P[1][1].toFixed(1)}`;
  for (let i = 1; i < P.length - 2; i++) {
    const [p0, p1, p2, p3] = [P[i - 1], P[i], P[i + 1], P[i + 2]];
    const c1: Pt = [p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6];
    const c2: Pt = [p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6];
    d += ` C ${c1[0].toFixed(1)} ${c1[1].toFixed(1)}, ${c2[0].toFixed(1)} ${c2[1].toFixed(1)}, ${p2[0].toFixed(1)} ${p2[1].toFixed(1)}`;
  }
  return d;
};

/** Wobbly line from a to b. */
export const roughLine = (a: Pt, b: Pt, seed: number, wob = 4) => {
  const n = 4;
  const pts: Pt[] = [];
  for (let i = 0; i <= n; i++) {
    const k = i / n;
    const j = i === 0 || i === n ? 0.4 : 1;
    pts.push([lerp(a[0], b[0], k) + (rnd(seed + i * 3.1) - 0.5) * wob * j, lerp(a[1], b[1], k) + (rnd(seed + i * 7.7) - 0.5) * wob * j]);
  }
  return spline(pts);
};

/** Hand-drawn ellipse that overshoots its start, like a pen loop. */
export const roughEllipse = (cx: number, cy: number, rx: number, ry: number, seed: number, wob = 0.06, turns = 1.12) => {
  const n = 22;
  const a0 = rnd(seed) * Math.PI * 2;
  const pts: Pt[] = [];
  for (let i = 0; i <= n; i++) {
    const a = a0 + (i / n) * Math.PI * 2 * turns;
    const k = 1 + (rnd(seed + i * 1.3) - 0.5) * wob * 2 + (i / n) * 0.05;
    pts.push([cx + Math.cos(a) * rx * k, cy + Math.sin(a) * ry * k]);
  }
  return spline(pts);
};

/** Hand-drawn rectangle: four slightly overshooting strokes in one path. */
export const roughRect = (x: number, y: number, w: number, h: number, seed: number, wob = 5) => {
  const o = 8;
  return [
    roughLine([x - o, y], [x + w + o * 0.5, y + 1], seed, wob),
    roughLine([x + w, y - o * 0.6], [x + w - 1, y + h + o], seed + 10, wob),
    roughLine([x + w + o * 0.4, y + h], [x - o * 0.7, y + h - 1], seed + 20, wob),
    roughLine([x, y + h + o * 0.5], [x + 1, y - o * 0.4], seed + 30, wob),
  ].join(' ');
};
