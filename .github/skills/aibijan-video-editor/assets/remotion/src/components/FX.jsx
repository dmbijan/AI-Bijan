// After-Effects-style building blocks shared by captions, titles and overlays:
//   <MotionBlurDefs/>  quantised directional blur filters (mb_<x>_<y>), referenced via dirBlur()
//   <FxText/>          kinetic typography presets (rise, pop, slam, track, type, cascade, focus, slide)
//   <Finish/>          final "adjustment layer": film grain + vignette, per style
//   <LeakSweep/>       procedural light leak (screen-blended gradient sweep)
// Every animation is a pure function of time t (seconds), so renders are deterministic and chunkable.
import React from 'react';
import {AbsoluteFill, staticFile} from 'remotion';
import {MB_STEPS, clamp, dirBlur, ease, lerp, prog, rand, shutterBlur, springAt} from '../lib/anim.js';

export const MotionBlurDefs = () => (
  <svg width={0} height={0} style={{position: 'absolute'}} aria-hidden>
    <defs>
      {MB_STEPS.flatMap((x) => MB_STEPS.map((y) => (x || y) ? (
        <filter key={`${x}_${y}`} id={`mb_${x}_${y}`} x="-30%" y="-60%" width="160%" height="220%" colorInterpolationFilters="sRGB">
          <feGaussianBlur stdDeviation={`${x} ${y}`} />
        </filter>
      ) : null))}
      {RGB_STEPS.map((d) => (
        // chromatic split (glitch hit): red shifted right, blue left, recombined with screen (exact when d = 0)
        <filter key={`rgb${d}`} id={`rgb_${d}`} x="-5%" y="-5%" width="110%" height="110%" colorInterpolationFilters="sRGB">
          <feColorMatrix in="SourceGraphic" type="matrix" values="1 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 1 0" result="r" />
          <feOffset in="r" dx={d} dy={0} result="r2" />
          <feColorMatrix in="SourceGraphic" type="matrix" values="0 0 0 0 0  0 1 0 0 0  0 0 0 0 0  0 0 0 1 0" result="g" />
          <feColorMatrix in="SourceGraphic" type="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 1 0 0  0 0 0 1 0" result="b" />
          <feOffset in="b" dx={-d} dy={0} result="b2" />
          <feBlend in="r2" in2="g" mode="screen" result="rg" />
          <feBlend in="rg" in2="b2" mode="screen" />
        </filter>
      ))}
    </defs>
  </svg>
);
export const RGB_STEPS = [3, 6, 10, 15, 22];
export const rgbFilter = (d) => {
  const a = Math.abs(d);
  if (a < 1.5) return '';
  let best = RGB_STEPS[0];
  for (const s of RGB_STEPS) if (a >= s * 0.8) best = s;
  return `url(#rgb_${best})`;
};

// ------------------------------------------------------------------------------------------------ FxText
// One line of text animated per word (or per character for cascade/type). `fx` presets:
//   rise     words slide up out of a mask (classic AE text reveal), vertical motion blur
//   pop      words scale in with spring overshoot
//   slam     whole line drops from 1.9x with a hard landing (pair with an impact SFX)
//   track    letter-spacing collapses from wide to tight while fading in (premium / editorial)
//   type     characters typed in with a caret
//   cascade  characters rise in one after another
//   focus    rack-focus: line sharpens from a soft blur while scaling down slightly
//   slide    words slide in from the left with horizontal motion blur
// Exits are always faster than entrances (0.18-0.3 s) and use the same direction language.
const unitsOf = (text, fx) => {
  if (fx === 'type' || fx === 'cascade') return Array.from(text);
  return text.split(/(\s+)/).filter((s) => s.length);
};

export const FX_PRESETS = ['rise', 'pop', 'slam', 'track', 'type', 'cascade', 'focus', 'slide'];

export function FxText({t, at, out = null, text, fx = 'rise', fps = 60, stagger, dur, style = {}, unitStyle, caretColor}) {
  const units = unitsOf(String(text ?? ''), fx);
  const st = stagger ?? ({rise: 0.055, pop: 0.06, slide: 0.05, cascade: 0.018, type: 0.028}[fx] ?? 0);
  const D = dur ?? ({rise: 0.55, pop: 0.45, slam: 0.32, track: 0.75, cascade: 0.45, focus: 0.6, slide: 0.45}[fx] ?? 0.5);
  const exitD = 0.22;
  const ex = out != null ? ease.exitFast(prog(t, out - exitD, out)) : 0;
  if (t < at) return <span style={{...style, opacity: 0}}>{text}</span>;

  if (fx === 'slam') {
    const p = prog(t, at, at + D);
    const s = (k) => {
      const pp = prog(k, at, at + D);
      return pp < 1 ? lerp(1.9, 0.97, ease.heavy(pp)) : lerp(0.97, 1, ease.snappy(prog(k, at + D, at + D + 0.18)));
    };
    const sc = s(t);
    const v = Math.abs(s(t + 1 / 120) - s(t - 1 / 120)) * 60; // scale units per second
    const b = shutterBlur(v * 260);
    return (
      <span style={{...style, display: 'inline-block', transform: `scale(${sc * (1 - 0.08 * ex)})`, opacity: Math.min(1, p * 5) * (1 - ex),
        filter: b > 0.8 ? `blur(${Math.min(10, b).toFixed(1)}px)` : 'none'}}>{text}</span>
    );
  }
  if (fx === 'track') {
    const p = ease.smooth(prog(t, at, at + D));
    return (
      <span style={{...style, display: 'inline-block', letterSpacing: `${lerp(0.55, style.letterSpacing ? 0 : -0.02, p)}em`,
        opacity: clamp(p * 1.6) * (1 - ex), filter: p < 0.7 ? `blur(${((1 - p / 0.7) * 6).toFixed(1)}px)` : 'none',
        transform: `translateY(${-24 * ease.exitFast(ex)}px)`}}>{text}</span>
    );
  }
  if (fx === 'focus') {
    const p = ease.smooth(prog(t, at, at + D));
    return (
      <span style={{...style, display: 'inline-block', opacity: clamp(p * 1.8) * (1 - ex), transform: `scale(${lerp(1.08, 1, p) - 0.04 * ex})`,
        filter: p < 0.98 ? `blur(${((1 - p) * 16).toFixed(1)}px)` : (ex > 0 ? `blur(${(ex * 8).toFixed(1)}px)` : 'none')}}>{text}</span>
    );
  }
  if (fx === 'type') {
    const n = Math.floor((t - at) / st) + 1;
    const blink = Math.floor((t - at) * 2.6) % 2 === 0;
    const done = n >= units.length;
    return (
      <span style={{...style, display: 'inline-block', opacity: 1 - ex, whiteSpace: 'pre'}}>
        {units.slice(0, n).join('')}<span style={{opacity: 0}}>{units.slice(n).join('')}</span>
        {(!done || blink) && ex === 0 ? <span style={{display: 'inline-block', width: '0.08em', height: '0.9em', marginLeft: '0.04em', verticalAlign: '-0.08em',
          background: caretColor || 'currentColor'}} /> : null}
      </span>
    );
  }
  // per-unit presets: rise / pop / slide / cascade
  return (
    <span style={{...style, display: 'inline-block', whiteSpace: 'pre'}}>
      {units.map((u, i) => {
        if (/^\s+$/.test(u)) return <span key={i} style={{display: 'inline-block', width: '0.26em'}} />;
        const a0 = at + i * st;
        if (fx === 'pop') {
          const p = springAt(t, a0, fps, {damping: 11, stiffness: 230});
          const pv = springAt(t + 1 / 120, a0, fps, {damping: 11, stiffness: 230}) - springAt(t - 1 / 120, a0, fps, {damping: 11, stiffness: 230});
          const b = Math.min(6, Math.abs(pv) * 60 * 0.06);
          return <span key={i} style={{display: 'inline-block', opacity: t < a0 ? 0 : Math.min(1, p * 2) * (1 - ex),
            transform: `translateY(${(1 - Math.min(1, p)) * 0.25}em) scale(${(0.45 + 0.55 * p) * (1 - 0.1 * ex)})`,
            filter: b > 0.6 ? `blur(${b.toFixed(1)}px)` : 'none', ...(unitStyle ? unitStyle(i, u) : {})}}>{u}</span>;
        }
        const ef = ease.snappy;
        const pos = (k) => 1 - ef(prog(k, a0, a0 + D));
        const p = pos(t);
        const v = Math.abs(pos(t + 1 / 120) - pos(t - 1 / 120)) * 60; // units of travel / s
        const fs = parseFloat(style.fontSize) || 80;
        if (fx === 'slide') {
          // short travel + late opacity so neighbouring words never pile on top of each other
          const dx = -0.45 * fs * p;
          return <span key={i} style={{display: 'inline-block', opacity: clamp((1 - p - 0.15) * 2.2) * (1 - ex), transform: `translateX(${dx + 30 * ex}px)`,
            filter: dirBlur(shutterBlur(v * 0.45 * fs), 0), ...(unitStyle ? unitStyle(i, u) : {})}}>{u}</span>;
        }
        // rise / cascade: masked vertical reveal
        const travel = 1.05 * fs;
        const exY = -0.6 * fs * ease.exitFast(ex);
        return (
          <span key={i} style={{display: 'inline-block', overflow: 'hidden', verticalAlign: 'bottom', paddingBottom: '0.06em', paddingTop: '0.02em', marginBottom: '-0.06em'}}>
            <span style={{display: 'inline-block', transform: `translateY(${travel * p + exY}px)`, opacity: 1 - ex * 0.6,
              filter: dirBlur(0, shutterBlur(v * travel)), ...(unitStyle ? unitStyle(i, u) : {})}}>{u}</span>
          </span>
        );
      })}
    </span>
  );
}

// ------------------------------------------------------------------------------------------------ Finish
// Top-most adjustment layer. grain: 0..0.12 opacity of a pre-baked noise tile that "boils" every frame
// (offset jumps deterministically), vignette: 0..0.5 corner darkening. Kept subtle: viewers should feel it, not see it.
export const Finish = ({E, t}) => {
  const F = E.style.finish || {};
  const W = E.width, H = E.height;
  const g = F.grain ?? 0;
  const vg = F.vignette ?? 0;
  if (!g && !vg) return null;
  const fr = Math.floor(t * E.fps);
  const ox = Math.floor(rand(fr + 1) * 512), oy = Math.floor(rand(fr + 17) * 512);
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {vg ? <AbsoluteFill style={{background: `radial-gradient(ellipse 75% 62% at 50% 44%, rgba(0,0,0,0) 58%, rgba(0,0,0,${vg}) 100%)`}} /> : null}
      {g ? <div style={{position: 'absolute', left: 0, top: 0, width: W, height: H, opacity: g, mixBlendMode: 'overlay',
        backgroundImage: `url(${staticFile('fx/grain.jpg')})`, backgroundSize: '512px 512px', backgroundPosition: `${-ox}px ${-oy}px`}} /> : null}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ Letterbox
// Cinematic bars (style.frame.letterbox): slide in over the first second ("the film starts"), optional
// hairline on the inner edge, optional opening at the very end. Sit above everything except grain.
export const Letterbox = ({E, t}) => {
  const L = E.style.frame?.letterbox;
  if (!L) return null;
  const cfg = typeof L === 'number' ? {h: L} : L;
  const W = E.width;
  const h = cfg.h ?? 130;
  const at = cfg.at ?? 0, dur = cfg.dur ?? 1.1;
  const p = at <= 0 && dur <= 0 ? 1 : ease.smooth(prog(t, at, at + dur));
  const op = cfg.open ? ease.smooth(prog(t, E.duration - (cfg.openDur ?? 0.9), E.duration)) : 0;
  const hh = h * p * (1 - op);
  if (hh < 0.5) return null;
  const col = cfg.color || '#0B0A09';
  const line = cfg.line;
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      <div style={{position: 'absolute', left: 0, top: 0, width: W, height: hh, background: col, borderBottom: line ? `2px solid ${line}` : 'none'}} />
      <div style={{position: 'absolute', left: 0, bottom: 0, width: W, height: hh, background: col, borderTop: line ? `2px solid ${line}` : 'none'}} />
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ LeakSweep
// Warm light leak drifting across the frame (screen blend). Used by the 'leak' cut transition and the LightLeak overlay.
export const LeakSweep = ({E, t, at, dur = 0.7, color, color2, strength = 0.75, dir = 1}) => {
  const p = prog(t, at, at + dur);
  if (p <= 0 || p >= 1) return null;
  const W = E.width, H = E.height;
  const env = Math.sin(p * Math.PI);
  const x = lerp(-0.25, 1.15, ease.smooth(p));
  const cx = dir > 0 ? x : 1 - x;
  const c1 = color || 'rgba(255,170,90,1)', c2 = color2 || 'rgba(255,90,60,1)';
  return (
    <AbsoluteFill style={{mixBlendMode: 'screen', opacity: env * strength, pointerEvents: 'none'}}>
      <div style={{position: 'absolute', left: (cx - 0.55) * W, top: -H * 0.1, width: W * 1.1, height: H * 1.2,
        background: `radial-gradient(ellipse 45% 55% at 50% 45%, ${c1} 0%, rgba(0,0,0,0) 70%)`, filter: 'blur(30px)'}} />
      <div style={{position: 'absolute', left: (cx - 0.2) * W, top: H * 0.25, width: W * 0.7, height: H * 0.8,
        background: `radial-gradient(ellipse 50% 50% at 50% 50%, ${c2} 0%, rgba(0,0,0,0) 70%)`, opacity: 0.6, filter: 'blur(40px)'}} />
    </AbsoluteFill>
  );
};

