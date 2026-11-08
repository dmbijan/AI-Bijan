// Word-synchronised kinetic captions. Three looks driven by style tokens:
//   box      (IMPACT)    white card, words pop in with spring + blur, active word on an accent marker,
//                        emphasis words as dark chips with accent text and optional icon
//   clean    (EDITORIAL) no card, words rise in softly, active word underlined, emphasis in serif italic
//   terminal (SYSTEM)    dark UI card, words type in with a caret, emphasis as accent-2 chips
// plus "raw": a deliberately plain default-subtitle look used for "before" moments, and the v3.x looks below
// (glass, reel, poster, lumen, type, strip, stroke, note).
import React from 'react';
import {bezier, clamp, dirBlur, ease, prog, shutterBlur, springAt} from '../lib/anim.js';

const qOut = bezier(0.22, 1, 0.36, 1);
import {Icon} from './Icons.jsx';

const zoneAt = (zones, t) => {
  let z = null;
  for (const zz of zones || []) if (t >= zz.from && t < zz.to) z = zz;
  return z;
};

function caseOf(text, mode) {
  return mode === 'upper' ? text.toUpperCase() : mode === 'lower' ? text.toLowerCase() : text;
}

// IMPACT words: the whole page (1-3 words) lands together with a short stagger so the card never shows
// empty space; word-level sync comes from the accent marker that jumps to each word as it is spoken and
// from a scale "bump" on emphasis chips at their exact start time.
function BoxWord({E, w, t, next, size, S, C, k, pageStart}) {
  const appear = pageStart + k * 0.035;
  const a = springAt(t, appear, E.fps, {damping: 13, stiffness: 240});
  const shown = t >= appear;
  const spoken = t >= w.start - 0.04;
  const active = spoken && t < next;
  const emph = !!w.emph;
  const av = (springAt(t + 1 / 120, appear, E.fps, {damping: 13, stiffness: 240}) - springAt(t - 1 / 120, appear, E.fps, {damping: 13, stiffness: 240})) * 60;
  const by = shown ? shutterBlur(Math.abs(av) * 0.3 * size) * 1.4 : 0;
  const bumpP = spoken ? prog(t, w.start - 0.04, w.start + 0.26) : 0;
  const bump = emph ? Math.sin(bumpP * Math.PI) * 0.06 : 0;
  const sc = Math.min(1, 0.6 + 0.4 * a) + bump;
  const hi = active && !emph ? springAt(t, w.start - 0.04, E.fps, {damping: 18, stiffness: 280}) : 0;
  // dark cards: switch the word to dark text only once the light marker fully covers it (no half-invisible word)
  const activeText = hi > 0.97 && S.activeText ? S.activeText : null;
  return (
    <span style={{position: 'relative', display: 'inline-flex', alignItems: 'center', gap: '0.16em',
      padding: emph ? '0.08em 0.24em' : '0.02em 0.12em', margin: emph ? '0 0.1em' : '0 0.01em', borderRadius: S.radius * 0.6,
      background: emph ? (S.chipBg || C.ink) : 'transparent', color: emph ? (S.chipText || C.accent) : activeText || S.text || C.ink, fontSize: emph ? '0.92em' : '1em',
      opacity: shown ? (emph || spoken ? 1 : 0.92) : 0, transform: `translateY(${(1 - Math.min(1, a)) * 0.3 * size}px) scale(${sc}) rotate(${emph ? -1.5 : 0}deg)`,
      filter: dirBlur(0, by), whiteSpace: 'nowrap', zIndex: emph ? 1 : 0}}>
      {hi > 0 ? <span style={{position: 'absolute', left: 0, right: 0, top: '0.02em', bottom: '0.0em', borderRadius: S.radius * 0.45,
        background: S.active || C.accent, transform: `scaleX(${Math.min(1, hi)})`, transformOrigin: '0 50%', zIndex: -1}} /> : null}
      {emph && w.icon ? <Icon name={w.icon} size={size * 0.8} color={S.chipText || C.accent} stroke={2.6} /> : null}
      {caseOf(w.text, S.case)}
    </span>
  );
}

function CleanWord({E, w, t, next, size, S, C}) {
  const a0 = w.start - 0.07, a1 = w.start + 0.22;
  const pos = (k) => 1 - ease.snappy(prog(k, a0, a1));
  const a = 1 - pos(t);
  const v = Math.abs(pos(t + 1 / 120) - pos(t - 1 / 120)) * 60;
  const active = t >= w.start - 0.04 && t < next;
  const ul = active ? ease.out(prog(t, w.start - 0.04, w.start + 0.12)) : 0;
  return (
    <span style={{position: 'relative', display: 'inline-block', margin: '0 0.12em', opacity: clamp(a * 2.2),
      transform: `translateY(${(1 - a) * 0.42 * size}px)`, filter: dirBlur(0, shutterBlur(v * 0.42 * size) * 1.3),
      fontFamily: w.emph ? E.style.fonts.serif : undefined, fontStyle: w.emph ? 'italic' : 'normal',
      fontWeight: w.emph ? (E.style.fonts.serifWeight ?? 400) : undefined, fontSize: w.emph ? '1.12em' : '1em', color: w.emph ? (S.emph || C.accent) : (S.text || C.paper)}}>
      {caseOf(w.text, S.case)}
      {ul > 0 ? <span style={{position: 'absolute', left: 0, right: 0, bottom: '-0.08em', height: '0.07em', borderRadius: 4,
        background: S.underline || C.accent, transform: `scaleX(${ul})`, transformOrigin: '0 50%'}} /> : null}
    </span>
  );
}

// "strip" variant: every page is a torn strip of newsprint (ink type on ivory, hard shadow, alternating tilt),
// the word being spoken gets a red marker underline, emphasis words switch to serif italic.
function StripWord({E, w, t, next, size, S, C}) {
  const active = t >= w.start - 0.04 && t < next;
  const spoken = t >= w.start - 0.04;
  const ul = active ? ease.snappy(prog(t, w.start - 0.04, w.start + 0.14)) : 0;
  return (
    <span style={{position: 'relative', display: 'inline-block', margin: '0 0.13em', opacity: spoken ? 1 : 0.55,
      fontFamily: w.emph ? E.style.fonts.serif : undefined, fontStyle: w.emph ? 'italic' : 'normal', fontWeight: w.emph ? 400 : undefined,
      fontSize: w.emph ? '1.16em' : '1em', color: S.text || C.ink}}>
      {ul > 0 ? <span style={{position: 'absolute', left: '-0.06em', right: '-0.06em', bottom: '0.02em', height: '0.2em', borderRadius: 3,
        background: S.active || C.accent, opacity: 0.85, transform: `scaleX(${ul}) rotate(-1deg)`, transformOrigin: '0 50%', zIndex: -1}} /> : null}
      {caseOf(w.text, S.case)}
    </span>
  );
}

// MOGRAPH MIX "glass": the whole page sits in a frosted dark pill; upcoming words wait dimmed and light up
// smoothly as they are spoken (eased, no bounce); emphasis words light up in the accent gradient.
function GlassWord({E, w, t, size, S, C}) {
  const k = qOut(prog(t, w.start - 0.07, w.start + 0.16));
  const flat = E.style.look === 'flat';
  const grad = w.emph && flat ? {color: S.emph || C.accent} : w.emph ? {backgroundImage: `linear-gradient(100deg, ${C.accent}, ${C.amber || C.accent})`, WebkitBackgroundClip: 'text', backgroundClip: 'text', color: 'transparent'} : {};
  return (
    <span style={{display: 'inline-block', margin: '0 0.12em', whiteSpace: 'nowrap', color: S.text || '#FFFFFF', opacity: (S.dim ?? 0.38) + (1 - (S.dim ?? 0.38)) * k,
      transform: `translateY(${(1 - k) * 0.12 * size}px)`, ...(w.emph && k > 0.02 ? grad : {})}}>
      {caseOf(w.text, S.case)}
    </span>
  );
}

// DEPTH "reel": the premium Reels subtitle - no box, a clean grotesk in sentence case, each word arrives out of a
// soft blur with a short rise as it is spoken; emphasis words switch to an italic serif in the accent colour.
function ReelWord({E, w, t, size, S, C}) {
  const a0 = w.start - 0.06;
  const k = qOut(prog(t, a0, a0 + 0.3));
  if (t < a0) return <span style={{display: 'inline-block', margin: '0 0.11em', opacity: 0}}>{caseOf(w.text, S.case)}</span>;
  const F = E.style.fonts || {};
  const emph = !!w.emph;
  return (
    <span style={{display: 'inline-block', margin: emph ? '0 0.13em' : '0 0.11em', whiteSpace: 'nowrap', opacity: k,
      transform: `translateY(${((1 - k) * 0.22 * size).toFixed(1)}px)`, filter: k < 0.98 ? `blur(${((1 - k) * 8).toFixed(1)}px)` : 'none',
      ...(emph ? {fontFamily: F.accent || F.serif, fontStyle: 'italic', fontWeight: 400, fontSize: '1.24em', lineHeight: 0.9, color: S.emph || C.accent,
        letterSpacing: '0'} : {color: S.text || '#FFFFFF'})}}>
      {caseOf(w.text, S.case)}
    </span>
  );
}

// POSTER "poster": heavy white words with a soft shadow that pop up word by word (scale 0.86 -> 1, eased); emphasis
// words sit on a solid accent label (slight tilt), like a designed thumbnail.
function PosterWord({E, w, t, size, S, C, k}) {
  const a0 = w.start - 0.05;
  if (t < a0) return <span style={{display: 'inline-block', margin: '0 0.13em', opacity: 0}}>{caseOf(w.text, S.case)}</span>;
  const p = qOut(prog(t, a0, a0 + 0.22));
  const emph = !!w.emph;
  return (
    <span style={{display: 'inline-block', margin: emph ? '0.06em 0.16em' : '0 0.13em', whiteSpace: 'nowrap', opacity: Math.min(1, p * 1.6),
      transform: `translateY(${((1 - p) * 0.2 * size).toFixed(1)}px) scale(${(0.86 + 0.14 * p).toFixed(3)}) rotate(${emph ? -2 : 0}deg)`,
      ...(emph ? {background: S.label || C.accent, color: S.labelText || '#FFFFFF', padding: '0.04em 0.24em 0.08em', borderRadius: '0.14em',
        boxShadow: '0 6px 14px rgba(0,0,0,0.25)', textShadow: 'none'} : {color: S.text || '#FFFFFF'})}}>
      {caseOf(w.text, S.case)}
    </span>
  );
}

// NOTED "note": the quiet subtitle of a notebook edit - sentence case with its punctuation, a clean sans (Geist), no box.
// Each word fades in out of a soft blur as it is spoken (no bounce, no rise). Emphasis words get a highlighter stroke
// that is drawn behind them left to right on the word, and the word turns to ink. On a paper board (zone tone "ink")
// the whole line is ink without a shadow.
function NoteWord({E, w, t, size, S, C, ink}) {
  const a0 = w.start - 0.05;
  if (t < a0) return <span style={{display: 'inline-block', margin: '0 0.12em', opacity: 0}}>{caseOf(w.text, S.case)}</span>;
  const k = qOut(prog(t, a0, a0 + 0.22));
  const emph = !!w.emph;
  const hp = emph ? qOut(prog(t, w.start - 0.02, w.start + 0.3)) : 0;
  const inkCol = C.ink || '#16140F';
  return (
    <span style={{position: 'relative', display: 'inline-block', margin: emph ? '0 0.16em' : '0 0.12em', whiteSpace: 'nowrap', opacity: k,
      filter: k < 0.98 ? `blur(${((1 - k) * 7).toFixed(1)}px)` : 'none', isolation: 'isolate',
      color: emph && hp > 0.35 ? inkCol : ink ? inkCol : (S.text || '#FFFFFF'), textShadow: emph && hp > 0.35 ? 'none' : undefined}}>
      {hp > 0 ? <span style={{position: 'absolute', left: '-0.14em', right: '-0.14em', top: '0.08em', bottom: '-0.02em', zIndex: -1, borderRadius: '0.12em',
        background: S.mark || C.accent, opacity: 0.94, transform: `scaleX(${hp.toFixed(3)}) rotate(-1.2deg)`, transformOrigin: '0 50%',
        boxShadow: ink ? 'none' : '0 2px 10px rgba(0,0,0,0.18)'}} /> : null}
      {caseOf(w.text, S.case)}
    </span>
  );
}

// LUMEN "lumen": clean white words rising out of a soft blur; emphasis words become the luminous gradient with a glow.
function LumenWord({E, w, t, size, S, C}) {
  const a0 = w.start - 0.06;
  if (t < a0) return <span style={{display: 'inline-block', margin: '0 0.11em', opacity: 0}}>{caseOf(w.text, S.case)}</span>;
  const k = qOut(prog(t, a0, a0 + 0.3));
  const g = S.grad || ['#FFE7A3', '#FF9A2E', '#E8501C'];
  const emph = !!w.emph;
  return (
    <span style={{display: 'inline-block', margin: '0 0.11em', whiteSpace: 'nowrap', opacity: k,
      transform: `translateY(${((1 - k) * 0.22 * size).toFixed(1)}px)`,
      ...(emph ? {color: 'transparent', backgroundImage: `linear-gradient(180deg, ${g[0]}, ${g[1]} 55%, ${g[2]})`, WebkitBackgroundClip: 'text', backgroundClip: 'text',
        textShadow: 'none', fontSize: '1.08em', filter: `drop-shadow(0 0 ${(size * 0.12).toFixed(1)}px ${g[1]})${k < 0.98 ? ` blur(${((1 - k) * 8).toFixed(1)}px)` : ''}`}
        : {color: S.text || '#FFFFFF', filter: k < 0.98 ? `blur(${((1 - k) * 8).toFixed(1)}px)` : 'none'})}}>
      {caseOf(w.text, S.case)}
    </span>
  );
}

// STORYTELLER "type": no box; characters are typed in as each word is spoken (untyped characters keep their
// space so the line never jumps), a blinking caret rides the end of the typed text, key words in the accent colour.
function TypeWord({w, t, S, C, caret}) {
  const txt = caseOf(w.text, S.case);
  const dur = Math.min(0.32, Math.max(0.08, (w.end - w.start) * 0.85));
  const n = Math.round(txt.length * clamp((t - (w.start - 0.05)) / dur));
  const col = w.emph ? (S.emph || C.accent) : (S.text || '#FFFFFF');
  return (
    <span style={{margin: '0 0.13em', whiteSpace: 'nowrap', position: 'relative'}}>
      <span style={{color: col}}>{txt.slice(0, n)}</span>
      <span style={{color: 'transparent', textShadow: 'none'}}>{txt.slice(n)}</span>
      {caret && n > 0 ? <span style={{position: 'absolute', top: '0.08em', bottom: '0.06em', width: '0.08em', marginLeft: '0.04em',
        left: `calc(${n / Math.max(1, txt.length) * 100}% )`, background: S.caret || C.accent, opacity: 0.4 + 0.6 * (0.5 + 0.5 * Math.cos(t * 10))}} /> : null}
    </span>
  );
}

function TermWord({E, w, t, next, S, C}) {
  const txt = caseOf(w.text, S.case);
  const typeDur = Math.min(0.16, Math.max(0.06, (w.end - w.start) * 0.7));
  const n = Math.round(txt.length * clamp((t - (w.start - 0.03)) / typeDur));
  const active = t >= w.start - 0.03 && t < next;
  if (n <= 0) return <span style={{opacity: 0, margin: '0 0.14em'}}>{txt}</span>;
  return (
    <span style={{margin: '0 0.14em', display: 'inline-flex', alignItems: 'center', gap: '0.15em',
      padding: w.emph ? '0 0.22em' : 0, borderRadius: 10, background: w.emph ? C.accent2 : 'transparent',
      color: w.emph ? C.paper : active ? C.accent : (S.text || C.paper)}}>
      {w.emph && w.icon ? <Icon name={w.icon} size={36} color={C.paper} /> : null}
      <span>{txt.slice(0, n)}<span style={{opacity: 0}}>{txt.slice(n)}</span></span>
    </span>
  );
}

function StrokeWord({E, w, t, next, size, S, C, k, pageStart}) {
  const appear = Math.min(w.start - 0.05, pageStart + k * 0.03);
  const p = springAt(t, appear, E.fps, {damping: 12, stiffness: 300});
  const pv = (springAt(t + 1 / 120, appear, E.fps, {damping: 12, stiffness: 300}) - springAt(t - 1 / 120, appear, E.fps, {damping: 12, stiffness: 300})) * 60;
  const shown = t >= appear;
  const active = t >= w.start - 0.04 && t < next;
  const lift = active ? springAt(t, w.start - 0.04, E.fps, {damping: 14, stiffness: 320}) : 0;
  const emph = !!w.emph;
  const col = emph ? (S.emph || C.accent) : active ? (S.active || C.accent) : (S.text || '#FFFFFF');
  const sw = Math.round(size * (S.strokeRatio ?? 0.16));
  // long words grow less on the active lift so they never touch their neighbours
  const liftK = Math.min(1, 6 / Math.max(1, String(w.text).length));
  const hard = S.hardShadow ? `0 ${Math.round(size * 0.075)}px 0 ${S.stroke || '#000'}, ` : '';
  if (emph && S.emphBox) {
    // emphasis word as a die-cut sticker: coloured box, ink text, white outline, slight counter-rotation
    const ol = Math.max(5, Math.round(size * 0.09));
    return (
      <span style={{position: 'relative', display: 'inline-block', opacity: shown ? 1 : 0, color: S.emphText || '#0A0A0A', background: S.emphBox,
        padding: '0.02em 0.2em 0.06em', borderRadius: size * 0.16, margin: '0 0.06em',
        boxShadow: `0 0 0 ${ol}px #FFFFFF, 0 ${ol + 3}px 0 ${ol}px rgba(0,0,0,0.35)`,
        transform: `translateY(${(1 - Math.min(1, p)) * 0.35 * size - lift * 0.05 * size}px) scale(${(0.6 + 0.4 * Math.min(1.08, p)) * (1 + 0.1 * lift)}) rotate(${-3 + 3 * (1 - Math.min(1, p))}deg)`,
        filter: dirBlur(0, shutterBlur(Math.abs(pv) * 0.35 * size) * 1.2), whiteSpace: 'nowrap'}}>
        {caseOf(w.text, S.case)}
      </span>
    );
  }
  return (
    <span style={{position: 'relative', display: 'inline-block', margin: 0, opacity: shown ? 1 : 0, color: col,
      transform: `translateY(${(1 - Math.min(1, p)) * 0.35 * size - lift * 0.06 * size}px) scale(${(0.7 + 0.3 * Math.min(1.06, p)) * (1 + (emph ? 0.14 : 0.07) * lift * liftK)})`,
      WebkitTextStroke: `${sw}px ${S.stroke || '#000'}`, paintOrder: 'stroke fill',
      textShadow: `${hard}0 ${Math.round(size * 0.08)}px ${Math.round(size * 0.18)}px rgba(0,0,0,0.45)`,
      filter: dirBlur(0, shutterBlur(Math.abs(pv) * 0.35 * size) * 1.2), whiteSpace: 'nowrap'}}>
      {caseOf(w.text, S.case)}
    </span>
  );
}

export const Captions = ({E, t}) => {
  const CAP = E.captions;
  if (!CAP || !CAP.pages) return null;
  const C = E.style.colors;
  let S = E.style.captions;
  const zone = zoneAt(CAP.zones, t);
  if (zone && zone.hidden) return null;
  const pi = CAP.pages.findIndex((p) => t >= p.start && t < p.end);
  if (pi < 0) return null;
  const page = CAP.pages[pi];
  const pz = zoneAt(CAP.zones, page.start) || zone || {};
  const variant = pz.variant || page.variant || S.variant;
  const y = pz.y ?? CAP.y;
  const maxW = pz.maxWidth ?? CAP.maxWidth;
  const W = E.width;
  const nextStart = (k) => (k + 1 < page.words.length ? page.words[k + 1].start - 0.04 : page.end);

  if (variant === 'raw') {
    return (
      <div style={{position: 'absolute', left: 0, width: W, top: y, transform: 'translateY(-100%)', display: 'flex', justifyContent: 'center'}}>
        <div style={{maxWidth: maxW, textAlign: 'center', fontFamily: 'DMSans', fontWeight: 500, fontSize: pz.size ?? 54, color: '#fff',
          WebkitTextStroke: '1.5px rgba(0,0,0,0.85)', textShadow: '0 2px 2px rgba(0,0,0,0.6)'}}>
          {page.words.filter((w) => t >= w.start - 0.05).map((w) => w.text).join(' ')}
        </div>
      </div>
    );
  }

  const isHook = variant === 'hook';
  const size = (pz.size ?? (isHook ? S.hookSize : S.size));
  const look = isHook ? S.variant : variant;
  const boxP = springAt(t, page.start - 0.06, E.fps, {damping: 14, stiffness: 260});
  const gapAfter = pi + 1 < CAP.pages.length ? CAP.pages[pi + 1].start - page.end : 1;
  const fadeOut = gapAfter > 0.05 ? 1 - ease.inOut(prog(t, page.end - (look === 'glass' ? 0.18 : 0.12), page.end)) : 1;

  // a paper surface under the captions (NoteBoard sets a zone with tone "ink"): boxless white looks switch to ink
  const inkZone = (CAP.zones || []).some((z) => z.tone === 'ink' && t >= z.from && t < z.to);
  const inkLook = inkZone && ['reel', 'poster', 'lumen', 'type', 'clean'].includes(look) && !(look === 'clean' && (pz.plate ?? page.plate));
  if (inkLook) S = {...S, text: C.ink || '#16140F', caret: C.ink || '#16140F'};
  let inner;
  if (look === 'type') {
    const lastTyped = page.words.reduce((acc, w, k) => (t >= w.start - 0.05 ? k : acc), -1);
    if (lastTyped < 0) return null;
    inner = (
      <div style={{maxWidth: maxW, display: 'flex', flexWrap: 'wrap', justifyContent: S.align === 'left' ? 'flex-start' : 'center', alignItems: 'baseline',
        fontFamily: E.style.fonts.body, fontWeight: S.weight || 800, fontSize: size, lineHeight: 1.14, letterSpacing: '-0.01em', opacity: fadeOut,
        textShadow: '0 3px 18px rgba(0,0,0,0.55), 0 1px 2px rgba(0,0,0,0.6)'}}>
        {page.words.map((w, k) => <TypeWord key={k} w={w} t={t} S={S} C={C} caret={k === lastTyped} />)}
      </div>
    );
  } else if (look === 'poster' || look === 'lumen') {
    inner = (
      <div style={{maxWidth: maxW, display: 'flex', flexWrap: 'wrap', justifyContent: 'center', alignItems: 'baseline', rowGap: size * 0.08,
        fontFamily: E.style.fonts.body, fontWeight: S.weight || (look === 'poster' ? 800 : 700), fontSize: size, lineHeight: 1.12,
        letterSpacing: look === 'poster' ? '-0.015em' : '-0.01em', opacity: fadeOut,
        textShadow: look === 'poster' ? '0 3px 12px rgba(0,0,0,0.45), 0 1px 2px rgba(0,0,0,0.5)' : '0 2px 16px rgba(0,0,0,0.5), 0 1px 2px rgba(0,0,0,0.45)'}}>
        {page.words.map((w, k) => (look === 'poster' ? <PosterWord key={k} E={E} w={w} t={t} size={size} S={S} C={C} k={k} />
          : <LumenWord key={k} E={E} w={w} t={t} size={size} S={S} C={C} />))}
      </div>
    );
  } else if (look === 'note') {
    const ink = (CAP.zones || []).some((z) => z.tone === 'ink' && t >= z.from && t < z.to);
    const scrim = !ink && S.scrim;
    inner = (
      <div style={{position: 'relative', maxWidth: maxW, display: 'flex', flexWrap: 'wrap', justifyContent: 'center', alignItems: 'baseline', rowGap: size * 0.12,
        fontFamily: E.style.fonts.body, fontWeight: S.weight || 560, fontSize: size, lineHeight: 1.16, letterSpacing: '-0.006em', opacity: fadeOut,
        textShadow: ink ? 'none' : '0 1px 2px rgba(0,0,0,0.42), 0 2px 12px rgba(0,0,0,0.38)'}}>
        {scrim ? <div style={{position: 'absolute', left: '-18%', right: '-18%', top: '-70%', bottom: '-70%', zIndex: -1, opacity: Math.min(1, boxP * 1.5),
          background: `radial-gradient(ellipse 50% 50% at 50% 50%, rgba(0,0,0,${S.scrim === true ? 0.3 : S.scrim}) 0%, rgba(0,0,0,0) 100%)`}} /> : null}
        {page.words.map((w, k) => <NoteWord key={k} E={E} w={w} t={t} size={size} S={S} C={C} ink={ink} />)}
      </div>
    );
  } else if (look === 'reel') {
    inner = (
      <div style={{maxWidth: maxW, display: 'flex', flexWrap: 'wrap', justifyContent: 'center', alignItems: 'baseline',
        fontFamily: E.style.fonts.body, fontWeight: S.weight || 600, fontSize: size, lineHeight: 1.14, letterSpacing: '-0.02em', opacity: fadeOut,
        textShadow: '0 2px 14px rgba(0,0,0,0.42), 0 1px 2px rgba(0,0,0,0.45)'}}>
        {page.words.map((w, k) => <ReelWord key={k} E={E} w={w} t={t} size={size} S={S} C={C} />)}
      </div>
    );
  } else if (look === 'glass') {
    const p = qOut(prog(t, page.start - 0.08, page.start + 0.32));
    inner = (
      <div style={{maxWidth: maxW, display: 'flex', flexWrap: 'wrap', justifyContent: 'center', alignItems: 'baseline',
        padding: `${size * 0.24}px ${size * 0.55}px ${size * 0.3}px`, borderRadius: E.style.look === 'flat' ? size * 0.26 : size,
        background: S.box || 'linear-gradient(160deg, rgba(255,255,255,0.14), rgba(255,255,255,0.03) 60%), rgba(14,10,36,0.62)',
        ...(E.style.look === 'flat' ? {border: 'none', boxShadow: '0 14px 30px rgba(18,18,18,0.18)'}
          : {border: '2px solid rgba(255,255,255,0.18)', boxShadow: 'inset 0 1.5px 0 rgba(255,255,255,0.24), 0 22px 50px rgba(4,2,20,0.4)'}),
        fontFamily: E.style.fonts.body, fontWeight: S.weight || 800, fontSize: size, lineHeight: 1.12, letterSpacing: '-0.01em',
        transform: `translateY(${(1 - p) * 26}px) scale(${0.95 + 0.05 * p})`, opacity: Math.min(1, p * 1.5) * fadeOut,
        filter: p < 0.97 ? `blur(${((1 - p) * 9).toFixed(1)}px)` : 'none'}}>
        {page.words.map((w, k) => <GlassWord key={k} E={E} w={w} t={t} size={size} S={S} C={C} />)}
      </div>
    );
  } else if (look === 'strip') {
    const tilt = (pi % 2 ? 1 : -1) * (S.tilt ?? 1.2);
    const sp = springAt(t, page.start - 0.06, E.fps, {damping: 15, stiffness: 240});
    inner = (
      <div style={{maxWidth: maxW, position: 'relative', opacity: Math.min(1, sp * 2) * fadeOut,
        transform: `translateY(${(1 - Math.min(1, sp)) * 30}px) rotate(${tilt + (1 - Math.min(1, sp)) * tilt * 2}deg) scale(${0.92 + 0.08 * Math.min(1, sp)})`,
        filter: 'drop-shadow(0 10px 0 rgba(20,18,14,0.22)) drop-shadow(0 16px 26px rgba(20,18,14,0.28))'}}>
        <div style={{display: 'flex', flexWrap: 'wrap', justifyContent: 'center', alignItems: 'baseline', padding: '0.26em 0.55em 0.32em',
          background: S.paper || C.paper, isolation: 'isolate', fontFamily: E.style.fonts.body, fontWeight: S.weight || 700, fontSize: size, lineHeight: 1.12,
          letterSpacing: '-0.012em', clipPath: 'polygon(0% 6%, 7% 0%, 19% 5%, 33% 0%, 47% 6%, 61% 1%, 76% 6%, 89% 0%, 100% 5%, 98.8% 50%, 100% 94%, 91% 100%, 77% 95%, 62% 100%, 48% 94%, 33% 100%, 18% 95%, 6% 100%, 0% 94%, 1.2% 50%)'}}>
          {page.words.map((w, k) => <StripWord key={k} E={E} w={w} t={t} next={nextStart(k)} size={size} S={S} C={C} />)}
        </div>
      </div>
    );
  } else if (look === 'stroke') {
    const tilt = S.tilt ? (pi % 2 ? 1 : -1) * S.tilt : 0;
    inner = (
      <div style={{maxWidth: maxW, display: 'flex', flexWrap: 'wrap', justifyContent: 'center', alignItems: 'baseline', rowGap: size * 0.02, columnGap: size * 0.3 + Math.round(size * (S.strokeRatio ?? 0.16)), // outline eats into the gap
        fontFamily: E.style.fonts.head, fontWeight: S.weight || 800, fontSize: size, lineHeight: 1.04, letterSpacing: '-0.01em', opacity: fadeOut,
        transform: tilt ? `rotate(${tilt}deg)` : undefined}}>
        {page.words.map((w, k) => <StrokeWord key={k} E={E} w={w} t={t} next={nextStart(k)} size={size} S={S} C={C} k={k} pageStart={page.start} />)}
      </div>
    );
  } else if (look === 'clean') {
    const plate = pz.plate ?? page.plate;
    // cinema-subtitle scrim: a feathered dark pool behind the words (not a box) keeps white type readable on bright walls
    const scrim = S.scrim && !plate && !inkLook;
    inner = (
      <div style={{position: 'relative', maxWidth: maxW, display: 'flex', flexWrap: 'wrap', justifyContent: 'center', alignItems: 'baseline',
        ...(plate ? {padding: '0.28em 0.5em 0.36em', borderRadius: S.radius || 14, background: 'rgba(14,14,16,0.72)',
          boxShadow: '0 14px 40px rgba(0,0,0,0.25)'} : {}),
        fontFamily: E.style.fonts.body, fontWeight: S.weight || 700, fontSize: size, lineHeight: 1.12, letterSpacing: '-0.01em',
        textShadow: '0 3px 22px rgba(0,0,0,0.45), 0 1px 2px rgba(0,0,0,0.5)', opacity: fadeOut}}>
        {scrim ? <div style={{position: 'absolute', left: '-22%', right: '-22%', top: '-75%', bottom: '-75%', zIndex: -1, opacity: Math.min(1, boxP * 1.5),
          background: `radial-gradient(ellipse 50% 50% at 50% 50%, rgba(0,0,0,${S.scrim === true ? 0.42 : S.scrim}) 0%, rgba(0,0,0,0) 100%)`}} /> : null}
        {page.words.map((w, k) => <CleanWord key={k} E={E} w={w} t={t} next={nextStart(k)} size={size} S={S} C={C} />)}
      </div>
    );
  } else if (look === 'terminal') {
    const blink = Math.floor(t * 2.4) % 2 === 0;
    inner = (
      <div style={{maxWidth: maxW, position: 'relative', padding: '0.42em 0.6em 0.42em 0.6em', borderRadius: S.radius,
        background: S.box || 'rgba(11,11,12,0.88)', boxShadow: '0 18px 50px rgba(0,0,0,0.3)', fontFamily: E.style.fonts.ui,
        fontWeight: 700, fontSize: size, lineHeight: 1.15, color: C.paper, transform: `scale(${0.94 + 0.06 * boxP})`,
        opacity: Math.min(1, boxP * 1.4) * fadeOut, display: 'flex', flexWrap: 'wrap', justifyContent: 'center', alignItems: 'center'}}>
        <span style={{position: 'absolute', left: 18, top: -20, fontSize: 24, padding: '4px 10px', borderRadius: 8,
          background: C.accent, color: C.ink, letterSpacing: '0.08em'}}>CC</span>
        {page.words.map((w, k) => <TermWord key={k} E={E} w={w} t={t} next={nextStart(k)} S={S} C={C} />)}
        <span style={{display: 'inline-block', width: '0.42em', height: '0.95em', marginLeft: 4, background: C.accent, opacity: blink ? 1 : 0}} />
      </div>
    );
  } else {
    inner = (
      <div style={{maxWidth: maxW, display: 'flex', flexWrap: 'wrap', justifyContent: 'center', alignItems: 'center',
        rowGap: size * 0.1, padding: `${size * 0.24}px ${size * 0.42}px`, borderRadius: S.radius, background: S.box || C.paper,
        boxShadow: '0 18px 44px rgba(0,0,0,0.20), 0 2px 6px rgba(0,0,0,0.08)', fontFamily: E.style.fonts.head, fontWeight: S.weight || 800,
        fontSize: size, lineHeight: 1.06, letterSpacing: '-0.015em', transform: `scale(${0.86 + 0.14 * boxP})`,
        transformOrigin: '50% 100%', opacity: Math.min(1, boxP * 1.6) * fadeOut, isolation: 'isolate'}}>
        {page.words.map((w, k) => <BoxWord key={k} E={E} w={w} t={t} next={nextStart(k)} size={size} S={S} C={C} k={k} pageStart={page.start} />)}
      </div>
    );
  }
  if (inkLook && inner) inner = React.cloneElement(inner, {style: {...inner.props.style, textShadow: 'none'}});
  return (
    <div style={{position: 'absolute', left: 0, width: W, top: y, transform: 'translateY(-100%)', display: 'flex', justifyContent: 'center'}}>
      {inner}
    </div>
  );
};
