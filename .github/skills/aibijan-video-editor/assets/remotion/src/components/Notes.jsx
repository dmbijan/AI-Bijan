// NOTE PACK (v3.10): explanations written above the speaker's head, like notes in a margin. The identity of NOTED,
// and usable in every style.
//
//   HeadNote   a small monospace label that types itself above the head (or at the top of the frame): the topic of
//              the moment, a running list "HAIKU · SONNET · OPUS" that grows word by word, retyped when the topic changes
//   NoteTags   strips of masking tape (or yellow / black tape, or outlined labels) laid on the frame above or beside
//              the head as each word is spoken; optional mono group label, a hairline pointer down to the head, and a
//              marker strike-through when the speaker drops an idea
//   NoteBoard  a ruled index card taped over the blurred frame for a spoken list: a typed mono heading with a
//              highlighter stroke, numbered lines typed on their words, a marker strike pass
//   NoteClips  the user's own clips / photos taped as cards around the head, each with a tape label
//   BigNote    one huge word with a typed mono kicker and a highlighter stroke (or a marker circle / underline) drawn
//              on it - the hook word of a simple video
//
// Rules: spoken words only (or user copy), pure functions of t, eased motion with real motion blur, nothing imitates a
// real product's UI.
import React from 'react';
import {AbsoluteFill, Img, OffthreadVideo, Sequence, staticFile} from 'remotion';
import {bezier, clamp, dirBlur, lerp, prog, rand, shutterBlur} from '../lib/anim.js';
import {faceOnCanvas} from './Footage.jsx';
import {measureText, toneColor} from './Depth.jsx';

const qOut = bezier(0.22, 1, 0.36, 1);
const qIn = bezier(0.64, 0, 0.78, 0);
const cInOut = bezier(0.65, 0, 0.35, 1);
const tw = (t, a, d, f = qOut) => f(prog(t, a, a + d));
const hexA = (hex, a) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || '')); if (!m) return hex;
  const n = parseInt(m[1], 16); return `rgba(${(n >> 16) & 255},${(n >> 8) & 255},${n & 255},${a})`;
};
const shade = (hex, k) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || '')); if (!m) return hex;
  const n = parseInt(m[1], 16); const f = (v) => Math.max(0, Math.min(255, Math.round(v * k)));
  return `rgb(${f((n >> 16) & 255)},${f((n >> 8) & 255)},${f(n & 255)})`;
};

// tokens with fallbacks, so the pack renders in every style
const tok = (E) => {
  const C = E.style.colors || {}, F = E.style.fonts || {};
  return {
    ink: C.ink || '#16140F', paper: C.paper || '#FFFFFF', muted: C.muted || '#6F6858',
    accent: C.accent || '#FFD43B', accent2: C.accent2 || '#E5383B',
    tag: C.tag || '#ECE3CF', tagEdge: C.tagEdge || '#C7B891', board: C.board || '#EEE6D3',
    mono: F.mono || 'GeistMono', tagFont: F.tag || F.display || F.head || 'InterTight', tagWeight: F.tagWeight || 800,
    ui: F.ui || F.body || 'Geist',
  };
};
// notes glide to the head instead of jumping with it at a jump cut: a short, recency-weighted average of the face
// position over the last ~0.3 s (pure function of t; samples where no full-frame face exists are skipped)
const faceSmooth = (E, t, win = 0.3) => {
  const f0 = faceOnCanvas(E, t);
  if (!f0) return null;
  let sx = 0, sy = 0, sh = 0, sw = 0;
  const n = 9;
  for (let j = 0; j < n; j++) {
    const tt = t - (j / (n - 1)) * win;
    if (tt < 0) break;
    const f = j === 0 ? f0 : faceOnCanvas(E, tt);
    if (!f) continue;
    const w = (n - j) * (n - j);
    sx += f.x * w; sy += f.y * w; sh += f.h * w; sw += w;
  }
  return {x: sx / sw, y: sy / sw, h: sh / sw};
};
const boardOn = (E, t) => (E.overlays || []).some((o) => o.type === 'NoteBoard' && t >= o.start - 1e-3 && t <= (o.end ?? 1e9) + 1e-3);
// a full-frame b-roll under the label: white type with a shadow reads on any picture
const brollOn = (E, t) => (E.overlays || []).some((o) => o.type === 'BRoll' && (o.mode || 'full') === 'full' && t >= o.start - 1e-3 && t <= (o.end ?? 1e9) + 1e-3);

// ------------------------------------------------------------------------------------------------ HeadNote
// {items:[{text, at, mode: "new" | "add"}] | text+at, anchor: "top" | "face", y (300), lift (1.45 × face height), x,
//  align: center/left, size (30), track (0.2 em), tone (auto/ink/paper/accent), cps (26), caret (true), dot (false), sep (" · ")}
// The displayed line is a pure function of t: "new" erases the line (fast backspace) and types the new text; "add"
// types the separator + text after what is there.
const headLine = (o, t) => {
  const items = (o.items && o.items.length ? o.items : [{text: o.text || '', at: o.at ?? o.start}])
    .filter((it) => it && it.text && (it.at ?? o.start) <= t + 1e-6)
    .map((it) => ({...it, at: it.at ?? o.start}))
    .sort((a, b) => a.at - b.at);
  const cps = o.cps || 26, sep = o.sep ?? '  ·  ';
  let line = '', typingUntil = -1;
  for (let k = 0; k < items.length; k++) {
    const it = items[k];
    const txt = String(it.text).toUpperCase();
    const next = items[k + 1];
    const stop = next ? next.at : t;
    const fresh = k === 0 || (it.mode || 'new') === 'new';
    if (fresh) {
      const before = line, eraseD = before ? Math.min(0.26, 0.012 * before.length + 0.06) : 0;
      const tEr = Math.min(stop, t);
      if (before && tEr < it.at + eraseD) {
        const n = Math.round(before.length * (1 - prog(tEr, it.at, it.at + eraseD)));
        line = before.slice(0, n); typingUntil = it.at + eraseD; continue;
      }
      const t0 = it.at + eraseD;
      const n = Math.round(txt.length * clamp((Math.min(stop, t) - t0) * cps / Math.max(1, txt.length)));
      line = txt.slice(0, Math.max(0, n)); typingUntil = t0 + txt.length / cps;
    } else {
      const add = (line ? sep : '') + txt;
      const n = Math.round(add.length * clamp((Math.min(stop, t) - it.at) * cps / Math.max(1, add.length)));
      line = line + add.slice(0, Math.max(0, n)); typingUntil = it.at + add.length / cps;
    }
  }
  const first = Math.min(...(o.items && o.items.length ? o.items : [{at: o.at ?? o.start}]).map((it) => it.at ?? o.start));
  if (!items.length) return {line: '', typing: false, idle: 0, waiting: t < first};
  return {line, typing: t < typingUntil + 0.02, idle: t - typingUntil, waiting: false};
};

export const HeadNote = ({E, t, o}) => {
  const K = tok(E), W = E.width;
  const size = o.size || 32;
  const {line, typing, idle, waiting} = headLine(o, t);
  const inK = tw(t, o.start, 0.22), exK = o.end != null ? tw(t, o.end - 0.25, 0.25, qIn) : 0;
  let x = o.x ?? W / 2, y = o.y ?? 300;
  if (o.anchor === 'face') {
    const f = faceSmooth(E, t);
    if (!f) return null;
    const hh = Math.min(f.h, 520);
    x = o.x ?? f.x; y = clamp(f.y - hh * (o.lift ?? 1.45) + (o.dy || 0), 285, E.height * 0.6);
  }
  const onBroll = brollOn(E, t);
  const col = boardOn(E, t) || onBroll ? '#FFFFFF' : toneColor(E, o.tone || 'auto');
  const dark = col === K.ink || /^#0|^#1/.test(String(col));
  // a dark glass pill behind the label over pictures (b-roll) or when asked: the label stays readable on bright skies
  const pill = o.pill === true || (o.pill !== false && onBroll);
  const blink = typing || (!waiting && idle < 0.5) ? 1 : (Math.floor(t * 2.2) % 2 === 0 ? 1 : 0);
  const caretOn = o.caret === false ? 0 : waiting ? blink : (typing || idle < 1.6) ? blink * (1 - clamp((idle - 1.2) / 0.4)) : 0;
  const track = o.track ?? 0.2;
  const left = o.align === 'left';
  return (
    <div style={{position: 'absolute', left: left ? x : x, top: y, transform: `translate(${left ? '0' : '-50%'}, -50%) translateY(${((1 - inK) * 10 - exK * 10).toFixed(1)}px)`,
      opacity: inK * (1 - exK), display: 'flex', alignItems: 'center', gap: size * 0.5, whiteSpace: 'nowrap', fontFamily: K.mono, fontWeight: o.weight || 560,
      fontSize: size, letterSpacing: `${track}em`, color: col, textShadow: dark || pill ? 'none' : '0 1px 2px rgba(0,0,0,0.45), 0 2px 12px rgba(0,0,0,0.35)',
      ...(pill && line ? {padding: `${(size * 0.42).toFixed(1)}px ${(size * 0.7).toFixed(1)}px`, borderRadius: 999, background: dark ? 'rgba(255,255,255,0.72)' : 'rgba(14,12,10,0.52)',
        boxShadow: '0 8px 24px rgba(0,0,0,0.22)', border: `1.5px solid ${dark ? 'rgba(0,0,0,0.08)' : 'rgba(255,255,255,0.14)'}`} : {})}}>
      {o.dot ? <span style={{width: size * 0.36, height: size * 0.36, borderRadius: '50%', background: K.accent, flex: '0 0 auto',
        boxShadow: `0 0 ${size * 0.4}px ${hexA(K.accent, 0.6)}`, opacity: 0.7 + 0.3 * Math.sin(t * 3)}} /> : null}
      <span>{line}</span>
      <span style={{display: 'inline-block', width: size * 0.56, height: size * 0.98, marginLeft: -size * track * 0.6 - size * 0.5, background: col,
        opacity: caretOn * 0.85, transform: 'translateY(0.04em)'}} />
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ tags (shared)
const tagMetrics = (K, text, size, surface) => {
  const fam = surface === 'outline' ? K.ui : K.tagFont, wt = surface === 'outline' ? 700 : K.tagWeight;
  const w = measureText(String(text), fam, wt, size);
  const padX = size * (surface === 'outline' ? 0.42 : 0.66), padY = size * (surface === 'outline' ? 0.22 : 0.2);
  return {w: Math.ceil(w + padX * 2), h: Math.ceil(size * 1.12 + padY * 2), fam, wt, padX, padY};
};
// flow boxes into rows (dir row) or a column; returns positions relative to the group's top-left + group size
const flow = (boxes, maxW, gap, dir, align) => {
  const pos = [];
  if (dir === 'column') {
    let y = 0, w = 0;
    for (const b of boxes) { pos.push([0, y]); y += b.h + gap; w = Math.max(w, b.w); }
    if (align !== 'left') boxes.forEach((b, i) => { pos[i][0] = (w - b.w) * (align === 'right' ? 1 : 0.5); });
    return {pos, w, h: Math.max(0, y - gap)};
  }
  const rows = [[]]; let rw = 0;
  boxes.forEach((b, i) => {
    if (rows[rows.length - 1].length && rw + gap + b.w > maxW) { rows.push([]); rw = 0; }
    rw += (rows[rows.length - 1].length ? gap : 0) + b.w; rows[rows.length - 1].push(i);
  });
  let y = 0, W = 0;
  const widths = rows.map((r) => r.reduce((s, i, k) => s + boxes[i].w + (k ? gap : 0), 0));
  W = Math.max(...widths, 0);
  rows.forEach((r, ri) => {
    let x = align === 'left' ? 0 : align === 'right' ? W - widths[ri] : (W - widths[ri]) / 2;
    const rh = Math.max(...r.map((i) => boxes[i].h));
    r.forEach((i) => { pos[i] = [x, y + (rh - boxes[i].h) / 2]; x += boxes[i].w + gap; });
    y += rh + gap * 1.15;
  });
  return {pos, w: W, h: Math.max(0, y - gap * 1.15)};
};

// torn tape ends: a zigzag down the left edge and up the right one, different for every strip
const tornClip = (seed, w) => {
  const k = Math.min(0.06, 9 / Math.max(60, w)), L = [], R = [], n = 7;
  for (let j = 0; j <= n; j++) {
    const yy = (j / n) * 100;
    L.push(`${(rand(seed * 13.1 + j) * k * 100).toFixed(2)}% ${yy.toFixed(1)}%`);
    R.push(`${(100 - rand(seed * 29.7 + j * 3.3) * k * 100).toFixed(2)}% ${(100 - yy).toFixed(1)}%`);
  }
  return `polygon(${[...L, ...R].join(', ')})`;
};
const strikeBar = (K, size, w, sp) => (
  <div style={{position: 'absolute', left: -size * 0.18, top: '50%', height: Math.max(5, size * 0.12), width: (w + size * 0.36) * sp, borderRadius: 99,
    background: K.accent2, transform: 'translateY(-50%) rotate(-3deg)', transformOrigin: '0 50%', boxShadow: '0 2px 6px rgba(0,0,0,0.18)'}} />
);

// one label on its word. Tape (paper / accent / ink) is LAID on from left to right like a strip pulled off the roll, then
// pressed; an outlined label is placed (a short drop, a turn into its angle, the shadow tightening as it lands).
const Tag = ({E, K, t, it, i, size, surface, m, x, y, exitAt, sub}) => {
  const at = it.at;
  if (at == null || t < at) return null;
  const sgn = i % 2 ? 1 : -1;
  const rot0 = it.rot ?? [-1.6, 1.2, -0.7, 1.9, -1.2, 0.8, -2.0, 1.4][i % 8];
  const exA = it.until ?? exitAt;
  const ex = exA != null ? tw(t, exA - 0.24 - (it.until != null ? 0 : (i % 4) * 0.025), 0.22, qIn) : 0;
  const st = it.strikeAt;
  const sp = st != null ? tw(t, st, 0.24) : 0;
  const dim = st != null && sp > 0 ? 1 - 0.38 * sp : 1;
  const tone = it.tone || (surface === 'paper' ? null : surface);
  const surf = tone === 'accent' ? 'accent' : tone === 'ink' ? 'ink' : tone === 'outline' ? 'outline' : tone === 'paper' ? 'paper' : surface;
  const txt = surf === 'ink' || surf === 'outline' ? '#FFFFFF' : K.ink;
  const text = (
    <div style={{position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center',
      fontFamily: m.fam, fontWeight: m.wt, fontSize: size, lineHeight: 1, letterSpacing: surf === 'outline' ? '-0.01em' : '-0.025em', color: txt, whiteSpace: 'nowrap',
      textShadow: surf === 'outline' ? '0 1px 2px rgba(0,0,0,0.4)' : 'none'}}>
      <span>{it.text}</span>
      {sub ? <span style={{marginTop: size * 0.22, fontFamily: K.mono, fontWeight: 500, fontSize: size * 0.36, letterSpacing: '0.16em', color: hexA(txt === K.ink ? K.ink : '#FFFFFF', 0.62)}}>{String(sub).toUpperCase()}</span> : null}
    </div>
  );
  if (surf === 'outline') {
    const pf = (k) => qOut(prog(k, at, at + 0.36));
    const p = pf(t), v = Math.abs(pf(t + 1 / 120) - pf(t - 1 / 120)) * 60 * size * 0.9;
    const lift = 1 - p;
    return (
      <div style={{position: 'absolute', left: x, top: y - lift * size * 0.95 - ex * size * 0.5, width: m.w, height: m.h,
        transform: `rotate(${(rot0 + lift * 6 * sgn).toFixed(2)}deg) scale(${(1 + lift * 0.08 - ex * 0.05).toFixed(4)})`,
        opacity: clamp(prog(t, at, at + 0.1)) * (1 - ex) * dim, filter: dirBlur(0, shutterBlur(v))}}>
        <div style={{position: 'absolute', inset: 0, borderRadius: size * 0.2, boxSizing: 'border-box', background: 'rgba(16,14,10,0.66)',
          border: `${Math.max(3, size * 0.075).toFixed(1)}px solid ${K.accent2}`, boxShadow: `0 ${(8 + 10 * lift).toFixed(1)}px ${(18 + 14 * lift).toFixed(1)}px rgba(0,0,0,0.28)`}} />
        {text}
        {st != null && sp > 0 ? strikeBar(K, size, m.w, sp) : null}
      </div>
    );
  }
  const face = surf === 'accent' ? K.accent : surf === 'ink' ? '#1C1A16' : K.tag;
  const lay = qOut(prog(t, at, at + 0.32));
  const press = 1 + 0.025 * Math.sin(Math.PI * clamp((t - at - 0.2) / 0.22));
  const seed = Math.round((at * 97 + i * 13) % 1000);
  return (
    <div style={{position: 'absolute', left: x, top: y - ex * size * 0.4, width: m.w, height: m.h,
      transform: `rotate(${(rot0 - (1 - lay) * 1.6 * sgn).toFixed(2)}deg) scale(${(press * (1 - 0.04 * ex)).toFixed(4)})`,
      opacity: (1 - ex) * dim, clipPath: lay < 0.999 ? `inset(-40% ${((1 - lay) * 100).toFixed(2)}% -40% -12%)` : 'none',
      filter: 'drop-shadow(0 1.5px 1px rgba(0,0,0,0.2)) drop-shadow(0 7px 12px rgba(20,14,4,0.2))'}}>
      <div style={{position: 'absolute', inset: 0, clipPath: tornClip(seed, m.w), opacity: surf === 'paper' ? 0.95 : 1,
        background: `linear-gradient(180deg, rgba(255,255,255,${surf === 'ink' ? 0.08 : 0.26}), rgba(255,255,255,0) 45%, rgba(0,0,0,0.05)), ` +
          `repeating-linear-gradient(90deg, rgba(90,70,30,${surf === 'ink' ? 0 : 0.045}) 0px, rgba(90,70,30,${surf === 'ink' ? 0 : 0.045}) 2px, rgba(0,0,0,0) 2px, rgba(0,0,0,0) 9px), ${face}`}} />
      {text}
      {st != null && sp > 0 ? strikeBar(K, size, m.w, sp) : null}
    </div>
  );
};

// a strip of tape (decoration on cards): torn ends, translucent
const Tape = ({w, h, x, y, rot, seed, color = 'rgba(236,227,206,0.84)'}) => (
  <div style={{position: 'absolute', left: x, top: y, width: w, height: h, transform: `rotate(${rot}deg)`, filter: 'drop-shadow(0 2px 2px rgba(0,0,0,0.14))'}}>
    <div style={{position: 'absolute', inset: 0, clipPath: tornClip(seed, w),
      background: `linear-gradient(180deg, rgba(255,255,255,0.3), rgba(255,255,255,0) 50%), repeating-linear-gradient(90deg, rgba(90,70,30,0.05) 0px, rgba(90,70,30,0.05) 2px, rgba(0,0,0,0) 2px, rgba(0,0,0,0) 9px), ${color}`}} />
  </div>
);

// a typed mono label (group header, board title)
const MonoLabel = ({K, t, text, at, x, y, size, color, weight = 500, track = 0.16, cps = 30, opacity = 1}) => {
  if (!text || at == null || t < at) return null;
  const s = String(text).toUpperCase();
  const n = Math.round(s.length * clamp((t - at) * cps / Math.max(1, s.length)));
  return (
    <div style={{position: 'absolute', left: x, top: y, fontFamily: K.mono, fontWeight: weight, fontSize: size, letterSpacing: `${track}em`, color, whiteSpace: 'nowrap', opacity}}>
      {s.slice(0, n)}<span style={{opacity: 0}}>{s.slice(n)}</span>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ NoteTags
// {items:[{text, at, until?, tone?, rot?, strikeAt?}], label?, labelAt?, slot (above / above-left / above-right / left /
//  right / chest) | x,y (centre, fixed), dx, dy, dir (row / column), maxW (820), size (46), gap, surface (paper / outline /
//  ink / accent), pointer (false), align}
const NOTE_SLOTS = {
  // anchor point (multiples of the face height from the face centre) and which edge of the group sits on it
  'above': {ax: 0, ay: -1.06, ex: 0.5, ey: 1},
  'above-left': {ax: -0.32, ay: -0.88, ex: 1, ey: 1},
  'above-right': {ax: 0.32, ay: -0.88, ex: 0, ey: 1},
  'left': {ax: -0.72, ay: -0.2, ex: 1, ey: 0.5},
  'right': {ax: 0.72, ay: -0.2, ex: 0, ey: 0.5},
  'chest': {ax: 0, ay: 1.25, ex: 0.5, ey: 0},
};
export const NOTE_SLOT_NAMES = Object.keys(NOTE_SLOTS);

export const NoteTags = ({E, t, o}) => {
  const K = tok(E), W = E.width, H = E.height;
  const size = o.size || 46, surface = o.surface || 'paper';
  const items = (o.items || []).filter((it) => it && it.text);
  if (!items.length) return null;
  const ms = items.map((it) => tagMetrics(K, it.text, size, it.tone === 'outline' ? 'outline' : surface));
  const gap = o.gap ?? size * 0.36;
  const dir = o.dir || (o.slot === 'left' || o.slot === 'right' ? 'column' : 'row');
  const align = o.align || (o.slot === 'left' || o.slot === 'above-left' ? 'right' : o.slot === 'right' || o.slot === 'above-right' ? 'left' : 'center');
  const L = flow(ms, o.maxW || 820, gap, dir, align);
  const labH = o.label ? size * 0.92 : 0;
  const gw = L.w, gh = L.h + labH;
  let gx, gy, f = null;
  if (o.slot || (o.x == null && o.y == null)) {
    f = faceSmooth(E, t);
    if (!f) return null;
    const S = NOTE_SLOTS[o.slot || 'above'] || NOTE_SLOTS.above;
    const hh = Math.min(f.h, 520);
    gx = f.x + S.ax * hh - S.ex * gw + (o.dx || 0);
    gy = f.y + S.ay * hh - S.ey * gh + (o.dy || 0);
  } else {
    gx = (o.x ?? W / 2) - gw / 2; gy = (o.y ?? H * 0.3) - gh / 2;
  }
  gx = clamp(gx, 56, W - 56 - gw); gy = clamp(gy, 270, H - 300 - gh);
  const first = Math.min(...items.map((it) => it.at ?? o.start));
  const pk = tw(t, first, 0.34);
  const exK = o.end != null ? tw(t, o.end - 0.24, 0.24, qIn) : 0;
  const lineCol = o.pointerColor ? (E.style.colors[o.pointerColor] || o.pointerColor) : toneColor(E, 'auto');
  const labCol = surface === 'outline' ? '#FFFFFF' : toneColor(E, o.labelTone || 'auto');
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {o.pointer && f ? (() => {
        // a hairline from the group down to the head, with a dot on the hair: an annotation, not decoration
        const hh = Math.min(f.h, 520);
        const side = o.slot === 'left' ? -1 : o.slot === 'right' ? 1 : 0;
        const below = o.slot === 'chest' || (!o.slot && gy > f.y);
        const x0 = side ? (side < 0 ? gx + gw + 10 : gx - 10) : gx + gw / 2;
        const y0 = side ? gy + gh / 2 : below ? gy - size * 0.32 : gy + gh + size * 0.32;
        const x1 = side ? f.x + side * hh * 0.42 : f.x + (o.pointerDx || 0);
        const y1 = side ? f.y - hh * 0.28 : below ? f.y + hh * 0.72 : f.y - hh * 0.66;
        const len = Math.hypot(x1 - x0, y1 - y0), dp = pk * (1 - exK);
        return (
          <svg width={W} height={H} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
            <path d={`M ${x0} ${y0} L ${x1} ${y1}`} stroke={lineCol} strokeWidth={3} strokeLinecap="round" fill="none" strokeDasharray={len} strokeDashoffset={len * (1 - dp)} opacity={0.85} />
            <circle cx={x1} cy={y1} r={7 * clamp(dp * 3 - 2)} fill={lineCol} opacity={0.9} />
            <circle cx={x1} cy={y1} r={7 + 14 * clamp(dp * 3 - 2)} fill="none" stroke={lineCol} strokeWidth={2} opacity={0.5 * (1 - clamp(dp * 3 - 2))} />
          </svg>
        );
      })() : null}
      {o.label ? <MonoLabel K={K} t={t} text={o.label} at={o.labelAt ?? first - 0.1} x={gx + (align === 'center' ? 0 : 0)} y={gy} size={size * 0.5}
        color={labCol} weight={600} opacity={1 - exK} /> : null}
      {items.map((it, i) => (
        <Tag key={i} E={E} K={K} t={t} it={{...it, at: it.at ?? o.start + i * 0.18}} i={i} size={size} surface={it.tone === 'outline' ? 'outline' : surface} m={ms[i]}
          x={gx + L.pos[i][0]} y={gy + labH + L.pos[i][1]} exitAt={o.end} />
      ))}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ NoteBoard
// {title?, titleAt?, groups:[{label?, at?, items:[{text, at, sub?, strikeAt?}]}] | items, strikeAt (all lines), y (330),
//  size (60), w (900), x, push (0.015), backdrop (true), blur (26), dim (0.46), numbers (true)}
// A ruled index card taped over the blurred, dimmed frame: the heading is typed in mono and highlighted, every line is
// numbered and typed on its word, a marker strikes lines out. The speaker keeps moving behind the blur.
export const NoteBoard = ({E, t, o}) => {
  const K = tok(E), W = E.width;
  const size = o.size || 60;
  const end = o.end ?? E.duration;
  const k = tw(t, o.start, 0.35, cInOut) * (1 - tw(t, end - 0.3, 0.3, cInOut));
  const groups = o.groups || [{items: o.items || []}];
  const rows = [];
  if (o.title) rows.push({kind: 'title', text: o.title, at: o.titleAt ?? o.start + 0.15});
  let n = 0;
  groups.forEach((g) => {
    const its = (g.items || []).filter((it) => it && it.text);
    if (g.label) rows.push({kind: 'label', text: g.label, at: g.at ?? ((its[0] && its[0].at != null ? its[0].at : o.start + 0.3) - 0.15)});
    its.forEach((it) => { n += 1; rows.push({kind: 'item', ...it, num: n, at: it.at ?? o.start + 0.3 + n * 0.3, strikeAt: it.strikeAt ?? (o.strikeAt != null ? o.strikeAt + (n - 1) * 0.08 : undefined)}); });
  });
  const lineH = Math.round(size * 1.5);
  const cw = Math.min(o.w || 900, W - 90), padT = Math.round(lineH * 0.85), padB = Math.round(lineH * 0.6);
  const ch = padT + rows.length * lineH + padB;
  const cx = o.x ?? W / 2, y0 = o.y ?? 330;
  const margin = Math.round(size * 1.95);
  const pin = (q) => qOut(prog(q, o.start, o.start + 0.5));
  const p = pin(t), vy = Math.abs(pin(t + 1 / 120) - pin(t - 1 / 120)) * 60 * 170;
  const e = tw(t, end - 0.3, 0.3, qIn);
  const rot = -1.1 - (1 - p) * 3.5 + e * 2;
  const push = 1 + (o.push ?? 0.015) * cInOut(prog(t, o.start, end));
  const cps = o.cps || 30;
  const typed = (txt, at) => { const s_ = String(txt); const nn = Math.round(s_.length * clamp((t - at) * cps / Math.max(1, s_.length))); return [s_.slice(0, nn), s_.slice(nn), nn < s_.length]; };
  const ruleCol = 'rgba(84,128,196,0.24)';
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {o.backdrop !== false ? <AbsoluteFill style={{backdropFilter: `blur(${((o.blur ?? 26) * k).toFixed(1)}px)`, WebkitBackdropFilter: `blur(${((o.blur ?? 26) * k).toFixed(1)}px)`,
        background: `rgba(18,14,8,${((o.dim ?? 0.46) * k).toFixed(3)})`}} /> : null}
      <div style={{position: 'absolute', left: cx - cw / 2, top: y0, width: cw, height: ch, opacity: clamp(p * 2) * (1 - e),
        transform: `translateY(${((1 - p) * 170 + e * 140).toFixed(1)}px) rotate(${rot.toFixed(2)}deg) scale(${push.toFixed(4)})`, filter: dirBlur(0, Math.min(24, shutterBlur(vy)))}}>
        <div style={{position: 'absolute', inset: 0, borderRadius: 8, overflow: 'hidden', background: '#FBF9F3',
          boxShadow: '0 40px 80px rgba(0,0,0,0.42), 0 2px 0 rgba(0,0,0,0.06)'}}>
          <div style={{position: 'absolute', inset: 0, backgroundImage: `url(${staticFile('fx/grain.jpg')})`, backgroundSize: '380px 380px', mixBlendMode: 'multiply', opacity: 0.06}} />
          <div style={{position: 'absolute', left: 0, right: 0, top: padT, height: rows.length * lineH + 2,
            backgroundImage: `repeating-linear-gradient(180deg, rgba(0,0,0,0) 0px, rgba(0,0,0,0) ${lineH - 2}px, ${ruleCol} ${lineH - 2}px, ${ruleCol} ${lineH}px)`}} />
          {o.title ? <div style={{position: 'absolute', left: 0, right: 0, top: padT + lineH - 2, height: 2.5, background: 'rgba(229,56,59,0.55)'}} /> : null}
          {o.numbers !== false ? <div style={{position: 'absolute', top: 0, bottom: 0, left: margin, width: 2, background: 'rgba(229,56,59,0.38)'}} /> : null}
        </div>
        <Tape w={230} h={54} x={cw / 2 - 115} y={-26} rot={-3.5} seed={7} />
        {rows.map((r, ri) => {
          if (t < r.at) return null;
          const top = padT + ri * lineH;
          const box = {position: 'absolute', left: margin + size * 0.45, right: size * 0.6, top, height: lineH, display: 'flex', alignItems: 'flex-end',
            paddingBottom: Math.round(lineH * 0.16), whiteSpace: 'nowrap'};
          if (r.kind === 'title' || r.kind === 'label') {
            const [a_, b_, typing] = typed(String(r.text).toUpperCase(), r.at);
            const ts = size * (r.kind === 'title' ? 0.5 : 0.42);
            const hl = r.kind === 'title' ? qOut(prog(t, r.at + String(r.text).length / cps, r.at + String(r.text).length / cps + 0.32)) : 0;
            const tw_ = measureText(String(r.text).toUpperCase(), K.mono, 600, ts) * 1.16;
            return (
              <div key={ri} style={{...box, left: r.kind === 'title' ? size * 0.55 : box.left}}>
                <div style={{position: 'relative', fontFamily: K.mono, fontWeight: 600, fontSize: ts, letterSpacing: `${r.kind === 'title' ? 0.16 : 0.12}em`,
                  color: r.kind === 'title' ? K.ink : K.muted, isolation: 'isolate'}}>
                  {hl > 0 ? <span style={{position: 'absolute', left: -ts * 0.25, top: ts * 0.35, height: ts * 0.75, width: (tw_ + ts * 0.5) * hl, background: K.accent,
                    opacity: 0.9, zIndex: -1, transform: 'rotate(-1deg)', borderRadius: 3}} /> : null}
                  {r.kind === 'label' ? <span style={{display: 'inline-block', width: ts * 0.5, height: ts * 0.5, background: K.accent2, marginRight: ts * 0.6, transform: 'translateY(-0.08em)'}} /> : null}
                  {a_}
                  {typing ? <span style={{display: 'inline-block', width: ts * 0.55, height: ts, background: K.ink, opacity: 0.75, marginLeft: 2, transform: 'translateY(0.12em)'}} /> : null}
                  <span style={{opacity: 0}}>{b_}</span>
                </div>
              </div>
            );
          }
          const [a_, b_, typing] = typed(r.text, r.at);
          const sp = r.strikeAt != null ? tw(t, r.strikeAt, 0.24) : 0;
          const tw_ = measureText(String(r.text), K.tagFont, K.tagWeight, size) * 0.975;
          return (
            <React.Fragment key={ri}>
              {o.numbers !== false ? <div style={{position: 'absolute', left: 0, width: margin - size * 0.32, top, height: lineH, display: 'flex', alignItems: 'flex-end', justifyContent: 'flex-end',
                paddingBottom: Math.round(lineH * 0.2), fontFamily: K.mono, fontWeight: 500, fontSize: size * 0.4, color: K.muted, letterSpacing: '0.06em'}}>{String(r.num).padStart(2, '0')}</div> : null}
              <div style={box}>
                <div style={{position: 'relative', fontFamily: K.tagFont, fontWeight: K.tagWeight, fontSize: size, lineHeight: 1, letterSpacing: '-0.025em', color: K.ink, opacity: sp > 0 ? 1 - 0.42 * sp : 1}}>
                  {a_}
                  {typing ? <span style={{display: 'inline-block', width: size * 0.5, height: size * 0.9, background: K.ink, opacity: 0.7, marginLeft: 3, transform: 'translateY(0.1em)'}} /> : null}
                  <span style={{opacity: 0}}>{b_}</span>
                  {r.sub ? <span style={{marginLeft: size * 0.4, fontFamily: K.mono, fontWeight: 500, fontSize: size * 0.36, letterSpacing: '0.14em', color: K.muted,
                    opacity: clamp((t - r.at - String(r.text).length / cps) * 4)}}>— {String(r.sub).toUpperCase()}</span> : null}
                  {sp > 0 ? <div style={{position: 'absolute', left: -size * 0.12, top: '55%', height: Math.max(5, size * 0.11), width: (tw_ + size * 0.24) * sp, borderRadius: 99,
                    background: K.accent2, transform: 'translateY(-50%) rotate(-2deg)', transformOrigin: '0 50%'}} /> : null}
                </div>
              </div>
            </React.Fragment>
          );
        })}
      </div>
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ NoteClips
// {items:[{file (set by the planner from src), kind, from (s), label, at, until?, tilt?}], w (230), h (300), frame (paper border),
//  size (label size, 34)}. Up to 3 cards: left of the head, above it, right of it (2 cards: left + right; 1: above).
export const NoteClips = ({E, t, o}) => {
  const f = faceSmooth(E, t);
  if (!f) return null;
  const K = tok(E), W = E.width, H = E.height;
  const items = (o.items || []).filter((it) => it && it.file);
  const n = items.length;
  if (!n) return null;
  const hh = Math.min(f.h, 520);
  const headTop = f.y - 0.8 * hh;
  let cw = o.w || 230, ch = o.h || 300;
  const room = headTop - 14 - 262;               // space above the head for the middle card
  const fit = n !== 2 && room < ch ? Math.max(0.62, room / ch) : 1;
  const mid = {x: f.x, y: headTop - 14 - (ch * fit) / 2, s: fit, tilt: 1.5};
  const sideX = hh * 0.56 + 26 + cw / 2;
  const L = {x: clamp(f.x - sideX, cw / 2 + 34, W - cw / 2 - 34), y: f.y - hh * 0.52, s: 1, tilt: -6};
  const R = {x: clamp(f.x + sideX, cw / 2 + 34, W - cw / 2 - 34), y: f.y - hh * 0.52, s: 1, tilt: 6};
  const slots = n === 1 ? [mid] : n === 2 ? [L, R] : [L, mid, R];
  const exK = o.end != null ? tw(t, o.end - 0.3, 0.3, qIn) : 0;
  const lsz = o.size || 34;
  return (
    <AbsoluteFill style={{pointerEvents: 'none'}}>
      {items.slice(0, 3).map((it, i) => {
        const at = it.at ?? o.start + i * 0.25;
        if (t < at) return null;
        const S = slots[i];
        const pf = (k) => qOut(prog(k, at, at + 0.5));
        const p = pf(t), v = Math.abs(pf(t + 1 / 120) - pf(t - 1 / 120)) * 60 * 140;
        const ex = Math.max(exK, it.until != null ? tw(t, it.until - 0.28, 0.28, qIn) : 0);
        const tilt = it.tilt ?? S.tilt;
        const w = cw * S.s, h = ch * S.s;
        const bob = Math.sin((t - at) * 1.2 + i * 2) * 4;
        const lm = it.label ? tagMetrics(K, it.label, lsz, 'paper') : null;
        const media = it.kind === 'video'
          ? <Sequence from={Math.round(at * E.fps)} layout="none"><OffthreadVideo src={staticFile(it.file)} muted startFrom={Math.round((it.from || 0) * E.fps)} style={{width: '100%', height: '100%', objectFit: 'cover'}} /></Sequence>
          : <Img src={staticFile(it.file)} style={{width: '100%', height: '100%', objectFit: 'cover', transform: `scale(${(1.02 + 0.06 * prog(t, at, at + 4)).toFixed(4)})`}} />;
        return (
          <div key={i} style={{position: 'absolute', left: S.x - w / 2, top: S.y - h / 2 - (1 - p) * 140 + bob - ex * 90, width: w, height: h,
            transform: `rotate(${(tilt + (1 - p) * (i - 1 || 1) * 12).toFixed(2)}deg) scale(${(0.9 + 0.1 * p).toFixed(4)})`, opacity: clamp(p * 2.4) * (1 - ex),
            filter: dirBlur(0, Math.min(24, shutterBlur(v)))}}>
            <div style={{position: 'absolute', inset: 0, borderRadius: 16, overflow: 'hidden', background: '#111',
              boxShadow: `0 0 0 ${o.frame === false ? 0 : 7}px #FBF9F3, 0 ${(22 + (1 - p) * 20).toFixed(1)}px ${(40 + (1 - p) * 20).toFixed(1)}px rgba(20,14,4,0.38)`}}>
              {media}
            </div>
            {o.tape !== false ? <Tape w={w * 0.5} h={Math.max(30, w * 0.15)} x={w * 0.25} y={-Math.max(30, w * 0.15) * 0.55} rot={(i - 1) * 4 - 2} seed={11 + i * 5} /> : null}
            {lm ? (
              <div style={{position: 'absolute', left: '50%', bottom: -lm.h * 0.55, transform: `translateX(-50%) rotate(${(-tilt * 0.5).toFixed(2)}deg)`, width: lm.w, height: lm.h}}>
                <Tag E={E} K={K} t={t} it={{text: it.label, at: at + 0.26, rot: 0}} i={i} size={lsz} surface="paper" m={lm} x={0} y={0} />
              </div>
            ) : null}
          </div>
        );
      })}
    </AbsoluteFill>
  );
};

// ------------------------------------------------------------------------------------------------ BigNote
// {text (1-2 spoken words), at, kicker?, kickerAt?, kickerPos (above / right / left), kickerSize, size (220), x, y (centre of
//  the word, 470), tone (auto / ink / paper / accent2 / #hex), mark (highlight / circle / underline / none), markAt,
//  case (none / upper / lower), layer (below; behind = the head covers its lower part)}
// The hook word written like a note: the kicker types in mono above, the word comes into focus, then a highlighter
// stroke is drawn behind it (or a marker circle / double underline is drawn around / under it).
export const BigNote = ({E, t, o}) => {
  const K = tok(E), W = E.width, F = E.style.fonts || {};
  const at = o.at ?? o.start, end = o.end ?? E.duration;
  const fam = F.display || 'InterTight', wt = F.displayWeight || 900;
  const raw = String(o.text || '');
  const text = o.case === 'upper' ? raw.toUpperCase() : o.case === 'lower' ? raw.toLowerCase() : raw;
  let size = o.size || 220;
  const track = -0.045;
  let wd = measureText(text, fam, wt, size) + track * size * Math.max(0, text.length - 1);
  if (wd > W - 120) { size = size * (W - 120) / wd; wd = W - 120; }
  const cx = o.x ?? W / 2, cy = o.y ?? 470;
  const col = toneColor(E, o.tone || 'auto');
  const dark = col === K.ink || /^#0|^#1/.test(String(col));
  const p = qOut(prog(t, at, at + 0.42));
  const ex = tw(t, end - 0.28, 0.28, qIn);
  const creep = 1 + 0.025 * prog(t, at, end);
  const mark = o.mark || 'highlight';
  const mAt = o.markAt ?? at + 0.22;
  const mp = qOut(prog(t, mAt, mAt + (mark === 'circle' ? 0.5 : 0.34)));
  const ks = o.kickerSize || Math.max(28, Math.round(size * 0.15));
  const kAt = o.kickerAt ?? o.start;
  const kTxt = String(o.kicker || '').toUpperCase();
  const kn = Math.round(kTxt.length * clamp((t - kAt) * 28 / Math.max(1, kTxt.length)));
  const shadow = dark ? 'none' : '0 2px 4px rgba(0,0,0,0.35), 0 10px 40px rgba(0,0,0,0.35)';
  const hlH = size * 0.36, hlTop = cy + size * 0.02;
  const rx = wd / 2 + size * 0.3, ry = size * 0.62;
  const circ = (() => {  // an open hand-drawn ellipse: starts at 200 deg, overshoots 30 deg
    const pts = [];
    for (let j = 0; j <= 48; j++) {
      const a = (200 + j * 7.9) * Math.PI / 180, wob = 1 + 0.035 * Math.sin(j * 1.7);
      pts.push(`${(cx + Math.cos(a) * rx * wob).toFixed(1)},${(cy + Math.sin(a) * ry * wob * (1 + 0.06 * j / 48)).toFixed(1)}`);
    }
    return 'M' + pts.join(' L');
  })();
  return (
    <AbsoluteFill style={{pointerEvents: 'none', opacity: 1 - ex, transform: `translateY(${(-ex * 18).toFixed(1)}px)`}}>
      {o.kicker && t >= kAt && (o.kickerPos || 'above') === 'above' ? (
        <div style={{position: 'absolute', left: 0, width: W, top: cy - size * 0.58 - ks * 1.5, display: 'flex', justifyContent: 'center'}}>
          <div style={{fontFamily: K.mono, fontWeight: 560, fontSize: ks, letterSpacing: '0.2em', color: col, whiteSpace: 'nowrap', textShadow: dark ? 'none' : '0 1px 2px rgba(0,0,0,0.45)'}}>
            {kTxt.slice(0, kn)}<span style={{opacity: 0}}>{kTxt.slice(kn)}</span>
          </div>
        </div>
      ) : null}
      {o.kicker && t >= kAt && (o.kickerPos === 'right' || o.kickerPos === 'left') ? (() => {
        // a margin note beside the word: the kicker in two lines + a drawn arrow pointing at the word
        const right = o.kickerPos === 'right';
        const words = kTxt.split(/\s+/), half = Math.ceil(words.length / 2);
        const lines = words.length > 1 ? [words.slice(0, half).join(' '), words.slice(half).join(' ')] : [kTxt];
        let left_ = kn;
        const shown = lines.map((ln) => { const s_ = ln.slice(0, Math.max(0, left_)); left_ -= ln.length + 1; return s_; });
        const edge = right ? cx + wd / 2 + size * 0.14 : cx - wd / 2 - size * 0.14;
        const ap = qOut(prog(t, kAt + 0.15, kAt + 0.5));
        // the note must stay inside the side safe area: shrink the mono size to the room beside the word
        const longest = Math.max(...lines.map((ln) => ln.length), 1);
        const room = right ? (W - 64) - (edge + 66) : (edge - 66) - 64;
        const ksF = Math.max(20, Math.min(ks, room / (longest * 0.6 * 1.16)));
        const ax0 = right ? edge + 6 : edge - 6, ay = cy - size * 0.1;
        return (
          <>
            <svg width={W} height={E.height} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
              <path d={`M ${ax0 + (right ? 52 : -52)} ${ay - 20} Q ${ax0 + (right ? 22 : -22)} ${ay - 24} ${ax0} ${ay}`} fill="none" stroke={col} strokeWidth={4} strokeLinecap="round"
                pathLength={1} strokeDasharray={1} strokeDashoffset={1 - ap} />
              {ap > 0.85 ? <path d={`M ${ax0 + (right ? 2 : -2)} ${ay - 18} L ${ax0} ${ay} L ${ax0 + (right ? 18 : -18)} ${ay - 6}`} fill="none" stroke={col} strokeWidth={4} strokeLinecap="round" strokeLinejoin="round" /> : null}
            </svg>
            <div style={{position: 'absolute', top: ay - 20 - ksF * 1.3, ...(right ? {left: ax0 + 60} : {right: W - ax0 + 60}), display: 'flex', flexDirection: 'column',
              alignItems: right ? 'flex-start' : 'flex-end', gap: ksF * 0.25, fontFamily: K.mono, fontWeight: 560, fontSize: ksF, letterSpacing: '0.16em', color: col, whiteSpace: 'nowrap',
              textShadow: dark ? 'none' : '0 1px 2px rgba(0,0,0,0.45)'}}>
              {lines.map((ln, j) => <div key={j}>{shown[j]}<span style={{opacity: 0}}>{ln.slice(shown[j].length)}</span></div>)}
            </div>
          </>
        );
      })() : null}
      {mark === 'highlight' && mp > 0 ? (
        <div style={{position: 'absolute', left: cx - wd / 2 - size * 0.1, top: hlTop, width: (wd + size * 0.2) * mp, height: hlH, background: K.accent, opacity: 0.92,
          transform: `rotate(-1.6deg) scale(${creep.toFixed(4)})`, transformOrigin: `${(wd / 2 + size * 0.1).toFixed(0)}px 50%`, mixBlendMode: dark ? 'multiply' : 'normal',
          clipPath: 'polygon(0% 8%, 2% 0%, 98% 4%, 100% 14%, 99% 88%, 97% 100%, 3% 94%, 0.5% 80%)'}} />
      ) : null}
      {t >= at ? (
        <div style={{position: 'absolute', left: cx, top: cy, transform: `translate(-50%, -50%) scale(${((1.06 - 0.06 * p) * creep).toFixed(4)})`, whiteSpace: 'nowrap',
          fontFamily: fam, fontWeight: wt, fontSize: size, lineHeight: 1, letterSpacing: `${track}em`, color: col, opacity: clamp(p * 1.6),
          filter: p < 0.98 ? `blur(${((1 - p) * 14).toFixed(1)}px)` : 'none', textShadow: shadow}}>{text}</div>
      ) : null}
      {mark === 'circle' && mp > 0 ? (
        <svg width={W} height={E.height} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
          <path d={circ} fill="none" stroke={K.accent2} strokeWidth={Math.max(6, size * 0.035)} strokeLinecap="round" strokeLinejoin="round"
            pathLength={1} strokeDasharray={1} strokeDashoffset={1 - mp} transform={`rotate(-4 ${cx} ${cy})`} />
        </svg>
      ) : null}
      {mark === 'underline' && mp > 0 ? (
        <svg width={W} height={E.height} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
          {[0, 1].map((j) => {
            const q = clamp(mp * 2 - j);
            const y_ = cy + size * (0.46 + 0.1 * j), x0 = cx - wd / 2 + size * 0.05 * j, x1 = cx + wd / 2 - size * 0.08 * j;
            return <path key={j} d={`M${x0} ${y_ + 4} Q ${(x0 + x1) / 2} ${y_ - 8} ${x1} ${y_ + 2}`} fill="none" stroke={K.accent2} strokeWidth={Math.max(5, size * 0.03)}
              strokeLinecap="round" pathLength={1} strokeDasharray={1} strokeDashoffset={1 - q} />;
          })}
        </svg>
      ) : null}
    </AbsoluteFill>
  );
};

export const NOTE_SURFACES = ['paper', 'outline', 'ink', 'accent'];
