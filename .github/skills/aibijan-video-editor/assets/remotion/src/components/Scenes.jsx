// MOGRAPH MIX building blocks: full-screen motion-graphic scenes that alternate with the talking head, plus the
// pieces that tie both together. Premium After-Effects language: eased tweens (quint/expo in-out, no rubbery
// springs), masked text reveals with blur-to-sharp, slow camera pushes.
//
// Two looks, chosen by style.look (preset "looks" in styles/mograph-mix.md):
//   aurora (default)  midnight backdrop with drifting coloured light, frosted-glass UI, coral -> amber gradient words
//   flat              warm paper backdrop with a fine grid, solid ink / orange / cobalt, solid cards and word blocks,
//                     no gradients, no glows, no glass
//
//   AuroraBackdrop / PaperBackdrop
//   HeroTitle       face beats: kinetic display words rising out of a mask (accent word highlighted), kicker chip
//                   above, tag pill below. flat: every word sits on a solid block that wipes in.
//   Accents         face-tracked accents: glints, focus brackets, an orbit ring (hidden during scenes)
//   ShapeWipe       cover transitions: 'orb' (circle covers, circular hole reveals) and 'bars' (staggered bars)
//   SceneStack / SceneChat / ScenePrompt / SceneFeatures / ScenePhones   scenes (see creative-plan.md)
//   EndCardHero     end card on the backdrop (EndCard variant 'hero')
//
// Everything is a pure function of time and generic on purpose (no product UI is imitated).
import React from 'react';
import {AbsoluteFill, Freeze, OffthreadVideo, staticFile} from 'remotion';
import {bezier, clamp, dirBlur, lerp, prog, sample, shutterBlur, vel} from '../lib/anim.js';
import {Icon} from './Icons.jsx';
import {faceOnCanvas, gradeFilter} from './Footage.jsx';

// ------------------------------------------------------------------------------------------------ easing
export const qOut = bezier(0.22, 1, 0.36, 1);      // easeOutQuint: fast start, long silky settle (entrances)
export const qIn = bezier(0.64, 0, 0.78, 0);       // easeInQuint: exits accelerate away
export const qInOut = bezier(0.83, 0, 0.17, 1);    // easeInOutQuint: moves between two states
export const cInOut = bezier(0.65, 0, 0.35, 1);    // easeInOutCubic: covers / slow pushes
export const backOut = bezier(0.34, 1.4, 0.64, 1); // tiny overshoot for small badges only
export const eOut = (p) => (p >= 1 ? 1 : 1 - Math.pow(2, -10 * p));
export const tw = (t, a, d, f = qOut) => f(prog(t, a, a + d));
export const fx = (bx, by, blur) => [dirBlur(bx, by), blur > 0.4 ? `blur(${blur.toFixed(1)}px)` : ''].filter((s) => s && s !== 'none').join(' ') || 'none';

export const pick = (C, name, dflt) => (name ? (C[name] || name) : dflt);
export const rgba = (hex, a) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || ''));
  if (!m) return hex;
  const n = parseInt(m[1], 16);
  return `rgba(${(n >> 16) & 255},${(n >> 8) & 255},${n & 255},${a})`;
};
export const pal = (C) => ({accent: C.accent || '#FF6A3D', amber: C.amber || '#FFB547', violet: C.accent2 || '#8B6CFF', pink: C.pink || '#FF5EA8',
  cyan: C.cyan || '#38D6FF', mint: C.mint || '#4FF0B4', ink: C.ink || '#170F30'});
export const glass = (dark = 0.42) => ({
  background: `linear-gradient(160deg, rgba(255,255,255,0.16), rgba(255,255,255,0.04) 58%), rgba(16,12,40,${dark})`,
  border: '2px solid rgba(255,255,255,0.18)',
  boxShadow: 'inset 0 1.5px 0 rgba(255,255,255,0.26), 0 28px 70px rgba(4,2,20,0.4)',
});

// ------------------------------------------------------------------------------------------------ theme
// Every colour decision goes through here so the looks stay consistent. Solid surfaces (no glass, no gradients) for
// the MOGRAPH MIX flat look and for every style with a light backdrop (IMPACT, SYSTEM, EDITORIAL, CREATOR POP), so a
// scene borrowed from another style always wears the current style's own tokens. style.surface 'solid'|'glass' forces it.
export const lumHex = (hex) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || '')); if (!m) return 0;
  const n = parseInt(m[1], 16), ch = [(n >> 16) & 255, (n >> 8) & 255, n & 255].map((v) => { v /= 255; return v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4; });
  return 0.2126 * ch[0] + 0.7152 * ch[1] + 0.0722 * ch[2];
};
// display face of the current style (styles without a display font use their headline font)
export const dispFont = (E) => {
  const F = E.style.fonts || {};
  return F.display ? {fontFamily: F.display, fontWeight: F.displayWeight ?? 800} : {fontFamily: F.head || F.ui, fontWeight: 800};
};
export const theme = (E) => {
  const C = E.style.colors, P = pal(C);
  const solid = E.style.surface ? E.style.surface === 'solid'
    : (E.style.look === 'flat' || (E.style.backdrop?.variant !== 'aurora' && lumHex(C.bg) > 0.45));
  if (solid) {
    const ink = C.ink || '#121212', paper = C.paper || '#FFFFFF', ac = C.accent || '#FF4D17', ac2 = C.accent2 || '#2340FF';
    const fill = {accent: ac, amber: ac, accent2: ac2, cool: ac2, mint: ink, ink, paper};
    return {
      flat: true, ink, paper, accent: ac, accent2: ac2,
      fg: ink, fgSoft: C.muted || '#6E6A62', textFx: 'none',
      fill, onFill: (tone) => (tone === 'accent' || tone === 'amber' || tone === 'paper' ? ink : paper),
      panel: () => ({background: paper, border: `2px solid ${C.line || '#D8D3C7'}`, boxShadow: '0 2px 0 rgba(18,18,18,0.05), 0 22px 44px rgba(18,18,18,0.10)'}),
      panelFg: ink, panelSub: C.muted || '#6E6A62',
      chip: {background: ink, border: 'none', boxShadow: 'none'}, chipFg: paper, dot: ac, glow: false,
      solidShadow: () => 'none',
      ok: ink, okFg: paper, hi: ac, line: ink, lineSoft: rgba(ink, 0.35),
      darkPanel: {background: ink, boxShadow: '0 30px 60px rgba(18,18,18,0.22)'}, darkFg: paper, darkInput: {background: 'rgba(255,255,255,0.07)', border: '2px solid rgba(255,255,255,0.12)'},
      bezel: {background: ink, boxShadow: '0 30px 60px rgba(18,18,18,0.25)'},
      bars: [ink, ac, ac2, ink, ac, ac2],
      artFills: [ac2, ac, ink], frame: `0 0 0 6px ${paper}, 0 24px 50px rgba(18,18,18,0.18)`,
    };
  }
  const G = {
    accent: `linear-gradient(110deg, ${P.accent}, ${P.amber})`,
    accent2: `linear-gradient(135deg, ${P.violet}, ${P.pink})`,
    cool: `linear-gradient(135deg, ${P.cyan}, ${P.violet})`,
    mint: `linear-gradient(135deg, ${P.mint}, ${P.cyan})`,
    amber: `linear-gradient(135deg, ${P.amber}, ${P.accent})`,
    paper: '#FFFFFF', ink: P.ink,
  };
  return {
    flat: false, ink: P.ink, paper: '#FFFFFF', accent: P.accent, accent2: P.violet,
    fg: '#FFFFFF', fgSoft: 'rgba(255,255,255,0.62)', textFx: 'drop-shadow(0 14px 36px rgba(6,3,28,0.5))',
    fill: G, onFill: (tone) => (tone === 'accent' || tone === 'amber' || tone === 'mint' || tone === 'paper' ? P.ink : '#FFFFFF'),
    panel: (dark = 0.38) => glass(dark), panelFg: '#FFFFFF', panelSub: 'rgba(255,255,255,0.62)',
    chip: glass(0.55), chipFg: '#FFFFFF', dot: P.amber, glow: true,
    solidShadow: (c) => `0 16px 40px ${rgba(c || P.accent, 0.35)}, inset 0 1.5px 0 rgba(255,255,255,0.45)`,
    ok: G.mint, okFg: P.ink, hi: P.amber, line: '#FFFFFF', lineSoft: 'rgba(255,255,255,0.55)',
    darkPanel: {background: 'linear-gradient(170deg, #1E1846, #120E2C)'}, darkFg: '#FFFFFF', darkInput: {background: 'rgba(255,255,255,0.05)', border: '2px solid rgba(255,255,255,0.1)'},
    bezel: {background: 'linear-gradient(145deg, #3A3460, #120E28 55%, #2A2450)'},
    bars: [P.violet, P.pink, P.accent, P.accent, P.amber, P.amber],
    artFills: [G.accent2, G.accent, G.cool], frame: '0 0 0 3px rgba(255,255,255,0.25), 0 30px 70px rgba(0,0,0,0.45)',
  };
};
const glint = (r) => `M0 ${-r} L${r * 0.09} ${-r * 0.09} L${r} 0 L${r * 0.09} ${r * 0.09} L0 ${r} L${-r * 0.09} ${r * 0.09} L${-r} 0 L${-r * 0.09} ${-r * 0.09}Z`;

const SvgDefs = ({id = 'mm'}) => (
  <defs>
    <filter id={`${id}Glow`} x="-100%" y="-100%" width="300%" height="300%"><feGaussianBlur stdDeviation="7" /></filter>
  </defs>
);

// accent text: gradient with one light sweep (aurora) or the solid accent colour (flat)
export const AccentText = ({E, children, shine = -1}) => {
  const T = theme(E), C = E.style.colors, P = pal(C);
  if (T.flat) return <span style={{color: T.accent}}>{children}</span>;
  const base = {backgroundImage: `linear-gradient(100deg, ${P.accent} 0%, ${P.amber} 100%)`, WebkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent'};
  return (
    <span style={{position: 'relative', display: 'inline-block'}}>
      <span style={base}>{children}</span>
      {shine > 0 && shine < 1 ? (
        <span style={{position: 'absolute', inset: 0, color: 'transparent', WebkitBackgroundClip: 'text', backgroundClip: 'text',
          backgroundImage: 'linear-gradient(100deg, rgba(255,255,255,0) 42%, rgba(255,250,235,0.95) 50%, rgba(255,255,255,0) 58%)',
          backgroundSize: '300% 100%', backgroundPosition: `${lerp(85, 15, shine)}% 0`}}>{children}</span>
      ) : null}
    </span>
  );
};

// one word rising out of its own mask: p = entrance 0..1, q = exit 0..1
export const MaskWord = ({p, q = 0, children, rot = 3, blurK = 10}) => {
  const b = (1 - p) * blurK + q * blurK * 0.8;
  return (
    <span style={{display: 'inline-block', overflow: 'hidden', padding: '0.08em 0.03em 0.16em', margin: '-0.08em -0.03em -0.16em', verticalAlign: 'top'}}>
      <span style={{display: 'inline-block', transform: `translateY(${((1 - p) - q) * 112}%) rotate(${(1 - p) * rot - q * rot}deg)`, transformOrigin: '0% 100%',
        filter: b > 0.4 ? `blur(${b.toFixed(1)}px)` : 'none', opacity: clamp(p * 1.8) * (1 - q * 0.6)}}>{children}</span>
    </span>
  );
};

// ------------------------------------------------------------------------------------------------ backdrops
export const AuroraBackdrop = ({E, t}) => {
  const C = E.style.colors, B = E.style.backdrop || {}, W = E.width, H = E.height, P = pal(C);
  const blobs = B.blobs || [
    [P.violet, 0.9, 1.25, 0.12, 0.2], [C.coral || '#FF5E5B', 0.72, 1.15, 0.92, 0.55],
    [P.amber, 0.5, 0.95, 0.25, 0.95], [P.cyan, 0.26, 0.72, 0.85, 0.08], [P.pink, 0.34, 0.8, 0.45, 0.62],
  ];
  return (
    <AbsoluteFill style={{background: C.bg || '#0E0B26', overflow: 'hidden'}}>
      {blobs.map(([col, a, sz, bx, by], i) => {
        const s = W * sz;
        const cx = W * (bx + 0.11 * Math.sin(t * 0.19 + i * 1.7)), cy = H * (by + 0.07 * Math.cos(t * 0.15 + i * 2.3));
        const pulse = 1 + 0.06 * Math.sin(t * 0.33 + i);
        return <div key={i} style={{position: 'absolute', left: cx - s * pulse / 2, top: cy - s * pulse / 2, width: s * pulse, height: s * pulse, borderRadius: '50%',
          background: `radial-gradient(circle, ${rgba(col, a)} 0%, ${rgba(col, a * 0.45)} 32%, ${rgba(col, 0)} 66%)`}} />;
      })}
      <div style={{position: 'absolute', left: -W * 0.5, right: -W * 0.5, top: H * 0.62, height: H * 0.6, opacity: B.grid ?? 0.18,
        transform: 'perspective(900px) rotateX(64deg)', transformOrigin: '50% 0%',
        backgroundImage: 'linear-gradient(rgba(255,255,255,0.5) 1.5px, transparent 1.5px), linear-gradient(90deg, rgba(255,255,255,0.5) 1.5px, transparent 1.5px)',
        backgroundSize: '120px 120px', backgroundPosition: `0 ${(t * 30) % 120}px`,
        WebkitMaskImage: 'linear-gradient(180deg, rgba(0,0,0,0.9), rgba(0,0,0,0) 75%)', maskImage: 'linear-gradient(180deg, rgba(0,0,0,0.9), rgba(0,0,0,0) 75%)'}} />
      <div style={{position: 'absolute', inset: 0, background: 'radial-gradient(ellipse at 50% 45%, rgba(0,0,0,0) 45%, rgba(4,2,16,0.55) 100%)'}} />
    </AbsoluteFill>
  );
};

// flat look: warm paper, a fine drafting grid that drifts slowly, crop marks in the corners. No gradients.
export const PaperBackdrop = ({E, t}) => {
  const C = E.style.colors, B = E.style.backdrop || {}, W = E.width, H = E.height;
  const ink = C.ink || '#121212', cell = B.cell || 90;
  const dy = (t * (B.drift ?? 8)) % (cell * 4);
  const minor = rgba(ink, B.minor ?? 0.05), major = rgba(ink, B.major ?? 0.09);
  return (
    <AbsoluteFill style={{background: C.bg || '#F1EEE7', overflow: 'hidden'}}>
      <div style={{position: 'absolute', left: 0, right: 0, top: -cell * 4, bottom: -cell * 4, transform: `translateY(${dy}px)`,
        backgroundImage: `linear-gradient(${major} 2px, transparent 2px), linear-gradient(90deg, ${major} 2px, transparent 2px),` +
          `linear-gradient(${minor} 1px, transparent 1px), linear-gradient(90deg, ${minor} 1px, transparent 1px)`,
        backgroundSize: `${cell * 4}px ${cell * 4}px, ${cell * 4}px ${cell * 4}px, ${cell}px ${cell}px, ${cell}px ${cell}px`,
        backgroundPosition: `${(W % (cell * 4)) / 2}px 0`}} />
      <svg width={W} height={H} style={{position: 'absolute', inset: 0}}>
        {[[60, 60, 1, 1], [W - 60, 60, -1, 1], [60, H - 60, 1, -1], [W - 60, H - 60, -1, -1]].map(([x, y, sx, sy], i) => (
          <path key={i} d={`M${x} ${y + sy * 34} L${x} ${y} L${x + sx * 34} ${y}`} stroke={ink} strokeWidth={3} fill="none" opacity={0.5} />
        ))}
      </svg>
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ small pieces
export const Chip = ({E, text, size = 38, dot = true, tone = 'glass', icon, style}) => {
  const T = theme(E);
  const solid = tone !== 'glass';
  const fg = solid ? T.onFill(tone) : T.chipFg;
  const bg = solid ? {background: T.fill[tone] || T.fill.accent, boxShadow: T.solidShadow()} : T.chip;
  return (
    <div style={{display: 'inline-flex', alignItems: 'center', gap: size * 0.36, padding: `${size * 0.3}px ${size * 0.62}px ${size * 0.32}px`,
      borderRadius: T.flat ? size * 0.3 : 999, ...bg, color: fg, fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: size, lineHeight: 1,
      letterSpacing: '-0.01em', whiteSpace: 'nowrap', ...style}}>
      {dot && !icon ? <span style={{width: size * 0.36, height: size * 0.36, borderRadius: '50%', background: solid ? fg : T.dot,
        boxShadow: !solid && T.glow ? `0 0 ${size * 0.5}px ${T.dot}` : 'none'}} /> : null}
      {icon ? <Icon name={icon} size={size * 0.95} color={fg} stroke={2.6} /> : null}
      <span>{text}</span>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ HeroTitle
// {text, accent, at, kicker, kickerAt, tag, tagAt, tagIcon, x, y, size,
//  layout: 'center' (default) | 'left' | 'right'   where the block sits (x = centre, left edge or right edge)
//  stack: true                                    one word per line (poster stack)
//  fx: 'rise' (default, masked rise / flat block wipe) | 'slam' (scale-in with motion blur) | 'slide' (push in sideways
//      with directional blur, from: 'left'|'right') | 'type' (letters typed with a caret, cps letters per second)
//  sizes: [..]                                    per-word size multipliers, e.g. [0.55, 1] for a small lead word}
export const HeroTitle = ({E, t, o}) => {
  const W = E.width, F = E.style.fonts, T = theme(E), D = dispFont(E);
  const at = o.at ?? o.start, kAt = o.kickerAt ?? o.start, tagAt = o.tagAt ?? at + 0.3;
  const size = o.size || 150, y = o.y ?? 1000, end = o.end;
  const lay = o.layout || 'center', stack = !!o.stack, kind = o.fx || 'rise';
  const align = lay === 'left' ? 'flex-start' : lay === 'right' ? 'flex-end' : 'center';
  const x = o.x ?? (lay === 'left' ? 80 : lay === 'right' ? W - 80 : W / 2);
  const box = lay === 'left' ? {left: x, width: W - x - 40} : lay === 'right' ? {left: 40, width: x - 40} : {left: x - W / 2, width: W};
  const words = String(o.text || '').split(/\s+/).filter(Boolean);
  const accent = String(o.accent ?? words[words.length - 1] ?? '').toLowerCase().split(/\s+/).map((a) => a.replace(/[^\w-]/g, ''));
  const outAt = end != null ? end - 0.36 : null;
  const exitP = (k) => (outAt != null ? tw(t, outAt + k * 0.035, 0.3, qIn) : 0);
  const float = T.flat ? 0 : Math.sin((t - at) * 1.5) * 3;
  const kp = tw(t, kAt, 0.6), kq = exitP(0);
  const tp = tw(t, tagAt, 0.65, eOut), tq = exitP(2);
  const tagTone = o.tagTone || (T.flat ? 'accent2' : 'accent');
  // typing: each word starts when the previous one is typed
  const cps = o.cps || 24;
  const starts = []; let acc = 0;
  words.forEach((w, i) => { starts.push(kind === 'type' ? at + acc / cps : at + i * (kind === 'slam' ? 0.11 : 0.075)); acc += w.length + 1; });
  const typedEnd = at + acc / cps;
  const dirOf = (i) => (o.from === 'right' ? 1 : o.from === 'left' ? -1 : lay === 'right' ? 1 : lay === 'left' ? -1 : (i % 2 ? 1 : -1));
  const renderWord = (w, i) => {
    const a = starts[i], wsz = size * ((o.sizes || [])[i] ?? 1);
    const isAcc = accent.includes(w.toLowerCase().replace(/[^\w-]/g, ''));
    const q = exitP(i);
    const pad = '0.04em 0.16em 0.1em';
    const block = (inner, scaleX = 1, origin = '0% 50%') => (
      <span key={i} style={{position: 'relative', display: 'inline-block', padding: pad, fontSize: wsz}}>
        <span style={{position: 'absolute', inset: 0, background: isAcc ? T.accent : T.ink, borderRadius: wsz * 0.06,
          transformOrigin: q > 0 ? '100% 50%' : origin, transform: `scaleX(${q > 0 ? 1 - q : scaleX})`}} />
        <span style={{position: 'relative', color: isAcc ? T.onFill('accent') : T.paper}}>{inner}</span>
      </span>
    );
    const colored = (txt) => (T.flat ? txt : (isAcc ? <AccentText E={E} shine={prog(t, a + 0.45, a + 1.25)}>{txt}</AccentText> : txt));
    if (kind === 'rise') {
      if (T.flat) return block(<MaskWord p={tw(t, a + 0.08, 0.7)} q={q} blurK={6}>{w}</MaskWord>, tw(t, a - 0.05, 0.5, eOut));
      return <span key={i} style={{fontSize: wsz}}><MaskWord p={tw(t, a, 0.72)} q={q}>{colored(w)}</MaskWord></span>;
    }
    const wrap = (inner, style) => (
      <span key={i} style={{display: 'inline-block', ...style}}>{inner}</span>
    );
    const exitStyle = {opacity: 1 - q, transform: `translateY(${-q * 30}%)`, filter: q > 0.02 ? `blur(${(q * 8).toFixed(1)}px)` : 'none'};
    if (kind === 'slam') {
      if (t < a) return <span key={i} style={{display: 'inline-block', fontSize: wsz, padding: T.flat ? pad : 0, visibility: 'hidden'}}>{w}</span>;
      const sf = (k) => lerp(1.6, 1, eOut(prog(k, a, a + 0.42)));
      const v = Math.abs(vel(sf, t)), bl = Math.min(14, shutterBlur(v * wsz * 2.2));
      const st = {transform: `scale(${sf(t)})`, opacity: clamp(prog(t, a, a + 0.1) * 1.2), filter: bl > 0.4 ? `blur(${bl.toFixed(1)}px)` : 'none'};
      const inner = T.flat ? block(w) : <span style={{fontSize: wsz}}>{colored(w)}</span>;
      return wrap(wrap(inner, st), exitStyle);
    }
    if (kind === 'slide') {
      const dir = dirOf(i);
      const xf = (k) => (1 - qOut(prog(k, a, a + 0.6))) * dir * W * 0.55;
      const v = Math.abs(vel(xf, t)), bx = Math.min(28, shutterBlur(v));
      const st = {transform: `translateX(${xf(t).toFixed(1)}px)`, opacity: clamp(prog(t, a, a + 0.15) * 1.5), filter: dirBlur(bx, 0)};
      const inner = T.flat ? block(w) : <span style={{fontSize: wsz}}>{colored(w)}</span>;
      return wrap(wrap(inner, st), exitStyle);
    }
    // type: letters appear one by one, caret on the word being typed
    const n = Math.max(0, Math.min(w.length, Math.floor((t - a) * cps)));
    if (n <= 0) return <span key={i} style={{display: 'inline-block', fontSize: wsz, padding: T.flat ? pad : 0, visibility: 'hidden'}}>{w}</span>;
    const typing = n < w.length || (i === words.length - 1 && t < typedEnd + 0.5);
    const caret = typing ? <span style={{display: 'inline-block', width: Math.max(4, wsz * 0.06), height: wsz * 0.8, marginLeft: wsz * 0.04, verticalAlign: '-0.05em',
      background: T.flat ? (isAcc ? T.ink : T.accent) : T.hi, opacity: 0.4 + 0.6 * (0.5 + 0.5 * Math.cos(t * 10))}} /> : null;
    const shown = <>{w.slice(0, n)}<span style={{opacity: 0}}>{w.slice(n)}</span>{caret}</>;
    const inner = T.flat ? block(shown) : <span style={{fontSize: wsz}}>{colored(shown)}</span>;
    return wrap(inner, exitStyle);
  };
  return (
    <div style={{position: 'absolute', ...box, top: y, transform: `translateY(calc(-50% + ${float}px))`,
      display: 'flex', flexDirection: 'column', alignItems: align, pointerEvents: 'none'}}>
      {o.kicker && t >= kAt ? (
        <div style={{marginBottom: size * (T.flat ? 0.08 : 0.12), transform: `translateX(${o.kickerX ?? (lay === 'center' ? -size * 0.5 : 0)}px) translateY(${(1 - kp) * 26 - kq * 20}px)`,
          opacity: clamp(kp * 1.6) * (1 - kq), filter: fx(0, 0, (1 - kp) * 9 + kq * 8)}}>
          <Chip E={E} text={o.kicker} size={o.kickerSize || 40} tone={T.flat ? 'paper' : 'glass'} />
        </div>
      ) : null}
      <div style={{display: 'flex', flexDirection: stack ? 'column' : 'row', flexWrap: stack ? 'nowrap' : 'wrap', justifyContent: stack ? 'flex-start' : align,
        alignItems: stack ? align : 'baseline', columnGap: T.flat ? size * 0.08 : size * 0.24, rowGap: T.flat ? size * 0.06 : 0,
        maxWidth: box.width - 40, fontFamily: D.fontFamily, fontWeight: D.fontWeight, fontSize: size, lineHeight: 0.98, letterSpacing: '-0.04em', color: T.flat ? T.paper : '#FFFFFF',
        filter: T.textFx}}>
        {words.map(renderWord)}
      </div>
      {o.tag && t >= tagAt ? (
        <div style={{marginTop: size * (T.flat ? 0.1 : 0.14), transform: `translateX(${o.tagX ?? (lay === 'center' ? size * 0.45 : 0)}px) translateY(${-tq * 18}px)`, opacity: 1 - tq,
          clipPath: `inset(-20% ${(1 - tp) * 100}% -20% 0 round ${T.flat ? 12 : 999}px)`}}>
          <div style={{display: 'inline-flex', alignItems: 'center', gap: 14, padding: '16px 30px 17px', borderRadius: T.flat ? 12 : 999,
            background: T.fill[tagTone], boxShadow: T.solidShadow(), color: T.onFill(tagTone),
            fontFamily: F.ui, fontWeight: 800, fontSize: o.tagSize || 40, lineHeight: 1, whiteSpace: 'nowrap'}}>
            <span style={{transform: `translateX(${(1 - tw(t, tagAt + 0.1, 0.5)) * -24}px)`, display: 'inline-block'}}>{o.tag}</span>
            {o.tagIcon ? <span style={{display: 'inline-block', transform: `translateY(${Math.sin((t - tagAt) * 6) * 3}px)`}}>
              <Icon name={o.tagIcon} size={(o.tagSize || 40) * 0.95} color={T.onFill(tagTone)} stroke={3} /></span> : null}
          </div>
        </div>
      ) : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Accents
// Face-tracked, visible only while the face is full frame: 'glints', 'brackets' (focus corners), 'orbit'.
export const Accents = ({E, t, o}) => {
  const f = faceOnCanvas(E, t);
  if (!f) return null;
  const T = theme(E), C = E.style.colors, P = pal(C), W = E.width, H = E.height, h = f.h;
  const items = o.items || ['glints'];
  const k = tw(t, o.start, 0.7) * (o.end != null ? 1 - tw(t, o.end - 0.3, 0.3, qIn) : 1);
  if (k <= 0.01) return null;
  const has = (n) => items.includes(n);
  const id = `ac${Math.round((o.start || 0) * 100)}`;
  const glints = [[-0.92, -0.62, 0.11, 0], [0.95, -0.4, 0.085, 1.9], [1.02, 0.5, 0.07, 3.3], [-0.98, 0.36, 0.06, 4.6]];
  const bx0 = f.x - h * 0.74, bx1 = f.x + h * 0.74, by0 = f.y - h * 0.98, by1 = f.y + h * 0.86, L = h * 0.22;
  const bk = tw(t, o.start, 0.8);
  const bs = (1 - bk) * 0.25 + 0.012 * Math.sin(t * 2.2);
  return (
    <svg width={W} height={H} style={{position: 'absolute', inset: 0, overflow: 'visible', pointerEvents: 'none', opacity: k}}>
      <SvgDefs id={id} />
      {has('orbit') ? (() => {
        const cx = f.x, cy = f.y - h * 0.62, rx = h * 0.72, ry = h * 0.17, ang = (t - o.start) * 1.6;
        const dx = cx + Math.cos(ang) * rx, dy = cy + Math.sin(ang) * ry, front = Math.sin(ang) > 0;
        const dotC = T.flat ? T.accent : P.amber;
        return (
          <g transform={`rotate(-12 ${cx} ${cy})`}>
            <ellipse cx={cx} cy={cy} rx={rx} ry={ry} fill="none" stroke={T.flat ? '#FFFFFF' : P.violet} strokeWidth={h * 0.012} opacity={0.55} />
            <circle cx={dx} cy={dy} r={h * (front ? 0.035 : 0.022)} fill={dotC} opacity={front ? 1 : 0.45} />
            {T.glow ? <circle cx={dx} cy={dy} r={h * 0.08} fill={dotC} opacity={front ? 0.5 : 0.2} filter={`url(#${id}Glow)`} /> : null}
          </g>
        );
      })() : null}
      {has('brackets') ? (
        <g transform={`translate(${f.x} ${f.y - h * 0.06}) scale(${1 + bs}) translate(${-f.x} ${-f.y + h * 0.06})`} fill="none" stroke="#FFFFFF"
          strokeWidth={Math.max(4, h * 0.014)} strokeLinecap={T.flat ? 'square' : 'round'} strokeLinejoin="round" opacity={0.92}>
          {[[bx0, by0, 1, 1], [bx1, by0, -1, 1], [bx0, by1, 1, -1], [bx1, by1, -1, -1]].map(([x, y, sx, sy], i) => (
            <path key={i} d={T.flat ? `M${x} ${y + sy * L} L${x} ${y} L${x + sx * L} ${y}` : `M${x} ${y + sy * L} L${x} ${y + sy * h * 0.04} Q${x} ${y} ${x + sx * h * 0.04} ${y} L${x + sx * L} ${y}`} />
          ))}
        </g>
      ) : null}
      {has('glints') ? glints.map(([dx, dy, r, ph], i) => {
        const tw_ = 0.5 + 0.5 * Math.sin(t * 2.1 + ph);
        const s = h * r * (0.35 + 0.65 * tw_ * tw_) * Math.min(1, k * 1.2);
        const x = f.x + dx * h, y = f.y + dy * h;
        if (T.flat) {
          // flat: a crisp plus mark, no glow
          const a = s * 0.9;
          return <path key={i} d={`M${x - a} ${y} L${x + a} ${y} M${x} ${y - a} L${x} ${y + a}`} stroke={i % 2 ? T.accent : '#FFFFFF'} strokeWidth={Math.max(3, h * 0.012)}
            strokeLinecap="square" transform={`rotate(${Math.sin(t * 0.8 + ph) * 8} ${x} ${y})`} />;
        }
        return (
          <g key={i} transform={`translate(${x} ${y}) rotate(${t * 12 + ph * 20})`}>
            <circle r={s * 0.9} fill={i % 2 ? P.amber : '#FFFFFF'} opacity={0.35} filter={`url(#${id}Glow)`} />
            <path d={glint(s)} fill="#FFFFFF" />
            <rect x={-s * 2.2} y={-0.8} width={s * 4.4} height={1.6} fill="#FFFFFF" opacity={0.5 * tw_} />
          </g>
        );
      }) : null}
    </svg>
  );
};

// ------------------------------------------------------------------------------------------------ ShapeWipe
// Cover transition centred on `at` (the switch happens under the cover).
//   orb : a circle grows from (x, y) until it covers the frame, then a circular hole opens from the same point
//   bars: bars sweep up one after another, cover, and continue up and away
export const ShapeWipe = ({E, t, o}) => {
  const C = E.style.colors, W = E.width, H = E.height, P = pal(C), T = theme(E);
  const at = o.at ?? o.start + 0.34, inD = o.inDur ?? 0.34, outD = o.outDur ?? 0.4;
  if (t < at - inD || (t > at + outD && !(o.shape === 'iris' && o.hold))) return null;
  const id = `sw${Math.round(at * 1000)}`;
  if (o.shape === 'bloom') {
    // light swells from the centre into full white at `at`, then fades away on the new shot
    const inP = cInOut(prog(t, at - inD, at)), outP = qOut(prog(t, at, at + outD));
    const a = t < at ? inP : 1 - outP, r = t < at ? lerp(12, 150, inP) : 150;
    const tint = o.color || '#FFF8EE';
    return (
      <AbsoluteFill style={{pointerEvents: 'none'}}>
        <AbsoluteFill style={{background: `radial-gradient(circle at ${(o.x ?? 0.5) * 100}% ${(o.y ?? 0.42) * 100}%, ${rgba(tint, a)} 0%, ${rgba(tint, a * 0.85)} ${r * 0.4}%, ${rgba(tint, 0)} ${r}%)`, mixBlendMode: 'screen'}} />
        <AbsoluteFill style={{background: tint, opacity: t < at ? Math.pow(inP, 3) : Math.pow(1 - outP, 1.4)}} />
      </AbsoluteFill>
    );
  }
  if (o.shape === 'iris') {
    // a black iris closes onto (x, y) and reopens on the new shot; hold: true stays closed (an ending)
    const ox = (o.x ?? 0.5) * W, oy = (o.y ?? 0.42) * H;
    const R = Math.hypot(Math.max(ox, W - ox), Math.max(oy, H - oy)) * 1.03;
    const hole = t < at ? R * (1 - cInOut(prog(t, at - inD, at))) : (o.hold ? 0 : R * qOut(prog(t, at, at + outD)));
    return (
      <svg width={W} height={H} style={{position: 'absolute', inset: 0}}>
        <defs><mask id={`${id}i`}><rect width={W} height={H} fill="#fff" /><circle cx={ox} cy={oy} r={Math.max(0, hole)} fill="#000" /></mask></defs>
        <rect width={W} height={H} fill={o.color || '#000000'} mask={`url(#${id}i)`} />
      </svg>
    );
  }
  if ((o.shape || 'orb') === 'bars') {
    const n = o.bars || 6, bw = W / n + 2, r = T.flat ? 0 : bw / 2, BH = H + bw * 2;
    const cols = T.bars;
    return (
      <svg width={W} height={H} style={{position: 'absolute', inset: 0, overflow: 'visible', transform: o.dir === 'down' ? 'scaleY(-1)' : 'none'}}>
        <defs>{T.flat ? null : Array.from({length: n}).map((_, i) => (
          <linearGradient key={i} id={`${id}g${i}`} x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stopColor={cols[i % cols.length]} /><stop offset="1" stopColor={cols[(i + 1) % cols.length]} />
          </linearGradient>))}
        </defs>
        {Array.from({length: n}).map((_, i) => {
          const d = i * 0.028;
          const y = (k) => {
            const pin = qInOut(prog(k, at - inD + d, at - inD + d + (inD - (n - 1) * 0.028) - 0.01));
            const pout = qInOut(prog(k, at + 0.02 + d, at + 0.02 + d + outD - (n - 1) * 0.028 - 0.02));
            return lerp(H + 4, -r, pin) + lerp(0, -(BH - r + 4), pout);
          };
          const yy = y(t), v = Math.abs(vel(y, t));
          return <rect key={i} x={i * (W / n) - 1} y={yy} width={bw} height={BH} rx={r}
            fill={T.flat ? cols[i % cols.length] : `url(#${id}g${i})`}
            style={{filter: dirBlur(0, Math.min(24, shutterBlur(v) * 0.7))}} />;
        })}
      </svg>
    );
  }
  const ox = (o.x ?? 0.5) * W, oy = (o.y ?? 0.46) * H;
  const R = Math.hypot(Math.max(ox, W - ox), Math.max(oy, H - oy)) * 1.03;
  const grow = t < at ? cInOut(prog(t, at - inD, at)) : 1;
  const hole = t >= at ? qOut(prog(t, at, at + outD)) * R * 1.02 : 0;
  const r1 = R * grow;
  const c1 = pick(C, o.color, P.violet), c2 = pick(C, o.color2, P.accent), c3 = pick(C, o.color3, P.amber);
  return (
    <svg width={W} height={H} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
      <defs>
        {T.flat ? null : (
          <radialGradient id={`${id}rg`} cx={ox} cy={oy} r={R} gradientUnits="userSpaceOnUse">
            <stop offset="0" stopColor={c3} /><stop offset="0.45" stopColor={c2} /><stop offset="1" stopColor={c1} />
          </radialGradient>
        )}
        <mask id={`${id}m`}><rect width={W} height={H} fill="#fff" /><circle cx={ox} cy={oy} r={hole} fill="#000" /></mask>
        <filter id={`${id}gl`} x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="10" /></filter>
      </defs>
      <g mask={`url(#${id}m)`}>
        <circle cx={ox} cy={oy} r={r1} fill={T.flat ? pick(C, o.color, T.accent) : `url(#${id}rg)`} />
      </g>
      {T.flat ? (
        // flat: a crisp ink ring trails the edges instead of a glow
        <>
          {t < at && r1 > 30 ? <circle cx={ox} cy={oy} r={r1 - 30} fill="none" stroke={T.ink} strokeWidth={14} /> : null}
          {t >= at && hole < R ? <circle cx={ox} cy={oy} r={hole + 18} fill="none" stroke={T.ink} strokeWidth={14} opacity={1 - prog(t, at + outD * 0.6, at + outD)} /> : null}
        </>
      ) : (
        <>
          {t < at ? <circle cx={ox} cy={oy} r={r1} fill="none" stroke="#FFF6E8" strokeWidth={10} opacity={0.75} filter={`url(#${id}gl)`} /> : null}
          {t >= at && hole < R ? <circle cx={ox} cy={oy} r={hole} fill="none" stroke="#FFF6E8" strokeWidth={8} opacity={0.9 * (1 - prog(t, at, at + outD))} filter={`url(#${id}gl)`} /> : null}
        </>
      )}
    </svg>
  );
};

// ------------------------------------------------------------------------------------------------ live footage clip
export const LiveClip = ({E, t, w, h, grade = 1, zoom = 1, fy = 0.42, radius = 0}) => {
  const vw = E.footage.width, vh = E.footage.height;
  const c = Math.max(w / vw, h / vh) * zoom;
  const VW = vw * c, VH = vh * c;
  const face = sample(E.faces, t) || [0.5, 0.42, 0.2];
  const tx = clamp(w * 0.5 - face[0] * VW, w - VW, 0), ty = clamp(h * fy - face[1] * VH, h - VH, 0);
  const last = E.footage.duration - 1 / E.fps;
  const vid = <OffthreadVideo src={staticFile(E.footage.src)} muted style={{position: 'absolute', left: 0, top: 0, width: VW, height: VH,
    maxWidth: 'none', transform: `translate(${tx}px, ${ty}px)`, filter: gradeFilter(grade, E.style.grade)}} />;
  return (
    <div style={{position: 'relative', width: w, height: h, overflow: 'hidden', borderRadius: radius, background: '#1B1640'}}>
      {t > last ? <Freeze frame={Math.max(0, Math.round(last * E.fps) - 1)}>{vid}</Freeze> : vid}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ scene frame
// enter 'pop' (default: eased scale + blur-to-sharp), 'slide' (push in from the right with motion blur) or 'cut';
// exit 'fade' (default), 'slide' (push out to the left) or 'cut'. A slow camera push runs for the whole scene.
export const SceneFrame = ({E, t, o, children}) => {
  const W = E.width;
  const st = o.start, en = o.end;
  let tx = 0, sc = 1, op = 1, bx = 0, bl = 0;
  if ((o.enter || 'pop') === 'slide') {
    const f = (k) => (1 - qOut(prog(k, st, st + 0.55))) * W * 0.9;
    tx = f(t); bx = shutterBlur(Math.abs(vel(f, t)));
  } else if (o.enter !== 'cut') {
    const p = tw(t, st, 0.6);
    sc = 0.94 + 0.06 * p; op = clamp(p * 1.5); bl = (1 - p) * 14;
  }
  if (en != null) {
    const exit = o.exit || 'fade';
    if (exit === 'slide') {
      const g = (k) => -qIn(prog(k, en - 0.34, en)) * W * 0.9;
      tx += g(t); bx += shutterBlur(Math.abs(vel(g, t)));
    } else if (exit === 'fade') {
      const q = tw(t, en - 0.3, 0.3, qIn); op *= 1 - q; sc *= 1 + 0.04 * q; bl += q * 12;
    }
  }
  const push = 1 + (o.push ?? 0.035) * cInOut(prog(t, st, en ?? st + 4));
  const tp = tw(t, st + 0.1, 0.6);
  return (
    <AbsoluteFill style={{transform: `translateX(${tx.toFixed(1)}px) scale(${(sc * push).toFixed(4)})`, opacity: op, filter: fx(Math.min(24, bx), 0, bl)}}>
      {o.tag && t >= st + 0.1 ? (
        <div style={{position: 'absolute', left: o.tagX ?? 100, top: o.tagY ?? 330, opacity: clamp(tp * 1.6), transform: `translateY(${(1 - tp) * 24}px)`,
          filter: fx(0, 0, (1 - tp) * 8)}}>
          <Chip E={E} text={o.tag} size={o.tagSize || 40} tone={o.tagTone || 'glass'} icon={o.tagIcon} />
        </div>
      ) : null}
      {children}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ SceneStack
// {tag, items:[{title, sub, icon, tone, badge, at}], repeat, stagger, y, w, cardH}
export const SceneStack = ({E, t, o}) => {
  const W = E.width, T = theme(E);
  let items = o.items || [];
  if (o.repeat && items.length) items = Array.from({length: o.repeat}, (_, k) => ({...items[0], at: (items[0].at ?? o.start + 0.2) + k * (o.stagger ?? 0.16)}));
  const w = o.w || 900, ch = o.cardH || 168, gap = o.gap ?? 28, y0 = o.y ?? 470, x0 = (W - w) / 2;
  return (
    <SceneFrame E={E} t={t} o={o}>
      {items.map((it, k) => {
        const a = it.at ?? o.start + 0.2 + k * 0.25;
        if (t < a) return null;
        const p = tw(t, a, 0.7);
        const tone = it.tone || 'accent2';
        return (
          <div key={k} style={{position: 'absolute', left: x0, top: y0 + k * (ch + gap), width: w, height: ch, perspective: 1400}}>
            <div style={{width: '100%', height: '100%', borderRadius: T.flat ? 22 : 38, ...T.panel(0.38), display: 'flex', alignItems: 'center', gap: 28, padding: '0 30px',
              transform: `translateY(${(1 - p) * 80}px) rotateX(${(1 - p) * 38}deg)`, transformOrigin: '50% 100%', opacity: clamp(p * 1.6), filter: fx(0, 0, (1 - p) * 12)}}>
              <div style={{width: 110, height: 110, borderRadius: T.flat ? 18 : 32, flex: 'none', display: 'flex', alignItems: 'center', justifyContent: 'center',
                background: T.fill[tone] || T.fill.accent2, boxShadow: T.flat ? 'none' : 'inset 0 2px 0 rgba(255,255,255,0.4), 0 14px 30px rgba(0,0,0,0.25)'}}>
                <Icon name={it.icon || 'sparkle'} size={58} color={T.flat ? T.onFill(tone) : '#FFFFFF'} stroke={2.5} />
              </div>
              <div style={{flex: 1, minWidth: 0}}>
                <div style={{fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: it.size || 50, color: T.panelFg, lineHeight: 1.1, letterSpacing: '-0.02em', whiteSpace: 'nowrap'}}>{it.title}</div>
                {it.sub ? <div style={{fontFamily: E.style.fonts.ui, fontWeight: 600, fontSize: 32, color: T.panelSub, marginTop: 6, whiteSpace: 'nowrap'}}>{it.sub}</div> : null}
              </div>
              {it.badge ? <Chip E={E} text={it.badge} size={30} tone="mint" dot={false} /> : (
                <div style={{width: 70, height: 70, borderRadius: 35, background: T.ok, display: 'flex', alignItems: 'center', justifyContent: 'center',
                  transform: `scale(${tw(t, a + 0.35, 0.5, backOut)})`, boxShadow: T.glow ? `0 0 30px ${rgba(pal(E.style.colors).mint, 0.5)}` : 'none'}}>
                  <Icon name="check" size={40} color={T.okFg} stroke={3.4} />
                </div>
              )}
            </div>
          </div>
        );
      })}
    </SceneFrame>
  );
};

// ------------------------------------------------------------------------------------------------ SceneChat
// {tag, messages:[{text | media:'clip', side:'left'|'right', at, typed, typing, progress:{at, dur}, grade}], y, w}
export const SceneChat = ({E, t, o}) => {
  const W = E.width, T = theme(E), P = pal(E.style.colors);
  const msgs = o.messages || [];
  const w = o.w || 940, y0 = o.y ?? 400, fs = o.fontSize || 48, maxCh = o.maxChars || 22;
  const mw = o.mediaW || 330, mh = o.mediaH || 420;
  const lines = (s) => Math.max(1, Math.ceil(String(s).length / maxCh));
  let yy = y0;
  const pos = msgs.map((m) => { const h = m.media ? mh + 28 : lines(m.text) * fs * 1.22 + fs * 0.9 + (m.progress ? 46 : 0); const p = yy; yy += h + 26; return {top: p, h}; });
  const tk = msgs.findIndex((m) => m.typed);
  const inputY = o.inputY ?? (tk >= 0 ? pos[tk].top - 8 : yy);
  const typedMsg = msgs.find((m) => m.typed && t >= m.at - (m.typing ?? 1.0) && t < m.at);
  const barMsg = msgs.find((m) => m.typed && t >= m.at - (m.typing ?? 1.0) - 0.35 && t < m.at + 0.4);
  const barK = barMsg ? tw(t, barMsg.at - (barMsg.typing ?? 1.0) - 0.35, 0.45) * (1 - tw(t, barMsg.at + 0.1, 0.3, qIn)) : 0;
  // right bubbles: gradient accent (aurora) / solid ink (flat); left bubbles: glass (aurora) / paper card (flat)
  const rightBg = T.flat ? {background: T.ink} : {background: T.fill.accent, boxShadow: T.solidShadow()};
  const rightFg = T.flat ? T.paper : T.ink;
  const leftBg = T.panel(0.4), leftFg = T.panelFg;
  const R = T.flat ? 26 : 40;
  return (
    <SceneFrame E={E} t={t} o={o}>
      {msgs.map((m, k) => {
        const right = m.side === 'right';
        const x = right ? (W + w) / 2 : (W - w) / 2;
        const dots = m.typing && !m.typed && t >= m.at - m.typing && t < m.at;
        if (dots) {
          return (
            <div key={k} style={{position: 'absolute', top: pos[k].top, left: right ? undefined : x + 90, right: right ? W - x : undefined,
              padding: '24px 32px', borderRadius: R, ...(right ? rightBg : leftBg), display: 'flex', gap: 12}}>
              {[0, 1, 2].map((i) => <div key={i} style={{width: 16, height: 16, borderRadius: 8, background: right ? rightFg : leftFg,
                opacity: 0.4 + 0.6 * (0.5 + 0.5 * Math.sin((t - m.at) * 8 - i * 0.9))}} />)}
            </div>
          );
        }
        if (t < m.at) return null;
        const p = tw(t, m.at, 0.6);
        const pr = m.progress ? qInOut(clamp((t - m.progress.at) / (m.progress.dur || 0.8))) : 0;
        return (
          <div key={k} style={{position: 'absolute', top: pos[k].top, left: right ? undefined : x, right: right ? W - x : undefined,
            display: 'flex', alignItems: 'flex-end', gap: 18, flexDirection: right ? 'row-reverse' : 'row',
            transform: `translateY(${(1 - p) * 40}px) scale(${0.86 + 0.14 * p})`, transformOrigin: right ? '100% 100%' : '0 100%',
            opacity: clamp(p * 1.6), filter: fx(0, 0, (1 - p) * 10)}}>
            {!right ? (
              <div style={{width: 74, height: 74, borderRadius: T.flat ? 20 : 37, flex: 'none', background: T.fill.accent2, display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: T.glow ? `0 0 0 4px rgba(255,255,255,0.18), 0 0 30px ${rgba(P.violet, 0.5)}` : 'none'}}><Icon name="sparkle" size={36} color="#fff" stroke={2.4} /></div>
            ) : null}
            <div style={{maxWidth: w - 100, padding: m.media ? 12 : `${fs * 0.42}px ${fs * 0.7}px ${fs * 0.48}px`, borderRadius: R,
              borderBottomLeftRadius: right ? R : 8, borderBottomRightRadius: right ? 8 : R,
              ...(right ? rightBg : leftBg), color: right ? rightFg : leftFg, fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: fs, lineHeight: 1.22, letterSpacing: '-0.01em'}}>
              {m.media ? (
                <div style={{position: 'relative'}}>
                  <LiveClip E={E} t={t} w={mw} h={mh} grade={m.grade ?? 1} radius={T.flat ? 18 : 30} />
                  <div style={{position: 'absolute', left: mw / 2 - 36, top: mh / 2 - 36, width: 72, height: 72, borderRadius: 36,
                    ...(T.flat ? {background: T.paper} : glass(0.35)),
                    display: 'flex', alignItems: 'center', justifyContent: 'center', opacity: 1 - tw(t, m.at + 0.45, 0.35)}}>
                    <Icon name="play" size={32} color={T.flat ? T.ink : '#fff'} />
                  </div>
                </div>
              ) : m.text}
              {m.progress ? (
                <div style={{marginTop: 16, display: 'flex', alignItems: 'center', gap: 16}}>
                  <div style={{flex: 1, height: 14, borderRadius: 7, background: T.flat ? (right ? 'rgba(255,255,255,0.2)' : '#E6E2D9') : 'rgba(255,255,255,0.14)', overflow: 'hidden', minWidth: 280}}>
                    <div style={{width: `${pr * 100}%`, height: '100%', borderRadius: 7, background: T.flat ? T.accent : T.ok,
                      boxShadow: T.glow ? `0 0 16px ${rgba(P.mint, 0.7)}` : 'none'}} />
                  </div>
                  <div style={{width: 42, height: 42, borderRadius: 21, background: T.flat ? T.accent : T.ok, display: 'flex', alignItems: 'center', justifyContent: 'center',
                    transform: `scale(${tw(t, m.progress.at + (m.progress.dur || 0.8), 0.45, backOut)})`}}>
                    <Icon name="check" size={26} color={T.ink} stroke={3.4} />
                  </div>
                </div>
              ) : null}
            </div>
          </div>
        );
      })}
      {barK > 0.01 ? (() => {
        const m = typedMsg;
        const txt = m ? String(m.text) : '';
        const n = m ? Math.round(txt.length * clamp((t - (m.at - (m.typing ?? 1.0))) / ((m.typing ?? 1.0) * 0.85))) : 0;
        const send = msgs.find((mm) => mm.typed && t >= mm.at && t < mm.at + 0.3);
        const bump = send ? Math.sin(prog(t, send.at, send.at + 0.3) * Math.PI) * 0.14 : 0;
        return (
          <div style={{position: 'absolute', left: (W - w) / 2, top: inputY, width: w, height: 118, borderRadius: T.flat ? 24 : 59, ...T.panel(0.5),
            display: 'flex', alignItems: 'center', padding: '0 20px 0 42px', gap: 16,
            transform: `translateY(${(1 - barK) * 36}px) scale(${0.94 + 0.06 * barK})`, opacity: barK, filter: fx(0, 0, (1 - barK) * 10)}}>
            <div style={{flex: 1, fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: 44, color: T.panelFg, whiteSpace: 'nowrap', overflow: 'hidden'}}>
              {txt.slice(0, n)}{m ? <span style={{display: 'inline-block', width: 4, height: 44, marginLeft: 4, verticalAlign: 'middle', background: T.hi,
                opacity: 0.35 + 0.65 * (0.5 + 0.5 * Math.cos(t * 9))}} /> : null}
            </div>
            <div style={{width: 76, height: 76, borderRadius: T.flat ? 20 : 38, background: T.fill.accent, display: 'flex', alignItems: 'center', justifyContent: 'center',
              transform: `scale(${1 + bump})`}}><Icon name="send" size={38} color={T.onFill('accent')} stroke={2.6} /></div>
          </div>
        );
      })() : null}
    </SceneFrame>
  );
};

// ------------------------------------------------------------------------------------------------ ScenePrompt
// {prompt, at (typing start), typing, attach, attachAt, clipLabel, steps:[{text, at}], label, y, w}
export const ScenePrompt = ({E, t, o}) => {
  const W = E.width, T = theme(E), P = pal(E.style.colors);
  const w = o.w || 960, x0 = (W - w) / 2, y0 = o.y ?? 440;
  const steps = o.steps || [];
  const h = o.h || (400 + steps.length * 80);
  const typeAt = o.at ?? o.start + 0.5, typing = o.typing ?? 1.0;
  const txt = String(o.prompt || '');
  const n = Math.round(txt.length * clamp((t - typeAt) / typing));
  const sendAt = typeAt + typing + 0.08;
  const bump = Math.sin(prog(t, sendAt, sendAt + 0.3) * Math.PI) * 0.14;
  const win = tw(t, o.start, 0.75);
  const attachAt = o.attachAt ?? typeAt - 0.1;
  const slotX = x0 + 60, slotY = y0 + 150, sw = 170, sh = 226;
  const fl = (k) => qInOut(prog(k, attachAt - 0.6, attachAt));
  const fp = fl(t);
  const px = (k) => lerp(W - 80, slotX, fl(k)), py = (k) => lerp(y0 + h + 60, slotY, fl(k)) - Math.sin(fl(k) * Math.PI) * 140;
  const cv = Math.hypot(vel(px, t), vel(py, t));
  const R = T.flat ? 26 : 40;
  return (
    <SceneFrame E={E} t={t} o={o}>
      {T.glow ? <div style={{position: 'absolute', left: x0 - 120, top: y0 - 140, width: w + 240, height: h + 280, borderRadius: '50%', opacity: 0.8 * win,
        background: `radial-gradient(ellipse at 50% 50%, ${rgba(P.violet, 0.55)} 0%, ${rgba(P.accent, 0.18)} 45%, rgba(0,0,0,0) 70%)`}} /> : null}
      <div style={{position: 'absolute', left: x0, top: y0, width: w, height: h, perspective: 1800}}>
        <div style={{position: 'absolute', inset: 0, borderRadius: R, padding: T.flat ? 0 : 2.5,
          background: T.flat ? 'transparent' : `linear-gradient(135deg, ${rgba(P.violet, 0.9)}, rgba(255,255,255,0.12) 40%, ${rgba(P.accent, 0.8)})`,
          boxShadow: T.flat ? T.darkPanel.boxShadow : '0 40px 90px rgba(4,2,20,0.55)', transform: `translateY(${(1 - win) * 90}px) rotateX(${(1 - win) * 22}deg)`, transformOrigin: '50% 100%',
          opacity: clamp(win * 1.5), filter: fx(0, 0, (1 - win) * 12)}}>
          <div style={{width: '100%', height: '100%', borderRadius: T.flat ? R : 38, background: T.darkPanel.background, padding: 34}}>
            <div style={{display: 'flex', alignItems: 'center', gap: 18}}>
              <div style={{width: 70, height: 70, borderRadius: T.flat ? 18 : 22, background: T.fill.accent2, display: 'flex', alignItems: 'center', justifyContent: 'center',
                boxShadow: T.glow ? `0 0 30px ${rgba(P.violet, 0.55)}` : 'none'}}>
                <Icon name="sparkle" size={42} color="#fff" stroke={2.4} />
              </div>
              <div style={{fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: 46, color: T.darkFg, letterSpacing: '-0.02em'}}>{o.label || 'AI'}</div>
              <div style={{flex: 1}} />
              {[0, 1, 2].map((i) => <div key={i} style={{width: 14, height: 14, borderRadius: 7, background: 'rgba(255,255,255,0.22)'}} />)}
            </div>
            <div style={{marginTop: 28, height: 270, borderRadius: T.flat ? 18 : 30, ...T.darkInput,
              position: 'relative', padding: 24, paddingLeft: o.attach ? 24 + sw + 24 : 32}}>
              <div style={{fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: o.fontSize || 50, color: T.darkFg, lineHeight: 1.22, paddingTop: 8, letterSpacing: '-0.01em'}}>
                {txt.slice(0, n)}
                {t >= typeAt - 0.3 && t < sendAt + 0.3 ? <span style={{display: 'inline-block', width: 4, height: 46, marginLeft: 4, verticalAlign: 'middle',
                  background: T.hi, opacity: 0.35 + 0.65 * (0.5 + 0.5 * Math.cos(t * 9))}} /> : null}
              </div>
              <div style={{position: 'absolute', right: 22, bottom: 22, width: 76, height: 76, borderRadius: T.flat ? 20 : 38, background: T.fill.accent,
                display: 'flex', alignItems: 'center', justifyContent: 'center', transform: `scale(${1 + bump})`,
                boxShadow: T.glow ? `0 0 ${30 * bump * 6}px ${rgba(P.accent, 0.8)}` : 'none'}}>
                <Icon name="send" size={38} color={T.onFill('accent')} stroke={2.6} />
              </div>
            </div>
            <div style={{marginTop: 24, display: 'flex', flexDirection: 'column', gap: 18}}>
              {steps.map((s, k) => {
                if (t < s.at) return null;
                const p = tw(t, s.at, 0.55);
                const done = t >= s.at + 0.35;
                return (
                  <div key={k} style={{display: 'flex', alignItems: 'center', gap: 20, opacity: clamp(p * 1.6), transform: `translateX(${(1 - p) * 40}px)`, filter: fx(0, 0, (1 - p) * 8)}}>
                    <div style={{width: 48, height: 48, borderRadius: 24, display: 'flex', alignItems: 'center', justifyContent: 'center',
                      background: done ? (T.flat ? T.accent : T.ok) : 'rgba(255,255,255,0.1)'}}>
                      {done ? <div style={{transform: `scale(${tw(t, s.at + 0.35, 0.45, backOut)})`}}><Icon name="check" size={28} color={T.ink} stroke={3.4} /></div>
                        : <div style={{transform: `rotate(${(t - s.at) * 540}deg)`}}><Icon name="refresh" size={26} color="#fff" stroke={2.6} /></div>}
                    </div>
                    <div style={{fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: 42, color: T.darkFg}}>{s.text}</div>
                  </div>
                );
              })}
            </div>
          </div>
        </div>
      </div>
      {o.attach && t >= attachAt - 0.6 ? (() => {
        const cs = lerp(2.0, 1, fp);
        return (
          <div style={{position: 'absolute', left: px(t), top: py(t), transform: `scale(${cs}) rotate(${(1 - fp) * 10}deg)`, transformOrigin: '0 0',
            filter: fx(Math.min(24, shutterBlur(cv) * 0.6), Math.min(24, shutterBlur(cv) * 0.3), 0)}}>
            <div style={{borderRadius: T.flat ? 14 : 22, overflow: 'hidden', boxShadow: T.flat ? `0 0 0 4px ${T.paper}, 0 18px 36px rgba(18,18,18,0.3)` : '0 0 0 3px rgba(255,255,255,0.7), 0 20px 40px rgba(0,0,0,0.45)', position: 'relative'}}>
              <LiveClip E={E} t={t} w={sw} h={sh} grade={0} />
              <div style={{position: 'absolute', left: 10, bottom: 10, padding: '5px 10px', borderRadius: 8, ...(T.flat ? {background: T.ink} : glass(0.6)), color: '#fff',
                fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: 18}}>{o.clipLabel || 'MP4'}</div>
            </div>
          </div>
        );
      })() : null}
    </SceneFrame>
  );
};

// ------------------------------------------------------------------------------------------------ SceneFeatures
// {items:[{text, word, at, art, caption, chip, accent}], y, size, chipSize, artScale}
// art: cut | caption | motion | zoom | broll | sound | <icon name>
const Art = ({E, t, it, at}) => {
  const W = E.width, T = theme(E), P = pal(E.style.colors);
  const art = it.art || 'sparkle';
  const p = tw(t, at, 0.6);
  const box = {position: 'absolute', left: 0, top: 0, width: W, height: 420, display: 'flex', alignItems: 'center', justifyContent: 'center',
    transform: `translateY(${(1 - p) * 50}px) scale(${0.9 + 0.1 * p})`, opacity: clamp(p * 1.6), filter: fx(0, 0, (1 - p) * 12)};
  const fills = T.artFills;
  if (art === 'cut') {
    const snip = qInOut(prog(t, at + 0.25, at + 0.6));
    const head = qInOut(prog(t, at + 0.05, at + 0.5));
    const stripes = 'repeating-linear-gradient(90deg, rgba(255,255,255,0.16) 0 4px, transparent 4px 28px)';
    const block = (wd, key) => <div key={key} style={{width: wd, height: 130, borderRadius: T.flat ? 14 : 26,
      background: T.flat ? `${stripes}, ${T.accent}` : `${stripes}, ${T.fill.accent}`,
      boxShadow: T.flat ? 'none' : `0 18px 40px ${rgba(P.accent, 0.35)}, inset 0 2px 0 rgba(255,255,255,0.45)`}} />;
    return (
      <div style={box}>
        <div style={{display: 'flex', alignItems: 'center', gap: 16 * (1 - snip), position: 'relative'}}>
          {block(320, 'a')}
          <div style={{width: 160 * (1 - snip), height: 130, borderRadius: T.flat ? 14 : 26, overflow: 'hidden', opacity: 1 - snip,
            border: `3px dashed ${T.lineSoft}`, background: T.flat ? 'transparent' : 'rgba(255,255,255,0.06)'}} />
          {block(320, 'b')}
          <div style={{position: 'absolute', top: -40, bottom: -40, left: lerp(-30, 856 * (1 - snip * 0.19), head), width: 6, borderRadius: 3, background: T.flat ? T.ink : P.amber,
            boxShadow: T.glow ? `0 0 24px ${P.amber}` : 'none'}} />
        </div>
      </div>
    );
  }
  if (art === 'caption') {
    const cap = String(it.caption || it.text);
    const n = Math.round(cap.length * clamp((t - at - 0.15) / 0.4));
    return (
      <div style={box}>
        <div style={{position: 'relative', borderRadius: T.flat ? 20 : 34, overflow: 'hidden', boxShadow: T.frame}}>
          <LiveClip E={E} t={t} w={440} h={330} grade={1} zoom={1.25} fy={0.4} />
          <div style={{position: 'absolute', left: 0, right: 0, bottom: 26, display: 'flex', justifyContent: 'center'}}>
            {n > 0 ? <div style={{padding: '12px 26px', borderRadius: T.flat ? 12 : 999, ...(T.flat ? {background: T.ink} : glass(0.6)), fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: 34, color: '#fff'}}>{cap.slice(0, n)}</div> : null}
          </div>
        </div>
      </div>
    );
  }
  if (art === 'motion') {
    const shapes = [[fills[0], 'circle'], [fills[1], 'square'], [fills[2], 'pill']];
    return (
      <div style={box}>
        {shapes.map(([g, kind], i) => {
          const ang = (t - at) * 2.2 + i * 2.094;
          const depth = (Math.sin(ang) + 1) / 2;
          const s = 120 * (0.72 + 0.45 * depth);
          const x = W / 2 + Math.cos(ang) * 270 - s / 2, y = 210 + Math.sin(ang) * 70 - s / 2;
          return <div key={i} style={{position: 'absolute', left: x, top: y, width: kind === 'pill' ? s * 1.5 : s, height: s, zIndex: Math.round(depth * 10),
            borderRadius: kind === 'circle' || kind === 'pill' ? s : s * (T.flat ? 0.12 : 0.28), background: g, opacity: T.flat ? 1 : 0.55 + 0.45 * depth,
            transform: `rotate(${kind === 'square' ? (t - at) * 90 : 0}deg)`,
            boxShadow: T.flat ? 'none' : `inset 0 3px 0 rgba(255,255,255,0.45), 0 ${20 * depth}px ${40 * depth}px rgba(0,0,0,0.35)`}} />;
        })}
      </div>
    );
  }
  if (art === 'zoom') {
    const z = lerp(1, 1.65, qInOut(prog(t, at + 0.1, at + 0.7)));
    const L = 56, k = lerp(1.08, 1, qOut(prog(t, at, at + 0.6)));
    return (
      <div style={box}>
        <div style={{position: 'relative', borderRadius: T.flat ? 20 : 34, overflow: 'hidden', boxShadow: T.frame}}>
          <LiveClip E={E} t={t} w={320} h={390} grade={1} zoom={z} fy={0.45} />
        </div>
        <svg width={440} height={510} style={{position: 'absolute', left: W / 2 - 220, top: 210 - 255, overflow: 'visible', transform: `scale(${k})`}}>
          {[[0, 0, 1, 1], [440, 0, -1, 1], [0, 510, 1, -1], [440, 510, -1, -1]].map(([x, y, sx, sy], i) => (
            <path key={i} d={T.flat ? `M${x} ${y + sy * L} L${x} ${y} L${x + sx * L} ${y}` : `M${x} ${y + sy * L} L${x} ${y + sy * 14} Q${x} ${y} ${x + sx * 14} ${y} L${x + sx * L} ${y}`}
              stroke={T.line} strokeWidth={9} fill="none" strokeLinecap={T.flat ? 'square' : 'round'} />
          ))}
        </svg>
      </div>
    );
  }
  if (art === 'broll') {
    const cards = [[fills[0], -12, -200], [T.flat ? T.ink : fills[2], 0, 0], [fills[1], 12, 200]];
    return (
      <div style={box}>
        {cards.map(([g, r, dx], i) => {
          const q = tw(t, at + i * 0.07, 0.65);
          return (
            <div key={i} style={{position: 'absolute', left: W / 2 - 135 + dx * q, top: 50 + (1 - q) * 40, width: 270, height: 320, borderRadius: T.flat ? 16 : 30,
              border: T.flat ? `8px solid ${T.paper}` : '3px solid rgba(255,255,255,0.35)', background: g,
              boxShadow: T.flat ? '0 18px 36px rgba(18,18,18,0.18)' : '0 30px 60px rgba(0,0,0,0.4)', overflow: 'hidden', zIndex: i === 1 ? 2 : 1,
              transform: `rotate(${r * q}deg)`}}>
              <svg width={264} height={314} style={{position: 'absolute', left: 0, top: 0}}>
                <circle cx={190} cy={78} r={30} fill="rgba(255,255,255,0.85)" />
                <path d="M0 314 L86 168 L140 236 L184 186 L264 314Z" fill="rgba(255,255,255,0.45)" />
              </svg>
            </div>
          );
        })}
      </div>
    );
  }
  if (art === 'sound') {
    const bars = 24;
    return (
      <div style={box}>
        {[0, 1, 2].map((i) => {
          const q = ((t - at) * 0.9 + i / 3) % 1;
          return <div key={i} style={{position: 'absolute', left: W / 2 - 300 - q * 120, top: 210 - 110 - q * 60, width: 600 + q * 240, height: 220 + q * 120,
            borderRadius: T.flat ? 40 : 999, border: `3px solid ${T.flat ? rgba(T.ink, 0.35 * (1 - q)) : rgba(P.amber, 0.5 * (1 - q))}`}} />;
        })}
        <div style={{display: 'flex', alignItems: 'center', gap: 22, padding: '28px 42px', borderRadius: T.flat ? 28 : 999, ...(T.flat ? {background: T.ink} : glass(0.45))}}>
          <div style={{width: 76, height: 76, borderRadius: T.flat ? 18 : 38, background: T.fill.accent, display: 'flex', alignItems: 'center', justifyContent: 'center'}}>
            <Icon name="sound" size={44} color={T.onFill('accent')} stroke={2.6} />
          </div>
          <div style={{display: 'flex', alignItems: 'center', gap: 8, height: 96}}>
            {Array.from({length: bars}).map((_, i) => {
              const v = 0.22 + 0.78 * Math.abs(Math.sin(t * 7 + i * 0.62) * Math.cos(t * 3.1 + i * 0.29));
              return <div key={i} style={{width: 9, height: 96 * v, borderRadius: T.flat ? 2 : 5, background: T.flat ? (i % 6 === 2 ? T.accent : T.paper) : `linear-gradient(180deg, ${P.amber}, ${P.accent})`}} />;
            })}
          </div>
        </div>
      </div>
    );
  }
  return (
    <div style={box}>
      <div style={{width: 240, height: 240, borderRadius: T.flat ? 40 : 72, ...T.panel(0.4), display: 'flex', alignItems: 'center', justifyContent: 'center'}}>
        <div style={{width: 150, height: 150, borderRadius: T.flat ? 28 : 46, background: T.fill.accent2, display: 'flex', alignItems: 'center', justifyContent: 'center'}}>
          <Icon name={art} size={90} color="#fff" stroke={2.4} />
        </div>
      </div>
    </div>
  );
};

export const SceneFeatures = ({E, t, o}) => {
  const W = E.width, T = theme(E);
  const items = o.items || [];
  let cur = -1;
  items.forEach((it, k) => { if (t >= it.at) cur = k; });
  const y0 = o.y ?? 350, size = o.size || 150;
  const it = cur >= 0 ? items[cur] : null, prev = cur > 0 ? items[cur - 1] : null;
  const word = (x) => String(x.word || x.text);
  const isAcc = (x) => x.accent !== false;
  return (
    <SceneFrame E={E} t={t} o={o}>
      <div style={{position: 'absolute', left: 50, right: 50, top: y0, display: 'flex', flexWrap: 'wrap', justifyContent: 'center', gap: 16}}>
        {items.map((x, k) => {
          const done = k < cur, on = k === cur;
          const ip = tw(t, o.start + 0.08 + k * 0.05, 0.55);
          return (
            <div key={k} style={{opacity: clamp(ip * 1.6) * (on || done ? 1 : (T.flat ? 0.45 : 0.7)), transform: `translateY(${(1 - ip) * 24}px)`, filter: fx(0, 0, (1 - ip) * 8)}}>
              <Chip E={E} text={x.chip || x.text} size={o.chipSize || 32} tone={on ? 'accent' : 'glass'} dot={false} icon={done ? 'check' : null} />
            </div>
          );
        })}
      </div>
      <div style={{position: 'absolute', left: 0, width: W, top: y0 + 170, height: size * 1.3, display: 'flex', justifyContent: 'center',
        ...dispFont(E), fontSize: size, lineHeight: 1, letterSpacing: '-0.045em', color: T.fg,
        filter: T.flat ? 'none' : 'drop-shadow(0 16px 40px rgba(6,3,28,0.5))'}}>
        {prev && t < it.at + 0.2 ? (
          <div style={{position: 'absolute', left: 0, right: 0, display: 'flex', justifyContent: 'center'}}>
            <MaskWord p={1} q={tw(t, it.at - 0.04, 0.22, qIn)}>{isAcc(prev) && T.flat ? <span style={{color: T.accent}}>{word(prev)}</span> : word(prev)}</MaskWord>
          </div>
        ) : null}
        {it ? (
          <div style={{position: 'absolute', left: 0, right: 0, display: 'flex', justifyContent: 'center'}}>
            <MaskWord p={tw(t, it.at + 0.1, 0.6)} q={0}>
              {isAcc(it) ? <AccentText E={E} shine={prog(t, it.at + 0.35, it.at + 0.95)}>{word(it)}</AccentText> : word(it)}
            </MaskWord>
          </div>
        ) : null}
      </div>
      {it ? <div key={cur} style={{position: 'absolute', left: 0, top: y0 + 390, width: W, height: 420, transform: `scale(${o.artScale ?? 1.2})`}}><Art E={E} t={t} it={it} at={it.at} /></div> : null}
    </SceneFrame>
  );
};

// ------------------------------------------------------------------------------------------------ ScenePhones
// {phones:[{label, at, grade, tone, icon, zoom}], y, w, arrow}
export const ScenePhones = ({E, t, o}) => {
  const W = E.width, T = theme(E), P = pal(E.style.colors);
  const ph = o.phones || [];
  const two = ph.length > 1;
  const pw = o.w || (two ? 400 : 470), phH = Math.round(pw * 1.8), cy = o.y ?? 760;
  const xs = two ? [W / 2 - pw - 26, W / 2 + 26] : [W / 2 - pw / 2];
  return (
    <SceneFrame E={E} t={t} o={o}>
      {two && o.arrow !== false && t >= ph[1].at - 0.1 ? (() => {
        const d = qInOut(prog(t, ph[1].at - 0.1, ph[1].at + 0.5));
        const ax = xs[0] + pw * 0.62, ay = cy - phH / 2 - 26, bx = xs[1] + pw * 0.38, by = ay;
        const pth = `M${ax} ${ay} C ${ax + 70} ${ay - 150}, ${bx - 70} ${by - 150}, ${bx} ${by}`;
        const tt = d; const mx = (1 - tt) ** 3 * ax + 3 * (1 - tt) ** 2 * tt * (ax + 70) + 3 * (1 - tt) * tt * tt * (bx - 70) + tt ** 3 * bx;
        const my = (1 - tt) ** 3 * ay + 3 * (1 - tt) ** 2 * tt * (ay - 150) + 3 * (1 - tt) * tt * tt * (by - 150) + tt ** 3 * by;
        return (
          <svg width={W} height={E.height} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
            <path d={pth} fill="none" stroke={T.lineSoft} strokeWidth={5} strokeLinecap="round" strokeDasharray="2 16" />
            <path d={pth} fill="none" stroke={T.flat ? T.accent : P.amber} strokeWidth={6} strokeLinecap="round" pathLength={1} strokeDasharray={`${d} 1`} />
            <circle cx={mx} cy={my} r={11} fill={T.flat ? T.ink : '#FFF6E8'} />
            {T.glow ? <circle cx={mx} cy={my} r={26} fill={P.amber} opacity={0.35} /> : null}
          </svg>
        );
      })() : null}
      {ph.map((p, k) => {
        if (t < p.at) return null;
        const sp = tw(t, p.at, 0.85);
        const ry = two ? (k ? -1 : 1) : 0;
        const lab = tw(t, p.at + 0.25, 0.55);
        return (
          <div key={k} style={{position: 'absolute', left: xs[k], top: cy - phH / 2, width: pw, height: phH, perspective: 1600}}>
            <div style={{position: 'absolute', inset: 0, transform: `translateY(${(1 - sp) * 160}px) rotateY(${ry * (10 + (1 - sp) * 22)}deg) rotateZ(${ry * -2}deg)`,
              transformOrigin: '50% 70%', opacity: clamp(sp * 1.6), filter: fx(0, 0, (1 - sp) * 14)}}>
              <div style={{position: 'absolute', inset: 0, borderRadius: pw * 0.16, padding: 13, ...T.bezel,
                boxShadow: T.flat ? T.bezel.boxShadow : `inset 0 0 0 2px rgba(255,255,255,0.16), 0 40px 80px rgba(3,1,18,0.55), 0 0 60px ${rgba(p.grade === 0 ? P.cyan : P.accent, 0.18)}`}}>
                <LiveClip E={E} t={t} w={pw - 26} h={phH - 26} grade={p.grade ?? 1} zoom={p.zoom ?? 1.05} radius={pw * 0.13} />
                <div style={{position: 'absolute', left: pw / 2 - 54, top: 30, width: 108, height: 30, borderRadius: 15, background: '#0A0818'}} />
                {T.flat ? null : <div style={{position: 'absolute', inset: 13, borderRadius: pw * 0.13, background: 'linear-gradient(120deg, rgba(255,255,255,0.14), rgba(255,255,255,0) 35%)'}} />}
              </div>
            </div>
            <div style={{position: 'absolute', left: 0, right: 0, bottom: -30, display: 'flex', justifyContent: 'center',
              opacity: clamp(lab * 1.6), transform: `translateY(${(1 - lab) * 24}px)`, filter: fx(0, 0, (1 - lab) * 8)}}>
              <Chip E={E} text={p.label} size={42} tone={p.tone || (p.grade === 0 ? (T.flat ? 'paper' : 'glass') : 'accent')} icon={p.icon} dot={p.grade === 0} />
            </div>
          </div>
        );
      })}
    </SceneFrame>
  );
};

// ------------------------------------------------------------------------------------------------ EndCardHero
export const EndCardHero = ({E, t, o}) => {
  const W = E.width, F = E.style.fonts, T = theme(E), P = pal(E.style.colors);
  const at = o.at || {};
  const kAt = at.kicker ?? o.start, wm = at.wordmark ?? o.start + 0.2, btnAt = at.button ?? o.start + 0.6;
  const y = o.y ?? 860, fs = o.size || 200;
  const kp = tw(t, kAt, 0.6), bp = tw(t, btnAt, 0.6, eOut);
  return (
    <div style={{position: 'absolute', left: 0, top: y, width: W, transform: 'translateY(-50%)', display: 'flex', flexDirection: 'column', alignItems: 'center'}}>
      {o.kicker && t >= kAt ? <div style={{marginBottom: 26, opacity: clamp(kp * 1.6), transform: `translateY(${(1 - kp) * 24}px)`, filter: fx(0, 0, (1 - kp) * 8)}}>
        <Chip E={E} text={o.kicker} size={o.kickerSize || 40} /></div> : null}
      <div style={{display: 'flex', flexDirection: 'column', alignItems: 'center', ...dispFont(E), fontSize: fs, lineHeight: 0.92,
        letterSpacing: '-0.05em', color: T.fg, filter: T.flat ? 'none' : 'drop-shadow(0 18px 46px rgba(6,3,28,0.55))'}}>
        <MaskWord p={tw(t, wm, 0.8)}>{o.word1}</MaskWord>
        {o.word2 ? <MaskWord p={tw(t, wm + 0.1, 0.8)}><AccentText E={E} shine={prog(t, wm + 0.6, wm + 1.4)}>{o.word2}</AccentText></MaskWord> : null}
      </div>
      {T.flat ? <div style={{marginTop: 22, height: 10, width: 420 * tw(t, wm + 0.35, 0.7, eOut), background: T.ink}} /> : null}
      {o.button && t >= btnAt ? (
        <div style={{marginTop: 40, clipPath: `inset(-20% ${(1 - bp) * 100}% -20% 0 round ${T.flat ? 12 : 999}px)`}}>
          <div style={{display: 'inline-flex', alignItems: 'center', gap: 16, padding: '20px 36px', borderRadius: T.flat ? 12 : 999, background: T.fill.accent, color: T.onFill('accent'),
            fontFamily: F.ui, fontWeight: 800, fontSize: o.buttonSize || 42, boxShadow: T.solidShadow()}}>
            {o.button}<Icon name="arrowDown" size={40} color={T.onFill('accent')} stroke={3} />
          </div>
        </div>
      ) : null}
      {o.chevrons !== false && t >= wm + 0.4 ? (
        <svg width={130} height={150} style={{marginTop: 26}}>
          {[0, 1, 2].map((i) => {
            const ph = ((t - wm - 0.4) * 1.3 - i * 0.2) % 1;
            const op = ph < 0 ? 0 : Math.sin(Math.max(0, ph) * Math.PI);
            return <path key={i} d={`M24 ${22 + i * 42} L65 ${56 + i * 42} L106 ${22 + i * 42}`} stroke={i === 1 ? (T.flat ? T.accent : P.amber) : T.fg} strokeWidth={12} fill="none"
              strokeLinecap={T.flat ? 'square' : 'round'} strokeLinejoin={T.flat ? 'miter' : 'round'} opacity={0.12 + 0.88 * op} />;
          })}
        </svg>
      ) : null}
    </div>
  );
};
