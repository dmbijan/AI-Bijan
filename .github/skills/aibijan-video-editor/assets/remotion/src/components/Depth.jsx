// DEPTH PACK (v3.7): the After-Effects "premium Reels" layer tricks, built on the person matte and the face track.
//
//   DepthText  big type BEHIND the speaker, four modes:
//                stack  lines rise out of masks, track in, then creep (one or two huge words)
//                echo   one word slams in, outlined echoes fan out above and below it
//                wall   rows of a repeated phrase, marquee in alternating directions (optionally drifting up forever)
//                split  two words part from behind the head to the left and right edges
//              looks per line/row: fill · outline · chrome · gradient · extrude · glow
//   HeadRing   a ring of spoken words orbiting the head in 3D: the back half passes BEHIND the head (layer behind),
//              the front half in front of it (the planner splits one HeadRing into both halves)
//   Doodles    hand-drawn marker doodles that draw on around the head and "boil" at 12 fps like real hand animation:
//              sparks · swirl · question · exclaim · halo · circle · shock · crown · bulb · stars · arrow · scribble · drops · heart
//   FeedWall   an endless tilted wall of vertical video cards scrolling up behind the speaker (the user's own frames or
//              plain colour cards), swipes snap one card per spoken cue, speed ramps up
//
// Rules: every text is spoken (or user copy) · nothing imitates a real app's UI · everything is a pure function of t.
import React from 'react';
import {AbsoluteFill, Img, staticFile} from 'remotion';
import {bezier, clamp, dirBlur, lerp, prog, rand, shutterBlur} from '../lib/anim.js';
import {faceOnCanvas, matteSrc} from './Footage.jsx';
import {dispFont, lumHex, theme} from './Scenes.jsx';

const qOut = bezier(0.22, 1, 0.36, 1);      // entrances: fast start, long silky settle
const qIn = bezier(0.64, 0, 0.78, 0);       // exits accelerate away
const qInOut = bezier(0.83, 0, 0.17, 1);    // snaps between two states (swipes)
const eOut = (p) => (p >= 1 ? 1 : 1 - Math.pow(2, -10 * p));
const tw = (t, a, d, f = qOut) => f(prog(t, a, a + d));
const blurF = (b) => (b > 0.4 ? `blur(${b.toFixed(1)}px)` : '');
const joinF = (...xs) => xs.filter((x) => x && x !== 'none').join(' ') || 'none';

// ------------------------------------------------------------------------------------------------ helpers
let _cv = null;
const _mw = new Map();
// Average advance per character (em) of the bundled faces, measured in Chromium: [UPPER, lower, digit, space].
// The canvas measurement is used only when it agrees with this table (a canvas that measures before a web font is
// ready silently falls back to another font and would size the type wrong for the whole render).
const EM = {Unbounded: [0.913, 0.759, 0.841, 0.236], Anton: [0.474, 0.455, 0.478, 0.234], PlusJakarta: [0.687, 0.568, 0.6, 0.18],
  PlusJakartaSans: [0.687, 0.568, 0.6, 0.18], Montserrat: [0.748, 0.625, 0.628, 0.3], BricolageGrotesque: [0.675, 0.575, 0.601, 0.223],
  PlayfairDisplay: [0.666, 0.52, 0.502, 0.239], TikTokSans: [0.72, 0.58, 0.639, 0.247], Poppins: [0.693, 0.606, 0.599, 0.191],
  Manrope: [0.671, 0.561, 0.594, 0.2], Geist: [0.707, 0.59, 0.641, 0.214], GeistMono: [0.6, 0.6, 0.6, 0.6], DMSans: [0.66, 0.55, 0.58, 0.22],
  SpaceGrotesk: [0.66, 0.56, 0.6, 0.22], InterTight: [0.692, 0.559, 0.632, 0.176], InstrumentSerif: [0.474, 0.4, 0.388, 0.17],
  Outfit: [0.685, 0.531, 0.56, 0.185], Archivo: [0.768, 0.612, 0.667, 0.18], MsMadi: [0.821, 0.337, 0.477, 0.215]};

// Luminous gradients (LUMEN): [highlight, body, edge]. The glow takes the body colour.
export const GRADS = {
  fire: ['#FFE7A3', '#FF9A2E', '#E8501C'], gold: ['#FFF6CF', '#F7C948', '#B7791F'], ice: ['#EFFFFF', '#6FF2FF', '#1AA6D6'],
  mint: ['#EFFFF6', '#5EF0B0', '#14A774'], rose: ['#FFE3EE', '#FF6FA8', '#D61F69'], violet: ['#F1E9FF', '#A98BFF', '#6A3DF0'],
  silver: ['#FFFFFF', '#D5DAE3', '#8D95A5'],
};
export const gradOf = (E, g) => (Array.isArray(g) ? g : GRADS[g] || GRADS[(E.style.depth && E.style.depth.grad) || 'fire'] || GRADS.fire);
const hexA = (hex, a) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || '')); if (!m) return hex;
  const n = parseInt(m[1], 16); return `rgba(${(n >> 16) & 255},${(n >> 8) & 255},${n & 255},${a})`;
};
const NARROW = new Set(['I', 'J', 'i', 'j', 'l', 't', 'f', 'r', '1', '.', ',', ':', ';', '!', "'", '|']);
const WIDE = new Set(['M', 'W', 'm', 'w']);
const estimate = (text, family, size) => {
  const [up, lo, dg, sp] = EM[family] || [0.72, 0.6, 0.62, 0.25];
  let w = 0;
  for (const ch of String(text)) {
    let e;
    if (ch === ' ' || ch === '\u00a0') e = sp;
    else if (/[A-Z]/.test(ch)) e = up;
    else if (/[a-z]/.test(ch)) e = lo;
    else if (/[0-9]/.test(ch)) e = dg * 1.1;
    else e = up * 0.6;
    if (NARROW.has(ch)) e *= 0.5; else if (WIDE.has(ch)) e *= 1.25;
    w += e;
  }
  return w * size;
};
// width of a string in px: canvas measureText when it agrees with the font table (+-15 %), otherwise the table estimate
export const measureText = (text, family, weight, size) => {
  const key = `${family}|${weight}|${size}|${text}`;
  if (_mw.has(key)) return _mw.get(key);
  const est = estimate(text, family, size);
  let w = est;
  try {
    if (!_cv && typeof document !== 'undefined') _cv = document.createElement('canvas');
    if (_cv) {
      const c = _cv.getContext('2d'); c.font = `${weight} ${size}px "${family}"`;
      const m = c.measureText(String(text)).width;
      if (m > 0 && Math.abs(m - est) / est < 0.15) w = m;
    }
  } catch (e) { /* estimate */ }
  _mw.set(key, w);
  return w;
};

// wall brightness behind the speaker (planner samples it from the footage): 'auto' tone -> ink on light walls, paper on dark
const autoInk = (E) => {
  const T = theme(E);
  const light = (E.wallLum ?? 0.3) > 0.5;
  return light ? (E.style.colors.ink && lumHex(E.style.colors.ink) < 0.2 ? E.style.colors.ink : '#0B0C10') : (T.paper || '#FFFFFF');
};
export const toneColor = (E, tone, dflt) => {
  const C = E.style.colors;
  if (!tone || tone === 'auto') return dflt || autoInk(E);
  if (tone === 'ink') return C.ink || '#0B0C10';
  if (tone === 'paper') return C.paper || '#FFFFFF';
  return C[tone] || tone;
};
// font roles: display (default) · accent (the style's italic accent serif, DEPTH: Instrument Serif) · serif · condensed · ui
const fontOf = (E, o) => {
  const F = E.style.fonts || {};
  if (o.family) return {fontFamily: o.family, fontWeight: o.weight || 900, ...(o.italic ? {fontStyle: 'italic'} : {})};
  if (o.font === 'script') return {fontFamily: F.script || 'MsMadi', fontWeight: 400};
  if (o.font === 'accent') return {fontFamily: F.accent || F.serif || 'PlayfairDisplay', fontWeight: F.accent ? 400 : (F.serifWeight || 500), fontStyle: 'italic'};
  if (o.font === 'serif') return {fontFamily: F.serif || 'PlayfairDisplay', fontWeight: o.weight || F.serifWeight || 600, ...(o.italic ? {fontStyle: 'italic'} : {})};
  if (o.font === 'condensed') return {fontFamily: 'Anton', fontWeight: 400};
  if (o.font === 'ui') return {fontFamily: F.ui || F.head, fontWeight: o.weight || 800};
  const d = dispFont(E);
  return {fontFamily: d.fontFamily, fontWeight: o.weight || d.fontWeight};
};
const shade = (hex, k) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || '')); if (!m) return hex;
  const n = parseInt(m[1], 16);
  const f = (v) => Math.max(0, Math.min(255, Math.round(v * k)));
  return `rgb(${f((n >> 16) & 255)},${f((n >> 8) & 255)},${f(n & 255)})`;
};
// the "look" of a piece of display type: returns CSS for the text element
export const typeLook = (E, look, color, size, grad) => {
  const C = E.style.colors;
  const sw = Math.max(2, Math.round(size * 0.02));
  switch (look) {
    case 'lumen': {
      // luminous gradient: a vertical three-stop fill + a tight bloom and a wide halo in the body colour
      const g = gradOf(E, grad);
      return {color: 'transparent', backgroundImage: `linear-gradient(180deg, ${g[0]} 0%, ${g[1]} 55%, ${g[2]} 100%)`,
        WebkitBackgroundClip: 'text', backgroundClip: 'text',
        filter: `drop-shadow(0 0 ${(size * 0.045).toFixed(1)}px ${hexA(g[1], 0.85)}) drop-shadow(0 0 ${(size * 0.2).toFixed(1)}px ${hexA(g[1], 0.42)})`};
    }
    case 'neon': {
      // outlined glowing tube: thin stroke in the light colour, glow in the body colour
      const g = gradOf(E, grad);
      return {color: 'transparent', WebkitTextStroke: `${Math.max(2, size * 0.022).toFixed(1)}px ${g[0]}`,
        filter: `drop-shadow(0 0 ${(size * 0.04).toFixed(1)}px ${g[1]}) drop-shadow(0 0 ${(size * 0.14).toFixed(1)}px ${hexA(g[1], 0.6)})`};
    }
    case 'outline': return {color: 'transparent', WebkitTextStroke: `${sw}px ${color}`};
    case 'chrome': return {color: 'transparent', backgroundImage: 'linear-gradient(180deg, #FFFFFF 0%, #C9CED8 38%, #4B5160 52%, #E8EBF0 64%, #8A909C 100%)',
      WebkitBackgroundClip: 'text', backgroundClip: 'text', WebkitTextStroke: `${Math.max(1, sw * 0.4)}px rgba(20,22,28,0.55)`,
      filter: 'drop-shadow(0 10px 18px rgba(0,0,0,0.35))'};
    case 'gradient': return {color: 'transparent', backgroundImage: `linear-gradient(100deg, ${C.accent || '#FFD23F'}, ${C.accent2 || '#7CE7FF'})`,
      WebkitBackgroundClip: 'text', backgroundClip: 'text'};
    case 'extrude': return {color, textShadow: Array.from({length: 9}, (_, i) => `0 ${((i + 1) * size * 0.075 / 9).toFixed(1)}px 0 ${shade(color, 0.62 - i * 0.03)}`).join(', ')
      + `, 0 ${size * 0.12}px ${size * 0.15}px rgba(0,0,0,0.4)`};
    case 'glow': return {color, textShadow: `0 0 ${size * 0.12}px ${color}, 0 0 ${size * 0.35}px ${color}`};
    default: return {color};
  }
};
// subtle parallax: type behind the speaker moves a little against the face (reads as depth when the camera moves)
const parallaxOf = (E, t, o) => {
  const k = o.parallax ?? 0.1;
  if (!k) return [0, 0];
  const f = faceOnCanvas(E, t);
  if (!f) return [0, 0];
  return [-(f.x - E.width / 2) * k, -(f.y - E.height * 0.4) * k * 0.5];
};

// ------------------------------------------------------------------------------------------------ DepthText
export const DepthText = ({E, t, o}) => {
  const mode = o.mode || 'stack';
  if (mode === 'wall') return <DepthWall E={E} t={t} o={o} />;
  if (mode === 'echo') return <DepthEcho E={E} t={t} o={o} />;
  if (mode === 'split') return <DepthSplit E={E} t={t} o={o} />;
  return <DepthStack E={E} t={t} o={o} />;
};

// type behind the speaker is uppercase by default; `case: "none"` keeps it as spoken (the accent font defaults to as-spoken)
const caseOf = (o, s, font) => ((o.case ?? (font === 'accent' || font === 'script' ? 'none' : 'upper')) === 'none' ? String(s) : o.case === 'lower' ? String(s).toLowerCase() : String(s).toUpperCase());

// stack: lines rise out of masks (staggered), letter spacing tightens, then a slow creep; exits lift out fast
const GLOWY = new Set(['lumen', 'neon', 'glow']);
// light sweep: a bright band glides across the glyphs once, shortly after they land (After Effects "CC Light Sweep")
const Sweep = ({t, at, text, style}) => {
  const p = prog(t, at, at + 0.85);
  if (p <= 0 || p >= 1) return null;
  const pos = lerp(130, -30, qInOut(p));
  return (
    <span style={{position: 'absolute', left: 0, top: 0, ...style, color: 'transparent', WebkitTextStroke: '0', filter: 'none',
      backgroundImage: 'linear-gradient(100deg, rgba(255,255,255,0) 38%, rgba(255,255,255,0.95) 50%, rgba(255,255,255,0) 62%)',
      backgroundSize: '260% 100%', backgroundPosition: `${pos.toFixed(1)}% 0`, WebkitBackgroundClip: 'text', backgroundClip: 'text',
      mixBlendMode: 'screen', pointerEvents: 'none'}}>{text}</span>
  );
};

const DepthStack = ({E, t, o}) => {
  const W = E.width;
  const lines = o.lines || [{text: o.text, at: o.at}];
  const maxW = o.maxW ?? (o.align === 'left' || o.align === 'right' ? W - 140 : W - 70);
  const at0 = o.at ?? o.start;
  const [px, py] = parallaxOf(E, t, o);
  const creep = 1 + (o.creep ?? 0.04) * prog(t, at0, o.end ?? at0 + 3);
  let y = o.y ?? 300;
  const out = [];
  lines.forEach((ln, i) => {
    const fRole = ln.font || o.font;
    const F = fontOf(E, {...o, font: fRole, ...(ln.italic ? {italic: true} : {})});
    const txt = caseOf({...o, ...(ln.case ? {case: ln.case} : {})}, ln.text || '', fRole);
    const want = ln.size || o.size || 240;
    const trackRest = ln.track ?? o.track ?? (fRole === 'script' ? 0 : -0.02);
    const wWant = measureText(txt, F.fontFamily, F.fontWeight, want) + Math.max(0, txt.length - 1) * trackRest * want;
    const size = Math.min(want, want * maxW / Math.max(1, wWant * 1.06 * (1 + (o.creep ?? 0.04))));
    const top = y; y += size * (ln.leading ?? o.leading ?? 0.9) + (o.gap ?? 0);
    const a = ln.at ?? at0 + i * 0.12;
    if (t < a - 0.01) return;
    const dur = o.dur ?? 0.62;
    const p = tw(t, a, dur);
    const ex = o.end != null ? tw(t, o.end - 0.32 + (lines.length - 1 - i) * 0.03, 0.3, qIn) : 0;
    const off = (1 - p) * size * 1.05 - ex * size * 1.1;
    const v = Math.abs(size * 1.05 * (tw(t + 1 / 120, a, dur) - tw(t - 1 / 120, a, dur)) * 60) + (ex > 0 && ex < 1 ? size * 4 : 0);
    const track = lerp(ln.trackFrom ?? o.trackFrom ?? (trackRest + 0.08), trackRest, tw(t, a, 1.1));
    const look = ln.look || (o.looks ? o.looks[i % o.looks.length] : (o.look || (i % 2 ? 'outline' : 'fill')));
    const color = toneColor(E, ln.tone || o.tone);
    const grad = ln.grad || o.grad;
    const L = typeLook(E, look, color, size, grad);
    const x = o.x ?? (o.align === 'left' ? 70 : o.align === 'right' ? W - 70 : W / 2);
    const pad = GLOWY.has(look) ? size * 0.32 : 0;                // room for the glow inside the reveal mask
    const align = o.align === 'left' ? 'flex-start' : o.align === 'right' ? 'flex-end' : 'center';
    const left = o.align === 'left' ? x : o.align === 'right' ? x - W * 2 : x - W;
    const fontCss = {...F, fontSize: size, lineHeight: 1.04, letterSpacing: `${track}em`, whiteSpace: 'nowrap'};
    out.push(
      <div key={`l${i}`} style={{position: 'absolute', left, width: W * 2, top: top - pad, height: size * 1.08 + pad * 2, overflow: 'hidden', display: 'flex',
        justifyContent: align, paddingTop: pad}}>
        <div style={{position: 'relative', ...fontCss, transform: `translateY(${off}px)`, ...L,
          filter: joinF(L.filter, dirBlur(0, Math.min(24, shutterBlur(v)))), opacity: (ln.opacity ?? o.opacity ?? 1)}}>
          {txt}
          {(ln.sweep ?? o.sweep) && look !== 'outline' && look !== 'neon' ? <Sweep t={t} at={a + (ln.sweepDelay ?? 0.32)} text={txt} style={fontCss} /> : null}
        </div>
      </div>,
    );
    // ghost echoes under this line (poster titles): outlined copies stepping down and fading
    const n = ln.echo || 0;
    for (let k = 1; k <= n; k++) {
      const pk = tw(t, a + 0.16 + k * 0.07, 0.6);
      if (pk <= 0) continue;
      const ecol = toneColor(E, ln.echoTone || o.echoTone || ln.tone || o.tone);
      out.push(
        <div key={`e${i}_${k}`} style={{position: 'absolute', left, width: W * 2, top: top + size * (ln.echoStep ?? 0.78) * k * pk, display: 'flex', justifyContent: align,
          ...fontCss, letterSpacing: `${trackRest}em`, ...typeLook(E, 'outline', ecol, size), opacity: (1 - k / (n + 1)) * 0.7 * pk * (1 - ex)}}>{txt}</div>,
      );
    }
  });
  return (
    <AbsoluteFill style={{pointerEvents: 'none', transform: `translate(${px}px, ${py}px) scale(${creep})`, transformOrigin: `${(o.x ?? W / 2)}px ${(o.y ?? 300) + 200}px`}}>
      {out}
    </AbsoluteFill>
  );
};

// echo: one word slams in, outlined copies fan out above and below it like an AE echo, then breathe
const DepthEcho = ({E, t, o}) => {
  const W = E.width, F = fontOf(E, o);
  const txt = caseOf(o, o.text || (o.lines && o.lines[0]?.text) || '', o.font);
  const want = o.size || 230;
  const size = Math.min(want, want * (o.maxW ?? W - 80) / Math.max(1, (measureText(txt, F.fontFamily, F.fontWeight, want) - txt.length * 0.02 * want) * 1.1));
  const a = o.at ?? o.start;
  const n = o.echoes ?? 3;
  const y = o.y ?? 520;
  const x = o.x ?? W / 2;
  const ex = o.end != null ? tw(t, o.end - 0.3, 0.3, qIn) : 0;
  const sf = (k) => lerp(o.slam ?? 1.45, 1, eOut(prog(k, a, a + 0.42)));
  const sc = sf(t), v = Math.abs(sf(t + 1 / 120) - sf(t - 1 / 120)) * 60;
  const color = toneColor(E, o.tone);
  const ecol = toneColor(E, o.echoTone || o.tone);
  const step = size * (o.spread ?? 0.86);
  const [px, py] = parallaxOf(E, t, o);
  const pieces = [];
  for (let k = n; k >= 1; k--) {
    for (const d of (o.dir === 'up' ? [-1] : o.dir === 'down' ? [1] : [-1, 1])) {
      const p = tw(t, a + 0.12 + k * 0.06, 0.7);
      const breathe = Math.sin((t - a) * 1.7 + k) * 3 * k;
      pieces.push(
        <div key={`${k}${d}`} style={{position: 'absolute', left: x - W, width: W * 2, top: y + d * (step * k * p + breathe) - size * 0.52, textAlign: 'center',
          ...F, fontSize: size, lineHeight: 1.04, letterSpacing: '-0.02em', whiteSpace: 'nowrap', ...typeLook(E, 'outline', ecol, size),
          opacity: clamp(p * 1.4) * (1 - k / (n + 1.4)) * (1 - ex)}}>{txt}</div>,
      );
    }
  }
  return (
    <AbsoluteFill style={{pointerEvents: 'none', transform: `translate(${px}px, ${py - ex * 40}px)`}}>
      {pieces}
      {t >= a ? (
        <div style={{position: 'absolute', left: x - W, width: W * 2, top: y - size * 0.52, textAlign: 'center', ...F, fontSize: size, lineHeight: 1.04,
          letterSpacing: '-0.02em', whiteSpace: 'nowrap', ...typeLook(E, o.look || 'fill', color, size, o.grad), transform: `scale(${sc * (1 + 0.03 * prog(t, a, o.end ?? a + 3))})`,
          opacity: clamp(prog(t, a, a + 0.1) * 1.3) * (1 - ex), filter: joinF(typeLook(E, o.look || 'fill', color, size, o.grad).filter, blurF(Math.min(14, shutterBlur(v * size * 2))))}}>
          {txt}
        </div>
      ) : null}
    </AbsoluteFill>
  );
};

// wall: rows of a repeated phrase sliding in alternating directions (marquee), optional endless upward drift and a speed ramp
const DepthWall = ({E, t, o}) => {
  const W = E.width;
  const a = o.at ?? o.start, D = Math.max(0.5, (o.end ?? a + 4) - a);
  const rowFonts = o.fonts || [o.font];
  const rowsText = (o.lines && o.lines.length ? o.lines.map((l) => l.text) : [o.text || '']);
  const nRows = o.rows ?? 5;
  const size = o.size || 170;
  const rowH = size * (o.leading ?? 0.92);
  const sep = o.sep ?? '  •  ';
  const y0 = o.y ?? 260;
  const v0 = o.speed ?? 110, ramp = o.ramp ?? 1;
  const tau = Math.max(0, t - a);
  const dist = v0 * (tau + (ramp - 1) * tau * tau / (2 * D));       // integral of a linearly ramping speed
  const vNow = v0 * (1 + (ramp - 1) * clamp(tau / D));
  const vy = o.scrollY ?? 0;
  const offY = vy * (tau + (ramp - 1) * tau * tau / (2 * D));
  const ex = o.end != null ? tw(t, o.end - 0.35, 0.35, qIn) : 0;
  const color = toneColor(E, o.tone);
  const looks = o.looks || ['fill', 'outline'];
  const [px, py] = parallaxOf(E, t, o);
  const total = (nRows + 2) * rowH;
  const rows = [];
  for (let i = 0; i < nRows + (vy ? 2 : 0); i++) {
    const fRole = rowFonts[i % rowFonts.length];
    const RF = fontOf(E, {...o, font: fRole});
    const rsize = fRole === 'accent' ? size * 1.12 : size;
    const base = caseOf(o, rowsText[i % rowsText.length], fRole);
    const unit = base + sep;
    const uw = measureText(unit, RF.fontFamily, RF.fontWeight, rsize);
    const reps = Math.ceil((W * 1.6) / Math.max(1, uw)) + 2;
    const dir = i % 2 ? 1 : -1;
    const pin = tw(t, a + i * 0.06, 0.75);
    const slideIn = (1 - pin) * W * 0.9 * -dir;
    const exitK = ex * W * 1.2 * dir;
    const phase = rand(i * 3.7 + 1) * uw;
    let x = ((dir * dist + phase) % uw + uw) % uw - uw;     // wraps seamlessly every unit width
    const yy = vy ? y0 - rowH + ((((i * rowH - offY) % total) + total) % total) : y0 + i * rowH;
    const bx = Math.min(24, shutterBlur(vNow * (o.blurK ?? 1) + (pin < 1 ? (1 - pin) * W * 2.2 : 0) + (ex > 0 ? ex * W * 4 : 0)));
    const look = looks[i % looks.length];
    rows.push(
      <div key={i} style={{position: 'absolute', left: 0, top: yy, width: W, height: rowH * 1.08, overflow: 'visible', opacity: clamp(pin * 1.5) * (1 - ex)}}>
        <div style={{position: 'absolute', left: x + slideIn + exitK, top: 0, whiteSpace: 'nowrap', ...RF, fontSize: rsize, lineHeight: 1.04, letterSpacing: fRole === 'accent' ? '0' : '-0.02em',
          ...typeLook(E, look, color, rsize, o.grad), filter: joinF(typeLook(E, look, color, rsize, o.grad).filter, dirBlur(bx, 0))}}>
          {unit.repeat(reps)}
        </div>
      </div>,
    );
  }
  const fade = o.fadeEdges === false ? undefined : `linear-gradient(180deg, transparent ${y0 - rowH * 0.3}px, #000 ${y0 + rowH * 0.6}px, #000 ${y0 + rowH * (nRows - 0.6)}px, transparent ${y0 + rowH * (nRows + 0.4)}px)`;
  return (
    <AbsoluteFill style={{pointerEvents: 'none', transform: `translate(${px}px, ${py}px) rotate(${o.rot ?? 0}deg)`, opacity: o.opacity ?? 1,
      WebkitMaskImage: vy ? fade : undefined, maskImage: vy ? fade : undefined}}>
      {rows}
    </AbsoluteFill>
  );
};

// split: two words start hidden behind the head and part to the left and right edges
const DepthSplit = ({E, t, o}) => {
  const W = E.width;
  const fr = o.fonts || [o.font, o.font];
  const FL = fontOf(E, {...o, font: fr[0]}), FR = fontOf(E, {...o, font: fr[1] ?? fr[0]});
  const F = FL;
  const a = o.at ?? o.start;
  const L = caseOf(o, o.text || (o.lines && o.lines[0]?.text) || '', fr[0]), R = caseOf(o, o.text2 || (o.lines && o.lines[1]?.text) || '', fr[1] ?? fr[0]);
  const want = o.size || 200;
  const half = W / 2 - (o.margin ?? 40);
  const sL = Math.min(want, want * half / Math.max(1, measureText(L, F.fontFamily, F.fontWeight, want) * 1.14));
  const sR = Math.min(want, want * half / Math.max(1, measureText(R, FR.fontFamily, FR.fontWeight, want) * 1.14));
  const size = Math.min(sL, sR);
  const f = faceOnCanvas(E, t);
  const cx = o.x ?? (f ? f.x : W / 2);
  const y = o.y ?? 420;
  const pf = (k) => qOut(prog(k, a, a + (o.dur ?? 0.8)));
  const p = pf(t), v = Math.abs(pf(t + 1 / 120) - pf(t - 1 / 120)) * 60 * W * 0.4;
  const ex = o.end != null ? tw(t, o.end - 0.3, 0.3, qIn) : 0;
  const color = toneColor(E, o.tone);
  const wL = measureText(L, FL.fontFamily, FL.fontWeight, size) * 1.06, wR = measureText(R, FR.fontFamily, FR.fontWeight, size) * 1.06;
  const xl = lerp(cx - wL * 0.5, (o.margin ?? 40), p) - ex * W * 0.6;
  const xr = lerp(cx - wR * 0.5, W - (o.margin ?? 40) - wR, p) + ex * W * 0.6;
  const colOf = (i) => toneColor(E, (o.tones && o.tones[i]) || o.tone);
  const st = (look, i) => ({position: 'absolute', top: y, whiteSpace: 'nowrap', ...(i ? FR : FL), fontSize: size, lineHeight: 1.04, letterSpacing: '-0.02em', ...typeLook(E, look, colOf(i), size, (o.grads && o.grads[i]) || o.grad),
    filter: joinF(typeLook(E, look, colOf(i), size, (o.grads && o.grads[i]) || o.grad).filter, dirBlur(Math.min(24, shutterBlur(v)), 0)), opacity: clamp(p * 2.2) * (1 - ex)});
  if (t < a) return null;
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      <div style={{...st(o.looks?.[0] || 'fill', 0), left: xl}}>{L}</div>
      <div style={{...st(o.looks?.[1] || 'outline', 1), left: xr}}>{R}</div>
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ HeadRing
// {text, at, half: front|back (set by the planner), radius (x face height), tilt (ry/rx), lift (x face height above the
//  face centre), roll (deg), speed (rad/s), size, tone, line, case}
export const HeadRing = ({E, t, o}) => {
  const f = faceOnCanvas(E, t);
  if (!f) return null;
  // no person matte (model unavailable): the back half would be drawn over the head - leave it out
  if (o.half === 'back' && !matteSrc(E, t)) return null;
  const F = fontOf(E, {...o, font: o.font || 'ui'});
  const a = o.at ?? o.start;
  const pin = tw(t, a, 0.8), ex = o.end != null ? tw(t, o.end - 0.4, 0.4, qIn) : 0;
  const k = pin * (1 - ex);
  if (k <= 0.002) return null;
  const hh = Math.min(f.h, 560);
  const rx = hh * (o.radius ?? 0.92) * (0.82 + 0.18 * k);
  const ry = rx * (o.tilt ?? 0.2);
  const cx = f.x + (o.dx ?? 0), cy = f.y - hh * (o.lift ?? 0.6) + (o.dy ?? 0) - (1 - pin) * 50;
  const roll = ((o.roll ?? -6) * Math.PI) / 180, cr = Math.cos(roll), sr = Math.sin(roll);
  const size = o.size ?? 52;
  const sep = o.sep ?? '  •  ';
  const unit = caseOf(o, o.text || '', o.font) + sep;
  const chars = Array.from(unit);
  const widths = chars.map((c) => measureText(c === ' ' ? ' ' : c, F.fontFamily, F.fontWeight, size) * (1 + (o.spacing ?? 0.06)));
  const uw = widths.reduce((s, x) => s + x, 0) || 1;
  const circ = 2 * Math.PI * rx;                                  // the glyphs sit on the circle before projection
  const reps = Math.max(1, Math.round(circ / uw));
  const totalW = uw * reps;
  const theta0 = (o.speed ?? 0.75) * (t - a) * (o.dir === 'reverse' ? -1 : 1) + (o.phase ?? 0);
  const color = toneColor(E, o.tone);
  const half = o.half || 'both';
  // a thin contrasting outline (paper text gets an ink edge, ink text a paper edge): readable over hair and wall alike
  const edge = o.stroke === false ? null : (o.strokeColor || (lumHex(String(color).startsWith('#') ? color : '#FFFFFF') > 0.4 ? '#0B0C10' : '#FFFFFF'));
  const sw = Math.max(1.5, size * 0.045);
  const halo = edge ? [[1, 0], [-1, 0], [0, 1], [0, -1], [0.7, 0.7], [-0.7, 0.7], [0.7, -0.7], [-0.7, -0.7]].map(([dx, dy]) => `${(dx * sw).toFixed(1)}px ${(dy * sw).toFixed(1)}px 0 ${edge}`).join(', ') : 'none';
  const out = [];
  let cum = 0;
  for (let r = 0; r < reps; r++) {
    for (let j = 0; j < chars.length; j++) {
      const wj = widths[j];
      const th = theta0 - ((cum + wj / 2) / totalW) * 2 * Math.PI;
      cum += wj;
      const s = Math.sin(th), c = Math.cos(th);
      const front = s > 0;
      if ((half === 'front' && !front) || (half === 'back' && front)) continue;
      if (chars[j] === ' ') continue;
      const lx = rx * c, ly = ry * s;
      const x = cx + lx * cr - ly * sr, y = cy + lx * sr + ly * cr;
      const slope = Math.atan((ry * -c) / (rx * (Math.abs(s) < 1e-4 ? 1e-4 : s)));
      const persp = 1 + 0.16 * s;
      const sx = s * persp;
      const vis = Math.abs(s) < 0.06 ? 0 : 1;
      out.push(
        <div key={`${r}_${j}`} style={{position: 'absolute', left: x, top: y, ...F, fontSize: size, lineHeight: 1, whiteSpace: 'pre', color, textShadow: halo,
          transform: `translate(-50%, -50%) rotate(${slope + roll}rad) scale(${sx.toFixed(3)}, ${persp.toFixed(3)})`,
          opacity: vis * k * (front ? 1 : 0.42 + 0.3 * (1 + s)), filter: front ? (o.glow ? `drop-shadow(0 0 10px ${color})` : 'drop-shadow(0 6px 10px rgba(0,0,0,0.28))') : 'blur(0.8px)'}}>
          {chars[j]}
        </div>,
      );
    }
  }
  let ring = null;
  if (o.line !== false) {
    const pts = [];
    const from = half === 'back' ? Math.PI : 0, to = half === 'front' ? Math.PI : 2 * Math.PI;
    for (let i = 0; i <= 64; i++) {
      const th = lerp(from, to, i / 64);
      const lx = rx * 1.12 * Math.cos(th), ly = ry * 1.12 * Math.sin(th);
      pts.push(`${(cx + lx * cr - ly * sr).toFixed(1)},${(cy + lx * sr + ly * cr).toFixed(1)}`);
    }
    ring = (
      <svg width={E.width} height={E.height} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
        <polyline points={pts.join(' ')} fill="none" stroke={color} strokeWidth={half === 'back' ? 2 : 3} strokeOpacity={(half === 'back' ? 0.3 : 0.65) * k} strokeLinecap="round" />
      </svg>
    );
  }
  return <AbsoluteFill style={{pointerEvents: 'none'}}>{ring}{out}</AbsoluteFill>;
};

// ------------------------------------------------------------------------------------------------ Doodles
// Shapes in face units (u = face height), origin at the face centre, y down. s = side (+1 right, -1 left).
const arcPts = (cx, cy, rx, ry, a0, a1, n = 36) => Array.from({length: n + 1}, (_, i) => {
  const a = lerp(a0, a1, i / n); return [cx + rx * Math.cos(a), cy + ry * Math.sin(a)];
});
const starPts = (cx, cy, r) => Array.from({length: 11}, (_, i) => {
  const a = -Math.PI / 2 + (i * Math.PI) / 5, rr = i % 2 ? r * 0.45 : r; return [cx + rr * Math.cos(a), cy + rr * Math.sin(a)];
});
const DOODLES = {
  sparks: (s) => [[-25, -55, -85].map((deg) => deg * Math.PI / 180)].flat().map((a) => {
    const bx = s * 0.6, by = -0.62, d0 = 0.06, d1 = 0.27;
    return [[bx + s * Math.cos(a) * d0, by + Math.sin(a) * d0], [bx + s * Math.cos(a) * (d0 + d1) * 0.55, by + Math.sin(a) * (d0 + d1) * 0.55 - 0.01], [bx + s * Math.cos(a) * (d0 + d1), by + Math.sin(a) * (d0 + d1)]];
  }),
  swirl: (s) => [Array.from({length: 54}, (_, i) => {
    const p = i / 53, a = p * Math.PI * 2 * 2.3, r = 0.03 + 0.2 * p; return [s * 0.04 + r * Math.cos(a) * s, -1.0 + r * 0.55 * Math.sin(a)];
  })],
  question: (s) => [
    [[-0.13, -0.12], [-0.1, -0.24], [0.02, -0.3], [0.14, -0.24], [0.15, -0.12], [0.06, -0.03], [0.0, 0.05], [0.0, 0.13]].map(([x, y]) => [s * 0.8 + x, -0.62 + y]),
    arcPts(s * 0.8, -0.62 + 0.24, 0.018, 0.018, 0, Math.PI * 2, 10),
  ],
  exclaim: (s) => [0, 0.13].flatMap((dx) => [
    [[s * 0.72 + dx - 0.01, -0.95], [s * 0.72 + dx, -0.8], [s * 0.72 + dx + 0.005, -0.68]],
    arcPts(s * 0.72 + dx + 0.008, -0.6, 0.016, 0.016, 0, Math.PI * 2, 10),
  ]),
  halo: () => [arcPts(0, -0.92, 0.34, 0.085, Math.PI * 0.9, Math.PI * 0.9 + Math.PI * 2.15, 60)],
  circle: () => [arcPts(0, -0.1, 0.72, 0.86, Math.PI * 1.15, Math.PI * 1.15 + Math.PI * 2.2, 80)],
  shock: () => Array.from({length: 6}, (_, i) => {
    const a = lerp(-Math.PI * 0.92, -Math.PI * 0.08, i / 5);
    return [[0.78 * Math.cos(a), -0.1 + 0.95 * Math.sin(a)], [1.0 * Math.cos(a), -0.1 + 1.2 * Math.sin(a)]];
  }),
  crown: () => [[[-0.28, -0.82], [-0.3, -1.08], [-0.15, -0.94], [0, -1.14], [0.15, -0.94], [0.3, -1.08], [0.28, -0.82], [-0.28, -0.82]]],
  bulb: (s) => {
    const cx = s * 0.78, cy = -0.82;
    return [arcPts(cx, cy, 0.15, 0.16, Math.PI * 0.72, Math.PI * 2.28, 40).concat([[cx + 0.06, cy + 0.2], [cx - 0.06, cy + 0.2], [cx - 0.08, cy + 0.15]]),
      [[cx - 0.055, cy + 0.25], [cx + 0.055, cy + 0.25]],
      ...[-1.2, -0.6, 0, 0.6, 1.2].map((d) => { const a = -Math.PI / 2 + d * 0.55; return [[cx + 0.21 * Math.cos(a), cy + 0.22 * Math.sin(a)], [cx + 0.3 * Math.cos(a), cy + 0.31 * Math.sin(a)]]; })];
  },
  stars: () => [0, 1, 2].map((i) => starPts(lerp(-0.32, 0.32, i / 2), -0.98 + (i === 1 ? -0.08 : 0), 0.06)),
  arrow: (s) => {
    const pts = Array.from({length: 24}, (_, i) => { const p = i / 23; return [s * lerp(1.18, 0.66, p), lerp(0.1, -0.42, p) - Math.sin(p * Math.PI) * 0.16]; });
    const [hx, hy] = pts[pts.length - 1], [px, py] = pts[pts.length - 3];
    const ang = Math.atan2(hy - py, hx - px);
    const head = [[hx + 0.09 * Math.cos(ang + 2.6), hy + 0.09 * Math.sin(ang + 2.6)], [hx, hy], [hx + 0.09 * Math.cos(ang - 2.6), hy + 0.09 * Math.sin(ang - 2.6)]];
    return [pts, head];
  },
  scribble: (s) => [Array.from({length: 70}, (_, i) => {
    const p = i / 69, a = p * Math.PI * 2 * 4.2;
    return [s * 0.05 + 0.26 * Math.sin(p * Math.PI * 2.1 + 0.4) + 0.08 * Math.cos(a), -1.0 + 0.07 * Math.sin(a * 1.03) + 0.04 * Math.sin(p * 9)];
  })],
  drops: (s) => [[[s * 0.6, -0.5], [s * 0.64, -0.4], [s * 0.66, -0.34], [s * 0.6, -0.3], [s * 0.555, -0.34], [s * 0.575, -0.4], [s * 0.6, -0.5]],
    [[s * 0.7, -0.62], [s * 0.725, -0.56], [s * 0.735, -0.52], [s * 0.7, -0.5], [s * 0.67, -0.52], [s * 0.682, -0.56], [s * 0.7, -0.62]]],
  heart: (s) => [Array.from({length: 40}, (_, i) => {
    const a = (i / 39) * Math.PI * 2, x = 16 * Math.sin(a) ** 3, y = -(13 * Math.cos(a) - 5 * Math.cos(2 * a) - 2 * Math.cos(3 * a) - Math.cos(4 * a));
    return [s * 0.78 + x * 0.0095, -0.75 + y * 0.0095];
  })],
};
export const DOODLE_KINDS = Object.keys(DOODLES);
// idle motion per kind (applied around the doodle's own centre)
const idleOf = (kind, tt) => {
  if (kind === 'swirl' || kind === 'scribble') return {rot: tt * 40, sc: 1};
  if (kind === 'stars') return {rot: Math.sin(tt * 2.2) * 8, sc: 1};
  if (kind === 'sparks' || kind === 'shock' || kind === 'bulb') return {rot: 0, sc: 1 + 0.06 * Math.sin(tt * 9)};
  return {rot: Math.sin(tt * 1.4) * 2, sc: 1 + 0.015 * Math.sin(tt * 2)};
};
const smoothPath = (P) => {
  if (P.length < 3) return `M${P.map((p) => `${p[0].toFixed(1)},${p[1].toFixed(1)}`).join(' L')}`;
  let d = `M${P[0][0].toFixed(1)},${P[0][1].toFixed(1)}`;
  for (let i = 0; i < P.length - 1; i++) {
    const p0 = P[i - 1] || P[i], p1 = P[i], p2 = P[i + 1], p3 = P[i + 2] || p2;
    const c1 = [p1[0] + (p2[0] - p0[0]) / 6, p1[1] + (p2[1] - p0[1]) / 6], c2 = [p2[0] - (p3[0] - p1[0]) / 6, p2[1] - (p3[1] - p1[1]) / 6];
    d += ` C${c1[0].toFixed(1)},${c1[1].toFixed(1)} ${c2[0].toFixed(1)},${c2[1].toFixed(1)} ${p2[0].toFixed(1)},${p2[1].toFixed(1)}`;
  }
  return d;
};
const plen = (P) => P.reduce((s, p, i) => (i ? s + Math.hypot(p[0] - P[i - 1][0], p[1] - P[i - 1][1]) : 0), 0);

// {items:[{kind, at, until?, side: right|left, color?, scale?, dx?, dy?, width?, dur?, label?}], color, boil (true), glow}
export const Doodles = ({E, t, o}) => {
  const f = faceOnCanvas(E, t);
  if (!f) return null;
  const W = E.width, H = E.height, FN = E.style.fonts || {};
  const boilK = o.boil === false ? 0 : (o.boilAmp ?? 0.007);
  const bf = Math.floor(t * (o.boilFps ?? 12));
  const items = (o.items || []).map((it, i) => ({...it, at: it.at ?? o.start + i * 0.3}));
  const ex = o.end != null ? tw(t, o.end - 0.28, 0.28, qIn) : 0;
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      <svg width={W} height={H} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
        {items.map((it, i) => {
          if (t < it.at || !DOODLES[it.kind]) return null;
          const u = Math.min(f.h, 480) * (o.scale ?? 1) * (it.scale ?? 1);
          const s = it.side === 'left' ? -1 : 1;
          const strokes = DOODLES[it.kind](s);
          const all = strokes.flat();
          const ccx = all.reduce((a, p) => a + p[0], 0) / all.length, ccy = all.reduce((a, p) => a + p[1], 0) / all.length;
          const idle = idleOf(it.kind, t - it.at);
          const ux = f.x + (it.dx ?? 0), uy = f.y + (it.dy ?? 0);
          const toPx = (p, j) => {
            const jx = boilK ? (rand(bf * 13.1 + j * 7.3 + i * 31) - 0.5) * 2 * boilK : 0, jy = boilK ? (rand(bf * 17.9 + j * 5.1 + i * 11) - 0.5) * 2 * boilK : 0;
            let x = p[0] + jx - ccx, y = p[1] + jy - ccy;
            const r = (idle.rot * Math.PI) / 180, c = Math.cos(r), sn = Math.sin(r);
            [x, y] = [(x * c - y * sn) * idle.sc, (x * sn + y * c) * idle.sc];
            return [ux + (x + ccx) * u, uy + (y + ccy) * u];
          };
          let px = strokes.map((st, si) => st.map((p, j) => toPx(p, j + si * 50)));
          // keep the doodle inside the frame's safe area (a close-up makes the face big and would push it off the top)
          const ys = px.flat().map((q) => q[1]), xs = px.flat().map((q) => q[0]);
          const shY = Math.max(0, 285 - Math.min(...ys)), shX = Math.max(0, 40 - Math.min(...xs)) - Math.max(0, Math.max(...xs) - (W - 40));
          if (shY || shX) px = px.map((P) => P.map(([x, y]) => [x + shX, y + shY]));
          const lens = px.map(plen), L = lens.reduce((a, b) => a + b, 0) || 1;
          const dur = it.dur ?? Math.min(0.75, 0.25 + L / 2600);
          const p = qOut(prog(t, it.at, it.at + dur));
          const out = Math.max(ex, it.until != null ? tw(t, it.until - 0.25, 0.25, qIn) : 0);
          if (out >= 0.999) return null;
          const color = toneColor(E, it.color || o.color, autoInk(E));
          const width = it.width ?? o.width ?? Math.max(6, u * 0.03);
          let acc = 0;
          return (
            <g key={i} opacity={1 - out} style={{filter: o.glow ? `drop-shadow(0 0 8px ${color})` : 'drop-shadow(0 3px 5px rgba(0,0,0,0.22))'}}>
              {px.map((P, si) => {
                const a0 = acc / L; acc += lens[si]; const a1 = acc / L;
                const q = clamp((p - a0) / Math.max(1e-3, a1 - a0));
                if (q <= 0.002) return null;
                return <path key={si} d={smoothPath(P)} fill="none" stroke={color} strokeWidth={width} strokeLinecap="round" strokeLinejoin="round"
                  pathLength={1} strokeDasharray="1 1" strokeDashoffset={1 - q} />;
              })}
              {it.label && p > 0.6 ? (() => {
                // the label sits under the arrow's tail, pulled inwards so it never leaves the frame
                const P0 = px[0][0], ls = it.labelSize ?? 46, lw = String(it.label).length * ls * 0.62;
                const lx = s > 0 ? Math.min(P0[0] + 10, W - 40) : Math.max(P0[0] - 10, 40);
                const anchor = s > 0 ? (lx + lw > W - 40 ? 'end' : 'start') : (lx - lw < 40 ? 'start' : 'end');
                return <text x={lx} y={P0[1] + width * 5 + ls * 0.4} textAnchor={anchor} fill={color}
                  style={{fontFamily: FN.ui || FN.head, fontWeight: 800, fontSize: ls, opacity: clamp((p - 0.6) * 3)}}>{it.label}</text>;
              })() : null}
            </g>
          );
        })}
      </svg>
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ FeedWall
// {at, cols (3), cardW (300), gap, tilt: [rotX, rotZ], scale, speed (px/s), ramp, swipes: [t...], thumbs (from frames_src),
//  bg (backing colour or 'none'), dim, blur, palette}
export const FeedWall = ({E, t, o}) => {
  // the wall replaces the background, so it needs the person matte; without it the speaker would vanish behind it
  if ((o.layer || 'behind') === 'behind' && !matteSrc(E, t)) return null;
  const W = E.width, H = E.height, C = E.style.colors;
  const a = o.at ?? o.start, D = Math.max(0.5, (o.end ?? a + 4) - a);
  const n = o.cols ?? 3, cw = o.cardW ?? 300, ch = cw * 16 / 9, gap = o.gap ?? 28;
  const pitch = ch + gap;
  const tau = Math.max(0, t - a);
  const v0 = o.speed ?? 120, ramp = o.ramp ?? 1;
  const swipeOff = (tt) => (o.swipes || []).reduce((s, ts) => s + pitch * qInOut(prog(tt, ts, ts + (o.swipeDur ?? 0.45))), 0);
  const base = (tt) => { const z = Math.max(0, tt - a); return v0 * (z + (ramp - 1) * z * z / (2 * D)) + swipeOff(tt); };
  const vNow = Math.abs(base(t + 1 / 120) - base(t - 1 / 120)) * 60;
  const pin = tw(t, a, 0.7), ex = o.end != null ? tw(t, o.end - 0.35, 0.35, qIn) : 0;
  const [rx, rz] = o.tilt ?? [14, -9];
  const thumbs = o.thumbs || [];
  const pal = o.palette || [C.accent || '#FFD23F', C.accent2 || '#7CE7FF', C.danger || '#FF3B47', '#8B6CFF', '#2EE59D'];
  const wallW = n * cw + (n - 1) * gap;
  const x0 = W / 2 - wallW / 2 + (o.dx ?? 0);
  const speeds = o.colSpeeds || [1, 1.3, 0.85, 1.15];
  const perCol = Math.ceil((H * 1.9) / pitch) + 2;
  const cols = [];
  for (let c = 0; c < n; c++) {
    const off = base(t) * speeds[c % speeds.length] + c * pitch * 0.37;
    const by = Math.min(24, shutterBlur(vNow * speeds[c % speeds.length]));
    const cards = [];
    for (let r = 0; r < perCol; r++) {
      const yy = ((r * pitch - off) % (perCol * pitch) + perCol * pitch) % (perCol * pitch) - pitch * 1.4 - H * 0.2;
      const idx = r + c * 3 + Math.floor((r * pitch - off) / (perCol * pitch)) * -perCol;
      const src = thumbs.length ? thumbs[((idx % thumbs.length) + thumbs.length) % thumbs.length] : null;
      const col = pal[((idx % pal.length) + pal.length) % pal.length];
      const prog_ = (t * 0.35 + rand(idx * 1.7)) % 1;
      cards.push(
        <div key={r} style={{position: 'absolute', left: 0, top: yy, width: cw, height: ch, borderRadius: cw * 0.08, overflow: 'hidden',
          background: src ? '#111' : `linear-gradient(160deg, ${col}, ${shade(col, 0.45)})`, boxShadow: '0 20px 40px rgba(0,0,0,0.35), inset 0 0 0 2px rgba(255,255,255,0.12)'}}>
          {src ? <Img src={staticFile(src)} style={{width: '100%', height: '100%', objectFit: 'cover', filter: 'saturate(0.9)'}} /> : null}
          <div style={{position: 'absolute', left: 0, right: 0, bottom: 0, height: '38%', background: 'linear-gradient(180deg, transparent, rgba(0,0,0,0.55))'}} />
          <div style={{position: 'absolute', left: cw * 0.08, bottom: cw * 0.2, width: cw * 0.13, height: cw * 0.13, borderRadius: '50%', background: 'rgba(255,255,255,0.85)'}} />
          <div style={{position: 'absolute', left: cw * 0.26, bottom: cw * 0.255, width: cw * 0.46, height: cw * 0.04, borderRadius: 9, background: 'rgba(255,255,255,0.85)'}} />
          <div style={{position: 'absolute', left: cw * 0.26, bottom: cw * 0.185, width: cw * 0.3, height: cw * 0.035, borderRadius: 9, background: 'rgba(255,255,255,0.55)'}} />
          <div style={{position: 'absolute', left: 0, bottom: 0, height: cw * 0.022, width: `${(prog_ * 100).toFixed(1)}%`, background: C.accent || '#FFD23F'}} />
        </div>,
      );
    }
    cols.push(
      <div key={c} style={{position: 'absolute', left: x0 + c * (cw + gap), top: 0, width: cw, height: H, filter: dirBlur(0, by)}}>{cards}</div>,
    );
  }
  const sc = (o.scale ?? 1.25) * lerp(1.12, 1, pin);
  return (
    <AbsoluteFill style={{pointerEvents: 'none', opacity: clamp(pin * 1.3) * (1 - ex)}}>
      {o.bg !== 'none' ? <AbsoluteFill style={{background: o.bg ? toneColor(E, o.bg) : (C.bg || '#07080C'), opacity: o.bgOpacity ?? 1}} /> : null}
      <AbsoluteFill style={{transform: `perspective(1800px) rotateX(${rx}deg) rotateZ(${rz}deg) scale(${sc})`, transformOrigin: '50% 45%',
        filter: (o.blur ?? 1.2) > 0.3 ? `blur(${o.blur ?? 1.2}px)` : 'none'}}>
        {cols}
      </AbsoluteFill>
      <AbsoluteFill style={{background: `radial-gradient(ellipse 70% 55% at 50% 42%, rgba(0,0,0,${(o.dim ?? 0.25) * 0.5}), rgba(0,0,0,${Math.min(0.85, (o.dim ?? 0.25) + 0.3)}) 100%)`}} />
    </AbsoluteFill>
  );
};
