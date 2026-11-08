// STORYTELLER building blocks: cinematic narration where the picture follows the emotion of the line.
//
//   MoodGrade    full-frame grade that eases in/out per beat: warm, amused, dream, shock (flash + red punch),
//                whisper (dark, rose), serious (cold, desaturated), dark (near-black drama), excited
//   SearchStack  generic search bars floating above the speaker's head (face-tracked), queries typed in; new bars
//                push older ones up and back (depth), `max` limits how many stay
//   Floaters     3D-looking glyphs / icons (?, !, eye, search, heart, lock) drifting around the head with depth of field
//   Shout        huge extruded 3D words that slam in (use layer "behind" to put them behind the speaker)
//   StoryTitle   cinematic title: tracked kicker + display word rising out of a mask + a light arc with a flare
//   SceneLens    dark scene: a cloud of spoken words drifting in depth, read through a moving magnifier
//
// All motion is eased (quint/expo); everything is a pure function of time; nothing imitates a real product's UI.
import React from 'react';
import {AbsoluteFill} from 'remotion';
import {bezier, clamp, lerp, prog, rand, shutterBlur, vel} from '../lib/anim.js';
import {Icon} from './Icons.jsx';
import {faceOnCanvas} from './Footage.jsx';
import {lumHex, theme} from './Scenes.jsx';

const qOut = bezier(0.22, 1, 0.36, 1);
const qIn = bezier(0.64, 0, 0.78, 0);
const qInOut = bezier(0.83, 0, 0.17, 1);
const cInOut = bezier(0.65, 0, 0.35, 1);
const eOut = (p) => (p >= 1 ? 1 : 1 - Math.pow(2, -10 * p));
const tw = (t, a, d, f = qOut) => f(prog(t, a, a + d));
const blurF = (b) => (b > 0.4 ? `blur(${b.toFixed(1)}px)` : 'none');
const col = (C, name, dflt) => (name ? (C[name] || name) : dflt);
const shade = (hex, k) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || '')); if (!m) return hex;
  const n = parseInt(m[1], 16);
  const f = (v) => Math.max(0, Math.min(255, Math.round(v * k)));
  return `rgb(${f((n >> 16) & 255)},${f((n >> 8) & 255)},${f(n & 255)})`;
};
// solid extrusion: n stacked hard shadows getting darker, plus one soft contact shadow
const extrude = (hex, depth, n = 10, dx = 0, k0 = 1) => Array.from({length: n}, (_, i) =>
  `${((i + 1) * dx).toFixed(1)}px ${((i + 1) * depth / n).toFixed(1)}px 0 ${shade(hex, k0 * (1 - i * 0.045))}`).join(', ') + `, ${dx * n}px ${depth * 1.6}px ${depth * 2}px rgba(0,0,0,0.45)`;

// ------------------------------------------------------------------------------------------------ MoodGrade
const MOODS = {
  neutral: {},
  warm: {tint: '#FFB25C', blend: 'soft-light', a: 0.38, vig: 0.28},
  dream: {tint: '#FFE2B8', blend: 'screen', a: 0.12, vig: 0.22, glow: 0.18},
  amused: {tint: '#FFC94D', blend: 'soft-light', a: 0.32, vig: 0.22},
  excited: {tint: '#FFD23F', blend: 'soft-light', a: 0.3, vig: 0.12, glow: 0.12},
  shock: {tint: '#FF2A3A', blend: 'soft-light', a: 0.4, desat: 0.15, vig: 0.5, flash: 0.75},
  whisper: {tint: '#FF6FA8', blend: 'soft-light', a: 0.26, desat: 0.35, vig: 0.75, crush: 0.12},
  serious: {tint: '#3C7DFF', blend: 'soft-light', a: 0.38, desat: 0.45, vig: 0.5, crush: 0.06},
  dark: {tint: '#17406E', blend: 'soft-light', a: 0.45, desat: 0.55, vig: 0.8, crush: 0.22},
  // v3.10: black & white that blooms into colour (in: 0.01, out: 0.6-0.9) - flashbacks, "before" moments
  mono: {desat: 1, vig: 0.12},
};
export const MoodGrade = ({E, t, o}) => {
  const M = MOODS[o.mood || 'neutral'] || {};
  const inD = o.in ?? 0.6, outD = o.out ?? 0.6;
  const k = tw(t, o.start, inD, qInOut) * (o.end != null ? 1 - tw(t, o.end - outD, outD, qInOut) : 1) * (o.amount ?? 1);
  if (k <= 0.004) return null;
  const flash = M.flash ? M.flash * (1 - qOut(prog(t, o.start, o.start + 0.35))) * (t >= o.start ? 1 : 0) : 0;
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {M.desat ? <AbsoluteFill style={{background: '#808080', mixBlendMode: 'saturation', opacity: M.desat * k}} /> : null}
      {M.tint ? <AbsoluteFill style={{background: o.tint || M.tint, mixBlendMode: M.blend, opacity: M.a * k}} /> : null}
      {M.glow ? <AbsoluteFill style={{background: 'radial-gradient(ellipse 60% 45% at 50% 38%, rgba(255,240,215,0.9), rgba(255,240,215,0) 70%)', mixBlendMode: 'screen', opacity: M.glow * k}} /> : null}
      {M.crush ? <AbsoluteFill style={{background: '#000', opacity: M.crush * k}} /> : null}
      {M.vig ? <AbsoluteFill style={{background: `radial-gradient(ellipse 72% 58% at 50% 42%, rgba(0,0,0,0) 28%, rgba(0,0,0,${M.vig}) 100%)`, opacity: k}} /> : null}
      {flash > 0.01 ? <AbsoluteFill style={{background: '#FFF6F0', opacity: flash}} /> : null}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ SearchStack
// {items:[{text, at, typing}], max, lift, gap, size}. Text is typed; an item without text shows a typing shimmer.
export const SearchStack = ({E, t, o}) => {
  const f = faceOnCanvas(E, t);
  if (!f) return null;
  const C = E.style.colors, W = E.width, F = E.style.fonts;
  const items = (o.items || []).filter((it) => t >= it.at - 0.01);
  if (!items.length) return null;
  // bars without a query (just "someone searched something") are smaller and stack tighter
  const empty = items.every((it) => !it.text);
  const size = (o.size || 50) * (empty ? 0.78 : 1), ph = size * 2.05, gap = o.gap ?? (empty ? 14 : 22), max = o.max ?? 3;
  const ex = o.end != null ? tw(t, o.end - 0.35, 0.35, qIn) : 0;
  const baseY = Math.max(290 + (Math.min(max, items.length) - 1) * (ph + gap) * 0.9, f.y - f.h * (o.lift ?? 1.08));
  const cx = clamp(f.x + (o.dx ?? 0), W * 0.3, W * 0.7);
  // surface: 'glass' (dark frosted, default on dark styles) | 'ink' (solid dark, default on solid styles) | 'paper' (solid light)
  const T = theme(E);
  const surf = o.surface || (T.flat ? 'ink' : 'glass');
  const SF = surf === 'paper'
    ? {box: {background: T.paper, border: `3px solid ${T.ink}`, color: T.ink}, icon: T.accent2, shimmer: 'rgba(0,0,0,0.16)', round: !T.flat}
    : surf === 'ink'
      ? {box: {background: T.ink, color: T.paper, boxShadow: '0 18px 40px rgba(0,0,0,0.28)'}, icon: T.accent, shimmer: 'rgba(255,255,255,0.3)', round: !T.flat}
      : {box: {background: 'linear-gradient(180deg, rgba(255,255,255,0.12), rgba(255,255,255,0.03)), rgba(10,12,18,0.66)', border: '2px solid rgba(255,255,255,0.22)',
          boxShadow: 'inset 0 1.5px 0 rgba(255,255,255,0.22), 0 22px 50px rgba(0,0,0,0.38)', color: '#FFFFFF'}, icon: col(C, 'accent2', '#7CE7FF'), shimmer: 'rgba(255,255,255,0.22)', round: true};
  return (
    <AbsoluteFill style={{pointerEvents: 'none', opacity: 1 - ex}}>
      {items.map((it, i) => {
        // continuous rank: every newer item that has arrived pushes this one up one slot (eased)
        let r = 0;
        for (const nx of items.slice(i + 1)) r += tw(t, nx.at, 0.55, qInOut);
        if (r >= max + 0.999) return null;
        const p = tw(t, it.at, 0.65);
        const fade = clamp(max - r);
        const y = baseY - r * (ph + gap) - (1 - p) * -40 - ex * 60;
        const sc = (1 - 0.08 * r) * (0.9 + 0.1 * p);
        const txt = String(it.text || '');
        const typing = it.typing ?? Math.min(1.4, 0.05 * txt.length + 0.3);
        const n = Math.round(txt.length * clamp((t - it.at - 0.12) / typing));
        const bob = Math.sin(t * 1.6 + i * 1.3) * 4;
        const done = t > it.at + 0.12 + typing + 0.6;
        return (
          <div key={i} style={{position: 'absolute', left: cx, top: y + bob, transform: `translate(-50%, -50%) scale(${sc})`,
            opacity: clamp(p * 1.5) * fade * (1 - 0.25 * Math.min(1, r)), filter: blurF((1 - p) * 10 + r * 1.2)}}>
            <div style={{display: 'flex', alignItems: 'center', gap: size * 0.5, padding: `${size * 0.42}px ${size * 0.8}px`, ...SF.box,
              fontFamily: F.ui, fontWeight: 700, fontSize: size, lineHeight: 1.12, minWidth: size * 8, maxWidth: W - 120, width: 'max-content',
              borderRadius: SF.round ? size * 0.9 : size * 0.28}}>
              <Icon name="search" size={size * 0.95} color={SF.icon} stroke={2.8} />
              {txt ? (
                <span style={{textWrap: 'balance'}}>{txt.slice(0, n)}{!done ? <span style={{display: 'inline-block', width: 4, height: size * 1.05, marginLeft: 3, verticalAlign: 'middle',
                  background: SF.icon, opacity: 0.35 + 0.65 * (0.5 + 0.5 * Math.cos(t * 9))}} /> : null}</span>
              ) : (
                <span style={{display: 'flex', gap: size * 0.3}}>
                  {[0.9, 1.6, 1.1].map((w, j) => <span key={j} style={{width: size * w * 1.6, height: size * 0.42, borderRadius: SF.round ? size * 0.21 : size * 0.08,
                    background: SF.shimmer, opacity: 0.45 + 0.55 * (0.5 + 0.5 * Math.sin(t * 5 - j * 0.8))}} />)}
                </span>
              )}
            </div>
          </div>
        );
      })}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ Floaters
// {items:[{glyph | icon, at, x?, y?, size?, depth?}], around: 'head' | 'frame'}. depth 0 = far (small, sharp), 1 = near (big, soft)
const SLOTS = [[-1.05, -0.55], [1.0, -0.75], [-1.2, 0.35], [1.15, 0.25], [-0.55, -1.25], [0.6, -1.2], [-0.95, 0.95], [1.05, 1.0]];
export const Floaters = ({E, t, o}) => {
  const C = E.style.colors, W = E.width, H = E.height, F = E.style.fonts;
  const f = (o.around || 'head') === 'head' ? faceOnCanvas(E, t) : null;
  if ((o.around || 'head') === 'head' && !f) return null;
  const ex = o.end != null ? tw(t, o.end - 0.4, 0.4, qIn) : 0;
  const tone = col(C, o.tone, '#E9EDF5');
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {(o.items || []).map((it, i) => {
        if (t < it.at) return null;
        const p = tw(t, it.at, 0.8);
        const depth = it.depth ?? [0.2, 0.7, 0.45, 0.9, 0.1, 0.55, 0.3, 0.8][i % 8];
        const s = (it.size || 150) * (0.6 + 0.8 * depth);
        const [sx, sy] = SLOTS[(it.slot ?? i) % SLOTS.length];
        const hh = f ? Math.min(f.h, 400) : 400;
        const bx = clamp(it.x ?? (f ? f.x + sx * hh : W * (0.15 + 0.7 * rand(i * 3.1))), s * 0.7, W - s * 0.7);
        const by = clamp(it.y ?? (f ? f.y + sy * hh : H * (0.2 + 0.5 * rand(i * 7.7))), 300, H * 0.64);
        const drift = (t - it.at);
        const x = bx + Math.sin(drift * 0.7 + i) * 18 * (0.5 + depth), y = by + Math.cos(drift * 0.6 + i * 2) * 14 - drift * 10 * depth - (1 - p) * 60;
        const ry = Math.sin(drift * 0.9 + i) * 28, rz = Math.sin(drift * 0.5 + i * 1.7) * 10 + (1 - p) * -30;
        const dof = depth > 0.6 ? (depth - 0.6) * 12 : 0;
        const accent = it.accent ? col(C, it.accent, C.accent) : null;
        const face = accent || tone;
        return (
          <div key={i} style={{position: 'absolute', left: x, top: y, transform: `translate(-50%, -50%) perspective(700px) rotateY(${ry}deg) rotateZ(${rz}deg) scale(${0.4 + 0.6 * p})`,
            opacity: clamp(p * 1.6) * (1 - ex) * (0.65 + 0.35 * (1 - Math.abs(depth - 0.5))), filter: blurF(dof + (1 - p) * 8 + ex * 6), zIndex: Math.round(depth * 10)}}>
            {it.icon ? (
              <div style={{filter: Array.from({length: 6}, (_, j) => `drop-shadow(0 ${s * 0.012}px 0 ${shade('#9AA3B5', 1 - j * 0.08)})`).join(' ') + ` drop-shadow(0 ${s * 0.1}px ${s * 0.12}px rgba(0,0,0,0.45))`}}>
                <Icon name={it.icon} size={s} color={face} stroke={3.2} />
              </div>
            ) : (
              <div style={{fontFamily: F.display || F.head, fontWeight: 900, fontSize: s, lineHeight: 1, color: face, textShadow: accent ? extrude(accent, s * 0.09, 9, 0, 0.62) : extrude('#8C95A8', s * 0.09, 9)}}>
                {it.glyph || '?'}
              </div>
            )}
          </div>
        );
      })}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ Shout
// {lines:[{text, at?, size?}], at, tone: 'red'|'gold'|'ice'|'white', y, x, layer}. Slams in (scale 1.5 -> 1, blur), slowly pushes, exits fast.
export const Shout = ({E, t, o}) => {
  const C = E.style.colors, W = E.width, F = E.style.fonts;
  const tones = {red: '#C8203F', gold: '#C98A12', ice: '#2E7FA8', white: '#9AA3B5'};
  const ext = tones[o.tone || 'red'] || col(C, o.tone, tones.red);
  const at = o.at ?? o.start, y = o.y ?? 520, size = o.size || 190;
  const ex = o.end != null ? tw(t, o.end - 0.28, 0.28, qIn) : 0;
  const push = 1 + 0.04 * prog(t, at, o.end ?? at + 3);
  return (
    <div style={{position: 'absolute', left: (o.x ?? W / 2) - W / 2, width: W, top: y, display: 'flex', flexDirection: 'column', alignItems: 'center',
      transform: `scale(${push * (1 + 0.08 * ex)})`, opacity: 1 - ex, pointerEvents: 'none'}}>
      {(o.lines || [{text: o.text}]).map((ln, i) => {
        const a = ln.at ?? at + i * 0.09;
        if (t < a) return <div key={i} style={{height: (ln.size || size) * 0.98}} />;
        const sf = (k) => lerp(1.55, 1, eOut(prog(k, a, a + 0.42)));
        const sc = sf(t), v = Math.abs(vel(sf, t));
        const fs = ln.size || size;
        return (
          <div key={i} style={{fontFamily: F.display || F.head, fontWeight: 900, fontSize: fs, lineHeight: 0.98, letterSpacing: '-0.03em', color: ln.color || '#FFFFFF',
            textTransform: o.case === 'none' ? 'none' : 'uppercase', whiteSpace: 'nowrap', textShadow: extrude(ext, fs * 0.075, 10),
            transform: `scale(${sc})`, opacity: clamp(prog(t, a, a + 0.12) * 1.2), filter: blurF(Math.min(14, shutterBlur(v * fs * 2.2)))}}>
            {ln.text}
          </div>
        );
      })}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ StoryTitle
// {kicker, kickerAt, text, at, y, size, arc (true), color}. Display words rise out of a mask; a light arc draws across.
export const StoryTitle = ({E, t, o}) => {
  const C = E.style.colors, W = E.width, F = E.style.fonts;
  const at = o.at ?? o.start, kAt = o.kickerAt ?? o.start, y = o.y ?? 470, size = o.size || 200;
  const gold = col(C, 'accent', '#FFD23F');
  const ex = o.end != null ? tw(t, o.end - 0.35, 0.35, qIn) : 0;
  const words = String(o.text || '').split(/\s+/).filter(Boolean);
  const kp = tw(t, kAt, 0.9);
  const tw_ = Math.min(W - 80, String(o.text || '').length * size * 0.72);
  const arcP = qInOut(prog(t, at + 0.15, at + 1.05));
  const ax = W / 2 - tw_ * 0.62, ay = y + size * 0.62, bx = W / 2 + tw_ * 0.6, by = y + size * 0.05, mx = W / 2, my = y - size * 0.75;
  const path = `M${ax} ${ay} Q${mx} ${my} ${bx} ${by}`;
  const hx = (1 - arcP) ** 2 * ax + 2 * (1 - arcP) * arcP * mx + arcP ** 2 * bx, hy = (1 - arcP) ** 2 * ay + 2 * (1 - arcP) * arcP * my + arcP ** 2 * by;
  const lineFade = 1 - prog(t, at + 1.2, at + 2.2) * 0.7;
  return (
    <div style={{position: 'absolute', left: 0, top: 0, width: W, height: E.height, pointerEvents: 'none', opacity: 1 - ex, filter: blurF(ex * 10)}}>
      {o.kicker && t >= kAt ? (
        <div style={{position: 'absolute', left: 0, width: W, top: y - size * 0.62, textAlign: 'center', fontFamily: F.ui, fontWeight: 800, fontSize: o.kickerSize || 40,
          letterSpacing: `${lerp(0.6, 0.22, kp)}em`, color: gold, opacity: clamp(kp * 1.4), textTransform: 'uppercase'}}>{o.kicker}</div>
      ) : null}
      <div style={{position: 'absolute', left: 0, width: W, top: y, display: 'flex', justifyContent: 'center', columnGap: size * 0.2,
        fontFamily: F.display || F.head, fontWeight: 800, fontSize: size, lineHeight: 1, letterSpacing: '-0.035em', color: o.color || '#FFFFFF',
        textShadow: `0 0 ${size * 0.25}px rgba(255,214,120,0.35), 0 ${size * 0.06}px ${size * 0.2}px rgba(0,0,0,0.5)`}}>
        {words.map((w, i) => {
          const p = tw(t, at + i * 0.09, 0.8);
          return (
            <span key={i} style={{display: 'inline-block', overflow: 'hidden', padding: '0.06em 0.04em 0.14em', margin: '-0.06em -0.04em -0.14em'}}>
              <span style={{display: 'inline-block', transform: `translateY(${(1 - p) * 110}%)`, filter: blurF((1 - p) * 10), opacity: clamp(p * 1.8)}}>{w}</span>
            </span>
          );
        })}
      </div>
      {o.arc !== false && t >= at + 0.15 ? (
        <svg width={W} height={E.height} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
          <defs><filter id={`sg${Math.round(at * 100)}`} x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="9" /></filter></defs>
          <path d={path} fill="none" stroke="#FFF3D6" strokeWidth={14} strokeLinecap="round" pathLength={1} strokeDasharray={`${arcP} 1`} opacity={0.45 * lineFade}
            filter={`url(#sg${Math.round(at * 100)})`} />
          <path d={path} fill="none" stroke="#FFFBF0" strokeWidth={4.5} strokeLinecap="round" pathLength={1} strokeDasharray={`${arcP} 1`} opacity={0.95 * lineFade} />
          {arcP < 1 ? (
            <g>
              <circle cx={hx} cy={hy} r={46} fill={gold} opacity={0.35} filter={`url(#sg${Math.round(at * 100)})`} />
              <circle cx={hx} cy={hy} r={12} fill="#FFFFFF" />
              <rect x={hx - 140} y={hy - 2} width={280} height={4} rx={2} fill="#FFFFFF" opacity={0.6} />
            </g>
          ) : null}
        </svg>
      ) : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ SceneLens
// {words:[text | {text, x, y, size}], focus:[{word, at}], tag?, accent words light up in the lens}
// A dark full-screen beat: spoken words drift in depth; a magnifier glides to each focus word on its cue.
export const SceneLens = ({E, t, o}) => {
  const C = E.style.colors, W = E.width, H = E.height, F = E.style.fonts;
  const T = theme(E), solid = o.surface ? o.surface === 'solid' : T.flat;
  const gold = solid ? T.accent : col(C, 'accent', '#FFD23F');
  const ink = solid ? T.ink : '#FFFFFF', ring = solid ? T.ink : '#E6EBF2';
  const marker = solid && lumHex(T.accent) > 0.45;   // a light accent (IMPACT lime) can't be text on paper: use it as a marker
  const st = o.start, en = o.end;
  const env = tw(t, st, 0.6) * (en != null ? 1 - tw(t, en - 0.3, 0.3, qIn) : 1);
  const words = (o.words || []).map((w, i) => {
    const d = typeof w === 'string' ? {text: w} : w;
    return {
      text: d.text,
      x: d.x ?? W * (0.12 + 0.76 * rand(i * 4.13 + 1)),
      y: d.y ?? (380 + (i % 7) * 115 + rand(i * 9.7) * 60),
      size: d.size ?? (34 + Math.round(rand(i * 2.9) * 36)),
    };
  });
  const focus = (o.focus || []).map((fo) => ({...fo, w: words.find((w) => w.text.toLowerCase() === String(fo.word).toLowerCase())})).filter((fo) => fo.w);
  // lens path: glide between focus words (eased), idle drift before the first cue
  let lx = W * 0.5, ly = H * 0.42;
  if (focus.length) {
    lx = focus[0].w.x; ly = focus[0].w.y;
    for (let i = 1; i < focus.length; i++) {
      const p = qInOut(prog(t, focus[i].at - 0.35, focus[i].at + 0.2));
      lx = lerp(lx, focus[i].w.x, p); ly = lerp(ly, focus[i].w.y, p);
    }
    const pre = 1 - qInOut(prog(t, focus[0].at - 0.6, focus[0].at));
    lx += pre * 220; ly += pre * 160;
  }
  lx += Math.sin(t * 0.9) * 6; ly += Math.cos(t * 0.8) * 5;
  const R = o.radius || 190, zoom = o.zoom || 1.75;
  const active = focus.filter((fo) => t >= fo.at - 0.1).map((fo) => fo.w.text);
  const cloud = (inLens) => words.map((w, i) => {
    const dx = Math.sin(t * 0.25 + i * 1.3) * 10, dy = Math.cos(t * 0.21 + i) * 8;
    const lit = inLens && active.includes(w.text);
    return (
      <div key={i} style={{position: 'absolute', left: w.x + dx, top: w.y + dy, transform: 'translate(-50%, -50%)', whiteSpace: 'nowrap',
        fontFamily: F.ui, fontWeight: 800, fontSize: w.size, letterSpacing: '-0.01em',
        color: lit ? (marker ? T.ink : gold) : inLens ? (solid ? ink : 'rgba(255,255,255,0.92)') : (solid ? 'rgba(18,18,18,0.16)' : 'rgba(255,255,255,0.17)'),
        background: lit && marker ? T.accent : 'transparent', padding: lit && marker ? '0 0.14em' : 0, borderRadius: 6,
        filter: inLens ? 'none' : 'blur(1.2px)'}}>{w.text}</div>
    );
  });
  return (
    <AbsoluteFill style={{opacity: env, background: o.bg || (solid ? (C.bg || '#F1EEE7') : '#06070B')}}>
      {solid ? null : <AbsoluteFill style={{background: 'radial-gradient(ellipse 80% 60% at 50% 42%, rgba(60,80,120,0.22), rgba(0,0,0,0) 70%)'}} />}
      <AbsoluteFill style={{transform: `scale(${1 + 0.04 * prog(t, st, en ?? st + 4)})`}}>{cloud(false)}</AbsoluteFill>
      <AbsoluteFill style={{clipPath: `circle(${R}px at ${lx}px ${ly}px)`}}>
        <AbsoluteFill style={{background: solid ? (C.paper || '#FFFFFF') : 'rgba(255,255,255,0.05)'}} />
        <AbsoluteFill style={{transform: `scale(${zoom})`, transformOrigin: `${lx}px ${ly}px`}}>{cloud(true)}</AbsoluteFill>
      </AbsoluteFill>
      <svg width={W} height={H} style={{position: 'absolute', inset: 0}}>
        <defs><radialGradient id="lensSheen" cx="0.35" cy="0.3" r="0.7"><stop offset="0" stopColor="#FFFFFF" stopOpacity="0.16" /><stop offset="0.6" stopColor="#FFFFFF" stopOpacity="0" /></radialGradient></defs>
        {solid ? null : <circle cx={lx} cy={ly} r={R} fill="url(#lensSheen)" />}
        <circle cx={lx} cy={ly} r={R + 7} fill="none" stroke={ring} strokeWidth={14} />
        {solid ? null : <circle cx={lx} cy={ly} r={R + 15} fill="none" stroke="rgba(0,0,0,0.5)" strokeWidth={3} />}
        <line x1={lx + (R + 12) * 0.72} y1={ly + (R + 12) * 0.72} x2={lx + (R + 160) * 0.72} y2={ly + (R + 160) * 0.72} stroke={ring} strokeWidth={30} strokeLinecap={solid ? 'square' : 'round'} />
      </svg>
      {o.tag ? <div style={{position: 'absolute', left: 0, width: W, top: 300, textAlign: 'center', fontFamily: F.ui, fontWeight: 800, fontSize: 36, letterSpacing: '0.24em',
        color: solid ? ink : gold, textTransform: 'uppercase', opacity: tw(t, st + 0.2, 0.8)}}>{o.tag}</div> : null}
    </AbsoluteFill>
  );
};
