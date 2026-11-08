import React from 'react';
import {C, FONT, TEXT} from '../../theme';
import {easeOut, prog} from './util';

const norm = (s: string) => s.toLowerCase().replace(/[^\p{L}\p{N}]+/gu, '');

/**
 * Headline whose words rise out of a mask one after another. `x` = centre, `y` = top.
 * `highlight` words (case/punctuation-insensitive) get the accent colour.
 */
export const KineticHeadline: React.FC<{
  t: number; text: string; at: number; x?: number; y: number; width?: number; size?: number; color?: string; highlight?: string[];
  highlightColor?: string; stagger?: number; align?: 'center' | 'left'; lineHeight?: number;
}> = ({t, text, at, x = 540, y, width = 940, size = 96, color = C.navy, highlight = [], highlightColor = C.coral, stagger = 0.07,
  align = 'center', lineHeight = 1.08}) => {
  const hl = new Set(highlight.map(norm));
  const words = text.split(/\s+/).filter(Boolean);
  return (
    <div style={{position: 'absolute', left: align === 'center' ? x - width / 2 : x, top: y, width, textAlign: align, fontFamily: FONT,
      fontWeight: 800, fontSize: size, lineHeight, color, wordSpacing: '0.04em'}}>
      {words.map((w, i) => {
        const p = prog(t, at + i * stagger, 0.5, easeOut);
        return (
          <React.Fragment key={i}>
            <span style={{display: 'inline-block', overflow: 'hidden', verticalAlign: 'top', paddingBottom: size * 0.08}}>
              <span style={{display: 'inline-block', transform: `translateY(${(1 - p) * 105}%)`, color: hl.has(norm(w)) ? highlightColor : undefined}}>{w}</span>
            </span>
            {i < words.length - 1 ? ' ' : ''}
          </React.Fragment>
        );
      })}
    </div>
  );
};

/** Small pill that marks hypothetical content: SIMULASI / WHAT IF / ILUSTRASI. `x` = centre, `y` = top. */
export const SimulasiBadge: React.FC<{t: number; text?: string; x?: number; y: number; at?: number; color?: string; dark?: boolean}> =
  ({t, text = 'SIMULASI', x = 540, y, at = 0, color = C.red, dark = false}) => {
    const p = prog(t, at, 0.4);
    const pulse = 0.55 + 0.45 * Math.abs(Math.sin((t - at) * 2.4));
    return (
      <div style={{position: 'absolute', left: x, top: y, transform: `translateX(-50%) scale(${0.85 + 0.15 * p})`, opacity: p,
        display: 'flex', alignItems: 'center', gap: 14, padding: '12px 26px', borderRadius: 999, fontFamily: TEXT, fontWeight: 600,
        fontSize: 28, letterSpacing: 5, color: dark ? C.cream : C.navy, background: dark ? '#ffffff1a' : '#0d12330f',
        border: `2px solid ${dark ? '#ffffff40' : '#0d123326'}`}}>
        <span style={{width: 14, height: 14, borderRadius: 99, background: color, opacity: pulse}} />{text}
      </div>
    );
  };

/**
 * Hand-drawn write-on mark over the 1080×1920 canvas: circle around (x, y, w, h), underline under it, or an
 * arrow from (x, y) to `to`. Use it to point at the one thing the narrator is talking about.
 */
export const Callout: React.FC<{
  t: number; at: number; shape: 'circle' | 'underline' | 'arrow'; x: number; y: number; w?: number; h?: number; to?: [number, number];
  color?: string; stroke?: number; dur?: number;
}> = ({t, at, shape, x, y, w = 300, h = 140, to, color = C.coral, stroke = 10, dur = 0.55}) => {
  const p = prog(t, at, dur, easeOut);
  if (p <= 0) return null;
  let d = '';
  let head = '';
  if (shape === 'circle') {
    const rx = w / 2, ry = h / 2, cx = x, cy = y;
    const pts: string[] = [];
    for (let i = 0; i <= 64; i++) {
      const a = -Math.PI * 0.6 + (i / 64) * Math.PI * 2.15;
      const wob = 1 + 0.035 * Math.sin(i * 0.9);
      pts.push(`${(cx + Math.cos(a) * rx * wob).toFixed(1)} ${(cy + Math.sin(a) * ry * wob).toFixed(1)}`);
    }
    d = `M${pts.join(' L')}`;
  } else if (shape === 'underline') {
    d = `M${x - w / 2} ${y} C ${x - w / 6} ${y + 14}, ${x + w / 6} ${y - 10}, ${x + w / 2} ${y + 4}`;
  } else {
    const [tx, ty] = to ?? [x + 200, y + 200];
    const mx = (x + tx) / 2 + (ty - y) * 0.18, my = (y + ty) / 2 - (tx - x) * 0.18;
    d = `M${x} ${y} Q ${mx} ${my} ${tx} ${ty}`;
    const ang = Math.atan2(ty - my, tx - mx);
    const L = 34;
    head = `M${tx - L * Math.cos(ang - 0.45)} ${ty - L * Math.sin(ang - 0.45)} L${tx} ${ty} L${tx - L * Math.cos(ang + 0.45)} ${ty - L * Math.sin(ang + 0.45)}`;
  }
  const headP = prog(t, at + dur * 0.85, 0.18);
  return (
    <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0, overflow: 'visible', pointerEvents: 'none'}}>
      <path d={d} pathLength={1} fill="none" stroke={color} strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round"
        strokeDasharray={1} strokeDashoffset={1 - p} />
      {head && <path d={head} pathLength={1} fill="none" stroke={color} strokeWidth={stroke} strokeLinecap="round" strokeLinejoin="round"
        strokeDasharray={1} strokeDashoffset={1 - headP} />}
    </svg>
  );
};
