// Pure animation helpers. Everything is a function of time t (seconds) so renders are deterministic.
import {spring} from 'remotion';

export const clamp = (x, a = 0, b = 1) => Math.min(b, Math.max(a, x));
export const lerp = (a, b, p) => a + (b - a) * p;
export const prog = (t, a, b) => (b <= a ? (t >= a ? 1 : 0) : clamp((t - a) / (b - a)));

// Cubic-bezier easing (same maths as CSS / After Effects' speed graph). Solved with Newton + bisection.
export const bezier = (x1, y1, x2, y2) => {
  const A = (a, b) => 1 - 3 * b + 3 * a, B = (a, b) => 3 * b - 6 * a, Cc = (a) => 3 * a;
  const calc = (t, a, b) => ((A(a, b) * t + B(a, b)) * t + Cc(a)) * t;
  const slope = (t, a, b) => 3 * A(a, b) * t * t + 2 * B(a, b) * t + Cc(a);
  return (x) => {
    if (x <= 0) return 0;
    if (x >= 1) return 1;
    let t = x;
    for (let i = 0; i < 6; i++) {
      const s = slope(t, x1, x2);
      if (Math.abs(s) < 1e-6) break;
      t -= (calc(t, x1, x2) - x) / s;
    }
    if (t < 0 || t > 1 || Math.abs(calc(t, x1, x2) - x) > 1e-4) {
      let lo = 0, hi = 1; t = x;
      for (let i = 0; i < 30; i++) { const v = calc(t, x1, x2); if (Math.abs(v - x) < 1e-5) break; if (v < x) lo = t; else hi = t; t = (lo + hi) / 2; }
    }
    return calc(t, y1, y2);
  };
};

// Named curves an After Effects motion designer would reach for.
//   easyEase  F9 "Easy Ease" (33 % influence both sides)         -> neutral moves
//   snappy    fast start, very long tail (70-80 % outgoing infl.) -> entrances, UI, text
//   heavy     slow start, hard landing                            -> slams, impacts
//   glide     gentle, for camera pushes and drifts
const CURVES = {
  easyEase: bezier(0.33, 0, 0.67, 1),
  snappy: bezier(0.16, 1, 0.3, 1),
  smooth: bezier(0.45, 0, 0.15, 1),
  heavy: bezier(0.7, 0, 0.84, 0),
  glide: bezier(0.37, 0, 0.18, 1),
  anticipate: bezier(0.36, -0.35, 0.3, 1),
  exitFast: bezier(0.55, 0, 1, 0.45),
};

export const ease = {
  ...CURVES,
  linear: (p) => p,
  out: (p) => 1 - Math.pow(1 - p, 3),
  in: (p) => p * p * p,
  inOut: (p) => (p < 0.5 ? 4 * p * p * p : 1 - Math.pow(-2 * p + 2, 3) / 2),
  outExpo: (p) => (p >= 1 ? 1 : 1 - Math.pow(2, -10 * p)),
  inOutExpo: (p) => (p <= 0 ? 0 : p >= 1 ? 1 : p < 0.5 ? Math.pow(2, 20 * p - 10) / 2 : (2 - Math.pow(2, -20 * p + 10)) / 2),
  outBack: (p) => {
    const c1 = 1.70158, c3 = c1 + 1;
    return 1 + c3 * Math.pow(p - 1, 3) + c1 * Math.pow(p - 1, 2);
  },
  // "smooth camera" curve: gentle start, long settle
  camera: (p) => (p < 0.5 ? 8 * p ** 4 : 1 - Math.pow(-2 * p + 2, 4) / 2),
};

// Spring value at (t - start). Same physics Remotion uses, but addressed in seconds.
export const springAt = (t, start, fps, config = {}) => {
  if (t < start) return 0;
  return spring({frame: (t - start) * fps, fps, config: {damping: 14, stiffness: 170, mass: 1, ...config}});
};

// Enter/exit envelope for an element visible in [start, end]. Returns {p, out} with p = entrance progress
// (spring, may overshoot) and out = exit progress (0..1).
export const life = (t, start, end, fps, {enter = {}, exitDur = 0.22} = {}) => {
  const p = springAt(t, start, fps, enter);
  const out = end == null ? 0 : ease.inOut(prog(t, end - exitDur, end));
  return {p, out, visible: t >= start - 1e-6 && (end == null || t <= end + 1e-6)};
};

// Velocity-based blur for a 0..1 progress function; approximates motion blur for fast entrances.
export const motionBlur = (f, t, k = 22, max = 10) => {
  const dt = 1 / 60;
  const v = Math.abs(f(t + dt) - f(t - dt)) / (2 * dt);
  return Math.min(max, v * k / 30);
};

// ---------------------------------------------------------------------------------- directional motion blur
// Real motion blur smears along the direction of travel, not in every direction. We emulate a 180 deg shutter:
// blur length = distance travelled in half a frame (1/120 s). SVG feGaussianBlur takes separate x/y deviations,
// so a horizontal move gets a horizontal smear. Filters are pre-declared (MotionBlurDefs) in quantised steps
// so thousands of frames share a handful of filter definitions.
export const MB_STEPS = [0, 1, 2, 3, 4, 6, 8, 11, 14, 18, 24];
const q = (v) => {
  const a = Math.abs(v);
  let best = 0;
  for (const s of MB_STEPS) if (a >= s * 0.85) best = s;
  return best;
};
// px/s velocity -> blur deviation px (shutter 180 deg at 60 fps => 1/120 s exposure; deviation ~ length/2)
export const shutterBlur = (pxPerSec, shutter = 1) => (pxPerSec / 120) * 0.5 * shutter;
// CSS filter value for a blur of (bx, by) px; returns 'none' when the element is (almost) still.
export const dirBlur = (bx, by) => {
  const x = q(bx), y = q(by);
  if (!x && !y) return 'none';
  return `url(#mb_${x}_${y})`;
};
// Velocity of a scalar track f(t) in units/s (central difference, one 60 fps frame)
export const vel = (f, t) => (f(t + 1 / 120) - f(t - 1 / 120)) * 60;

// Smooth value noise in [-1, 1] (for subtle camera drift). Deterministic.
export const noise1 = (x, seed = 0) => {
  const i = Math.floor(x), f = x - i;
  const a = rand(i + seed * 101) * 2 - 1, b = rand(i + 1 + seed * 101) * 2 - 1;
  const u = f * f * (3 - 2 * f);
  return a + (b - a) * u;
};
export const fbm = (x, seed = 0) => noise1(x, seed) * 0.65 + noise1(x * 2.1, seed + 7) * 0.35;

// Deterministic pseudo-random in [0,1)
export const rand = (seed) => {
  let x = Math.sin(seed * 12.9898 + 78.233) * 43758.5453;
  return x - Math.floor(x);
};

// Keyframe track: keys [{t, dur, ease, ...values}] -> interpolated values at time t.
// A key's values are reached at k.t + k.dur, starting from the state at k.t.
export const evalTrack = (keys, t, fields, resolve = (k) => k) => {
  if (!keys || keys.length === 0) return null;
  let state = {...resolve(keys[0], t)};
  for (let i = 1; i < keys.length; i++) {
    const k = keys[i];
    if (t < k.t) break;
    const target = resolve(k, t);
    const dur = k.dur ?? 0.4;
    const p = dur <= 0 || k.ease === 'cut' ? 1 : (ease[k.ease || 'camera'] || ease.camera)(prog(t, k.t, k.t + dur));
    const next = {...state};
    for (const f of fields) {
      if (target[f] == null) continue;
      next[f] = state[f] == null ? target[f] : lerp(state[f], target[f], p);
    }
    state = next;
  }
  return state;
};

// Linear interpolation inside a sampled series [[t, ...values]]
export const sample = (series, t) => {
  if (!series || series.length === 0) return null;
  if (t <= series[0][0]) return series[0].slice(1);
  if (t >= series[series.length - 1][0]) return series[series.length - 1].slice(1);
  let lo = 0, hi = series.length - 1;
  while (hi - lo > 1) {
    const m = (lo + hi) >> 1;
    if (series[m][0] <= t) lo = m; else hi = m;
  }
  const a = series[lo], b = series[hi];
  const p = (t - a[0]) / (b[0] - a[0] || 1);
  return a.slice(1).map((v, i) => lerp(v, b[i + 1], p));
};
