import React from 'react';
import {CaptionPage} from '../../components/WordCaptions';
import {display, drawOn, noise, p, pageAt, rnd, roughEllipse, roughLine, roughRect, spline} from '../shared';

// SKETSA style kit — living-notebook look. Shared by the prototype and every SKETSA episode.
const DEFAULT_TOP = 1490;
export const SKETSA = {
  paper: '#FBF7EA', rule: '#C3D7EC', margin: '#EBA3A3', ink: '#1F2433', inkSoft: '#8E93A3',
  yellow: '#FFE15A', coral: '#FF7A5C', blue: '#4C8FE3', blueLight: '#BFDBFA', green: '#7CCB9A',
};
export const S = SKETSA;
export const HEAD = "'Sketsa Head', cursive";
export const HAND = "'Sketsa Hand', cursive";
export const GRAIN = noise(0.7, 3, 5, 400);

export type Pt = [number, number];

// ---------- kit ----------
export const Boil: React.FC<{id: string; frame: number; every?: number; scale?: number}> = ({id, frame, every = 5, scale = 3.2}) => (
  <filter id={id} x="-2%" y="-2%" width="104%" height="104%">
    <feTurbulence type="fractalNoise" baseFrequency="0.022" numOctaves="2" seed={(Math.floor(frame / every) % 6) + 1} result="n" />
    <feDisplacementMap in="SourceGraphic" in2="n" scale={scale} xChannelSelector="R" yChannelSelector="G" />
  </filter>
);

export const Page: React.FC<{children?: React.ReactNode}> = ({children}) => (
  <div style={{position: 'absolute', inset: 0, background: S.paper, overflow: 'hidden'}}>
    {Array.from({length: 26}).map((_, i) => (
      <div key={i} style={{position: 'absolute', left: 0, right: 0, top: 200 + i * 66, height: 2, background: S.rule}} />
    ))}
    <div style={{position: 'absolute', top: 0, bottom: 0, left: 104, width: 2.5, background: S.margin}} />
    <div style={{position: 'absolute', inset: 0, backgroundImage: GRAIN, backgroundSize: '400px', opacity: 0.16, mixBlendMode: 'multiply'}} />
    {children}
  </div>
);

/** Pen stroke props: ink, round caps, drawn on by q. */
export const pen = (q: number, w = 5, color = S.ink) => ({fill: 'none', stroke: color, strokeWidth: w, strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const, ...drawOn(q)});

/** Highlighter swipe that grows left→right behind something. */
export const Swipe: React.FC<{x: number; y: number; w: number; h: number; q: number; color?: string; seed?: number}> = ({x, y, w, h, q, color = S.yellow, seed = 1}) => (
  q > 0 ? <path d={`M ${x} ${y + h * 0.5} L ${x + w * q} ${y + h * 0.5 + (rnd(seed) - 0.5) * 6}`} stroke={color} strokeWidth={h} strokeLinecap="round" opacity={0.75} style={{mixBlendMode: 'multiply'}} /> : null
);

/** Handwriting that "writes" itself: clip grows left→right. */
export const Write: React.FC<{id: string; x: number; y: number; q: number; children: React.ReactNode; size: number; color?: string; font?: string; rot?: number; anchor?: 'start' | 'middle'; w?: number}> = ({
  id, x, y, q, children, size, color = S.ink, font = HEAD, rot = 0, anchor = 'start', w = 900,
}) => {
  if (q <= 0) return null;
  const left = anchor === 'middle' ? x - w / 2 : x - 10;
  return (
    <g transform={`rotate(${rot} ${x} ${y})`}>
      <clipPath id={id}><rect x={left} y={y - size * 1.1} width={w * q} height={size * 1.6} /></clipPath>
      <text x={x} y={y} textAnchor={anchor} fill={color} clipPath={`url(#${id})`} style={{fontFamily: font, fontSize: size}}>{children}</text>
    </g>
  );
};

export const HAIR = ['spiky', 'bun', 'cap', 'long', 'curl', 'scarf'] as const;
export const PENCIL = '#A7ACB9';
export const Stick: React.FC<{x: number; base: number; q: number; seed: number; style: (typeof HAIR)[number]; color: string; wave?: number; pencil?: boolean}> = ({x, base, q, seed, style, color, wave = 0, pencil = false}) => {
  const pn = (qq: number, w = 5, c: string = S.ink) => (pencil ? pen(1, w * 0.5, PENCIL) : pen(qq, w, c));
  const hy = base - 170;
  const parts = (i: number) => p(q, i * 0.16, i * 0.16 + 0.36);
  const armL: Pt = [x - 44, base - 96 - wave * 70];
  return (
    <g>
      {style === 'scarf' && <path d={roughEllipse(x, hy + 4, 40, 42, seed + 9, 0.03, 1.02)} {...pn(parts(0), 5, color)} />}
      <path d={roughEllipse(x, hy, 30, 31, seed, 0.04)} {...pn(parts(0))} />
      {style === 'spiky' && <path d={`M ${x - 20} ${hy - 22} L ${x - 14} ${hy - 42} L ${x - 4} ${hy - 26} L ${x + 4} ${hy - 44} L ${x + 12} ${hy - 26} L ${x + 22} ${hy - 40} L ${x + 22} ${hy - 20}`} {...pn(parts(1), 4.5, color)} />}
      {style === 'bun' && <path d={roughEllipse(x + 4, hy - 40, 14, 13, seed + 3)} {...pn(parts(1), 5, color)} />}
      {style === 'cap' && <path d={`M ${x - 30} ${hy - 6} Q ${x} ${hy - 50} ${x + 30} ${hy - 6} M ${x + 22} ${hy - 8} L ${x + 56} ${hy - 4}`} {...pn(parts(1), 5.5, color)} />}
      {style === 'long' && <path d={`M ${x - 28} ${hy - 14} Q ${x - 46} ${hy + 20} ${x - 34} ${hy + 52} M ${x + 28} ${hy - 14} Q ${x + 46} ${hy + 20} ${x + 34} ${hy + 52} M ${x - 28} ${hy - 14} Q ${x} ${hy - 44} ${x + 28} ${hy - 14}`} {...pn(parts(1), 4.5, color)} />}
      {style === 'curl' && <path d={`M ${x - 6} ${hy - 30} q 10 -18 18 -4 q 6 12 -6 12`} {...pn(parts(1), 4.5, color)} />}
      <g opacity={!pencil && parts(2) > 0 ? 1 : 0}>
        <circle cx={x - 10} cy={hy - 3} r={3.5} fill={S.ink} />
        <circle cx={x + 10} cy={hy - 3} r={3.5} fill={S.ink} />
      </g>
      <path d={`M ${x - 11} ${hy + 10} Q ${x} ${hy + 19} ${x + 11} ${hy + 10}`} {...pn(parts(2), 3.5)} />
      <path d={roughLine([x, hy + 32], [x + 1, base - 66], seed + 1, 3)} {...pn(parts(2))} />
      <path d={`${roughLine([x, base - 128], armL, seed + 2, 3)} ${roughLine([x, base - 128], [x + 44, base - 92], seed + 3, 3)}`} {...pn(parts(3))} />
      <path d={`${roughLine([x, base - 66], [x - 26, base], seed + 4, 3)} ${roughLine([x, base - 66], [x + 26, base], seed + 5, 3)}`} {...pn(parts(4))} />
    </g>
  );
};

/** Pointing hand, drawn pointing down (+y) with the fingertip at the origin. */
export const Hand: React.FC<{q: number}> = ({q}) => (
  <g>
    <path d={roughRect(-46, -380, 160, 110, 61, 4)} {...pen(q, 5)} />
    <path d={`M -36 -350 L 100 -350 M -36 -320 L 100 -320`} {...pen(q, 3, S.blue)} />
    <path d="M -34 -268 Q -40 -170 -22 -150 L -22 -18 Q -22 6 0 6 Q 22 6 22 -18 L 22 -146 Q 40 -128 58 -146 Q 76 -128 94 -148 Q 108 -200 96 -268 Z" fill={S.paper} stroke={S.ink} strokeWidth={5} strokeLinejoin="round" />
    <path d="M -12 -14 Q 0 -28 12 -14 M -16 -96 Q 0 -88 16 -96 M 30 -200 Q 60 -214 84 -196" {...pen(q, 3)} />
  </g>
);

export const Caption: React.FC<{t: number; pages: CaptionPage[]; top?: number}> = ({t, pages, top = DEFAULT_TOP}) => {
  const pg = pageAt(t, pages);
  if (!pg) return null;
  const pop = p(t, pg.start - 0.08, pg.start + 0.1);
  return (
    <div style={{position: 'absolute', top, left: 60, right: 60, display: 'flex', justifyContent: 'center', opacity: pop, transform: `translateY(${(1 - pop) * 12}px) rotate(-1deg)`}}>
      <div style={{fontFamily: HAND, fontSize: 66, lineHeight: 1.15, textAlign: 'center', color: S.ink, maxWidth: 940, background: `${S.paper}e6`, padding: '6px 20px', borderRadius: 12}}>
        {pg.words.map((w, i) => {
          const q = p(t, w.start - 0.04, w.start + 0.14);
          const said = t >= w.start - 0.04;
          const on = said && t < w.end + 0.1;
          return (
            <React.Fragment key={i}>
              <span style={{color: said ? S.ink : S.inkSoft, padding: '0 6px', backgroundImage: `linear-gradient(transparent 30%, ${S.yellow}cc 30%, ${S.yellow}cc 88%, transparent 88%)`,
                backgroundRepeat: 'no-repeat', backgroundSize: `${on ? q * 100 : 0}% 100%`}}>{display(w.word)}</span>
              {i < pg.words.length - 1 ? ' ' : ''}
            </React.Fragment>
          );
        })}
      </div>
    </div>
  );
};

// ---------- doodle pieces (from the SKETSA demo) ----------
export const Spotlight: React.FC<{lamp: Pt; target: Pt; spread: number; q: number; power?: number; seed?: number; lampScale?: number}> = ({lamp, target, spread, q, power = 1, seed = 1, lampScale = 1}) => {
  if (q <= 0) return null;
  const [lx, ly] = lamp, [tx, ty] = target;
  const ang = Math.atan2(tx - lx, ty - ly);
  const deg = (-ang * 180) / Math.PI;
  const cone = `M ${lx - 26 * lampScale} ${ly + 30 * lampScale} L ${tx - spread} ${ty} Q ${tx} ${ty + 40} ${tx + spread} ${ty} L ${lx + 26 * lampScale} ${ly + 30 * lampScale} Z`;
  return (
    <g>
      <path d={cone} fill={S.yellow} opacity={0.5 * q * power} style={{mixBlendMode: 'multiply'}} />
      <path d={`${roughLine([lx - 26 * lampScale, ly + 30 * lampScale], [tx - spread, ty], seed, 4)} ${roughLine([lx + 26 * lampScale, ly + 30 * lampScale], [tx + spread, ty], seed + 3, 4)}`} {...pen(q, 3, '#E0B400')} />
      <path d={roughEllipse(tx, ty + 6, spread, 26, seed + 5, 0.04)} {...pen(q, 3, '#E0B400')} />
      <g transform={`translate(${lx} ${ly}) rotate(${deg}) scale(${lampScale})`}>
        <path d="M -30 -50 L 30 -50 L 44 30 L -44 30 Z" fill={S.paper} stroke={S.ink} strokeWidth={5} strokeLinejoin="round" />
        <path d="M -44 30 L 44 30" {...pen(1, 7, S.ink)} />
        <path d="M 0 -50 L 0 -90" {...pen(1, 5, S.ink)} />
      </g>
    </g>
  );
};

export const Bubble: React.FC<{x: number; y: number; w: number; h: number; q: number; seed: number; tail?: Pt; children?: React.ReactNode; thought?: boolean}> = ({x, y, w, h, q, seed, tail, children, thought = false}) => {
  if (q <= 0) return null;
  const s = 0.6 + 0.4 * q;
  return (
    <g transform={`translate(${x + w / 2} ${y + h / 2}) scale(${s}) translate(${-x - w / 2} ${-y - h / 2})`} opacity={Math.min(1, q * 2)}>
      <ellipse cx={x + w / 2} cy={y + h / 2} rx={w / 2} ry={h / 2} fill={S.paper} />
      <path d={roughEllipse(x + w / 2, y + h / 2, w / 2, h / 2, seed, 0.04)} {...pen(1, 4.5)} />
      {tail && !thought && <path d={`M ${x + w / 2 - 20} ${y + h - 6} L ${tail[0]} ${tail[1]} L ${x + w / 2 + 16} ${y + h - 10}`} fill={S.paper} stroke={S.ink} strokeWidth={4.5} strokeLinejoin="round" />}
      {tail && thought && [0.35, 0.65].map((k, i) => (
        <path key={i} d={roughEllipse(x + w / 2 + (tail[0] - x - w / 2) * k, y + h + (tail[1] - y - h) * k, 14 - i * 5, 12 - i * 4, seed + i)} {...pen(1, 4)} fill={S.paper} />
      ))}
      {children}
    </g>
  );
};

/** Hand-drawn pie chart; `part` of it hatched with marker strokes. */
export const Pie: React.FC<{cx: number; cy: number; r: number; part: number; q: number; fill: number; color: string; seed: number; id: string}> = ({cx, cy, r, part, q, fill, color, seed, id}) => {
  const end = -Math.PI / 2 + Math.PI * 2 * part * fill;
  const large = part * fill > 0.5 ? 1 : 0;
  const wedge = fill > 0 ? `M ${cx} ${cy} L ${cx} ${cy - r} A ${r} ${r} 0 ${large} 1 ${cx + Math.cos(end) * r} ${cy + Math.sin(end) * r} Z` : '';
  return (
    <g>
      <clipPath id={id}><path d={wedge} /></clipPath>
      <g clipPath={`url(#${id})`}>
        {Array.from({length: 16}).map((_, i) => (
          <path key={i} d={roughLine([cx - r + i * (2 * r / 15) - 40, cy + r], [cx - r + i * (2 * r / 15) + 40, cy - r], seed + i, 3)} stroke={color} strokeWidth={26} strokeLinecap="round" fill="none" opacity={0.75} />
        ))}
      </g>
      <path d={roughEllipse(cx, cy, r, r, seed, 0.03)} {...pen(q, 6)} />
      {fill > 0 && <path d={`M ${cx} ${cy - r} L ${cx} ${cy} L ${cx + Math.cos(end) * r} ${cy + Math.sin(end) * r}`} {...pen(1, 4.5)} />}
    </g>
  );
};

/** A small head that either looks at the student ("!") or at their own phone. */
export const Head: React.FC<{x: number; y: number; q: number; looks: boolean; seed: number}> = ({x, y, q, looks, seed}) => {
  if (q <= 0) return null;
  return (
    <g opacity={Math.min(1, q * 2)}>
      <path d={roughEllipse(x, y, 30, 31, seed, 0.04)} {...pen(1, 4)} fill={S.paper} />
      {looks ? (
        <g>
          <circle cx={x - 14} cy={y - 4} r={4} fill={S.ink} /><circle cx={x + 6} cy={y - 4} r={4} fill={S.ink} />
          <text x={x + 26} y={y - 30} fill={S.coral} style={{fontFamily: HEAD, fontSize: 44}}>!</text>
        </g>
      ) : (
        <g>
          <path d={`M ${x - 14} ${y + 4} q 5 4 10 0 M ${x + 4} ${y + 4} q 5 4 10 0`} {...pen(1, 3)} />
          <rect x={x - 16} y={y + 30} width={32} height={46} rx={6} fill={S.blueLight} stroke={S.ink} strokeWidth={3.5} />
        </g>
      )}
    </g>
  );
};

/** Blush marks + a sweat drop next to a head at (x, y). */
export const Cheeks: React.FC<{x: number; y: number; q: number}> = ({x, y, q}) =>
  q > 0 ? (
    <g opacity={q}>
      {[-1, 1].map((s) => <path key={s} d={`M ${x + s * 22 - 8} ${y + 8} l 6 -6 M ${x + s * 22} ${y + 8} l 6 -6`} {...pen(1, 3.5, S.coral)} />)}
      <path d={`M ${x + 40} ${y - 26} q 8 14 0 20 q -8 -6 0 -20`} {...pen(1, 3, S.blue)} />
    </g>
  ) : null;

export const Bed: React.FC<{x: number; y: number; q: number; awake: boolean; smile: number}> = ({x, y, q, awake, smile}) => (
  <g>
    <path d={roughRect(x, y, 440, 120, 5)} {...pen(q, 5)} />
    <path d={`${roughLine([x, y + 120], [x, y + 180], 7)} ${roughLine([x + 440, y + 120], [x + 440, y + 180], 8)} ${roughLine([x, y - 90], [x, y + 10], 9)}`} {...pen(q, 5)} />
    <path d={roughEllipse(x + 80, y - 22, 64, 26, 11)} {...pen(q, 4.5)} />
    <path d={roughEllipse(x + 100, y - 52, 34, 33, 12)} {...pen(q, 5)} fill={S.paper} />
    {awake ? (
      <g><circle cx={x + 88} cy={y - 56} r={5} fill={S.ink} /><circle cx={x + 112} cy={y - 56} r={5} fill={S.ink} /></g>
    ) : (
      <path d={`M ${x + 80} ${y - 54} q 7 6 14 0 M ${x + 104} ${y - 54} q 7 6 14 0`} {...pen(1, 3.5)} />
    )}
    <path d={`M ${x + 90} ${y - 38} Q ${x + 100} ${y - 38 + 8 * smile} ${x + 112} ${y - 38}`} {...pen(q, 3.5)} />
    <path d={spline([[x + 130, y], [x + 230, y - 34], [x + 340, y - 18], [x + 440, y - 4]])} {...pen(q, 5, S.blue)} />
    {Array.from({length: 6}).map((_, i) => (
      <path key={i} d={roughLine([x + 150 + i * 48, y + 110], [x + 190 + i * 48, y + 20], 120 + i, 3)} stroke={S.blueLight} strokeWidth={26} strokeLinecap="round" fill="none" opacity={0.85 * p(q, 0.5 + i * 0.05, 0.7 + i * 0.05)} />
    ))}
  </g>
);

/** Scale a doodle group about its own anchor (ox, oy) and place that anchor at (x, y). */
export const Sc: React.FC<{x: number; y: number; ox: number; oy: number; k: number; children: React.ReactNode; o?: number}> = ({x, y, ox, oy, k, children, o = 1}) => (
  <g transform={`translate(${x} ${y}) scale(${k}) translate(${-ox} ${-oy})`} opacity={o}>{children}</g>
);

