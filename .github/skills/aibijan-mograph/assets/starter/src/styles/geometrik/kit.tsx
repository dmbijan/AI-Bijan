import React from 'react';
import {Easing} from 'remotion';
import {CaptionPage} from '../../components/WordCaptions';
import {display, p, pageAt} from '../shared';

// GEOMETRIK style kit — Swiss/Bauhaus data editorial (grid page, primary blocks, giant condensed numbers).
export const GEOMETRIK = {paper: '#F2EEE4', ink: '#121212', red: '#E5402E', blue: '#2149A6', yellow: '#F6C21C', grey: '#D9D3C6'};
export const G = GEOMETRIK;
export const HEAD = "'Geo Head', sans-serif";
export const TEXT = "'Geo Text', sans-serif";
export const SNAP = Easing.bezier(0.7, 0, 0.2, 1);
export const ps = (t: number, a0: number, b0: number) => p(t, a0, b0, SNAP);


export const Big: React.FC<{children: React.ReactNode; size: number; color?: string; stretch?: number; weight?: number; style?: React.CSSProperties}> = ({children, size, color = G.ink, stretch = 100, weight = 900, style}) => (
  <div style={{fontFamily: HEAD, fontWeight: weight, fontStretch: `${stretch}%`, fontSize: size, lineHeight: 0.84, letterSpacing: -size * 0.02, color, ...style}}>{children}</div>
);

export const Caption: React.FC<{t: number; pages: CaptionPage[]; top?: number}> = ({t, pages, top = 1500}) => {
  const pg = pageAt(t, pages);
  if (!pg) return null;
  const pop = ps(t, pg.start - 0.08, pg.start + 0.1);
  return (
    <div style={{position: 'absolute', left: 72, right: 72, top, display: 'flex', justifyContent: 'center'}}>
      <div style={{background: G.ink, padding: '16px 26px', transform: `scaleX(${0.6 + 0.4 * pop})`, opacity: pop, fontFamily: TEXT, fontWeight: 800, fontSize: 52, lineHeight: 1.2, color: '#fff', textAlign: 'center', maxWidth: 936}}>
        {pg.words.map((w, i) => {
          const on = t >= w.start - 0.04 && t < w.end + 0.1;
          return (
            <React.Fragment key={i}>
              <span style={{background: on ? G.yellow : 'transparent', color: on ? G.ink : t >= w.start - 0.04 ? '#fff' : '#ffffff70', padding: '0 6px'}}>{display(w.word)}</span>
              {i < pg.words.length - 1 ? ' ' : ''}
            </React.Fragment>
          );
        })}
      </div>
    </div>
  );
};


/** Off-white page with the 90 px grid. */
export const GridPage: React.FC<{color?: string}> = ({color = GEOMETRIK.paper}) => (
  <div style={{position: 'absolute', inset: 0, background: color}}>
    <div style={{position: 'absolute', inset: 0, backgroundImage: `linear-gradient(90deg, ${GEOMETRIK.ink}10 1px, transparent 1px), linear-gradient(0deg, ${GEOMETRIK.ink}10 1px, transparent 1px)`, backgroundSize: '90px 90px', backgroundPosition: '72px 0, 0 60px'}} />
  </div>
);

// ---------- layout helpers (from the GEOMETRIK demo) ----------
export const label = (size = 30, color: string = G.ink): React.CSSProperties => ({fontFamily: TEXT, fontWeight: 800, fontSize: size, letterSpacing: size * 0.12, color});
export const Abs: React.FC<{x: number; y: number; children: React.ReactNode; style?: React.CSSProperties; o?: number}> = ({x, y, children, style, o = 1}) =>
  o > 0 ? <div style={{position: 'absolute', left: x, top: y, opacity: o, ...style}}>{children}</div> : null;
export const Block: React.FC<{x: number; y: number; w: number; h: number; color: string; q: number; children?: React.ReactNode; round?: boolean; from?: 'left' | 'up' | 'scale'}> = ({x, y, w, h, color, q, children, round, from = 'scale'}) => {
  if (q <= 0) return null;
  const tf = from === 'scale' ? `scale(${q})` : from === 'left' ? `translateX(${(1 - q) * -400}px)` : `translateY(${(1 - q) * 300}px)`;
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, height: h, background: color, borderRadius: round ? '50%' : 0, transform: tf, opacity: Math.min(1, q * 3),
      display: 'flex', alignItems: 'center', justifyContent: 'center', flexDirection: 'column'}}>{children}</div>
  );
};

