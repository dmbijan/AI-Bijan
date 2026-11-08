// Scene kit: building blocks Claude composes into a scene for ANY script, from JSON (no code per video).
//
//   SceneBuild   {items: [...], camera: [{at, zoom, x, y, dur}], anchor: 'face'?}
//     - full-screen scene (layout none, "scene": true): items at canvas positions (x, y = centre), slow camera push,
//       optional camera keys that zoom into a point
//     - face mode ("anchor": "face"): items float in slots around the speaker's head (above, above-left/right,
//       left/right, chest...), never on the face
//
//   item kinds  figure (person: stand/wave/point/cheer/shrug/hold, label, something above the head)
//               crowd (rows of people, optional search bars above every head)
//               thing (45 flat objects: house, store, money, clock, chart, scale, lock, box, truck, chat, bulb, ...)
//               icon · chip · text · bubble (speech/thought) · card · tag (price tag, can hang and swing + stamp)
//               bar (search/prompt bar, typed) · counter (number counting) · bars (bar chart) · meter (progress)
//               arrow (drawn curve) · steps (numbered process) · frame (the speaker's footage in a frame or phone)
//               rect / circle (panels, spotlights)
//   every item  at, until, move (rise|pop|drop|slide|grow|slam|fade|none, from: left|right), idle (float|swing|pulse|spin),
//               x, y | slot, dx, dy, size, tone (main|accent|accent2|soft|faint|paper|danger|#hex), rot, label, z
//
// Pure functions of time, eased (quint/expo), real motion blur on fast moves, colours from the current style.
import React from 'react';
import {AbsoluteFill} from 'remotion';
import {clamp, lerp, prog, rand, shutterBlur, vel} from '../lib/anim.js';
import {Icon} from './Icons.jsx';
import {faceOnCanvas} from './Footage.jsx';
import {Chip, LiveClip, MaskWord, SceneFrame, backOut, dispFont, eOut, fx, qIn, qInOut, qOut, rgba, theme, tw} from './Scenes.jsx';
import {THINGS} from './Things.jsx';

// ------------------------------------------------------------------------------------------------ colours
export const kitColors = (E) => {
  const T = theme(E), C = E.style.colors;
  const fg = T.flat ? T.ink : '#FFFFFF';
  const paper = T.flat ? T.paper : '#2A2350';
  const tones = {main: fg, ink: fg, accent: T.accent, accent2: T.accent2, paper, soft: rgba(fg, 0.38), faint: rgba(fg, 0.18),
    danger: C.danger || '#E5322D', muted: T.flat ? (C.muted || rgba(fg, 0.6)) : 'rgba(255,255,255,0.6)'};
  const tone = (n, d = 'accent') => (n && tones[n]) || (n && String(n).startsWith('#') ? n : tones[d]);
  return {T, C, fg, paper, ln: fg, tone, chipTone: (n) => (n === 'accent' || n === 'accent2' || n === 'paper' || n === 'ink' ? n : (T.flat ? 'ink' : 'glass'))};
};

// ------------------------------------------------------------------------------------------------ motion
const ENTER = 0.62;
export const itemMotion = (t, it, W) => {
  const a = it.at ?? 0;
  if (t < a - 1e-3) return null;
  const m = it.move || 'rise';
  let tx = 0, ty = 0, sc = 1, op = 1, rot = it.rot || 0, blur = 0, bx = 0, by = 0;
  if (m === 'rise') { const p = tw(t, a, ENTER); ty = (1 - p) * 70; op = clamp(p * 1.6); blur = (1 - p) * 10; }
  else if (m === 'pop') { const p = tw(t, a, 0.5, backOut); sc = 0.35 + 0.65 * p; op = clamp(prog(t, a, a + 0.16) * 1.4); blur = (1 - clamp(p)) * 6; }
  else if (m === 'drop') {
    const f = (k) => -(1 - eOut(prog(k, a, a + 0.55))) * 1100;
    ty = f(t); by = Math.min(18, shutterBlur(Math.abs(vel(f, t))) * 0.7);
  } else if (m === 'slide') {
    const dir = it.from === 'right' ? 1 : -1;
    const f = (k) => (1 - qOut(prog(k, a, a + 0.6))) * dir * W * 0.7;
    tx = f(t); bx = Math.min(28, shutterBlur(Math.abs(vel(f, t)))); op = clamp(prog(t, a, a + 0.15) * 1.5);
  } else if (m === 'grow') { const p = tw(t, a, 0.7); sc = Math.max(0.001, p); op = clamp(p * 3); }
  else if (m === 'slam') {
    const f = (k) => lerp(1.7, 1, eOut(prog(k, a, a + 0.4)));
    sc = f(t); blur = Math.min(14, Math.abs(vel(f, t)) * 3); op = clamp(prog(t, a, a + 0.1) * 1.3);
  } else if (m === 'fade') { op = tw(t, a, 0.5); }
  const age = t - a;
  const idle = it.idle;
  if (idle === 'float') ty += Math.sin(age * 1.6 + (it.seed ?? a)) * 9;
  if (idle === 'swing') rot += 12 * Math.exp(-age * 1.4) * Math.sin(age * 5.2) + Math.sin(age * 1.3) * 1.2;
  if (idle === 'pulse') sc *= 1 + 0.05 * Math.max(0, Math.sin(age * 6));
  if (idle === 'spin') rot += age * 40;
  if (it.until != null) { const q = tw(t, it.until - 0.3, 0.3, qIn); op *= 1 - q; sc *= 1 - 0.08 * q; blur += q * 8; }
  return {transform: `translate(${tx.toFixed(1)}px, ${ty.toFixed(1)}px) rotate(${rot.toFixed(2)}deg) scale(${sc.toFixed(4)})`, opacity: clamp(op), filter: fx(bx, by, blur), age};
};

// ------------------------------------------------------------------------------------------------ pieces
const ThingSvg = ({K, it, t, size}) => {
  const draw = THINGS[it.name] || THINGS.star;
  const c1 = K.tone(it.tone, 'accent'), c3 = K.tone(it.tone2, it.name === 'cross' ? 'danger' : 'accent2');
  const w = it.line ?? 5;
  return (
    <svg width={size} height={size} viewBox="0 0 120 120" style={{display: 'block', overflow: 'visible'}}>
      {draw({c1, c2: K.paper, c3, ln: K.ln, w, t, it})}
    </svg>
  );
};

const POSES = {
  wave: [['M58 -168 Q120 -224 112 -332', [112, -340]]],
  point: [['M58 -160 Q140 -176 212 -178', [216, -178]]],
  cheer: [['M58 -168 Q120 -224 112 -332', [112, -340]], ['M-58 -168 Q-120 -224 -112 -332', [-112, -340]]],
  shrug: [['M60 -150 Q150 -150 166 -214', [168, -222]], ['M-60 -150 Q-150 -150 -166 -214', [-168, -222]]],
  hold: [['M64 -150 Q96 -90 40 -84', [34, -84]], ['M-64 -150 Q-96 -90 -40 -84', [-34, -84]]],
};
const Figure = ({E, K, it, t, size}) => {
  const col = K.tone(it.tone, 'main');
  const h = size, w = h * 460 / 430;
  const arms = POSES[it.pose] || [];
  return (
    <div style={{position: 'relative', width: w, height: h}}>
      <svg width={w} height={h} viewBox="-230 -420 460 430" style={{position: 'absolute', inset: 0, overflow: 'visible', transform: it.flip ? 'scaleX(-1)' : 'none'}}>
        <path d="M-78 0 L-78 -118 Q-78 -196 0 -196 Q78 -196 78 -118 L78 0 Z" fill={col} />
        <circle cx={0} cy={-262} r={56} fill={col} />
        {arms.map(([d, [hx, hy]], k) => (
          <g key={k}><path d={d} fill="none" stroke={col} strokeWidth={34} strokeLinecap="round" /><circle cx={hx} cy={hy} r={21} fill={col} /></g>
        ))}
      </svg>
      {it.holds ? (
        <div style={{position: 'absolute', left: w / 2, top: h * 0.78, transform: 'translate(-50%, -50%)'}}>
          <ThingSvg K={K} it={{name: it.holds, tone: it.holdsTone || (it.tone === 'accent' ? 'accent2' : 'accent')}} t={t} size={h * 0.3} />
        </div>
      ) : null}
      {it.label ? (
        <div style={{position: 'absolute', left: w / 2, top: h * 0.66, transform: 'translate(-50%, -50%)'}}>
          <Chip E={E} text={it.label} size={it.labelSize || 38} tone={it.labelTone || (it.tone === 'accent' ? 'ink' : 'paper')} dot={false} />
        </div>
      ) : null}
    </div>
  );
};

// search / prompt bar; typed from typeAt, shimmer placeholder before
const Bar = ({E, K, it, t}) => {
  const size = it.size || 44, txt = String(it.text || '');
  const typeAt = it.typeAt, typing = it.typing ?? Math.min(1.4, 0.05 * txt.length + 0.3);
  const n = typeAt != null ? Math.round(txt.length * clamp((t - typeAt) / typing)) : txt.length;
  const caret = typeAt != null && t >= typeAt - 0.2 && t < typeAt + typing + 0.5;
  const dark = K.T.flat ? K.T.ink : 'rgba(10,12,18,0.72)';
  const icon = it.icon || 'search';
  return (
    <div style={{display: 'flex', alignItems: 'center', gap: size * 0.45, padding: `${size * 0.36}px ${size * 0.62}px`, minWidth: it.w || size * 9,
      maxWidth: E.width - 120, width: 'max-content', background: dark, borderRadius: K.T.flat ? size * 0.26 : size * 0.9, color: K.T.flat ? K.T.paper : '#FFFFFF',
      fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: size, lineHeight: 1.15,
      boxShadow: K.T.flat ? 'none' : '0 18px 40px rgba(0,0,0,0.35)', border: K.T.flat ? 'none' : '2px solid rgba(255,255,255,0.2)'}}>
      <Icon name={icon} size={size * 0.95} color={K.T.accent} stroke={2.8} />
      {txt && n > 0 ? (
        <span style={{textWrap: 'balance'}}>{txt.slice(0, n)}
          {caret ? <span style={{display: 'inline-block', width: Math.max(3, size * 0.07), height: size * 1.02, marginLeft: 3, verticalAlign: 'middle',
            background: K.T.accent, opacity: 0.35 + 0.65 * (0.5 + 0.5 * Math.cos(t * 9))}} /> : null}</span>
      ) : (
        <span style={{display: 'flex', gap: size * 0.28}}>
          {[0.9, 1.5, 1.0].map((w, j) => <span key={j} style={{width: size * w * 1.5, height: size * 0.4, borderRadius: K.T.flat ? size * 0.08 : size * 0.2,
            background: K.T.flat ? rgba(K.T.paper, 0.32) : 'rgba(255,255,255,0.24)', opacity: 0.45 + 0.55 * (0.5 + 0.5 * Math.sin(t * 5 - j * 0.8))}} />)}
          {caret ? <span style={{display: 'inline-block', width: Math.max(3, size * 0.07), height: size * 1.02, background: K.T.accent,
            opacity: 0.35 + 0.65 * (0.5 + 0.5 * Math.cos(t * 9))}} /> : null}
        </span>
      )}
    </div>
  );
};

const Bubble = ({E, K, it}) => {
  const size = it.size || 46, thought = it.style === 'thought';
  const bg = K.T.flat ? K.T.paper : 'rgba(255,255,255,0.12)', fgc = K.fg;
  const tail = it.tail || 'down';
  const border = K.T.flat ? `4px solid ${K.T.ink}` : '2px solid rgba(255,255,255,0.3)';
  return (
    <div style={{position: 'relative'}}>
      <div style={{padding: `${size * 0.5}px ${size * 0.75}px`, background: it.tone ? K.tone(it.tone) : bg, border, borderRadius: thought ? size * 1.4 : size * 0.6,
        color: it.tone === 'accent' || it.tone === 'accent2' ? (it.tone === 'accent' ? K.T.onFill('accent') : '#FFFFFF') : fgc,
        fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: size, lineHeight: 1.2, maxWidth: it.w || 760, width: 'max-content', textWrap: 'balance', textAlign: 'center',
        backdropFilter: K.T.flat ? 'none' : 'blur(10px)'}}>{it.text}</div>
      {thought ? (
        [[0.25, 0.32], [0.14, 0.2]].map(([dx, r], k) => (
          <div key={k} style={{position: 'absolute', left: tail === 'right' ? `${70 + k * 10}%` : `${30 - k * 10}%`, bottom: -size * (0.55 + k * 0.55), width: size * r * 2, height: size * r * 2,
            borderRadius: '50%', background: it.tone ? K.tone(it.tone) : bg, border}} />))
      ) : (
        <svg width={size * 1.1} height={size * 0.9} style={{position: 'absolute', left: tail === 'right' ? '68%' : tail === 'left' ? '14%' : '42%', bottom: -size * 0.82, overflow: 'visible'}}>
          <path d={`M0 0 L${size * (tail === 'left' ? 0.1 : 0.5)} ${size * 0.8} L${size * 0.9} 0`} fill={it.tone ? K.tone(it.tone) : bg}
            stroke={K.T.flat ? K.T.ink : 'rgba(255,255,255,0.3)'} strokeWidth={K.T.flat ? 4 : 2} strokeLinejoin="round" />
          <rect x={2} y={-6} width={size * 0.86} height={8} fill={it.tone ? K.tone(it.tone) : bg} />
        </svg>
      )}
    </div>
  );
};

const Card = ({E, K, it, t}) => {
  const w = it.w || 760, size = it.size || 48;
  const panel = K.T.panel(0.38);
  return (
    <div style={{display: 'flex', alignItems: 'center', gap: 26, width: w, padding: '22px 28px', borderRadius: K.T.flat ? 22 : 34, ...panel}}>
      {it.thing ? <div style={{flex: 'none'}}><ThingSvg K={K} it={{name: it.thing, tone: it.thingTone || 'accent'}} t={t} size={size * 2.1} /></div>
        : it.icon ? <div style={{width: size * 2, height: size * 2, flex: 'none', borderRadius: K.T.flat ? 16 : 28, background: K.tone(it.iconTone || 'accent'),
          display: 'flex', alignItems: 'center', justifyContent: 'center'}}><Icon name={it.icon} size={size * 1.15} color={K.T.onFill(it.iconTone || 'accent')} stroke={2.6} /></div> : null}
      <div style={{minWidth: 0}}>
        <div style={{fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: size, lineHeight: 1.1, color: K.T.panelFg, letterSpacing: '-0.01em'}}>{it.text}</div>
        {it.sub ? <div style={{fontFamily: E.style.fonts.ui, fontWeight: 600, fontSize: size * 0.66, color: K.T.panelSub, marginTop: 6}}>{it.sub}</div> : null}
      </div>
    </div>
  );
};

const Tag = ({E, K, it, t}) => {
  const w = it.w || 560, h = it.h || Math.round(w * 1.12), D = dispFont(E);
  const ink = K.fg, paper = K.T.flat ? K.T.paper : 'rgba(255,255,255,0.08)';
  const path = `M${w * 0.17} 0 L${w * 0.83} 0 L${w} ${w * 0.17} L${w} ${h} L0 ${h} L0 ${w * 0.17} Z`;
  const sp = it.stampAt != null ? prog(t, it.stampAt, it.stampAt + 0.38) : 0;
  const ss = lerp(1.7, 1, eOut(sp));
  const sv = it.stampAt != null ? Math.abs(vel((k) => lerp(1.7, 1, eOut(prog(k, it.stampAt, it.stampAt + 0.38))), t)) : 0;
  return (
    <div style={{position: 'relative', width: w, height: h}}>
      {it.hang ? <div style={{position: 'absolute', left: w / 2 - 3, top: -(it.string ?? 420), width: 6, height: (it.string ?? 420) + w * 0.1, background: ink}} /> : null}
      <svg width={w} height={h} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
        <path d={path} fill={paper} stroke={ink} strokeWidth={9} strokeLinejoin="round" />
        <circle cx={w / 2} cy={w * 0.1} r={w * 0.045} fill={K.T.flat ? (E.style.colors.bg || '#F1EEE7') : 'rgba(0,0,0,0.4)'} stroke={ink} strokeWidth={7} />
        <line x1={w * 0.1} y1={h - w * 0.18} x2={w * 0.9} y2={h - w * 0.18} stroke={ink} strokeWidth={4} strokeDasharray="14 12" />
      </svg>
      <div style={{position: 'absolute', left: 0, right: 0, top: h * 0.26, display: 'flex', justifyContent: 'center', ...D, fontSize: it.size || w * 0.23,
        letterSpacing: '-0.045em', color: ink, lineHeight: 1}}>{it.text}</div>
      {it.sub ? <div style={{position: 'absolute', left: 0, right: 0, top: h - w * 0.15, textAlign: 'center', fontFamily: E.style.fonts.ui, fontWeight: 800,
        fontSize: w * 0.06, color: K.tone('muted')}}>{it.sub}</div> : null}
      {it.stamp && sp > 0 ? (
        <div style={{position: 'absolute', left: w / 2, top: h * 0.62, transform: `translate(-50%, -50%) rotate(-7deg) scale(${ss.toFixed(3)})`,
          opacity: clamp(sp * 4), filter: sv > 1 ? `blur(${Math.min(12, sv * 2).toFixed(1)}px)` : 'none'}}>
          <div style={{display: 'flex', alignItems: 'center', gap: 16, padding: '16px 30px 18px', background: K.tone(it.stampTone || 'accent'), color: K.T.onFill(it.stampTone || 'accent'),
            fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: w * 0.1, lineHeight: 1, whiteSpace: 'nowrap', borderRadius: K.T.flat ? 10 : 999,
            border: K.T.flat ? `6px solid ${K.T.ink}` : 'none'}}>
            {it.stampIcon ? <Icon name={it.stampIcon} size={w * 0.09} color={K.T.onFill(it.stampTone || 'accent')} stroke={3} /> : null}{it.stamp}
          </div>
        </div>
      ) : null}
    </div>
  );
};

const Text = ({E, K, it, t}) => {
  const size = it.size || 120, D = it.font === 'ui' ? {fontFamily: E.style.fonts.ui, fontWeight: 800} : dispFont(E);
  const words = String(it.text || '').split(/\s+/).filter(Boolean);
  const acc = String(it.accent || '').toLowerCase().split(/\s+/).map((a) => a.replace(/[^\w-]/g, ''));
  const col = K.tone(it.tone, 'main'), a = it.at ?? 0;
  const style = {display: 'flex', flexWrap: 'wrap', justifyContent: it.align === 'left' ? 'flex-start' : it.align === 'right' ? 'flex-end' : 'center', columnGap: size * 0.22,
    maxWidth: it.maxW || E.width - 120, ...D, fontSize: size, lineHeight: 1, letterSpacing: '-0.035em', color: col, textAlign: it.align || 'center'};
  const isAcc = (w) => acc.includes(w.toLowerCase().replace(/[^\w-]/g, ''));
  if (it.fx === 'type') {
    const cps = it.cps || 22, total = it.text.length;
    const n = Math.round(total * clamp((t - a) / (total / cps)));
    return <div style={style}><span>{it.text.slice(0, n)}<span style={{display: 'inline-block', width: size * 0.06, height: size * 0.85, marginLeft: size * 0.04,
      background: K.T.accent, opacity: t < a + total / cps + 0.6 ? 0.4 + 0.6 * (0.5 + 0.5 * Math.cos(t * 10)) : 0}} /></span></div>;
  }
  return (
    <div style={style}>
      {words.map((w, i) => (
        <MaskWord key={i} p={it.fx === 'none' ? 1 : tw(t, a + i * 0.07, 0.7)}><span style={{color: isAcc(w) ? K.T.accent : col}}>{w}</span></MaskWord>
      ))}
    </div>
  );
};

const fmtNum = (v, d = 0) => {
  try { return new Intl.NumberFormat('id-ID', {minimumFractionDigits: d, maximumFractionDigits: d}).format(v); } catch (e) { return v.toFixed(d); }
};
const Counter = ({E, K, it, t}) => {
  const size = it.size || 220, D = dispFont(E);
  const ca = it.countAt ?? it.at ?? 0, cd = it.countDur ?? 1.0;
  const v = lerp(it.from ?? 0, Number(it.value) || 0, qOut(prog(t, ca, ca + cd)));
  return (
    <div style={{display: 'flex', flexDirection: 'column', alignItems: 'center'}}>
      <div style={{...D, fontSize: size, lineHeight: 0.95, letterSpacing: '-0.04em', color: K.tone(it.tone, 'main'), fontVariantNumeric: 'tabular-nums', whiteSpace: 'nowrap'}}>
        {it.prefix || ''}{fmtNum(v, it.decimals || 0)}<span style={{color: K.T.accent}}>{it.suffix || ''}</span>
      </div>
    </div>
  );
};

const BarsChart = ({E, K, it, t}) => {
  const vals = it.values || [], n = vals.length || 1, w = it.w || 760, h = it.h || 520;
  const mx = it.max || Math.max(...vals, 1), bw = (w - (n - 1) * 34) / n;
  const ats = it.ats || vals.map((_, i) => (it.at ?? 0) + i * 0.25);
  return (
    <div style={{position: 'relative', width: w, height: h + 70}}>
      <div style={{position: 'absolute', left: -20, right: -20, top: h, height: 6, borderRadius: 3, background: K.fg}} />
      {vals.map((v, i) => {
        const p = qOut(prog(t, ats[i], ats[i] + 0.7)), bh = (v / mx) * (h - 60) * p;
        const hi = it.highlight === i || (it.highlight == null && i === n - 1);
        return (
          <div key={i}>
            <div style={{position: 'absolute', left: i * (bw + 34), top: h - bh, width: bw, height: bh, borderRadius: K.T.flat ? 6 : 16,
              background: hi ? K.T.accent : K.tone(it.tone, 'soft'), border: K.T.flat ? `4px solid ${K.T.ink}` : 'none', borderBottom: 'none'}} />
            {it.show && p > 0.05 ? <div style={{position: 'absolute', left: i * (bw + 34), width: bw, top: h - bh - 64, textAlign: 'center', fontFamily: E.style.fonts.ui,
              fontWeight: 800, fontSize: 46, color: K.fg, opacity: clamp(p * 2)}}>{it.prefix || ''}{fmtNum(v * clamp(p * 1.1), it.decimals || 0)}{it.suffix || ''}</div> : null}
            {(it.labels || [])[i] ? <div style={{position: 'absolute', left: i * (bw + 34) - 20, width: bw + 40, top: h + 18, textAlign: 'center', fontFamily: E.style.fonts.ui,
              fontWeight: 800, fontSize: it.labelSize || 34, color: K.fg, opacity: t >= ats[i] ? 1 : 0.25}}>{it.labels[i]}</div> : null}
          </div>
        );
      })}
    </div>
  );
};

const Meter = ({E, K, it, t}) => {
  const w = it.w || 760, h = it.h || 64, a = it.at ?? 0, d = it.dur ?? 1.2;
  const p = qInOut(prog(t, a + 0.2, a + 0.2 + d)) * clamp(Number(it.value ?? 0.8));
  return (
    <div style={{width: w}}>
      {it.text ? <div style={{fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: it.size || 44, color: K.fg, marginBottom: 16}}>{it.text}</div> : null}
      <div style={{position: 'relative', width: w, height: h, borderRadius: K.T.flat ? 10 : h / 2, background: K.tone('faint'), border: K.T.flat ? `4px solid ${K.T.ink}` : 'none', overflow: 'hidden'}}>
        <div style={{position: 'absolute', left: 0, top: 0, bottom: 0, width: `${p * 100}%`, background: K.tone(it.tone, 'accent'), borderRadius: K.T.flat ? 0 : h / 2}} />
      </div>
    </div>
  );
};

const Arrow = ({K, it, t}) => {
  const [x0, y0] = it.from || [300, 900], [x1, y1] = it.to || [780, 900];
  const dx = x1 - x0, dy = y1 - y0, len = Math.hypot(dx, dy) || 1, b = it.bend ?? 0.25;
  const cx = (x0 + x1) / 2 - (dy / len) * len * b, cy = (y0 + y1) / 2 + (dx / len) * len * b;
  const a = it.at ?? 0, p = qInOut(prog(t, a, a + (it.dur ?? 0.6)));
  if (p <= 0) return null;
  const tx = x1 - cx, ty = y1 - cy, ang = Math.atan2(ty, tx) * 180 / Math.PI;
  const col = K.tone(it.tone, 'main'), sw = it.width || 10;
  const q = it.until != null ? tw(t, it.until - 0.3, 0.3, qIn) : 0;
  return (
    <svg width={4000} height={4000} style={{position: 'absolute', left: 0, top: 0, overflow: 'visible', opacity: 1 - q}}>
      <path d={`M${x0} ${y0} Q${cx} ${cy} ${x1} ${y1}`} fill="none" stroke={col} strokeWidth={sw} strokeLinecap="round" pathLength={1}
        strokeDasharray={it.dashed ? '0.03 0.03' : `${p} 1`} opacity={it.dashed ? p : 1} />
      {p > 0.92 ? <path d={`M${-sw * 3.2} ${-sw * 2.4} L0 0 L${-sw * 3.2} ${sw * 2.4}`} transform={`translate(${x1} ${y1}) rotate(${ang})`} fill="none" stroke={col}
        strokeWidth={sw} strokeLinecap="round" strokeLinejoin="round" opacity={clamp((p - 0.92) * 12)} /> : null}
    </svg>
  );
};

const Steps = ({E, K, it, t}) => {
  const its = it.items || [], gap = it.gap || 170, down = (it.dir || 'down') === 'down', size = it.size || 46;
  const pos = (k) => (down ? [60, 60 + k * gap] : [100 + k * gap, 60]);
  const last = its.length - 1;
  return (
    <div style={{position: 'relative', width: down ? 760 : gap * Math.max(1, last) + 200, height: down ? gap * Math.max(1, last) + 120 : 300}}>
      {its.map((s, k) => {   // connecting lines first, so the nodes sit on top
        if (k === 0 || t < s.at - 0.25) return null;
        const [x, y] = pos(k), lineP = qInOut(prog(t, s.at - 0.25, s.at + 0.15));
        return <div key={`l${k}`} style={{position: 'absolute', left: down ? x - 4 : x - gap, top: down ? y - gap : y - 4, width: down ? 8 : gap * lineP, height: down ? gap * lineP : 8,
          background: K.fg, borderRadius: 4}} />;
      })}
      {its.map((s, k) => {
        if (t < s.at) return null;
        const p = tw(t, s.at, 0.55, backOut), lp = tw(t, s.at, 0.6);
        const [x, y] = pos(k), hi = k === last || s.hi;
        return (
          <React.Fragment key={k}>
            <div style={{position: 'absolute', left: x - 50, top: y - 50, width: 100, height: 100, borderRadius: K.T.flat ? 18 : 50, background: hi ? K.T.accent : K.fg,
              color: hi ? K.T.onFill('accent') : (K.T.flat ? K.T.paper : K.T.ink), transform: `scale(${p})`, display: 'flex', alignItems: 'center', justifyContent: 'center',
              ...dispFont(E), fontSize: 56}}>{s.n ?? k + 1}</div>
            <div style={{position: 'absolute', left: down ? x + 80 : x - 150, top: down ? y - size * 0.62 : y + 70, width: down ? 600 : 300, textAlign: down ? 'left' : 'center',
              fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: size, lineHeight: 1.15, color: K.fg, opacity: clamp(lp * 1.6), transform: `translateX(${(1 - lp) * 30}px)`}}>{s.text}</div>
          </React.Fragment>
        );
      })}
    </div>
  );
};

const Frame = ({E, K, it, t}) => {
  const w = it.w || 420, h = it.h || Math.round(w * (it.device === 'phone' ? 1.9 : 1.25));
  const phone = it.device === 'phone';
  return (
    <div style={{position: 'relative', width: w, height: h, borderRadius: phone ? w * 0.16 : (K.T.flat ? 18 : 30), padding: phone ? 12 : 0,
      background: phone ? (K.T.flat ? K.T.ink : '#120E28') : 'transparent', boxShadow: K.T.flat ? `0 0 0 ${phone ? 0 : 6}px ${K.T.paper}, 0 24px 50px rgba(18,18,18,0.2)` : '0 30px 70px rgba(0,0,0,0.45)'}}>
      <LiveClip E={E} t={t} w={phone ? w - 24 : w} h={phone ? h - 24 : h} grade={it.grade ?? 1} zoom={it.zoom ?? 1.05} fy={it.fy ?? 0.42} radius={phone ? w * 0.13 : (K.T.flat ? 18 : 30)} />
    </div>
  );
};

// crowd: rows of people drifting sideways; optional blank search bars above every head
const Crowd = ({E, K, it, t}) => {
  const W = E.width, n = it.n || 7, a = it.at ?? 0, base = it.y ?? 1100, sz = it.size || 1;
  const people = Array.from({length: n}, (_, i) => {
    const back = i % 2 === 0, s = (back ? 0.8 : 1.08) * sz;
    const dir = rand(i * 3.7 + (it.seed || 0)) > 0.5 ? 1 : -1;
    const x0 = ((i + 0.5) / n) * W + (rand(i * 1.9) - 0.5) * 70;
    const x = it.walk === false ? x0 : ((x0 + dir * (t - a) * (18 + 14 * rand(i)) + W * 1.2) % (W * 1.2)) - W * 0.1;
    const feet = back ? base - 260 * sz : base, bob = it.walk === false ? 0 : Math.abs(Math.sin((t - a) * 3.2 + i)) * 6 * s;
    return {i, s, x, y: feet - bob, back, inAt: a + (i % 5) * 0.06};
  });
  const hi = it.highlight;
  const dim = it.dimAt != null ? 1 - 0.6 * tw(t, it.dimAt, 0.8, qInOut) : 1;   // step back when someone steps forward
  const dblur = it.dimAt != null ? 3 * tw(t, it.dimAt, 0.8, qInOut) : 0;
  return (
    <div style={{position: 'absolute', left: 0, top: 0, width: W, height: E.height, opacity: dim, filter: dblur > 0.3 ? `blur(${dblur.toFixed(1)}px)` : 'none'}}>
      <svg width={W} height={E.height} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
        {people.map((c) => {
          const p = tw(t, c.inAt, 0.6);
          const col = hi === c.i ? K.T.accent : K.tone(c.back ? 'faint' : 'soft');
          return (
            <g key={c.i} transform={`translate(${c.x.toFixed(1)} ${(c.y + (1 - p) * 60).toFixed(1)}) scale(${c.s.toFixed(3)})`} opacity={clamp(p * 1.5)}>
              <path d="M-78 0 L-78 -118 Q-78 -196 0 -196 Q78 -196 78 -118 L78 0 Z" fill={col} /><circle cx={0} cy={-262} r={56} fill={col} />
            </g>
          );
        })}
      </svg>
      {it.bars ? people.map((c) => {
        const ba = (it.barsAt ?? a + 0.3) + c.i * 0.07;
        if (t < ba) return null;
        const p = tw(t, ba, 0.55);
        return (
          <div key={c.i} style={{position: 'absolute', left: c.x, top: c.y - 318 * c.s - 38, transform: `translate(-50%, -50%) translateY(${(1 - p) * 26}px) scale(${0.8 + 0.2 * p})`,
            opacity: clamp(p * 1.6)}}>
            <Bar E={E} K={K} it={{size: c.back ? 24 : 30, w: c.back ? 150 : 190}} t={t} />
          </div>
        );
      }) : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ item dispatcher
const FULL_CANVAS = new Set(['arrow', 'crowd']);
const renderContent = (E, K, it, t) => {
  switch (it.kind) {
    case 'figure': return <Figure E={E} K={K} it={it} t={t} size={it.size || 520} />;
    case 'thing': return <ThingSvg K={K} it={it} t={t} size={it.size || 220} />;
    case 'icon': return <Icon name={it.name} size={it.size || 140} color={K.tone(it.tone, 'main')} stroke={it.stroke || 2.4} />;
    case 'chip': return <Chip E={E} text={it.text} size={it.size || 42} tone={K.chipTone(it.tone)} icon={it.icon} dot={!!it.dot} />;
    case 'text': return <Text E={E} K={K} it={it} t={t} />;
    case 'bubble': return <Bubble E={E} K={K} it={it} />;
    case 'card': return <Card E={E} K={K} it={it} t={t} />;
    case 'tag': return <Tag E={E} K={K} it={it} t={t} />;
    case 'bar': return <Bar E={E} K={K} it={it} t={t} />;
    case 'counter': return <Counter E={E} K={K} it={it} t={t} />;
    case 'bars': return <BarsChart E={E} K={K} it={it} t={t} />;
    case 'meter': return <Meter E={E} K={K} it={it} t={t} />;
    case 'steps': return <Steps E={E} K={K} it={it} t={t} />;
    case 'frame': return <Frame E={E} K={K} it={it} t={t} />;
    case 'rect': return <div style={{width: it.w || 400, height: it.h || 300, borderRadius: it.radius ?? (K.T.flat ? 14 : 30), background: K.tone(it.tone, 'faint'),
      border: it.stroke ? `${K.T.flat ? 5 : 2}px solid ${K.fg}` : 'none'}} />;
    case 'circle': return <div style={{width: (it.r || 160) * 2, height: (it.r || 160) * 2, borderRadius: '50%', background: it.ring ? 'transparent' : K.tone(it.tone, 'faint'),
      border: it.ring ? `${it.width || 8}px solid ${K.tone(it.tone, 'main')}` : 'none'}} />;
    default: return null;
  }
};

const above = (E, K, it, t, size) => {
  const ab = it.above;
  if (!ab || t < (ab.at ?? it.at ?? 0)) return null;
  const p = tw(t, ab.at ?? it.at ?? 0, 0.55);
  // keep a wide bar / bubble inside the frame: estimate its width and slide it horizontally if needed
  const fs = ab.size || 44, est = ab.kind === 'thing' ? (ab.size || size * 0.32) : Math.min(E.width - 120, String(ab.text || '').length * fs * 0.56 + fs * 3.2);
  const cx = it.x ?? E.width / 2, shift = clamp(cx, 60 + est / 2, E.width - 60 - est / 2) - cx;
  const inner = ab.kind === 'bubble' ? <Bubble E={E} K={K} it={ab} /> : ab.kind === 'thing' ? <ThingSvg K={K} it={ab} t={t} size={ab.size || size * 0.32} />
    : ab.kind === 'text' ? <Text E={E} K={K} it={ab} t={t} /> : <Bar E={E} K={K} it={ab} t={t} />;
  return (
    <div style={{position: 'absolute', left: `calc(50% + ${shift.toFixed(1)}px)`, top: -(ab.gap ?? 26), transform: `translate(-50%, -100%) translateY(${(1 - p) * 26}px) scale(${0.85 + 0.15 * p})`,
      transformOrigin: '50% 100%', opacity: clamp(p * 1.6), filter: p < 0.98 ? `blur(${((1 - p) * 8).toFixed(1)}px)` : 'none'}}>{inner}</div>
  );
};

export const renderItem = (E, K, it, t, key) => {
  if (FULL_CANVAS.has(it.kind)) {
    if (t < (it.at ?? 0)) return null;
    return <div key={key} style={{position: 'absolute', inset: 0, zIndex: it.z || 0}}>{it.kind === 'arrow' ? <Arrow K={K} it={it} t={t} /> : <Crowd E={E} K={K} it={it} t={t} />}</div>;
  }
  const m = itemMotion(t, it, E.width);
  if (!m) return null;
  const isFig = it.kind === 'figure';
  const headTop = isFig ? (it.size || 520) * 0.237 : 0;
  const hang = it.kind === 'tag' && it.hang;
  return (
    <div key={key} style={{position: 'absolute', left: it.x ?? E.width / 2, top: it.y ?? E.height * 0.42, zIndex: it.z || 0,
      transform: `translate(-50%, ${hang ? '0%' : '-50%'}) ${m.transform}`, transformOrigin: hang ? '50% 0%' : '50% 50%', opacity: m.opacity, filter: m.filter}}>
      <div style={{position: 'relative'}}>
        {renderContent(E, K, it, t)}
        {isFig && it.above ? <div style={{position: 'absolute', left: 0, right: 0, top: headTop}}>{above(E, K, it, t, it.size || 520)}</div> : null}
        {!isFig && it.label ? (
          <div style={{position: 'absolute', left: '50%', top: '100%', transform: `translate(-50%, ${it.labelGap ?? 18}px)`}}>
            <Chip E={E} text={it.label} size={it.labelSize || 38} tone={K.chipTone(it.labelTone || 'ink')} dot={false} />
          </div>
        ) : null}
      </div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ SceneBuild
const SLOTS = {
  'above': [0, -1.5], 'above-left': [-1.05, -1.2], 'above-right': [1.05, -1.2], 'left': [-1.25, -0.1], 'right': [1.25, -0.1],
  'chest': [0, 1.4], 'chest-left': [-0.95, 1.3], 'chest-right': [0.95, 1.3],
};
export const FACE_SLOTS = Object.keys(SLOTS);
const camAt = (keys, t, W, H) => {
  let z = 1, cx = W / 2, cy = H / 2;
  for (const k of keys || []) {
    const p = qInOut(prog(t, k.at, k.at + (k.dur ?? 0.8)));
    z = lerp(z, k.zoom ?? 1, p); cx = lerp(cx, k.x ?? W / 2, p); cy = lerp(cy, k.y ?? H / 2, p);
  }
  return `translate(${W / 2}px, ${H / 2}px) scale(${z.toFixed(4)}) translate(${-cx}px, ${-cy}px)`;
};

export const SceneBuild = ({E, t, o}) => {
  const K = kitColors(E), W = E.width, H = E.height;
  const items = o.items || [];
  if (o.anchor === 'face') {
    const f = faceOnCanvas(E, t);
    if (!f) return null;
    const ex = o.end != null ? tw(t, o.end - 0.3, 0.3, qIn) : 0;
    if (ex >= 0.999) return null;
    return (
      <AbsoluteFill style={{pointerEvents: 'none', opacity: 1 - ex}}>
        {items.map((it, i) => {
          const [sx, sy] = SLOTS[it.slot || 'above'] || SLOTS.above;
          const size = it.size || (it.kind === 'figure' ? f.h * 1.2 : it.kind === 'thing' ? f.h * 0.55 : undefined);
          // keep the whole item AND its label chip inside the frame (a slot near the edge used to clip long labels)
          const ls = it.labelSize || 38, lw = it.label ? String(it.label).length * ls * 0.6 + ls * 1.5 : 0;
          const half = Math.max(110, (size || 200) * 0.5 + 24, lw * 0.5 + 28);
          const x = clamp(f.x + sx * f.h + (it.dx || 0), half, W - half), y = Math.max(250, f.y + sy * f.h + (it.dy || 0));
          return renderItem(E, K, {...it, x, y, ...(size ? {size} : {})}, t, i);
        })}
      </AbsoluteFill>
    );
  }
  return (
    <SceneFrame E={E} t={t} o={o}>
      <AbsoluteFill style={{transform: camAt(o.camera, t, W, H), transformOrigin: '0 0'}}>
        {items.map((it, i) => renderItem(E, K, it, t, i))}
      </AbsoluteFill>
    </SceneFrame>
  );
};
