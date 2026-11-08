// PREMIUM PACK (v3.9): the polished pieces of high-end After-Effects Reels, shared by every style and the identity of
// LUMEN (luminous, cinematic) and POSTER (designed colour fields).
//
//   Glossy       glossy 3D objects that spin and float with depth of field: coin (with a symbol), check, cross, star,
//                heart, bolt, up, down, plus - each a lit disc / shape with a rim, a specular highlight and a soft glow
//   GlowPill     a glass (or solid) pill with spoken words and a round badge (check / cross / star / bolt / arrow) that
//                pops in with a glint ring - "langsung jadi ✓"
//   BadgeOrbit   round white badges with icons or kit objects arranged on an arc around the head, popping in one by one
//   SideCards    tilted rounded cards that fly in beside the head: the user's own photos, the speaker's own frames, or a
//                kit object on a gradient, with a label chip
//   Checklist    a list that builds over a blurred, tinted copy of the frame: header pill + rows whose check badge pops on
//                the spoken word
//
// Rules: spoken words only (or user copy), generic shapes (no real logos / app UI), pure functions of t, eased motion.
import React from 'react';
import {AbsoluteFill, Img, staticFile} from 'remotion';
import {bezier, clamp, dirBlur, lerp, prog, rand, shutterBlur} from '../lib/anim.js';
import {faceOnCanvas} from './Footage.jsx';
import {Icon, ICONS} from './Icons.jsx';
import {THINGS} from './Things.jsx';
import {lumHex, theme} from './Scenes.jsx';
import {gradOf, toneColor} from './Depth.jsx';

const qOut = bezier(0.22, 1, 0.36, 1);
const qIn = bezier(0.64, 0, 0.78, 0);
const qInOut = bezier(0.83, 0, 0.17, 1);
const backOut = bezier(0.34, 1.45, 0.64, 1);
const tw = (t, a, d, f = qOut) => f(prog(t, a, a + d));
const blurF = (b) => (b > 0.4 ? `blur(${b.toFixed(1)}px)` : '');
const joinF = (...xs) => xs.filter((x) => x && x !== 'none').join(' ') || 'none';
const hexA = (hex, a) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || '')); if (!m) return hex;
  const n = parseInt(m[1], 16); return `rgba(${(n >> 16) & 255},${(n >> 8) & 255},${n & 255},${a})`;
};
const shade = (hex, k) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || '')); if (!m) return hex;
  const n = parseInt(m[1], 16); const f = (v) => Math.max(0, Math.min(255, Math.round(v * k)));
  return `rgb(${f((n >> 16) & 255)},${f((n >> 8) & 255)},${f(n & 255)})`;
};

// positions around the face (multiples of the face height), the same vocabulary as the scene kit
const SLOTS = {
  'above': [0, -1.45], 'above-left': [-1.0, -1.15], 'above-right': [1.0, -1.15], 'left': [-1.15, -0.15], 'right': [1.15, -0.15],
  'chest': [0, 1.35], 'chest-left': [-0.9, 1.2], 'chest-right': [0.9, 1.2], 'shoulder-left': [-1.05, 0.65], 'shoulder-right': [1.05, 0.65],
};
export const PREMIUM_SLOTS = Object.keys(SLOTS);
const anchor = (E, t, it, half = 120) => {
  const W = E.width, H = E.height;
  if (it.slot) {
    const f = faceOnCanvas(E, t);
    if (!f) return null;
    const [sx, sy] = SLOTS[it.slot] || SLOTS.above;
    const hh = Math.min(f.h, 520);
    return [clamp(f.x + sx * hh + (it.dx || 0), half, W - half), clamp(f.y + sy * hh + (it.dy || 0), 280, H - 300)];
  }
  return [it.x ?? W / 2, it.y ?? H * 0.4];
};

// ------------------------------------------------------------------------------------------------ Glossy
const PAL = {
  coin: ['#FFF6CF', '#F7C948', '#B7791F', '#7A4E0E'], check: ['#D9FFE9', '#3DDC84', '#119A55', '#0A5C33'],
  cross: ['#FFE0E0', '#FF5A5A', '#C81E2C', '#7A0E17'], star: ['#FFF6CF', '#FFCF3F', '#D9931A', '#7A4E0E'],
  heart: ['#FFE3EE', '#FF5C8A', '#D61F55', '#7A0E2E'], bolt: ['#FFFBD6', '#FFE14D', '#E0A800', '#6B5000'],
  up: ['#D9FFE9', '#3DDC84', '#119A55', '#0A5C33'], down: ['#FFE0E0', '#FF5A5A', '#C81E2C', '#7A0E17'], plus: ['#E5EEFF', '#5B8CFF', '#2F55D4', '#14286B'],
};
const GLYPH = {
  check: 'M30 52 L44 66 L72 36', cross: 'M34 34 L66 66 M66 34 L34 66', up: 'M50 72 L50 30 M32 46 L50 28 L68 46',
  down: 'M50 28 L50 70 M32 54 L50 72 L68 54', plus: 'M50 30 L50 70 M30 50 L70 50', bolt: 'M54 24 L34 54 L50 54 L44 76 L66 44 L50 44 Z',
};
const STAR = Array.from({length: 10}, (_, i) => { const a = -Math.PI / 2 + (i * Math.PI) / 5, r = i % 2 ? 19 : 42; return `${(50 + Math.cos(a) * r).toFixed(1)},${(52 + Math.sin(a) * r).toFixed(1)}`; }).join(' ');
const HEART = 'M50 82 C20 62 14 44 22 32 C30 20 46 22 50 36 C54 22 70 20 78 32 C86 44 80 62 50 82 Z';

// one glossy object, viewBox 100x100. spin = rotation around the vertical axis (radians): width follows cos, the rim
// (thickness) shows on the side turned away, the highlight slides with the angle.
const GlossyShape = ({kind, spin, id, symbol, F}) => {
  const P = PAL[kind] || PAL.coin;
  const c = Math.cos(spin), s = Math.sin(spin);
  const sx = Math.max(0.1, Math.abs(c));
  const rimDx = -Math.sign(c || 1) * s * 7;          // thickness offset
  const hl = 30 + 25 * s;                             // highlight x
  const disc = kind === 'coin' || GLYPH[kind];
  const shapeOf = (fill, stroke) => (kind === 'star' ? <polygon points={STAR} fill={fill} stroke={stroke} strokeWidth={2} strokeLinejoin="round" />
    : kind === 'heart' ? <path d={HEART} fill={fill} stroke={stroke} strokeWidth={2} /> : <circle cx={50} cy={50} r={44} fill={fill} stroke={stroke} strokeWidth={2} />);
  return (
    <svg viewBox="-10 -10 120 120" width="100%" height="100%" style={{overflow: 'visible'}}>
      <defs>
        <radialGradient id={`gf${id}`} cx={`${hl}%`} cy="28%" r="80%">
          <stop offset="0" stopColor={P[0]} /><stop offset="0.45" stopColor={P[1]} /><stop offset="1" stopColor={P[2]} />
        </radialGradient>
        <linearGradient id={`gs${id}`} x1="0" x2="1" y1="0" y2="1">
          <stop offset="0" stopColor="#FFFFFF" stopOpacity="0" /><stop offset="0.5" stopColor="#FFFFFF" stopOpacity="0.75" /><stop offset="1" stopColor="#FFFFFF" stopOpacity="0" />
        </linearGradient>
        <clipPath id={`gc${id}`}>{kind === 'star' ? <polygon points={STAR} /> : kind === 'heart' ? <path d={HEART} /> : <circle cx={50} cy={50} r={44} />}</clipPath>
      </defs>
      {/* rim / thickness */}
      <g transform={`translate(${50 + rimDx} 50) scale(${sx} 1) translate(-50 -50)`}>{shapeOf(P[3], P[3])}</g>
      {/* face */}
      <g transform={`translate(50 50) scale(${sx} 1) translate(-50 -50)`}>
        {shapeOf(`url(#gf${id})`, P[2])}
        {disc ? <circle cx={50} cy={50} r={35} fill="none" stroke={P[2]} strokeWidth={3} opacity={0.75} /> : null}
        {kind === 'coin' && symbol ? (
          <text x={50} y={51} textAnchor="middle" dominantBaseline="central" fontFamily={F} fontWeight={900} fontSize={symbol && symbol.length > 2 ? 22 : 30}
            fill={P[2]} stroke={P[0]} strokeWidth={0.8} paintOrder="stroke">{symbol}</text>
        ) : null}
        {kind === 'coin' && !symbol ? <polygon points={STAR} fill={P[2]} transform="translate(50 50) scale(0.48) translate(-50 -52)" /> : null}
        {kind !== 'coin' && GLYPH[kind] ? (
          <path d={GLYPH[kind]} fill={kind === 'bolt' ? '#FFFFFF' : 'none'} stroke="#FFFFFF" strokeWidth={kind === 'bolt' ? 2 : 10} strokeLinecap="round" strokeLinejoin="round" />
        ) : null}
        <g clipPath={`url(#gc${id})`}>
          <rect x={-40 + 70 * ((s + 1) / 2)} y={-20} width={26} height={150} fill={`url(#gs${id})`} transform="rotate(22 50 50)" opacity={0.85} />
        </g>
      </g>
    </svg>
  );
};

// {items:[{kind, at, slot|x,y, size (110), depth (0-1), spin (turns/s), symbol, dx, dy}], glow}
export const Glossy = ({E, t, o}) => {
  const F = (E.style.fonts || {}).display || 'Outfit';
  const ex = o.end != null ? tw(t, o.end - 0.3, 0.3, qIn) : 0;
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {(o.items || []).map((it, i) => {
        const at = it.at ?? o.start + i * 0.15;
        if (t < at) return null;
        const pos = anchor(E, t, it, (it.size || 110) * 0.7);
        if (!pos) return null;
        const depth = it.depth ?? [0.3, 0.7, 0.5, 0.15, 0.85][i % 5];
        const size = (it.size || 110) * (0.75 + 0.5 * depth);
        const pIn = (k) => backOut(prog(k, at, at + 0.55));
        const p = pIn(t), v = Math.abs(pIn(t + 1 / 120) - pIn(t - 1 / 120)) * 60;
        const life = t - at;
        const spin = (it.spin ?? 0.55) * life * Math.PI * 2 + (1 - Math.min(1, p)) * 3 + i;
        const bob = Math.sin(life * 1.4 + i * 1.7) * 10 * (0.5 + depth), sway = Math.sin(life * 0.9 + i) * 6;
        const exitK = Math.max(ex, it.until != null ? tw(t, it.until - 0.28, 0.28, qIn) : 0);
        const dof = depth > 0.72 ? (depth - 0.72) * 14 : depth < 0.12 ? (0.12 - depth) * 20 : 0;
        const glowCol = (PAL[it.kind] || PAL.coin)[1];
        return (
          <div key={i} style={{position: 'absolute', left: pos[0] + sway, top: pos[1] + bob - (1 - Math.min(1, p)) * 50, width: size, height: size,
            transform: `translate(-50%, -50%) scale(${(Math.max(0, p) * (1 - 0.6 * exitK)).toFixed(3)}) rotate(${(Math.sin(life * 0.8 + i) * 8 + (1 - Math.min(1, p)) * -25).toFixed(1)}deg)`,
            opacity: clamp(p * 2) * (1 - exitK), zIndex: Math.round(depth * 10),
            filter: joinF(blurF(dof + shutterBlur(v * size) * 0.4), o.glow === false ? '' : `drop-shadow(0 ${size * 0.08}px ${size * 0.12}px rgba(0,0,0,0.35)) drop-shadow(0 0 ${size * 0.18}px ${hexA(glowCol, 0.45)})`)}}>
            <GlossyShape kind={it.kind || 'coin'} spin={spin} id={`${o.start}_${i}`.replace(/\./g, '_')} symbol={it.symbol} F={F} />
          </div>
        );
      })}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ GlowPill
// {text, sub?, at, badge (check|cross|star|bolt|up|down|plus|none), badgeAt, side (right|left), slot|x,y, size (44),
//  surface (glass|ink|paper|accent), tone (badge colour kind)}
export const GlowPill = ({E, t, o}) => {
  const C = E.style.colors, FN = E.style.fonts || {};
  const at = o.at ?? o.start;
  const pos = anchor(E, t, o, 300);
  if (!pos) return null;
  const size = o.size || 44;
  const pf = (k) => qOut(prog(k, at, at + 0.55));
  const p = pf(t), v = Math.abs(pf(t + 1 / 120) - pf(t - 1 / 120)) * 60 * 60;
  const ex = o.end != null ? tw(t, o.end - 0.26, 0.26, qIn) : 0;
  const surf = o.surface || 'glass';
  const box = surf === 'paper' ? {background: '#FFFFFF', color: C.ink || '#111', boxShadow: '0 18px 40px rgba(0,0,0,0.25)'}
    : surf === 'ink' ? {background: C.ink || '#111', color: '#FFFFFF', boxShadow: '0 18px 40px rgba(0,0,0,0.3)'}
      : surf === 'accent' ? {background: C.accent, color: lumHex(C.accent) > 0.45 ? (C.ink || '#111') : '#FFFFFF', boxShadow: `0 16px 40px ${hexA(C.accent, 0.45)}`}
        : {background: 'linear-gradient(180deg, rgba(255,255,255,0.16), rgba(255,255,255,0.05)), rgba(18,14,10,0.42)', color: '#FFFFFF',
          border: '1.5px solid rgba(255,255,255,0.28)', boxShadow: 'inset 0 1.5px 0 rgba(255,255,255,0.3), 0 20px 46px rgba(0,0,0,0.35)',
          backdropFilter: 'blur(14px)', WebkitBackdropFilter: 'blur(14px)'};
  const kind = o.badge ?? 'check';
  const bAt = o.badgeAt ?? at + 0.3;
  const bp = backOut(prog(t, bAt, bAt + 0.42));
  const ring = prog(t, bAt + 0.08, bAt + 0.6);
  const P = PAL[o.tone || kind] || PAL.check;
  const badge = kind === 'none' ? null : (
    <div style={{position: 'relative', width: size * 1.25, height: size * 1.25, flex: 'none', transform: `scale(${Math.max(0, bp).toFixed(3)})`}}>
      <div style={{position: 'absolute', inset: 0, borderRadius: '50%', background: `radial-gradient(circle at 35% 30%, ${P[0]}, ${P[1]} 55%, ${P[2]})`,
        boxShadow: `0 0 ${size * 0.5}px ${hexA(P[1], 0.55)}, inset 0 -3px 6px rgba(0,0,0,0.2)`}} />
      {GLYPH[kind] ? <svg viewBox="0 0 100 100" style={{position: 'absolute', inset: '12%'}}><path d={GLYPH[kind]} fill={kind === 'bolt' ? '#fff' : 'none'} stroke="#fff" strokeWidth={kind === 'bolt' ? 2 : 12} strokeLinecap="round" strokeLinejoin="round" /></svg>
        : kind === 'star' ? <svg viewBox="0 0 100 100" style={{position: 'absolute', inset: '14%'}}><polygon points={STAR} fill="#fff" /></svg> : null}
      {ring > 0 && ring < 1 ? <div style={{position: 'absolute', inset: -size * 0.5 * ring, borderRadius: '50%', border: `${(3 * (1 - ring)).toFixed(1)}px solid ${P[0]}`, opacity: 1 - ring}} /> : null}
    </div>
  );
  return (
    <div style={{position: 'absolute', left: pos[0], top: pos[1], transform: `translate(-50%, -50%) translateY(${((1 - p) * 46 + ex * 24).toFixed(1)}px) scale(${(0.94 + 0.06 * p).toFixed(3)})`,
      opacity: clamp(p * 1.6) * (1 - ex), filter: joinF(blurF((1 - p) * 10), dirBlur(0, Math.min(18, shutterBlur(v * 0.8))))}}>
      <div style={{display: 'flex', alignItems: 'center', gap: size * 0.45, flexDirection: o.side === 'left' ? 'row-reverse' : 'row',
        padding: `${size * 0.38}px ${size * 0.42}px ${size * 0.38}px ${size * 0.7}px`, borderRadius: size * 1.2, ...box}}>
        <div style={{display: 'flex', flexDirection: 'column', gap: size * 0.08}}>
          <div style={{fontFamily: FN.ui || FN.head, fontWeight: 800, fontSize: size, lineHeight: 1.05, letterSpacing: '-0.01em', whiteSpace: 'nowrap'}}>{o.text}</div>
          {o.sub ? <div style={{fontFamily: FN.ui || FN.head, fontWeight: 600, fontSize: size * 0.56, opacity: clamp((t - (o.subAt ?? at + 0.35)) * 4) * 0.72, whiteSpace: 'nowrap'}}>{o.sub}</div> : null}
        </div>
        {badge}
      </div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ BadgeOrbit
// {items:[{icon | thing, at, tone}], radius (0.95 x face height), from/to (degrees, 200 -> 340 = over the head), size (84), surface}
export const BadgeOrbit = ({E, t, o}) => {
  const f = faceOnCanvas(E, t);
  if (!f) return null;
  const C = E.style.colors;
  const items = o.items || [];
  const n = Math.max(1, items.length);
  const hh = Math.min(f.h, 520);
  const R = hh * (o.radius ?? 0.98), Ry = R * (o.squash ?? 0.92);
  const cx = f.x + (o.dx ?? 0), cy = f.y - hh * (o.lift ?? 0.12) + (o.dy ?? 0);
  const a0 = (o.from ?? 195), a1 = (o.to ?? 345);
  const drift = Math.sin((t - o.start) * 0.6) * (o.sway ?? 4);
  const ex = o.end != null ? tw(t, o.end - 0.3, 0.3, qIn) : 0;
  const size = o.size || 84;
  const K = theme(E);
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {items.map((it, i) => {
        const at = it.at ?? o.start + i * 0.12;
        if (t < at) return null;
        const ang = ((n === 1 ? (a0 + a1) / 2 : lerp(a0, a1, i / (n - 1))) + drift) * Math.PI / 180;
        const p = backOut(prog(t, at, at + 0.45));
        const x = cx + Math.cos(ang) * R * (0.86 + 0.14 * Math.min(1, p)), y = cy + Math.sin(ang) * Ry * (0.86 + 0.14 * Math.min(1, p)) + Math.sin((t - at) * 1.8 + i) * 5;
        const col = toneColor(E, it.tone || o.tone || 'accent');
        const exK = Math.max(ex, it.until != null ? tw(t, it.until - 0.25, 0.25, qIn) : 0);
        const surf = o.surface === 'glass' ? {background: 'rgba(255,255,255,0.16)', border: '1.5px solid rgba(255,255,255,0.35)', backdropFilter: 'blur(10px)', WebkitBackdropFilter: 'blur(10px)'}
          : o.surface === 'ink' ? {background: C.ink || '#111'} : {background: '#FFFFFF'};
        const T = it.thing && THINGS[it.thing];
        return (
          <div key={i} style={{position: 'absolute', left: x, top: y, width: size, height: size, borderRadius: '50%', ...surf,
            transform: `translate(-50%, -50%) scale(${(Math.max(0, p) * (1 - 0.5 * exK)).toFixed(3)}) rotate(${((1 - Math.min(1, p)) * -40).toFixed(1)}deg)`,
            opacity: clamp(p * 2) * (1 - exK), boxShadow: '0 10px 24px rgba(0,0,0,0.28)', display: 'flex', alignItems: 'center', justifyContent: 'center'}}>
            {T ? (
              <svg viewBox="0 0 120 120" width={size * 0.72} height={size * 0.72}>{T({c1: col, c2: '#FFFFFF', c3: col, ln: K.flat ? (C.ink || '#111') : '#1A1A1A', w: 4, t: t - at, it})}</svg>
            ) : <Icon name={ICONS.includes(it.icon) ? it.icon : 'sparkle'} size={size * 0.5} color={col} stroke={2.8} />}
          </div>
        );
      })}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ SideCards
// {items:[{file (set by the planner from src/frame) | thing, label, side: left|right, at, until, tilt, dy, tone}], w (290), h (360), frame (white border)}
export const SideCards = ({E, t, o}) => {
  const f = faceOnCanvas(E, t);
  if (!f) return null;
  const W = E.width, C = E.style.colors, FN = E.style.fonts || {};
  const cw = o.w || 290, ch = o.h || 360;
  const ex = o.end != null ? tw(t, o.end - 0.32, 0.32, qIn) : 0;
  const K = theme(E);
  const hh = Math.min(f.h, 520);
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {(o.items || []).map((it, i) => {
        const at = it.at ?? o.start + i * 0.2;
        if (t < at) return null;
        const side = it.side || (i % 2 ? 'right' : 'left');
        const s = side === 'left' ? -1 : 1;
        const gap = hh * (o.spread ?? 0.62) + cw / 2;
        const bx = clamp(f.x + s * gap, cw * 0.42, W - cw * 0.42), by = f.y + (it.dy ?? hh * 0.15);
        const pf = (k) => qOut(prog(k, at, at + 0.62));
        const p = pf(t), v = Math.abs(pf(t + 1 / 120) - pf(t - 1 / 120)) * 60 * 520;
        const exK = Math.max(ex, it.until != null ? tw(t, it.until - 0.32, 0.32, qIn) : 0);
        const tilt = it.tilt ?? s * 8;
        const fly = (1 - p) * 520 * s + exK * 600 * s;
        const rot = tilt + (1 - p) * s * 24 + exK * s * 18;
        const bob = Math.sin((t - at) * 1.3 + i) * 7;
        const g = gradOf(E, it.grad || (i % 2 ? 'ice' : 'fire'));
        const T = it.thing && THINGS[it.thing];
        return (
          <div key={i} style={{position: 'absolute', left: bx + fly, top: by + bob, width: cw, height: ch, transform: `translate(-50%, -50%) rotate(${rot.toFixed(2)}deg)`,
            opacity: clamp(p * 2) * (1 - exK), filter: dirBlur(Math.min(24, shutterBlur(v)), 0)}}>
            <div style={{position: 'absolute', inset: 0, borderRadius: cw * 0.09, overflow: 'hidden', background: it.file ? '#111' : `linear-gradient(160deg, ${g[0]}, ${g[1]} 55%, ${g[2]})`,
              boxShadow: `0 0 0 ${o.frame === false ? 0 : 7}px #FFFFFF, 0 28px 50px rgba(0,0,0,0.38)`}}>
              {it.file ? <Img src={staticFile(it.file)} style={{width: '100%', height: '100%', objectFit: 'cover'}} />
                : T ? <svg viewBox="0 0 120 120" style={{position: 'absolute', left: '14%', top: '12%', width: '72%', height: '72%'}}>{T({c1: '#FFFFFF', c2: g[2], c3: '#FFFFFF', ln: shade(g[2], 0.5), w: 4, t: t - at, it})}</svg> : null}
            </div>
            {it.label ? (
              <div style={{position: 'absolute', left: '50%', bottom: -26, transform: `translateX(-50%) rotate(${(-tilt * 0.4).toFixed(1)}deg)`, padding: '10px 20px', borderRadius: 999,
                background: K.flat ? (C.ink || '#111') : 'rgba(14,12,10,0.78)', color: '#FFFFFF', fontFamily: FN.ui || FN.head, fontWeight: 800, fontSize: 32, whiteSpace: 'nowrap',
                boxShadow: '0 10px 24px rgba(0,0,0,0.3)', opacity: clamp((t - at - 0.35) * 4)}}>{it.label}</div>
            ) : null}
          </div>
        );
      })}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ Checklist
// {title?, titleAt?, items:[{text, at}], y (420), w (780), size (40), tint (colour name), blur (22), dim (0.25), backdrop (true)}
export const Checklist = ({E, t, o}) => {
  const W = E.width, C = E.style.colors, FN = E.style.fonts || {};
  const k = tw(t, o.start, 0.35, qInOut) * (o.end != null ? 1 - tw(t, o.end - 0.3, 0.3, qInOut) : 1);
  if (k <= 0.002) return null;
  const tint = toneColor(E, o.tint || 'accent');
  const size = o.size || 40, w = o.w || 780, rowH = size * 2.2, y0 = o.y ?? 420;
  const bl = (o.blur ?? 22) * k;
  const exT = o.end != null ? tw(t, o.end - 0.3, 0.3, qIn) : 0;
  const ink = C.ink || '#111';
  const P = PAL[o.badge || 'check'];
  const titleAt = o.titleAt ?? o.start + 0.1;
  const tp = tw(t, titleAt, 0.5);
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {o.backdrop !== false ? (
        <AbsoluteFill style={{backdropFilter: `blur(${bl.toFixed(1)}px)`, WebkitBackdropFilter: `blur(${bl.toFixed(1)}px)`,
          background: `linear-gradient(180deg, ${hexA(tint, 0.42 * k)}, ${hexA(tint, 0.3 * k)}), rgba(0,0,0,${((o.dim ?? 0.18) * k).toFixed(3)})`}} />
      ) : null}
      {o.title && t >= titleAt ? (
        <div style={{position: 'absolute', left: W / 2, top: y0 - size * 2.1, transform: `translate(-50%, ${((1 - tp) * -24).toFixed(1)}px) scaleX(${(0.6 + 0.4 * tp).toFixed(3)})`,
          padding: `${size * 0.32}px ${size * 0.8}px`, background: tint, color: lumHex(tint) > 0.5 ? ink : '#FFFFFF', borderRadius: size * 0.22,
          fontFamily: FN.head, fontWeight: 900, fontSize: size * 0.9, letterSpacing: '0.04em', textTransform: 'uppercase', whiteSpace: 'nowrap',
          opacity: clamp(tp * 1.8) * (1 - exT), boxShadow: `0 14px 30px ${hexA(tint, 0.4)}`}}>{o.title}</div>
      ) : null}
      {(o.items || []).map((it, i) => {
        const at = it.at ?? o.start + 0.3 + i * 0.5;
        if (t < at - 0.25) return null;
        const p = tw(t, at - 0.25, 0.55);
        const cp = backOut(prog(t, at, at + 0.4));
        const exI = o.end != null ? tw(t, o.end - 0.3 + i * 0.04, 0.26, qIn) : 0;
        return (
          <div key={i} style={{position: 'absolute', left: W / 2 - w / 2, top: y0 + i * (rowH + size * 0.45), width: w, height: rowH, borderRadius: rowH * 0.24,
            background: '#FFFFFF', boxShadow: '0 16px 34px rgba(0,0,0,0.22)', display: 'flex', alignItems: 'center', gap: size * 0.55, padding: `0 ${size * 0.6}px`,
            transform: `translateX(${((1 - p) * -90 + exI * 120).toFixed(1)}px)`, opacity: clamp(p * 1.8) * (1 - exI), filter: blurF((1 - p) * 8)}}>
            <div style={{width: size * 1.2, height: size * 1.2, borderRadius: '50%', flex: 'none', transform: `scale(${Math.max(0, cp).toFixed(3)})`,
              background: `radial-gradient(circle at 35% 30%, ${P[0]}, ${P[1]} 55%, ${P[2]})`, display: 'flex', alignItems: 'center', justifyContent: 'center',
              boxShadow: `0 0 ${size * 0.4}px ${hexA(P[1], 0.45)}`}}>
              <svg viewBox="0 0 100 100" width={size * 0.8} height={size * 0.8}><path d={GLYPH.check} fill="none" stroke="#FFFFFF" strokeWidth={13} strokeLinecap="round" strokeLinejoin="round" /></svg>
            </div>
            <div style={{fontFamily: FN.ui || FN.head, fontWeight: 800, fontSize: size, color: ink, whiteSpace: 'nowrap', letterSpacing: '-0.01em'}}>{it.text}</div>
          </div>
        );
      })}
    </AbsoluteFill>
  );
};

export const GLOSSY_KINDS = Object.keys(PAL);
