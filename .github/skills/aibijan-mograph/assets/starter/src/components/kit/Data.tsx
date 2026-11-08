import React from 'react';
import {C, FONT, TEXT} from '../../theme';
import {easeInOut, formatNumber, lerp, prog} from './util';

export type Bar = {label: string; value: number; color?: string; display?: string};

/** Animated bar chart with direct labels. `x` = centre, `y` = top. Use only real or clearly labelled simulated numbers. */
export const BarChart: React.FC<{
  t: number; data: Bar[]; x?: number; y: number; width?: number; height?: number; growAt?: number; stagger?: number; dur?: number;
  max?: number; format?: (v: number) => string; highlight?: number; color?: string; dark?: boolean;
}> = ({t, data, x = 540, y, width = 900, height = 640, growAt = 0, stagger = 0.12, dur = 0.8, max, format = (v) => formatNumber(v),
  highlight, color = C.blue, dark = false}) => {
  const top = Math.max(...data.map((d) => d.value), 1);
  const m = max ?? top * 1.12;
  const n = data.length, gap = width / n, bw = Math.min(150, gap * 0.62);
  const ink = dark ? C.cream : C.navy;
  const plot = height - 90;
  return (
    <div style={{position: 'absolute', left: x - width / 2, top: y, width, height}}>
      <div style={{position: 'absolute', left: 0, right: 0, top: plot, height: 4, borderRadius: 2, background: dark ? '#ffffff55' : '#0d123333'}} />
      {data.map((d, i) => {
        const p = prog(t, growAt + i * stagger, dur, easeInOut);
        const h = (d.value / m) * (plot - 70) * p;
        const cx = gap * i + gap / 2;
        const fill = d.color ?? (highlight === undefined ? color : i === highlight ? C.yellow : dark ? '#ffffff40' : '#0d123326');
        return (
          <React.Fragment key={i}>
            <div style={{position: 'absolute', left: cx - bw / 2, top: plot - h, width: bw, height: h, borderRadius: '18px 18px 6px 6px', background: fill}} />
            <div style={{position: 'absolute', left: cx - gap / 2, width: gap, top: plot - h - 62, textAlign: 'center', fontFamily: FONT,
              fontWeight: 800, fontSize: 40, color: ink, opacity: p}}>{d.display ?? format(d.value * p)}</div>
            <div style={{position: 'absolute', left: cx - gap / 2, width: gap, top: plot + 18, textAlign: 'center', fontFamily: TEXT,
              fontSize: 30, lineHeight: 1.15, color: ink, opacity: 0.85}}>{d.label}</div>
          </React.Fragment>
        );
      })}
    </div>
  );
};

/** Animated line chart that draws itself, with an end label. `x` = centre, `y` = top. */
export const LineChart: React.FC<{
  t: number; points: number[]; labels?: string[]; x?: number; y: number; width?: number; height?: number; drawAt?: number; dur?: number;
  color?: string; format?: (v: number) => string; min?: number; max?: number; dark?: boolean;
}> = ({t, points, labels = [], x = 540, y, width = 900, height = 560, drawAt = 0, dur = 1.6, color = C.coral,
  format = (v) => formatNumber(v), min, max, dark = false}) => {
  const id = React.useId().replace(/:/g, '');
  const lo = min ?? Math.min(...points), hi = max ?? Math.max(...points);
  const padX = 30, padTop = 70, padBottom = 80;
  const W = width, H = height, plotH = H - padTop - padBottom;
  const pts = points.map((v, i) => [padX + (i / Math.max(1, points.length - 1)) * (W - 2 * padX), padTop + (1 - (v - lo) / Math.max(1e-9, hi - lo)) * plotH] as const);
  const len = pts.slice(1).reduce((s, [px, py], i) => s + Math.hypot(px - pts[i][0], py - pts[i][1]), 0);
  const p = prog(t, drawAt, dur, easeInOut);
  const d = pts.map(([px, py], i) => `${i ? 'L' : 'M'}${px.toFixed(1)} ${py.toFixed(1)}`).join(' ');
  const area = `${d} L${pts[pts.length - 1][0]} ${padTop + plotH} L${pts[0][0]} ${padTop + plotH} Z`;
  // current tip position along the polyline
  let remain = len * p, tip = pts[0];
  for (let i = 1; i < pts.length; i++) {
    const seg = Math.hypot(pts[i][0] - pts[i - 1][0], pts[i][1] - pts[i - 1][1]);
    if (remain <= seg) { const q = seg ? remain / seg : 0; tip = [lerp(pts[i - 1][0], pts[i][0], q), lerp(pts[i - 1][1], pts[i][1], q)] as const; break; }
    remain -= seg; tip = pts[i];
  }
  const ink = dark ? C.cream : C.navy;
  const idx = Math.min(points.length - 1, Math.round(p * (points.length - 1)));
  return (
    <div style={{position: 'absolute', left: x - W / 2, top: y, width: W, height: H}}>
      <svg width={W} height={H} style={{overflow: 'visible'}}>
        <defs>
          <linearGradient id={`a${id}`} x1="0" y1="0" x2="0" y2="1"><stop offset="0" stopColor={color} stopOpacity="0.35" /><stop offset="1" stopColor={color} stopOpacity="0" /></linearGradient>
          <clipPath id={`c${id}`}><rect x="0" y="0" width={tip[0]} height={H} /></clipPath>
        </defs>
        <line x1={padX} x2={W - padX} y1={padTop + plotH} y2={padTop + plotH} stroke={ink} strokeOpacity="0.25" strokeWidth="4" />
        <path d={area} fill={`url(#a${id})`} clipPath={`url(#c${id})`} />
        <path d={d} fill="none" stroke={color} strokeWidth="10" strokeLinecap="round" strokeLinejoin="round" strokeDasharray={len} strokeDashoffset={len * (1 - p)} />
        {p > 0 && <circle cx={tip[0]} cy={tip[1]} r="15" fill={color} stroke="white" strokeWidth="6" />}
      </svg>
      {p > 0 && (
        <div style={{position: 'absolute', left: tip[0], top: tip[1] - 78, transform: 'translateX(-50%)', fontFamily: FONT, fontWeight: 800,
          fontSize: 40, color: ink, whiteSpace: 'nowrap'}}>{format(points[idx])}</div>
      )}
      {labels.map((l, i) => (
        <div key={i} style={{position: 'absolute', top: padTop + plotH + 20, left: pts[i][0], transform: 'translateX(-50%)', fontFamily: TEXT,
          fontSize: 28, color: ink, opacity: 0.8, whiteSpace: 'nowrap'}}>{l}</div>
      ))}
    </div>
  );
};

/** Percentage ring that fills and counts up. `x`/`y` = centre. */
export const PercentRing: React.FC<{t: number; at?: number; value: number; x: number; y: number; size?: number; color?: string; label?: string; dark?: boolean; dur?: number}> =
  ({t, at = 0, value, x, y, size = 360, color = C.yellow, label, dark = false, dur = 1.2}) => {
    const p = prog(t, at, dur, easeInOut);
    const r = size / 2 - 22, c = 2 * Math.PI * r;
    const ink = dark ? C.cream : C.navy;
    return (
      <div style={{position: 'absolute', left: x - size / 2, top: y - size / 2, width: size, height: size}}>
        <svg width={size} height={size} style={{transform: 'rotate(-90deg)'}}>
          <circle cx={size / 2} cy={size / 2} r={r} fill="none" stroke={ink} strokeOpacity="0.15" strokeWidth="30" />
          <circle cx={size / 2} cy={size / 2} r={r} fill="none" stroke={color} strokeWidth="30" strokeLinecap="round"
            strokeDasharray={c} strokeDashoffset={c * (1 - (value / 100) * p)} />
        </svg>
        <div style={{position: 'absolute', inset: 0, display: 'flex', flexDirection: 'column', alignItems: 'center', justifyContent: 'center', color: ink}}>
          <div style={{fontFamily: FONT, fontWeight: 800, fontSize: size * 0.26}}>{formatNumber(value * p)}%</div>
          {label && <div style={{fontFamily: TEXT, fontSize: size * 0.085, opacity: 0.8, maxWidth: size * 0.7, textAlign: 'center'}}>{label}</div>}
        </div>
      </div>
    );
  };

const tempColor = (v: number) => {
  const stops: Array<[number, [number, number, number]]> = [[-10, [57, 140, 230]], [10, [120, 200, 230]], [24, [154, 214, 172]], [32, [255, 200, 61]], [40, [229, 72, 77]]];
  let a = stops[0], b = stops[stops.length - 1];
  for (let i = 1; i < stops.length; i++) if (v <= stops[i][0]) { a = stops[i - 1]; b = stops[i]; break; }
  const q = Math.max(0, Math.min(1, (v - a[0]) / Math.max(1e-9, b[0] - a[0])));
  return `rgb(${a[1].map((c, i) => Math.round(lerp(c, b[1][i], q))).join(',')})`;
};

/** Thermometer that moves from `from` to `to` °C starting at `at`. `x` = centre, `y` = top. */
export const Thermometer: React.FC<{t: number; from: number; to: number; at?: number; dur?: number; x: number; y: number; height?: number;
  min?: number; max?: number; unit?: string; dark?: boolean}> =
  ({t, from, to, at = 0, dur = 1.6, x, y, height = 720, min = -10, max = 40, unit = '°C', dark = false}) => {
    const v = lerp(from, to, prog(t, at, dur, easeInOut));
    const tubeW = 70, bulb = 130, tubeH = height - bulb;
    const q = Math.max(0, Math.min(1, (v - min) / (max - min)));
    const col = tempColor(v);
    const ink = dark ? C.cream : C.navy;
    const level = tubeH - 30 - q * (tubeH - 60);
    return (
      <div style={{position: 'absolute', left: x - bulb / 2, top: y, width: bulb, height}}>
        <div style={{position: 'absolute', left: (bulb - tubeW) / 2, top: 0, width: tubeW, height: tubeH + 20, borderRadius: 999, background: '#ffffff', boxShadow: 'inset 0 0 0 6px #d8dde8'}} />
        <div style={{position: 'absolute', left: (bulb - tubeW) / 2 + 16, width: tubeW - 32, top: level, height: tubeH + 20 - level, borderRadius: 999, background: col}} />
        <div style={{position: 'absolute', left: 0, top: tubeH - 10, width: bulb, height: bulb, borderRadius: 999, background: col, boxShadow: '0 0 0 12px #ffffff, 0 18px 30px #0d123333'}} />
        {Array.from({length: 6}, (_, i) => {
          const tv = min + ((max - min) * i) / 5;
          const ty = tubeH - 30 - (i / 5) * (tubeH - 60);
          return <div key={i} style={{position: 'absolute', left: -78, top: ty - 18, width: 70, textAlign: 'right', fontFamily: TEXT, fontSize: 26, color: ink, opacity: 0.6}}>{Math.round(tv)}°</div>;
        })}
        <div style={{position: 'absolute', left: bulb + 18, top: level - 34, fontFamily: FONT, fontWeight: 800, fontSize: 64, color: ink, whiteSpace: 'nowrap'}}>
          {(v < 0 ? '−' : '') + formatNumber(Math.abs(v), Math.abs(to - from) < 6 ? 1 : 0)}{unit}
        </div>
      </div>
    );
  };
