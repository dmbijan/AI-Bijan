import React from 'react';
import {C, FONT} from '../../theme';
import {CITIES, INDONESIA_PATH, MAP_VIEW, NEIGHBOR_PATHS, project} from './indonesiaMapData';
import {easeBack, easeInOut, prog} from './util';

export type MapPin = {
  /** a key of CITIES (e.g. "Jakarta", "Makassar") or give lonLat */
  city?: string; lonLat?: [number, number]; label?: string; at?: number; color?: string;
  labelSide?: 'right' | 'left' | 'top' | 'bottom';
};

const resolve = (pin: MapPin): [number, number] => {
  const ll = pin.lonLat ?? (pin.city ? CITIES[pin.city] : undefined);
  if (!ll) throw new Error(`Unknown city "${pin.city}" - add it to CITIES or pass lonLat`);
  return project(ll[0], ll[1]);
};

/**
 * Indonesia map (Natural Earth, public domain) that reveals west → east, with animated city pins and an
 * optional route line. `x` = centre, `y` = top; height = width × 0.382.
 */
export const IndonesiaMap: React.FC<{
  t: number; x?: number; y: number; width?: number; revealAt?: number; revealDur?: number; fill?: string; stroke?: string;
  showNeighbors?: boolean; neighborFill?: string; pins?: MapPin[]; route?: {cities?: string[]; lonLat?: Array<[number, number]>; at: number; dur?: number; color?: string};
  labelSize?: number; dark?: boolean;
}> = ({t, x = 540, y, width = 1000, revealAt = 0, revealDur = 1.2, fill = C.green, stroke = '#ffffff', showNeighbors = true,
  neighborFill, pins = [], route, labelSize = 30, dark = false}) => {
  const id = React.useId().replace(/:/g, '');
  const s = width / MAP_VIEW.width;
  const H = MAP_VIEW.height * s;
  const reveal = prog(t, revealAt, revealDur, easeInOut);
  const nFill = neighborFill ?? (dark ? '#ffffff1f' : '#0d12331a');
  const routePts = route ? (route.lonLat ?? []).map(([lo, la]) => project(lo, la)).concat((route.cities ?? []).map((c) => resolve({city: c}))) : [];
  const routeLen = routePts.slice(1).reduce((acc, p, i) => acc + Math.hypot(p[0] - routePts[i][0], p[1] - routePts[i][1]), 0);
  const routeP = route ? prog(t, route.at, route.dur ?? 1.4, easeInOut) : 0;
  return (
    <div style={{position: 'absolute', left: x - width / 2, top: y, width, height: H}}>
      <svg width={width} height={H} viewBox={`0 0 ${MAP_VIEW.width} ${MAP_VIEW.height}`} style={{overflow: 'visible'}}>
        <defs>
          <clipPath id={`r${id}`}><rect x={-20} y={-40} width={(MAP_VIEW.width + 40) * reveal} height={MAP_VIEW.height + 80} /></clipPath>
          <clipPath id={`v${id}`}><rect x={0} y={0} width={MAP_VIEW.width} height={MAP_VIEW.height} /></clipPath>
        </defs>
        {showNeighbors && <g clipPath={`url(#v${id})`}>{Object.values(NEIGHBOR_PATHS).map((d, i) => <path key={i} d={d} fill={nFill} opacity={reveal} />)}</g>}
        <g clipPath={`url(#r${id})`}>
          <path d={INDONESIA_PATH} fill={fill} stroke={stroke} strokeWidth={1.2} strokeLinejoin="round" />
        </g>
        {routePts.length > 1 && (
          <polyline points={routePts.map((p) => p.join(',')).join(' ')} fill="none" stroke={route?.color ?? C.coral} strokeWidth={4}
            strokeLinecap="round" strokeLinejoin="round" strokeDasharray={`${routeLen}`} strokeDashoffset={routeLen * (1 - routeP)} />
        )}
        {pins.map((pin, i) => {
          const [px, py] = resolve(pin);
          const p = prog(t, pin.at ?? revealAt + revealDur, 0.45, easeBack);
          if (p <= 0) return null;
          const pulse = ((t - (pin.at ?? 0)) * 0.9) % 1;
          return (
            <g key={i} transform={`translate(${px} ${py})`}>
              <circle r={8 + 18 * pulse} fill="none" stroke={pin.color ?? C.coral} strokeWidth={2.5} opacity={(1 - pulse) * 0.8 * p} />
              <circle r={8 * p} fill={pin.color ?? C.coral} stroke="white" strokeWidth={3} />
            </g>
          );
        })}
      </svg>
      {pins.map((pin, i) => {
        if (!pin.label && !pin.city) return null;
        const [px, py] = resolve(pin);
        const p = prog(t, (pin.at ?? revealAt + revealDur) + 0.1, 0.4, easeBack);
        if (p <= 0) return null;
        const side = pin.labelSide ?? 'right';
        const off = 22;
        const pos: React.CSSProperties =
          side === 'right' ? {left: px * s + off, top: py * s, transform: `translateY(-50%) scale(${p})`, transformOrigin: '0 50%'} :
          side === 'left' ? {right: width - px * s + off, top: py * s, transform: `translateY(-50%) scale(${p})`, transformOrigin: '100% 50%'} :
          side === 'top' ? {left: px * s, bottom: H - py * s + off, transform: `translateX(-50%) scale(${p})`, transformOrigin: '50% 100%'} :
          {left: px * s, top: py * s + off, transform: `translateX(-50%) scale(${p})`, transformOrigin: '50% 0'};
        return (
          <div key={i} style={{position: 'absolute', ...pos, whiteSpace: 'nowrap', fontFamily: FONT, fontWeight: 800, fontSize: labelSize,
            color: C.navy, background: C.paper, padding: `${labelSize * 0.2}px ${labelSize * 0.45}px`, borderRadius: 999, boxShadow: '0 8px 18px #0d123333'}}>
            {pin.label ?? pin.city}
          </div>
        );
      })}
    </div>
  );
};

export {CITIES};
