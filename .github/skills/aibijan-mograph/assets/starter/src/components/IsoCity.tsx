import React from 'react';
import {C, mix, shade, rand} from '../theme';

// Original isometric miniature neighbourhood. One reusable diorama whose
// atmosphere (clear → haze → orange) is driven by props, so the same city can
// be revisited across the episode and visibly *change*.

const N = 8;
const TW = 118;
const TH = TW * 0.5774;
const OX = 540;
const OY = 770;
const SLAB = 46;

type Pt = [number, number];
const P = (gx: number, gy: number, z = 0): Pt => [OX + ((gx - gy) * TW) / 2, OY + ((gx + gy) * TH) / 2 - z];
const poly = (pts: Pt[]) => pts.map((p) => `${p[0].toFixed(1)},${p[1].toFixed(1)}`).join(' ');

type Kind = 'tower' | 'house' | 'shop' | 'tree';
type Obj = {gx: number; gy: number; kind: Kind; h?: number; color?: string};

const LAYOUT: Obj[] = [
  {gx: 0, gy: 0, kind: 'tower', h: 300, color: '#86AEDF'},
  {gx: 1, gy: 0, kind: 'tower', h: 390, color: '#F3D7A6'},
  {gx: 2, gy: 0, kind: 'tower', h: 260, color: '#EC9374'},
  {gx: 0, gy: 1, kind: 'tower', h: 250, color: '#F6E7C8'},
  {gx: 1, gy: 1, kind: 'tree'},
  {gx: 2, gy: 1, kind: 'tower', h: 190, color: '#A3C8EC'},
  {gx: 0, gy: 2, kind: 'tower', h: 170, color: '#EAB97C'},
  {gx: 1, gy: 2, kind: 'tower', h: 225, color: '#CADCF2'},
  {gx: 2, gy: 2, kind: 'tree'},
  {gx: 4, gy: 0, kind: 'tower', h: 340, color: '#FFD27A'},
  {gx: 5, gy: 0, kind: 'tower', h: 220, color: '#92BCE6'},
  {gx: 6, gy: 0, kind: 'tower', h: 285, color: '#F2A383'},
  {gx: 7, gy: 0, kind: 'tower', h: 160, color: '#E9DFC8'},
  {gx: 4, gy: 1, kind: 'tree'},
  {gx: 5, gy: 1, kind: 'tower', h: 150, color: '#F7E2B5'},
  {gx: 6, gy: 1, kind: 'tower', h: 200, color: '#ADCDEB'},
  {gx: 7, gy: 1, kind: 'tree'},
  {gx: 4, gy: 2, kind: 'house', color: '#F28A6B'},
  {gx: 5, gy: 2, kind: 'tower', h: 130, color: '#A2D3B5'},
  {gx: 6, gy: 2, kind: 'tree'},
  {gx: 7, gy: 2, kind: 'tower', h: 120, color: '#F4C894'},
  {gx: 0, gy: 3, kind: 'house', color: '#F6C27A'},
  {gx: 1, gy: 3, kind: 'tree'},
  {gx: 2, gy: 3, kind: 'house', color: '#E77D63'},
  {gx: 4, gy: 3, kind: 'shop', h: 92, color: '#FFE1A6'},
  {gx: 5, gy: 3, kind: 'tree'},
  {gx: 6, gy: 3, kind: 'house', color: '#8DC5E8'},
  {gx: 7, gy: 3, kind: 'tower', h: 104, color: '#F6E2BC'},
  {gx: 0, gy: 4, kind: 'tree'},
  {gx: 1, gy: 4, kind: 'shop', h: 82, color: '#F0B48A'},
  {gx: 2, gy: 4, kind: 'tree'},
  {gx: 4, gy: 4, kind: 'house', color: '#F9D38B'},
  {gx: 5, gy: 4, kind: 'tower', h: 112, color: '#BFD9F3'},
  {gx: 6, gy: 4, kind: 'tree'},
  {gx: 7, gy: 4, kind: 'house', color: '#EE8E73'},
  {gx: 0, gy: 6, kind: 'tree'},
  {gx: 1, gy: 6, kind: 'house', color: '#9ED0B2'},
  {gx: 2, gy: 6, kind: 'shop', h: 78, color: '#F7C9A0'},
  {gx: 4, gy: 6, kind: 'tree'},
  {gx: 5, gy: 6, kind: 'house', color: '#F5B97A'},
  {gx: 6, gy: 6, kind: 'tree'},
  {gx: 7, gy: 6, kind: 'shop', h: 80, color: '#C9DDF2'},
  {gx: 0, gy: 7, kind: 'tree'},
  {gx: 1, gy: 7, kind: 'tree'},
  {gx: 2, gy: 7, kind: 'house', color: '#F09A7A'},
  {gx: 4, gy: 7, kind: 'tree'},
  {gx: 5, gy: 7, kind: 'tree'},
  {gx: 6, gy: 7, kind: 'house', color: '#FAD48E'},
  {gx: 7, gy: 7, kind: 'tree'},
];

const isRoad = (gx: number, gy: number) => gx === 3 || gy === 5;

export type CityProps = {
  t: number; // absolute seconds (drives traffic + drift)
  haze: number; // 0 clear → 1 thick smoke
  orange?: number; // 0 → 1 orange/red haze sky
  lights?: number; // window lights 0..1
  sun?: number; // sun visibility 0..1
  sunScale?: number;
  smokeDrift?: number; // foreground smoke puffs 0..1
  camScale?: number;
  camX?: number;
  camY?: number;
  children?: React.ReactNode; // extra overlays drawn in city space (above city)
  skyChildren?: React.ReactNode; // drawn in the sky, behind the city
};

export const SUN = {x: 780, y: 400, r: 96};

export const IsoCity: React.FC<CityProps> = ({
  t,
  haze,
  orange = 0,
  lights = 0,
  sun = 1,
  sunScale = 1,
  smokeDrift = 0,
  camScale = 1,
  camX = 0,
  camY = 0,
  children,
  skyChildren,
}) => {
  const hazeCol = mix('#B9AA95', '#E0773A', orange);
  const fogFor = (depth: number) => Math.min(0.92, (haze * (1 - depth * 0.5) * 0.85 + orange * 0.35 * (1 - depth * 0.4)));
  const tint = (c: string, depth: number) => mix(c, hazeCol, fogFor(depth));

  // Sky gradient stops
  const skyTop = mix(mix('#5FAEEF', '#7D7468', haze), '#9C3415', orange);
  const skyMid = mix(mix('#A9D8F7', '#A89A86', haze), '#E26A2C', orange);
  const skyLow = mix(mix('#E4F4FD', '#CDBEA8', haze), '#F6A651', orange);
  const tableTop = mix(mix('#BFE0C5', '#A69D8C', haze), '#B8693D', orange);
  const tableLow = mix(mix('#8FC4A0', '#7E7568', haze), '#7A3A22', orange);

  const items: {key: number; el: React.ReactNode}[] = [];

  // --- buildings / trees
  LAYOUT.forEach((o, i) => {
    const depth = (o.gx + o.gy + 1) / (2 * N);
    const ins = o.kind === 'tree' ? 0.25 : 0.12;
    const x0 = o.gx + ins;
    const y0 = o.gy + ins;
    const x1 = o.gx + 1 - ins;
    const y1 = o.gy + 1 - ins;
    const key = o.gx + o.gy + 1;
    if (o.kind === 'tree') {
      const [cx, cy] = P(o.gx + 0.5, o.gy + 0.5, 0);
      const sway = Math.sin(t * 1.3 + i) * 2;
      const g = tint('#5DB37A', depth);
      items.push({
        key,
        el: (
          <g key={`o${i}`}>
            <ellipse cx={cx} cy={cy + 4} rx={30} ry={14} fill="#000" opacity={0.14} />
            <rect x={cx - 5} y={cy - 36} width={10} height={36} fill={tint('#8A5A3C', depth)} />
            <circle cx={cx + sway} cy={cy - 58} r={30} fill={g} />
            <circle cx={cx + sway - 9} cy={cy - 67} r={15} fill={shade(g, 0.18)} />
            <path d={`M${cx + sway - 30} ${cy - 56} a30 30 0 0 0 60 0`} fill={shade(g, -0.16)} />
          </g>
        ),
      });
      return;
    }
    const base = tint(o.color!, depth);
    const h = o.kind === 'house' ? 62 : o.h!;
    const top = shade(base, 0.22);
    const right = base;
    const left = shade(base, -0.2);
    const els: React.ReactNode[] = [];
    // ground shadow (towards lower-left, sun upper-right)
    els.push(
      <polygon
        key="sh"
        points={poly([P(x0, y1, 0), P(x1, y1, 0), P(x1 - 0.1, y1 + Math.min(0.9, h / 260), 0), P(x0 - 0.1, y1 + Math.min(0.9, h / 260), 0)])}
        fill="#000"
        opacity={0.1 * (1 - haze * 0.6)}
      />,
    );
    els.push(<polygon key="l" points={poly([P(x0, y1, 0), P(x1, y1, 0), P(x1, y1, h), P(x0, y1, h)])} fill={left} />);
    els.push(<polygon key="r" points={poly([P(x1, y0, 0), P(x1, y1, 0), P(x1, y1, h), P(x1, y0, h)])} fill={right} />);

    // windows
    const lit = mix(tint('#3E5677', depth), '#FFD978', lights);
    const glass = tint('#DDEFFC', depth);
    const rows = o.kind === 'house' ? 1 : Math.max(1, Math.floor((h - 30) / 34));
    const cols = 3;
    for (let r = 0; r < rows; r++) {
      const z0 = 22 + r * 34;
      const z1 = z0 + 18;
      if (z1 > h - 10) break;
      for (let c = 0; c < cols; c++) {
        const u0 = 0.14 + c * 0.27;
        const u1 = u0 + 0.16;
        const on = rand(i * 31 + r * 7 + c) < 0.62;
        const colL = on ? mix(glass, lit, lights > 0 ? 1 : 0) : mix(glass, '#2B3A55', lights * 0.6);
        const colR = on ? mix(shade(glass, 0.1), lit, lights > 0 ? 1 : 0) : mix(glass, '#2B3A55', lights * 0.6);
        const xa = x0 + (x1 - x0) * u0;
        const xb = x0 + (x1 - x0) * u1;
        els.push(<polygon key={`wl${r}${c}`} points={poly([P(xa, y1, z0), P(xb, y1, z0), P(xb, y1, z1), P(xa, y1, z1)])} fill={colL} opacity={0.92} />);
        const ya = y0 + (y1 - y0) * u0;
        const yb = y0 + (y1 - y0) * u1;
        els.push(<polygon key={`wr${r}${c}`} points={poly([P(x1, ya, z0), P(x1, yb, z0), P(x1, yb, z1), P(x1, ya, z1)])} fill={colR} opacity={0.92} />);
      }
    }
    if (o.kind === 'shop') {
      // striped awning on the street-facing (left) face
      for (let k = 0; k < 6; k++) {
        const a = x0 + ((x1 - x0) * k) / 6;
        const b = x0 + ((x1 - x0) * (k + 1)) / 6;
        els.push(
          <polygon
            key={`aw${k}`}
            points={poly([P(a, y1, h - 14), P(b, y1, h - 14), P(b, y1 + 0.14, h - 34), P(a, y1 + 0.14, h - 34)])}
            fill={tint(k % 2 ? '#FFFFFF' : '#E5484D', depth)}
          />,
        );
      }
    }
    if (o.kind === 'house') {
      const ym = (y0 + y1) / 2;
      const zr = h + 46;
      const roof = tint(o.color === '#8DC5E8' ? '#3F79B8' : '#C4502A', depth);
      els.push(<polygon key="rb" points={poly([P(x0, y0, h), P(x1, y0, h), P(x1, ym, zr), P(x0, ym, zr)])} fill={shade(roof, 0.15)} />);
      els.push(<polygon key="gb" points={poly([P(x1, y0, h), P(x1, y1, h), P(x1, ym, zr)])} fill={right} />);
      els.push(<polygon key="rf" points={poly([P(x0 - 0.04, ym, zr), P(x1 + 0.04, ym, zr), P(x1 + 0.04, y1 + 0.08, h - 6), P(x0 - 0.04, y1 + 0.08, h - 6)])} fill={roof} />);
      // door
      els.push(<polygon key="door" points={poly([P(x0 + 0.38, y1, 0), P(x0 + 0.56, y1, 0), P(x0 + 0.56, y1, 34), P(x0 + 0.38, y1, 34)])} fill={tint('#6B4A3A', depth)} />);
    } else {
      els.push(<polygon key="t" points={poly([P(x0, y0, h), P(x1, y0, h), P(x1, y1, h), P(x0, y1, h)])} fill={top} />);
      if (o.kind === 'tower' && h > 200) {
        // rooftop unit
        els.push(<polygon key="ru" points={poly([P(x0 + 0.2, y0 + 0.2, h + 18), P(x0 + 0.45, y0 + 0.2, h + 18), P(x0 + 0.45, y0 + 0.45, h + 18), P(x0 + 0.2, y0 + 0.45, h + 18)])} fill={shade(top, 0.1)} />);
        els.push(<polygon key="rul" points={poly([P(x0 + 0.2, y0 + 0.45, h), P(x0 + 0.45, y0 + 0.45, h), P(x0 + 0.45, y0 + 0.45, h + 18), P(x0 + 0.2, y0 + 0.45, h + 18)])} fill={shade(left, -0.05)} />);
        els.push(<polygon key="rur" points={poly([P(x0 + 0.45, y0 + 0.2, h), P(x0 + 0.45, y0 + 0.45, h), P(x0 + 0.45, y0 + 0.45, h + 18), P(x0 + 0.45, y0 + 0.2, h + 18)])} fill={shade(right, -0.05)} />);
      }
    }
    items.push({key, el: <g key={`o${i}`}>{els}</g>});
  });

  // --- traffic
  const cars = [
    {lane: 'v', off: 0.2, speed: 0.95, pos: 3.3, color: '#E5484D', dir: 1},
    {lane: 'v', off: 4.8, speed: 0.95, pos: 3.3, color: '#FFFFFF', dir: 1},
    {lane: 'v', off: 2.0, speed: 0.8, pos: 3.68, color: '#398CE6', dir: -1},
    {lane: 'h', off: 1.0, speed: 0.9, pos: 5.3, color: '#FFC83D', dir: 1},
    {lane: 'h', off: 5.5, speed: 0.75, pos: 5.68, color: '#3FB37F', dir: -1},
  ];
  cars.forEach((c, i) => {
    const s = (((t * c.speed + c.off) % 9) + 9) % 9;
    const along = c.dir > 0 ? s - 0.5 : 8.5 - s;
    if (along < 0.05 || along > 7.6) return;
    const vertical = c.lane === 'v';
    const cx = vertical ? c.pos : along;
    const cy = vertical ? along : c.pos;
    const hw = vertical ? 0.13 : 0.24;
    const hd = vertical ? 0.24 : 0.13;
    const x0 = cx - hw, x1 = cx + hw, y0 = cy - hd, y1 = cy + hd;
    const depth = (cx + cy) / (2 * N);
    const body = tint(c.color, depth);
    const hgt = 16;
    items.push({
      key: cx + cy + 0.3,
      el: (
        <g key={`car${i}`}>
          <polygon points={poly([P(x0, y1, 0), P(x1, y1, 0), P(x1, y1, hgt), P(x0, y1, hgt)])} fill={shade(body, -0.22)} />
          <polygon points={poly([P(x1, y0, 0), P(x1, y1, 0), P(x1, y1, hgt), P(x1, y0, hgt)])} fill={body} />
          <polygon points={poly([P(x0, y0, hgt), P(x1, y0, hgt), P(x1, y1, hgt), P(x0, y1, hgt)])} fill={shade(body, 0.2)} />
          <polygon
            points={poly([P(x0 + 0.04, y0 + 0.05, hgt + 9), P(x1 - 0.04, y0 + 0.05, hgt + 9), P(x1 - 0.04, y1 - 0.05, hgt + 9), P(x0 + 0.04, y1 - 0.05, hgt + 9)])}
            fill={tint('#2E3B57', depth)}
          />
          {lights > 0.2 && (
            <circle cx={P(vertical ? cx : x1, vertical ? (c.dir > 0 ? y1 : y0) : cy, 8)[0]} cy={P(vertical ? cx : x1, vertical ? (c.dir > 0 ? y1 : y0) : cy, 8)[1]} r={7} fill="#FFE9A8" opacity={lights * 0.9} />
          )}
        </g>
      ),
    });
  });

  items.sort((a, b) => a.key - b.key);

  // --- ground tiles
  const tiles: React.ReactNode[] = [];
  for (let gx = 0; gx < N; gx++) {
    for (let gy = 0; gy < N; gy++) {
      const depth = (gx + gy + 1) / (2 * N);
      const road = isRoad(gx, gy);
      const obj = LAYOUT.find((o) => o.gx === gx && o.gy === gy);
      const col = road ? '#5D6478' : obj?.kind === 'tree' ? '#A9D9A4' : '#EEE6D3';
      tiles.push(<polygon key={`g${gx}-${gy}`} points={poly([P(gx, gy), P(gx + 1, gy), P(gx + 1, gy + 1), P(gx, gy + 1)])} fill={tint(col, depth)} stroke={tint(road ? '#4E5466' : '#D9D0BC', depth)} strokeWidth={1.2} />);
      if (road && gx === 3 && gy !== 5) {
        tiles.push(<polygon key={`d${gx}-${gy}`} points={poly([P(3.47, gy + 0.2), P(3.53, gy + 0.2), P(3.53, gy + 0.62), P(3.47, gy + 0.62)])} fill={tint('#F8F1DD', depth)} />);
      }
      if (road && gy === 5 && gx !== 3) {
        tiles.push(<polygon key={`e${gx}-${gy}`} points={poly([P(gx + 0.2, 5.47), P(gx + 0.62, 5.47), P(gx + 0.62, 5.53), P(gx + 0.2, 5.53)])} fill={tint('#F8F1DD', depth)} />);
      }
    }
  }

  // smoke puffs: far band (behind city) + near band (in front)
  const puffs = (count: number, seed: number, yBase: number, spread: number, rBase: number, op: number) =>
    Array.from({length: count}, (_, i) => {
      const sp = 10 + rand(seed + i) * 18;
      const x = ((rand(seed + i * 3) * 1500 + t * sp) % 1600) - 260;
      const y = yBase + (rand(seed + i * 5) - 0.5) * spread + Math.sin(t * 0.6 + i) * 8;
      const r = rBase * (0.7 + rand(seed + i * 7) * 0.7);
      return <circle key={`${seed}-${i}`} cx={x} cy={y} r={r} fill={`url(#puff)`} opacity={op} />;
    });

  const sunR = SUN.r * sunScale;
  const sunCol = mix(mix('#FFE07A', '#F3E2C2', haze * 0.7), '#FF6A2A', orange);

  return (
    <svg width={1080} height={1920} viewBox="0 0 1080 1920" style={{position: 'absolute', inset: 0}}>
      <defs>
        <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0" stopColor={skyTop} />
          <stop offset="0.45" stopColor={skyMid} />
          <stop offset="0.62" stopColor={skyLow} />
          <stop offset="0.66" stopColor={tableTop} />
          <stop offset="1" stopColor={tableLow} />
        </linearGradient>
        <radialGradient id="sunGlow">
          <stop offset="0" stopColor={sunCol} stopOpacity={0.9} />
          <stop offset="0.35" stopColor={sunCol} stopOpacity={0.35} />
          <stop offset="1" stopColor={sunCol} stopOpacity={0} />
        </radialGradient>
        <radialGradient id="puff">
          <stop offset="0" stopColor={mix('#CFC2AE', '#E58A4A', orange)} stopOpacity={0.95} />
          <stop offset="0.55" stopColor={mix('#B4A691', '#D2703A', orange)} stopOpacity={0.55} />
          <stop offset="1" stopColor={mix('#B4A691', '#D2703A', orange)} stopOpacity={0} />
        </radialGradient>
      </defs>
      <rect width={1080} height={1920} fill="url(#sky)" />
      <g transform={`translate(${540 + camX} ${960 + camY}) scale(${camScale}) translate(-540 -960)`}>
        {/* sun */}
        <g opacity={sun}>
          <circle cx={SUN.x} cy={SUN.y} r={sunR * 3.2} fill="url(#sunGlow)" />
          {Array.from({length: 12}, (_, i) => {
            const a = (i / 12) * Math.PI * 2 + t * 0.15;
            return (
              <line
                key={i}
                x1={SUN.x + Math.cos(a) * sunR * 1.25}
                y1={SUN.y + Math.sin(a) * sunR * 1.25}
                x2={SUN.x + Math.cos(a) * sunR * 1.55}
                y2={SUN.y + Math.sin(a) * sunR * 1.55}
                stroke={sunCol}
                strokeWidth={10}
                strokeLinecap="round"
                opacity={0.8 * (1 - haze)}
              />
            );
          })}
          <circle cx={SUN.x} cy={SUN.y} r={sunR} fill={sunCol} />
        </g>
        {skyChildren}
        {/* far haze band */}
        <g opacity={Math.min(1, haze * 1.2 + orange * 0.6)}>{puffs(9, 11, 560, 260, 210, 0.75)}</g>
        {/* soft shadow under diorama */}
        <ellipse cx={540} cy={1340} rx={520} ry={70} fill="#000" opacity={0.16} />
        {/* slab sides */}
        <polygon points={poly([P(0, N, 0), P(N, N, 0), P(N, N, -SLAB), P(0, N, -SLAB)])} fill={tint('#8C6A4F', 1)} />
        <polygon points={poly([P(N, 0, 0), P(N, N, 0), P(N, N, -SLAB), P(N, 0, -SLAB)])} fill={tint('#A88262', 1)} />
        <polygon points={poly([P(0, N, -12), P(N, N, -12), P(N, N, -16), P(0, N, -16)])} fill={tint('#6F513B', 1)} opacity={0.6} />
        {tiles}
        {items.map((i) => i.el)}
        {children}
        {/* near smoke drifting over the city */}
        <g opacity={smokeDrift}>{puffs(10, 77, 900, 700, 190, 0.8)}</g>
      </g>
      {/* global veil */}
      <rect width={1080} height={1920} fill={hazeCol} opacity={haze * 0.22 + orange * 0.12} />
    </svg>
  );
};

/** City-space anchor helper for overlays. */
export const cityPoint = P;
export {C};
