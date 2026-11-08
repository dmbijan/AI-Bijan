import React from 'react';
import {CaptionPage} from '../../components/WordCaptions';
import {display, drawOn, noise, p, pageAt} from '../shared';

// BLUEPRINT style kit — technical-drawing look. Shared by the prototype and every BLUEPRINT episode.
const DEFAULT_TOP = 1500;
export const BLUEPRINT = {
  bg: '#0E3A6B', deep: '#07223F', line: '#EAF3FF', line2: '#8EC2FF', cyan: '#7FD8FF', yellow: '#FFD447', red: '#FF6A55',
  face1: '#174A82', face2: '#113D6E', face3: '#0C3260',
};
export const B = BLUEPRINT;
export const HEAD = "'BP Head', sans-serif";
export const MONO = "'BP Mono', monospace";
export const GRAIN = noise(0.9, 2, 3, 400);

export type Pt = [number, number];
export const COS = Math.cos(Math.PI / 6);
export const isoAt = (cx: number, cy: number, k: number) => (x: number, y: number, z: number): Pt => [cx + (x - y) * COS * k, cy + (x + y) * 0.5 * k - z * k];
export const poly = (pts: Pt[], close = true) => `M ${pts.map((q) => `${q[0].toFixed(1)} ${q[1].toFixed(1)}`).join(' L ')}${close ? ' Z' : ''}`;
export const seg = (a: Pt, b: Pt) => `M ${a[0].toFixed(1)} ${a[1].toFixed(1)} L ${b[0].toFixed(1)} ${b[1].toFixed(1)}`;

// ---------- kit ----------
export const Typed: React.FC<{x: number; y: number; text: string; t: number; start: number; cps?: number; size?: number; color?: string; weight?: number; anchor?: 'start' | 'middle' | 'end'; family?: string; spacing?: number}> = ({
  x, y, text, t, start, cps = 30, size = 26, color = B.line, weight = 600, anchor = 'start', family = MONO, spacing = 1,
}) => {
  const n = Math.max(0, Math.min(text.length, Math.floor((t - start) * cps)));
  if (n === 0) return null;
  return <text x={x} y={y} textAnchor={anchor} fill={color} style={{fontFamily: family, fontWeight: weight, fontSize: size, letterSpacing: spacing}}>{text.slice(0, n)}</text>;
};

/** Leader-line callout: dot on the part, elbow line, numbered balloon + label. */
export const Callout: React.FC<{a: Pt; l: Pt; n: string; label: string; t: number; start: number; color?: string; side?: 'left' | 'right'}> = ({a, l, n, label, t, start, color = B.line, side = 'right'}) => {
  const q = p(t, start, start + 0.35);
  if (q <= 0) return null;
  const elbow: Pt = [l[0], l[1]];
  const end: Pt = [side === 'right' ? l[0] + 40 : l[0] - 40, l[1]];
  const bx = side === 'right' ? end[0] + 26 : end[0] - 26;
  return (
    <g>
      <circle cx={a[0]} cy={a[1]} r={7} fill={color} opacity={Math.min(1, q * 3)} />
      <path d={`M ${a[0]} ${a[1]} L ${elbow[0]} ${elbow[1]} L ${end[0]} ${end[1]}`} fill="none" stroke={color} strokeWidth={2.5} {...drawOn(q)} />
      <g opacity={p(t, start + 0.2, start + 0.4)}>
        <circle cx={bx} cy={end[1]} r={22} fill={B.bg} stroke={color} strokeWidth={2.5} />
        <text x={bx} y={end[1] + 8} textAnchor="middle" fill={color} style={{fontFamily: MONO, fontWeight: 600, fontSize: 22}}>{n}</text>
      </g>
      <Typed x={side === 'right' ? bx + 34 : bx - 34} y={end[1] + 9} text={label} t={t} start={start + 0.25} anchor={side === 'right' ? 'start' : 'end'} color={color} size={26} />
    </g>
  );
};

/** Dimension line with end ticks and a centred value. */
export const Dim: React.FC<{a: Pt; b: Pt; text: string; q: number; color?: string; offset?: number}> = ({a, b, text, q, color = B.line2, offset = -16}) => {
  if (q <= 0) return null;
  const mx = (a[0] + b[0]) / 2, my = (a[1] + b[1]) / 2;
  const ang = Math.atan2(b[1] - a[1], b[0] - a[0]);
  const nx = -Math.sin(ang), ny = Math.cos(ang);
  const tick = (c: Pt) => `M ${c[0] - nx * 14 + Math.cos(ang) * 8} ${c[1] - ny * 14 + Math.sin(ang) * 8} L ${c[0] + nx * 14 - Math.cos(ang) * 8} ${c[1] + ny * 14 - Math.sin(ang) * 8}`;
  return (
    <g>
      <path d={seg(a, b)} stroke={color} strokeWidth={2} {...drawOn(q)} />
      <path d={`${tick(a)} ${tick(b)}`} stroke={color} strokeWidth={2.5} opacity={q} />
      <text x={mx + nx * offset} y={my + ny * offset} textAnchor="middle" fill={color} opacity={p(q, 0.5, 1)}
        transform={`rotate(${(ang * 180) / Math.PI} ${mx + nx * offset} ${my + ny * offset})`} style={{fontFamily: MONO, fontWeight: 600, fontSize: 22, letterSpacing: 1}}>{text}</text>
    </g>
  );
};

/** Isometric box: filled faces + visible edges (drawn on) + hidden edges dashed. */
export const IsoBox: React.FC<{iso: ReturnType<typeof isoAt>; x0: number; x1: number; y0: number; y1: number; z0: number; z1: number; q: number; topInset?: number; stroke?: string; fill?: boolean}> = ({
  iso, x0, x1, y0, y1, z0, z1, q, topInset = 0, stroke = B.line, fill = true,
}) => {
  const ti = topInset;
  const b = [iso(x0, y0, z0), iso(x1, y0, z0), iso(x1, y1, z0), iso(x0, y1, z0)];
  const tp = [iso(x0 + ti, y0 + ti, z1), iso(x1 - ti, y0 + ti, z1), iso(x1 - ti, y1 - ti, z1), iso(x0 + ti, y1 - ti, z1)];
  const faceQ = p(q, 0.55, 1);
  return (
    <g>
      {fill && (
        <g opacity={faceQ}>
          <path d={poly([b[1], b[2], tp[2], tp[1]])} fill={B.face2} />
          <path d={poly([b[2], b[3], tp[3], tp[2]])} fill={B.face3} />
          <path d={poly(tp)} fill={B.face1} />
        </g>
      )}
      <g fill="none" stroke={stroke} strokeWidth={3} strokeLinejoin="round" strokeLinecap="round">
        <path d={poly(tp)} {...drawOn(p(q, 0, 0.5))} />
        <path d={`${seg(b[1], b[2])} ${seg(b[2], b[3])}`} {...drawOn(p(q, 0.15, 0.65))} />
        <path d={`${seg(b[1], tp[1])} ${seg(b[2], tp[2])} ${seg(b[3], tp[3])}`} {...drawOn(p(q, 0.3, 0.8))} />
      </g>
      <path d={`${seg(b[0], b[1])} ${seg(b[0], b[3])} ${seg(b[0], tp[0])}`} fill="none" stroke={B.line2} strokeWidth={1.6} strokeDasharray="8 8" opacity={0.55 * p(q, 0.5, 1)} />
    </g>
  );
};

/** Scale figure (architectural person) in elevation. */
export const Figure: React.FC<{x: number; base: number; h: number; q: number}> = ({x, base, h, q}) => {
  const top = base - h;
  const d = `M ${x - 15} ${top + 34} Q ${x} ${top + 26} ${x + 15} ${top + 34} L ${x + 17} ${base - h * 0.48} L ${x + 10} ${base - h * 0.48} L ${x + 9} ${base} L ${x + 1.5} ${base} L ${x} ${base - h * 0.38} L ${x - 1.5} ${base} L ${x - 9} ${base} L ${x - 10} ${base - h * 0.48} L ${x - 17} ${base - h * 0.48} Z`;
  return (
    <g fill="none" stroke={B.line} strokeWidth={2.5} strokeLinejoin="round">
      <circle cx={x} cy={top + 13} r={12} {...drawOn(q)} />
      <path d={d} {...drawOn(p(q, 0.2, 1))} />
    </g>
  );
};

export const Caption: React.FC<{t: number; pages: CaptionPage[]; top?: number}> = ({t, pages, top = DEFAULT_TOP}) => {
  const pg = pageAt(t, pages);
  if (!pg) return null;
  const pop = p(t, pg.start - 0.08, pg.start + 0.1);
  const mark = (s: React.CSSProperties) => <div style={{position: 'absolute', width: 22, height: 22, borderColor: B.cyan, borderStyle: 'solid', borderWidth: 0, ...s}} />;
  return (
    <div style={{position: 'absolute', top, left: 0, right: 0, display: 'flex', justifyContent: 'center', opacity: pop, transform: `translateY(${(1 - pop) * 14}px)`}}>
      <div style={{position: 'relative', background: '#071F3Ce8', border: `1.5px solid ${B.line2}66`, padding: '20px 34px', maxWidth: 960,
        fontFamily: MONO, fontWeight: 600, fontSize: 50, lineHeight: 1.22, textAlign: 'center', color: B.line}}>
        {mark({left: -8, top: -8, borderLeftWidth: 3, borderTopWidth: 3})}
        {mark({right: -8, top: -8, borderRightWidth: 3, borderTopWidth: 3})}
        {mark({left: -8, bottom: -8, borderLeftWidth: 3, borderBottomWidth: 3})}
        {mark({right: -8, bottom: -8, borderRightWidth: 3, borderBottomWidth: 3})}
        {pg.words.map((w, i) => {
          const on = t >= w.start - 0.04 && t < w.end + 0.08;
          const said = t >= w.start - 0.04;
          return (
            <React.Fragment key={i}>
              <span style={{color: on ? B.yellow : said ? B.line : '#6F9BD0', boxShadow: on ? `inset 0 -5px 0 ${B.yellow}` : 'none'}}>{display(w.word)}</span>
              {i < pg.words.length - 1 ? ' ' : ''}
            </React.Fragment>
          );
        })}
      </div>
    </div>
  );
};

// ---------- sheet pieces (from the BLUEPRINT demo) ----------
/** Stroke props for linework that draws itself on. */
export const line = (q: number, w = 3, color = B.line) => ({fill: 'none', stroke: color, strokeWidth: w, strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const, ...drawOn(q)});

export const Hatch: React.FC<{x: number; y: number; w: number; h: number; q: number; id: string}> = ({x, y, w, h, q, id}) => (
  <g>
    <defs>
      <pattern id={id} width="22" height="22" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><line x1="0" y1="0" x2="0" y2="22" stroke={B.line2} strokeWidth="2" strokeOpacity=".55" /></pattern>
      <clipPath id={`${id}-c`}><rect x={x} y={y} width={w} height={h * q} /></clipPath>
    </defs>
    <rect x={x} y={y} width={w} height={h} fill={`url(#${id})`} clipPath={`url(#${id}-c)`} />
    <rect x={x} y={y} width={w} height={h} fill="none" stroke={B.line} strokeWidth={2.5} {...drawOn(q)} />
  </g>
);

export const BreakMark: React.FC<{x: number; y: number; w: number; q: number}> = ({x, y, w, q}) => (
  <path d={`M ${x - w / 2} ${y} l ${w * 0.25} -16 l ${w * 0.18} 30 l ${w * 0.18} -30 l ${w * 0.18} 30 l ${w * 0.21} -14`} {...line(q, 5, B.red)} />
);

export const Panel: React.FC<{x: number; y: number; w: number; h: number; title: string; q: number; children?: React.ReactNode}> = ({x, y, w, h, title, q, children}) => (
  <g>
    <rect x={x} y={y} width={w} height={h} fill={B.bg} fillOpacity={0.6 * q} stroke={B.line} strokeWidth={3} {...drawOn(q)} />
    <line x1={x} y1={y + 70} x2={x + w} y2={y + 70} stroke={B.line2} strokeWidth={2} {...drawOn(p(q, 0.3, 1))} />
    <text x={x + 26} y={y + 46} fill={B.cyan} opacity={p(q, 0.4, 1)} style={{fontFamily: MONO, fontWeight: 600, fontSize: 28, letterSpacing: 2}}>{title}</text>
    {q > 0.5 && children}
  </g>
);

