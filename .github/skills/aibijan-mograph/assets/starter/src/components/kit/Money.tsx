import React from 'react';
import {C, FONT, TEXT} from '../../theme';
import {easeBack, easeInOut, formatNumber, formatRupiah, lerp, prog} from './util';

/**
 * Big animated rupiah amount, e.g. Rp5.000.000 → Rp5.000. `x` = centre, `y` = top.
 * With `showFrom`, the old amount stays above and gets struck through when the count starts.
 */
export const RupiahCounter: React.FC<{
  t: number; from: number; to: number; at: number; dur?: number; x?: number; y: number; size?: number; color?: string;
  prefix?: string; showFrom?: boolean; strikeColor?: string;
}> = ({t, from, to, at, dur = 1.4, x = 540, y, size = 128, color = C.navy, prefix = 'Rp', showFrom = false, strikeColor = C.red}) => {
  const p = prog(t, at, dur, easeInOut);
  const settle = prog(t, at + dur, 0.35, easeBack);
  const strike = prog(t, at, 0.35);
  const value = lerp(from, to, p);
  return (
    <div style={{position: 'absolute', left: x - 500, top: y, width: 1000, textAlign: 'center', color, fontFamily: FONT, fontWeight: 800}}>
      {showFrom && (
        <div style={{display: 'inline-block', position: 'relative', fontSize: size * 0.42, opacity: 0.55, marginBottom: size * 0.08}}>
          {formatRupiah(from, prefix)}
          <div style={{position: 'absolute', left: -6, right: -6, top: '52%', height: Math.max(4, size * 0.05), borderRadius: 4,
            background: strikeColor, transform: `scaleX(${strike})`, transformOrigin: '0 50%'}} />
        </div>
      )}
      <div style={{fontSize: size, lineHeight: 1.05, fontVariantNumeric: 'tabular-nums',
        transform: `scale(${p >= 1 ? 1 + 0.06 * (1 - settle) * Math.sin(settle * Math.PI) : 1})`}}>
        {formatRupiah(value, prefix)}
      </div>
    </div>
  );
};

const NOTE_COLORS: Record<number, [string, string]> = {
  100000: ['#E8607A', '#B8324F'], 50000: ['#4C8FD6', '#245A9E'], 20000: ['#5DBB82', '#2E7D52'], 10000: ['#9B79D1', '#5E4296'],
  5000: ['#C49A6C', '#8A6239'], 2000: ['#9AA3B2', '#5E6779'], 1000: ['#8FCB6B', '#4F8A35'],
};

/**
 * A stylised, clearly non-real banknote (no portraits, emblems or security features). `x`/`y` = centre.
 * Colour follows the denomination unless `color` is given.
 */
export const Banknote: React.FC<{
  t: number; value: number; x: number; y: number; width?: number; rotate?: number; enterAt?: number; color?: string; label?: string;
}> = ({t, value, x, y, width = 560, rotate = 0, enterAt = 0, color, label = 'RUPIAH'}) => {
  const [c1, c2] = color ? [color, color] : (NOTE_COLORS[value] ?? ['#7FA7C9', '#3F6A8F']);
  const p = prog(t, enterAt, 0.55, easeBack);
  const h = width * 0.48;
  const id = React.useId().replace(/:/g, '');
  const num = formatNumber(value);
  return (
    <div style={{position: 'absolute', left: x - width / 2, top: y - h / 2, width, height: h, opacity: Math.min(1, p * 1.3),
      transform: `rotate(${rotate}deg) scale(${0.7 + 0.3 * p})`, filter: 'drop-shadow(0 18px 26px #0d123340)'}}>
      <svg width={width} height={h} viewBox="0 0 600 288">
        <defs>
          <linearGradient id={`g${id}`} x1="0" y1="0" x2="1" y2="1"><stop offset="0" stopColor={c1} /><stop offset="1" stopColor={c2} /></linearGradient>
        </defs>
        <rect x="0" y="0" width="600" height="288" rx="18" fill={`url(#g${id})`} />
        <rect x="14" y="14" width="572" height="260" rx="10" fill="none" stroke="#ffffff66" strokeWidth="3" />
        {Array.from({length: 9}, (_, i) => (
          <ellipse key={i} cx="150" cy="144" rx={40 + i * 13} ry={30 + i * 10} fill="none" stroke="#ffffff2e" strokeWidth="2" />
        ))}
        {Array.from({length: 14}, (_, i) => (
          <path key={`w${i}`} d={`M300 ${70 + i * 11} q 40 -14 80 0 t 80 0 t 80 0`} fill="none" stroke="#ffffff22" strokeWidth="2" />
        ))}
        <circle cx="150" cy="144" r="52" fill="#ffffff26" />
        <text x="566" y="70" textAnchor="end" fontFamily="'Mograph Sans','Mograph Fallback',sans-serif" fontWeight="800" fontSize="54" fill="white">{num}</text>
        <text x="566" y="250" textAnchor="end" fontFamily="'Mograph Text','Mograph Fallback',sans-serif" fontSize="26" letterSpacing="6" fill="#ffffffcc">{label}</text>
        <text x="40" y="250" fontFamily="'Mograph Sans','Mograph Fallback',sans-serif" fontWeight="800" fontSize="34" fill="#ffffffaa">{num}</text>
      </svg>
    </div>
  );
};

/** A row of small label + value pills, e.g. prices before/after. `x` = centre, `y` = top. */
export const PriceTag: React.FC<{t: number; at?: number; x: number; y: number; label: string; value: string; color?: string; size?: number}> =
  ({t, at = 0, x, y, label, value, color = C.yellow, size = 44}) => {
    const p = prog(t, at, 0.45, easeBack);
    return (
      <div style={{position: 'absolute', left: x, top: y, transform: `translateX(-50%) scale(${0.6 + 0.4 * p})`, opacity: Math.min(1, p * 1.4),
        display: 'flex', alignItems: 'stretch', borderRadius: 18, overflow: 'hidden', boxShadow: '0 14px 28px #0d123333'}}>
        <div style={{background: C.navy, color: C.cream, fontFamily: TEXT, fontSize: size * 0.62, padding: `${size * 0.28}px ${size * 0.4}px`, display: 'flex', alignItems: 'center'}}>{label}</div>
        <div style={{background: color, color: C.navy, fontFamily: FONT, fontWeight: 800, fontSize: size, padding: `${size * 0.16}px ${size * 0.45}px`}}>{value}</div>
      </div>
    );
  };
