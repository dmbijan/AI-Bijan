import React from 'react';
import {Easing} from 'remotion';
import {CaptionPage} from '../../components/WordCaptions';
import {display, drawOn, noise, p, pageAt, pb, rnd, roughEllipse, roughLine} from '../shared';

// ARSIP style kit — case-file documentary look. Shared by the prototype and every ARSIP episode.
export const ARSIP = {
  desk: '#1B1712', paper: '#EEE5D0', paperDark: '#D9CCAE', card: '#F5F0E3', manila: '#CFAE6E', manilaDark: '#B8955A',
  ink: '#1E1C19', inkSoft: '#5B544A', red: '#C22F26', string: '#B0241C', marker: '#FFD54A', tape: '#E8DDBDcc',
};
export const A = ARSIP;
export const HEAD = "'Arsip Head', serif";
export const TYPE = "'Arsip Type', monospace";
export const GRAIN = noise(0.85, 3, 4, 400);
export const DESK = noise(0.006, 4, 9, 1024);

// ---------- kit ----------
export const Sheet: React.FC<{x: number; y: number; w: number; h: number; rot?: number; bg?: string; children?: React.ReactNode; style?: React.CSSProperties}> = ({
  x, y, w, h, rot = 0, bg = A.paper, children, style,
}) => (
  <div style={{position: 'absolute', left: x, top: y, width: w, height: h, transform: `rotate(${rot}deg)`, background: bg,
    boxShadow: '0 28px 50px rgba(0,0,0,.55), 0 3px 8px rgba(0,0,0,.4)', overflow: 'hidden', ...style}}>
    <div style={{position: 'relative', width: '100%', height: '100%'}}>{children}</div>
    <div style={{position: 'absolute', inset: 0, backgroundImage: GRAIN, backgroundSize: '400px', opacity: 0.2, mixBlendMode: 'multiply'}} />
    <div style={{position: 'absolute', inset: 0, boxShadow: 'inset 0 0 80px rgba(120,82,36,.32)'}} />
  </div>
);

export const Tape: React.FC<{x: number; y: number; w?: number; rot?: number}> = ({x, y, w = 150, rot = -8}) => (
  <div style={{position: 'absolute', left: x, top: y, width: w, height: 44, background: A.tape, transform: `rotate(${rot}deg)`,
    boxShadow: '0 2px 4px #0003', clipPath: 'polygon(2% 0,98% 6%,100% 30%,97% 55%,100% 100%,3% 94%,0 70%,3% 40%)'}} />
);

export const TypeOn: React.FC<{text: string; t: number; start: number; cps?: number; style?: React.CSSProperties}> = ({text, t, start, cps = 20, style}) => {
  const n = Math.max(0, Math.min(text.length, Math.floor((t - start) * cps)));
  const typing = n > 0 && n < text.length;
  return (
    <span style={style}>
      {text.slice(0, n)}
      <span style={{opacity: typing ? 1 : 0}}>▌</span>
    </span>
  );
};

/** Rubber stamp: slams in, ink broken up by noise. */
export const Stamp: React.FC<{t: number; at: number; x: number; y: number; w: number; h: number; rot: number; lines: string[]; size?: number; color?: string; id: string}> = ({
  t, at: start, x, y, w, h, rot, lines, size = 72, color = A.red, id,
}) => {
  if (t < start) return null;
  const q = p(t, start, start + 0.13);
  const scale = 1.9 - 0.9 * q;
  return (
    <div style={{position: 'absolute', left: x, top: y, transform: `translate(-50%,-50%) rotate(${rot}deg) scale(${scale})`, opacity: Math.min(1, q * 2.5) * 0.94}}>
      <svg width={w} height={h} viewBox={`0 0 ${w} ${h}`} style={{overflow: 'visible'}}>
        <defs>
          <filter id={`ink-${id}`} x="-5%" y="-5%" width="110%" height="110%">
            <feTurbulence type="fractalNoise" baseFrequency="0.55" numOctaves="2" seed="7" result="n" />
            <feColorMatrix in="n" type="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  -2.6 0 0 0 2.5" result="m" />
            <feComposite in="SourceGraphic" in2="m" operator="in" />
          </filter>
        </defs>
        <g filter={`url(#ink-${id})`} fill="none" stroke={color}>
          <rect x={6} y={6} width={w - 12} height={h - 12} rx={14} strokeWidth={10} fill="#EEE5D0" fillOpacity={0.86} />
          <rect x={22} y={22} width={w - 44} height={h - 44} rx={8} strokeWidth={3.5} />
          {lines.map((ln, i) => (
            <text key={i} x={w / 2} y={h / 2 + (i - (lines.length - 1) / 2) * size * 1.05 + size * 0.34} textAnchor="middle" fill={color} stroke="none"
              style={{fontFamily: TYPE, fontWeight: 700, fontSize: i === 0 ? size : size * 0.5, letterSpacing: i === 0 ? 4 : 6}}>{ln}</text>
          ))}
        </g>
      </svg>
    </div>
  );
};

export const Polaroid: React.FC<{x: number; y: number; w: number; h: number; rot: number; q: number; label?: string; children: React.ReactNode; tapeRot?: number}> = ({
  x, y, w, h, rot, q, label, children, tapeRot = -6,
}) => {
  if (q <= 0) return null;
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, height: h, transform: `rotate(${rot + (1 - q) * 10}deg) scale(${1.22 - 0.22 * q})`, opacity: Math.min(1, q * 3)}}>
      <Sheet x={0} y={0} w={w} h={h} bg="#F3EFE6">
        <div style={{position: 'absolute', left: 22, top: 22, right: 22, bottom: 92, overflow: 'hidden', background: '#2a2119'}}>{children}</div>
        {label && <div style={{position: 'absolute', left: 22, right: 22, bottom: 24, fontFamily: TYPE, fontWeight: 700, fontSize: 27, color: A.ink, textAlign: 'center'}}>{label}</div>}
      </Sheet>
      <Tape x={w / 2 - 75} y={-20} rot={tapeRot} />
    </div>
  );
};

/** Halftone newspaper photo of a crowd (q = print-in progress). */
export const CrowdPhoto: React.FC<{w: number; h: number; q: number}> = ({w, h, q}) => {
  const heads: Array<[number, number, number]> = [];
  for (let row = 0; row < 3; row++) {
    const n = 7 + row;
    for (let i = 0; i < n; i++) {
      const x = (i + 0.5 + (row % 2) * 0.4) * (w / n) + (rnd(row * 20 + i) - 0.5) * 30;
      const s = 0.62 + row * 0.2 + rnd(i * 3 + row) * 0.08;
      heads.push([x, h * (0.32 + row * 0.2), s]);
    }
  }
  return (
    <svg width={w} height={h} viewBox={`0 0 ${w} ${h}`} style={{display: 'block', opacity: q, filter: `blur(${(1 - q) * 5}px) contrast(${0.6 + 0.4 * q})`}}>
      <defs>
        <pattern id="ht" width="9" height="9" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><circle cx="4.5" cy="4.5" r="3.4" fill={A.ink} /></pattern>
        <pattern id="htL" width="9" height="9" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><circle cx="4.5" cy="4.5" r="1.5" fill={A.ink} /></pattern>
      </defs>
      <rect width={w} height={h} fill={A.paperDark} />
      <rect width={w} height={h} fill="url(#htL)" opacity=".75" />
      {heads.map(([x, y, s], i) => (
        <g key={i} fill="url(#ht)" transform={`translate(${x} ${y}) scale(${s})`}>
          <circle cx="0" cy="-58" r="30" />
          <path d="M-62 40 Q-60 -18 0 -20 Q60 -18 62 40 L62 200 L-62 200 Z" />
        </g>
      ))}
    </svg>
  );
};

export const TextBars: React.FC<{lines: number; w: number; seed: number}> = ({lines, w, seed}) => (
  <div>
    {Array.from({length: lines}).map((_, i) => (
      <div key={i} style={{height: 9, marginBottom: 9, background: '#00000038', width: i === lines - 1 ? w * (0.35 + rnd(seed + i) * 0.3) : w - rnd(seed + i) * 18}} />
    ))}
  </div>
);

export const Reveal: React.FC<{q: number; children: React.ReactNode; style?: React.CSSProperties}> = ({q, children, style}) => (
  <div style={{overflow: 'hidden', ...style}}>
    <div style={{transform: `translateY(${(1 - q) * 105}%)`}}>{children}</div>
  </div>
);

export const EvidenceTag: React.FC<{x: number; y: number; rot: number; text: string; q: number}> = ({x, y, rot, text, q}) => (
  <div style={{position: 'absolute', left: x, top: y, transform: `rotate(${rot}deg) scale(${q})`, transformOrigin: '0 50%', opacity: q > 0 ? 1 : 0}}>
    <div style={{background: A.manila, padding: '10px 22px 10px 40px', fontFamily: TYPE, fontWeight: 700, fontSize: 26, color: A.ink,
      clipPath: 'polygon(14% 0,100% 0,100% 100%,14% 100%,0 50%)', boxShadow: '0 6px 10px #0005'}}>{text}</div>
    <div style={{position: 'absolute', left: 16, top: '50%', width: 12, height: 12, marginTop: -6, borderRadius: 6, background: '#2a2119'}} />
  </div>
);

export const Caption: React.FC<{t: number; pages: CaptionPage[]; top?: number}> = ({t, pages, top = 1500}) => {
  const pg = pageAt(t, pages);
  if (!pg) return null;
  const pop = p(t, pg.start - 0.08, pg.start + 0.12);
  const torn = Array.from({length: 24}, (_, i) => `${(i / 23) * 100}% ${rnd(i + pg.start) * 7}%`).join(',') + ',' +
    Array.from({length: 24}, (_, i) => `${100 - (i / 23) * 100}% ${93 + rnd(i * 2 + pg.start) * 7}%`).join(',');
  return (
    <div style={{position: 'absolute', top, left: 0, right: 0, display: 'flex', justifyContent: 'center', opacity: pop,
      transform: `translateY(${(1 - pop) * 16}px)`, filter: 'drop-shadow(0 10px 14px rgba(0,0,0,.55))'}}>
      <div style={{background: '#F2EBD8', padding: '24px 36px', transform: 'rotate(-1deg)', clipPath: `polygon(${torn})`, maxWidth: 960,
        fontFamily: TYPE, fontWeight: 700, fontSize: 54, lineHeight: 1.2, textAlign: 'center', color: A.ink}}>
        {pg.words.map((w, i) => {
          const hl = p(t, w.start - 0.04, w.start + 0.14);
          const said = t >= w.start - 0.04;
          return (
            <span key={i} style={{color: said ? A.ink : '#9A907F', backgroundImage: `linear-gradient(transparent 16%, ${A.marker}dd 16%, ${A.marker}dd 90%, transparent 90%)`,
              backgroundRepeat: 'no-repeat', backgroundSize: `${t < w.end + 0.08 ? hl * 100 : 0}% 100%`, padding: '0 4px'}}>
              {display(w.word)}{i < pg.words.length - 1 ? ' ' : ''}
            </span>
          );
        })}
      </div>
    </div>
  );
};

// ---------- case-board pieces (from the ARSIP demo) ----------
export type Variant = 'helmet' | 'beard' | 'laurel' | 'fringe';
export const Bust: React.FC<{v: Variant}> = ({v}) => (
  <svg width="100%" height="100%" viewBox="0 0 400 440" preserveAspectRatio="xMidYMid slice">
    <defs>
      <linearGradient id={`mg-${v}`} x1="0" y1="0" x2="0" y2="1"><stop offset="0" stopColor="#C9B48E" /><stop offset="1" stopColor="#9C8661" /></linearGradient>
    </defs>
    <rect width="400" height="440" fill={`url(#mg-${v})`} />
    {Array.from({length: 11}).map((_, i) => (
      <g key={i}>
        <line x1="0" x2="400" y1={22 + i * 40} y2={22 + i * 40} stroke="#3B2F22" strokeOpacity=".35" strokeWidth="2" />
        <text x="8" y={18 + i * 40} fill="#3B2F22" fillOpacity=".55" style={{fontFamily: TYPE, fontWeight: 700, fontSize: 15}}>{190 - i * 10}</text>
      </g>
    ))}
    <g fill="#2A2119">
      <path d="M 120 200 C 110 140, 140 90, 200 88 C 250 86, 285 120, 288 160 L 302 190 L 290 196 C 292 208, 288 220, 282 226 L 286 236 C 280 244, 270 250, 258 250 C 250 268, 240 280, 228 288 L 230 322 C 280 332, 330 362, 352 440 L 48 440 C 70 362, 120 332, 160 322 L 162 282 C 130 262, 122 236, 120 200 Z" />
      {v === 'beard' && <path d="M 168 226 C 172 300, 246 318, 290 238 L 266 250 C 244 262, 212 252, 196 230 Z" fill="#17110c" />}
      {v === 'fringe' && <path d="M 150 120 C 180 92, 240 88, 272 118 L 262 124 C 240 108, 190 108, 160 132 Z" fill="#17110c" />}
    </g>
    {v === 'helmet' && (
      <g>
        <path d="M 108 196 C 96 112, 156 66, 214 68 C 272 70, 304 118, 298 178 L 250 170 L 236 196 Z" fill="#55432f" />
        <path d="M 134 96 C 160 18, 262 14, 290 84" fill="none" stroke="#7A2B1C" strokeWidth="28" strokeLinecap="round" />
      </g>
    )}
    {(v === 'laurel' || v === 'fringe') && Array.from({length: 11}).map((_, i) => {
      const a = Math.PI * (1.05 + i * 0.08);
      const x = 205 + Math.cos(a) * 98, y = 168 + Math.sin(a) * 86;
      return <ellipse key={i} cx={x} cy={y} rx="20" ry="9" fill="#5E6B33" transform={`rotate(${(a * 180) / Math.PI + 70} ${x} ${y})`} />;
    })}
    <rect x="100" y="362" width="200" height="62" fill="#14110d" />
  </svg>
);

export const Mugshot: React.FC<{x: number; y: number; rot: number; q: number; n: string; v: Variant; plate: string; tape?: number}> = ({x, y, rot, q, n, v, plate, tape = -6}) => (
  <Polaroid x={x} y={y} w={430} h={560} rot={rot} q={q} label={`TERSANGKA ${n}`} tapeRot={tape}>
    <div style={{position: 'absolute', inset: 0, filter: 'sepia(.25) contrast(1.05)'}}>
      <Bust v={v} />
      <div style={{position: 'absolute', left: 0, right: 0, bottom: 18, textAlign: 'center', fontFamily: TYPE, fontWeight: 700, fontSize: 25, color: '#EDE3CC', letterSpacing: 2}}>{plate}</div>
    </div>
  </Polaroid>
);

export const NameCard: React.FC<{x: number; y: number; rot: number; t: number; at: number; name: string; sub: string; subAt: number}> = ({x, y, rot, t, at, name, sub, subAt}) => {
  const q = p(t, at, at + 0.3);
  if (q <= 0) return null;
  return (
    <div style={{position: 'absolute', left: x, top: y, transform: `rotate(${rot}deg) translateY(${(1 - q) * 20}px)`, opacity: q}}>
      <Sheet x={0} y={0} w={430} h={132} bg={A.card}>
        <div style={{position: 'absolute', left: 22, top: 14, fontFamily: HEAD, fontWeight: 900, fontSize: 52, color: A.ink}}>{name}</div>
        <div style={{position: 'absolute', left: 22, top: 84, fontFamily: TYPE, fontWeight: 700, fontSize: 23, color: A.inkSoft}}>
          <TypeOn text={sub} t={t} start={subAt} cps={32} />
        </div>
      </Sheet>
    </div>
  );
};

export const Card: React.FC<{x: number; y: number; w: number; h: number; rot: number; q: number; children: React.ReactNode; bg?: string}> = ({x, y, w, h, rot, q, children, bg = A.card}) =>
  q > 0 ? (
    <div style={{position: 'absolute', left: x, top: y, transform: `rotate(${rot + (1 - q) * 6}deg) translateY(${(1 - q) * -50}px) scale(${1.08 - 0.08 * q})`, opacity: Math.min(1, q * 2.2)}}>
      <Sheet x={0} y={0} w={w} h={h} bg={bg}>{children}</Sheet>
    </div>
  ) : null;

export const Pin: React.FC<{x: number; y: number; q?: number}> = ({x, y, q = 1}) =>
  q > 0 ? (
    <svg style={{position: 'absolute', left: x - 20, top: y - 20, overflow: 'visible'}} width={40} height={40}>
      <circle cx={23} cy={25} r={13} fill="#0006" /><circle cx={20} cy={20} r={13} fill="#C9302C" /><circle cx={16} cy={16} r={4} fill="#fff9" />
    </svg>
  ) : null;

export const MarkerX: React.FC<{cx: number; cy: number; q: number; s?: number}> = ({cx, cy, q, s = 46}) => (
  <svg style={{position: 'absolute', left: 0, top: 0, overflow: 'visible'}} width={1} height={1}>
    <path d={roughLine([cx - s, cy - s], [cx + s, cy + s], cx, 5)} fill="none" stroke={A.red} strokeWidth={9} strokeLinecap="round" {...drawOn(p(q, 0, 0.5))} />
    <path d={roughLine([cx + s, cy - s], [cx - s, cy + s], cy, 5)} fill="none" stroke={A.red} strokeWidth={9} strokeLinecap="round" {...drawOn(p(q, 0.5, 1))} />
  </svg>
);

export const Ring: React.FC<{cx: number; cy: number; rx: number; ry: number; q: number; color?: string; seed?: number; w?: number}> = ({cx, cy, rx, ry, q, color = A.red, seed = 3, w = 8}) => (
  <svg style={{position: 'absolute', left: 0, top: 0, overflow: 'visible'}} width={1} height={1}>
    <path d={roughEllipse(cx, cy, rx, ry, seed, 0.05)} fill="none" stroke={color} strokeWidth={w} strokeLinecap="round" strokeOpacity={0.92} {...drawOn(q)} />
  </svg>
);

/** Classic wall calendar page: 7 columns, the month starts on Sunday so 28 days are exactly four rows. */
export const CalendarPage: React.FC<{t: number; x: number; y: number; w: number; rot: number; shown: number; title: string; sub: string; extra?: number; extraLabel?: (i: number) => string; markers?: number; markerAt?: number; circle28?: number}> = ({
  t, x, y, w, rot, shown, title, sub, extra = 0, extraLabel, markers = 0, markerAt = 0, circle28 = 0,
}) => {
  const cw = (w - 60) / 7;
  const ch = cw * 1.08;
  const gridTop = 220;
  const h = gridTop + ch * 5 + 50;
  const days = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
  return (
    <Sheet x={x} y={y} w={w} h={h} rot={rot} bg="#F4EEDF">
      <div style={{position: 'absolute', left: 0, right: 0, top: 0, height: 26, background: A.red}} />
      <div style={{position: 'absolute', left: 30, top: 44, fontFamily: HEAD, fontWeight: 900, fontSize: w * 0.13, lineHeight: 1, color: A.ink}}>{title}</div>
      <div style={{position: 'absolute', right: 30, top: 70, fontFamily: TYPE, fontWeight: 700, fontSize: w * 0.03, color: A.red}}>{sub}</div>
      {days.map((d, i) => (
        <div key={d} style={{position: 'absolute', left: 30 + i * cw, top: gridTop - 40, width: cw, textAlign: 'center', fontFamily: TYPE, fontWeight: 700, fontSize: w * 0.024, color: i === 0 ? A.red : A.inkSoft}}>{d}</div>
      ))}
      {Array.from({length: 35}).map((_, i) => {
        const col = i % 7, row = Math.floor(i / 7);
        const n = i + 1;
        const real = n <= 28 || n - 28 <= extra;
        const visible = n <= 28 ? n <= shown : n - 28 <= extra;
        return (
          <div key={i} style={{position: 'absolute', left: 30 + col * cw, top: gridTop + row * ch, width: cw, height: ch,
            border: real && visible ? `1.5px solid ${A.ink}55` : `2.5px dashed ${A.inkSoft}66`, background: n > 28 && real && visible ? `${A.marker}aa` : 'transparent'}}>
            {visible && real && <div style={{position: 'absolute', left: 8, top: 4, fontFamily: HEAD, fontWeight: 700, fontSize: w * 0.05, color: col === 0 ? A.red : A.ink}}>
              {n > 28 && extraLabel ? extraLabel(n) : n}</div>}
          </div>
        );
      })}
      {Array.from({length: markers}).map((_, i) => {
        const q = pb(t, markerAt + i * 0.15, markerAt + i * 0.15 + 0.35);
        if (q <= 0) return null;
        const cx = 30 + i * cw + cw / 2, cy = gridTop + 4 * ch + ch / 2;
        return (
          <svg key={i} style={{position: 'absolute', left: cx - 40, top: cy - 34, overflow: 'visible', transform: `translateY(${(1 - q) * -60}px)`}} width={80} height={70}>
            <path d="M 10 66 L 22 6 L 58 6 L 70 66 Z" fill={A.marker} stroke="#00000055" strokeWidth={2} />
            <text x={40} y={52} textAnchor="middle" fill={A.ink} style={{fontFamily: TYPE, fontWeight: 700, fontSize: 34}}>{i + 1}</text>
          </svg>
        );
      })}
      {circle28 > 0 && <Ring cx={30 + 6 * cw + cw / 2} cy={gridTop + 3 * ch + ch / 2} rx={cw * 0.62} ry={ch * 0.55} q={circle28} seed={28} />}
    </Sheet>
  );
};

