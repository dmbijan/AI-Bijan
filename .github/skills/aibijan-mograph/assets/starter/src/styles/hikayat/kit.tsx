import React from 'react';
import {CaptionPage} from '../../components/WordCaptions';
import {display, noise, p, pageAt, rnd} from '../shared';

// HIKAYAT style kit — history & legend as a shadow-puppet play (kelir, carved perforated silhouettes, gunungan, lontar).
export const HIKAYAT = {
  glow: '#F7C66C', glow2: '#E08A36', edge: '#5E240C', dark: '#1B0D06', leaf: '#D9B678', leafDark: '#B98E4E',
  ink: '#3A2211', ember: '#C2410F', stage: '#2A160A',
};
export const H = HIKAYAT;
export const HEAD = "'Hik Head', serif";
export const TEXT = "'Hik Text', serif";
export const CLOTH = noise(0.55, 3, 21, 512);
export const FIBER = noise(0.02, 2, 5, 512);


export type Pt = [number, number];
export const polyD = (pts: Pt[]) => `M ${pts.map(([x, y]) => `${x.toFixed(1)} ${y.toFixed(1)}`).join(' L ')} Z`;
export const inside = ([x, y]: Pt, poly: Pt[]) => {
  let c = false;
  for (let i = 0, j = poly.length - 1; i < poly.length; j = i++) {
    const [xi, yi] = poly[i], [xj, yj] = poly[j];
    if (yi > y !== yj > y && x < ((xj - xi) * (y - yi)) / (yj - yi) + xi) c = !c;
  }
  return c;
};
/** Perforation points (tatahan) inside a polygon, keeping a solid rim of `rim` px. */
export const holes = (poly: Pt[], step: number, rim: number, seed: number): Pt[] => {
  const xs = poly.map((q) => q[0]), ys = poly.map((q) => q[1]);
  const out: Pt[] = [];
  // lattice anchored at 0,0 (not at the shape's bounding box) so a morphing or moving shape keeps its holes still
  for (let y = Math.floor(Math.min(...ys) / step) * step; y <= Math.max(...ys); y += step) {
    const row = Math.round(y / step);
    for (let x = Math.floor(Math.min(...xs) / step) * step + (row % 2 ? step / 2 : 0); x <= Math.max(...xs); x += step) {
      const pt: Pt = [x + (rnd(seed + x * 0.1 + y) - 0.5) * 2, y];
      if ([[0, 0], [rim, 0], [-rim, 0], [0, rim], [0, -rim]].every(([dx, dy]) => inside([pt[0] + dx, pt[1] + dy], poly))) out.push(pt);
    }
  }
  return out;
};

// ---------- puppets (polygons in local coords, feet/rod point at 0,0, facing right) ----------
export type Part = {poly: Pt[]; perforate?: number};
export const KING: Part[] = [
  {poly: [[-60, -470], [-52, -560], [-34, -520], [-22, -600], [-6, -532], [10, -590], [22, -528], [40, -572], [44, -486], [-40, -470]], perforate: 13},
  {poly: [[-46, -470], [40, -478], [52, -456], [92, -446], [56, -430], [50, -404], [26, -392], [-4, -384], [-34, -396], [-48, -430]]},
  {poly: [[-40, -392], [30, -392], [58, -250], [-58, -244]], perforate: 15},
  {poly: [[-62, -250], [62, -254], [120, -40], [70, -24], [26, 0], [-34, 0], [-80, -30], [-118, -60]], perforate: 17},
  {poly: [[-40, -388], [-118, -300], [-150, -110], [-128, -104], [-104, -290], [-30, -360]]},
  {poly: [[18, -388], [96, -318], [150, -380], [164, -366], [104, -292], [12, -356]]},
];
export const THIEF: Part[] = [
  {poly: [[-40, -470], [36, -476], [50, -458], [86, -450], [52, -430], [46, -404], [20, -394], [-6, -388], [-32, -400], [-44, -432]]},
  {poly: [[-46, -480], [42, -492], [60, -470], [-50, -458]]},
  {poly: [[-38, -394], [28, -394], [48, -260], [-50, -252]], perforate: 15},
  {poly: [[-52, -256], [50, -258], [74, -40], [30, 0], [-26, 0], [-70, -46]], perforate: 17},
  {poly: [[-38, -386], [-110, -300], [-138, -120], [-118, -116], [-94, -288], [-30, -360]]},
  {poly: [[16, -386], [92, -330], [150, -352], [156, -334], [98, -306], [10, -354]]},
];
export const WOLF: Pt[] = [[-200, -150], [-150, -200], [-60, -214], [40, -212], [110, -250], [130, -300], [150, -262], [190, -250], [232, -226], [200, -206], [160, -200], [140, -150], [150, -20], [124, -20], [110, -120], [20, -120], [-60, -120], [-80, -20], [-106, -20], [-110, -130], [-170, -120], [-230, -170], [-270, -150], [-232, -196]];
export const QMARK: Pt[] = [[-120, -620], [-60, -700], [40, -716], [120, -670], [140, -580], [100, -500], [30, -450], [20, -360], [-50, -360], [-56, -470], [10, -540], [56, -590], [40, -640], [-10, -650], [-54, -610], [-66, -560]];
export const gunungan = (): Pt[] => {
  const pts: Pt[] = [];
  const N = 90;
  for (let i = 0; i <= N; i++) {
    const k = i / N;
    const w = 300 * Math.pow(1 - k, 0.72) * (1 + 0.28 * Math.sin(Math.PI * k)) + (k > 0.04 && k < 0.96 ? 10 * Math.abs(Math.sin(k * Math.PI * 9)) : 0);
    pts.push([w, -1050 * k]);
  }
  for (let i = N; i >= 0; i--) pts.push([-pts[i][0], pts[i][1]]);
  return pts;
};
export const GUNUNGAN = gunungan();

/** A carved leather silhouette: solid rim, perforations show the lamp light, a soft second shadow on the screen. */
export const Silhouette: React.FC<{id: string; parts: Part[]; x: number; y: number; s?: number; rot?: number; flip?: boolean; rod?: boolean; extra?: React.ReactNode; opacity?: number}> = ({
  id, parts, x, y, s = 1, rot = 0, flip = false, rod = true, extra, opacity = 1,
}) => {
  const hs = React.useMemo(() => parts.flatMap((pt, i) => (pt.perforate ? holes(pt.poly, pt.perforate, 9, i * 7 + id.length) : [])), [parts, id]);
  const body = (
    <>
      {parts.map((pt, i) => <path key={i} d={polyD(pt.poly)} />)}
    </>
  );
  const tf = `translate(${x} ${y}) rotate(${rot}) scale(${flip ? -s : s} ${s})`;
  return (
    <g opacity={opacity}>
      <defs>
        <mask id={`m-${id}`} maskUnits="userSpaceOnUse" x={-2000} y={-3000} width={4000} height={4000}>
          <g fill="#fff">{body}</g>
          <g fill="#000">{hs.map(([hx, hy], i) => <ellipse key={i} cx={hx} cy={hy} rx={3.6} ry={5.2} />)}</g>
        </mask>
      </defs>
      {/* second shadow: the puppet a little off the screen */}
      <g transform={`translate(${x + 22} ${y + 10}) rotate(${rot}) scale(${flip ? -s * 1.04 : s * 1.04} ${s * 1.04})`} fill={H.dark} opacity={0.32} filter="url(#hik-soft)">{body}</g>
      <g transform={tf}>
        <rect x={-600} y={-1200} width={1200} height={1300} fill={H.dark} mask={`url(#m-${id})`} />
        {extra}
        {rod && <line x1={0} y1={0} x2={0} y2={420} stroke={H.dark} strokeWidth={7} />}
      </g>
    </g>
  );
};

// ---------- lontar ----------
export const Lontar: React.FC<{x: number; y: number; w: number; h?: number; q: number; children?: React.ReactNode; rot?: number}> = ({x, y, w, h = 110, q, children, rot = 0}) => {
  if (q <= 0) return null;
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, height: h, transform: `rotate(${rot}deg) translateY(${(1 - q) * -30}px)`, opacity: Math.min(1, q * 2),
      clipPath: `inset(0 ${(1 - q) * 100}% 0 0 round ${h / 2}px)`}}>
      <div style={{position: 'absolute', inset: 0, borderRadius: `${h * 0.45}px / ${h / 2}px`, background: `linear-gradient(180deg, #E6C88E, ${H.leaf} 45%, ${H.leafDark})`,
        boxShadow: '0 14px 26px rgba(20,8,2,.55), inset 0 2px 0 rgba(255,240,200,.5)'}}>
        <div style={{position: 'absolute', inset: 0, borderRadius: 'inherit', backgroundImage: `repeating-linear-gradient(180deg, transparent 0 9px, rgba(90,55,20,.18) 9px 10px), ${FIBER}`, backgroundSize: '100% 100%, 512px', mixBlendMode: 'multiply', opacity: 0.7}} />
        {[0.07, 0.93].map((k) => <div key={k} style={{position: 'absolute', left: `${k * 100}%`, top: '50%', width: 14, height: 14, marginLeft: -7, marginTop: -7, borderRadius: 7, background: '#3a2211', boxShadow: 'inset 0 2px 2px #000'}} />)}
      </div>
      <div style={{position: 'absolute', inset: 0, display: 'flex', alignItems: 'center', padding: `0 ${h * 0.9}px`}}>{children}</div>
    </div>
  );
};
export const incised: React.CSSProperties = {color: H.ink, textShadow: '0 1.5px 0 rgba(255,236,190,.55), 0 -1px 0 rgba(40,20,5,.35)'};

export const Tally: React.FC<{n: number; shown: number; color?: string}> = ({n, shown, color = H.ink}) => (
  <div style={{display: 'flex', gap: 7, alignItems: 'center'}}>
    {Array.from({length: n}).map((_, i) => (
      <div key={i} style={{width: 7, height: 54, borderRadius: 3, background: color, opacity: i < shown ? 1 : 0, marginRight: (i + 1) % 7 === 0 ? 14 : 0,
        transform: `rotate(${(rnd(i) - 0.5) * 8}deg)`, boxShadow: '0 1px 0 rgba(255,236,190,.5)'}} />
    ))}
  </div>
);

export const Caption: React.FC<{t: number; pages: CaptionPage[]; top?: number}> = ({t, pages, top = 1488}) => {
  const pg = pageAt(t, pages);
  if (!pg) return null;
  const pop = p(t, pg.start - 0.08, pg.start + 0.12);
  return (
    <div style={{position: 'absolute', left: 60, right: 60, top, display: 'flex', justifyContent: 'center', opacity: pop}}>
      <div style={{position: 'relative', padding: '18px 70px', borderRadius: '50px / 48px', background: `linear-gradient(180deg, #E6C88E, ${H.leaf} 45%, ${H.leafDark})`,
        boxShadow: '0 14px 26px rgba(20,8,2,.6)', fontFamily: TEXT, fontWeight: 700, fontSize: 58, lineHeight: 1.12, textAlign: 'center', maxWidth: 960}}>
        {pg.words.map((w, i) => {
          const on = t >= w.start - 0.04 && t < w.end + 0.1;
          const said = t >= w.start - 0.04;
          return (
            <React.Fragment key={i}>
              <span style={{...incised, color: on ? H.ember : said ? H.ink : 'rgba(58,34,17,.45)'}}>{display(w.word)}</span>
              {i < pg.words.length - 1 ? ' ' : ''}
            </React.Fragment>
          );
        })}
      </div>
    </div>
  );
};


/** Ornaments drawn on top of the gunungan silhouette (tree + gate). */
export const GununganOrnament: React.FC = () => (
  <g fill="#1B0D06" stroke="#1B0D06">
    <rect x={-14} y={-820} width={28} height={760} stroke="none" />
    {[0, 1, 2, 3, 4, 5].map((i) => [-1, 1].map((sd) => <path key={`${i}${sd}`} d={`M 0 ${-260 - i * 95} Q ${sd * 90} ${-300 - i * 95} ${sd * (190 - i * 26)} ${-380 - i * 95}`} fill="none" strokeWidth={12} strokeLinecap="round" />))}
    <path d="M -120 -60 L 120 -60 L 120 -210 L 0 -290 L -120 -210 Z" stroke="none" />
    <rect x={-40} y={-190} width={80} height={130} fill="#E9A54E" opacity={0.85} stroke="none" />
  </g>
);

/** The lit screen: warm lamp glow, cloth weave, flicker. Put it first inside an AbsoluteFill. */
export const Kelir: React.FC<{t: number; dim?: number; tint?: string}> = ({t, dim = 0, tint}) => {
  const flick = 1 + 0.05 * Math.sin(t * 13.1) + 0.035 * Math.sin(t * 29.7 + 1) + 0.025 * Math.sin(t * 6.3 + 2);
  return (
    <>
      <div style={{position: 'absolute', inset: 0, background: `radial-gradient(ellipse 820px 1150px at 52% 38%, ${HIKAYAT.glow} 0%, ${HIKAYAT.glow2} 38%, ${HIKAYAT.edge} 78%, #1E0B04 100%)`}} />
      {tint && <div style={{position: 'absolute', inset: 0, background: tint, mixBlendMode: 'multiply'}} />}
      <div style={{position: 'absolute', inset: 0, backgroundImage: CLOTH, backgroundSize: '512px', opacity: 0.22, mixBlendMode: 'multiply'}} />
      <div style={{position: 'absolute', inset: 0, background: `radial-gradient(ellipse 700px 900px at 52% 36%, rgba(255,226,160,${0.22 * flick}), transparent 70%)`, mixBlendMode: 'screen'}} />
      <div style={{position: 'absolute', inset: 0, background: '#0A0402', opacity: dim}} />
    </>
  );
};

/** Banana-trunk stage at the bottom where the rods stand. */
export const Stage: React.FC<{top?: number}> = ({top = 1700}) => (
  <div style={{position: 'absolute', left: 0, right: 0, top, height: 1920 - top, background: `linear-gradient(180deg, ${HIKAYAT.stage}, #120802)`, boxShadow: '0 -20px 40px rgba(0,0,0,.5)'}}>
    <div style={{position: 'absolute', inset: 0, backgroundImage: 'repeating-linear-gradient(90deg, transparent 0 46px, rgba(0,0,0,.25) 46px 48px)'}} />
  </div>
);

/** SVG defs every HIKAYAT svg needs once (soft second shadow). */
export const HikDefs: React.FC = () => (
  <defs><filter id="hik-soft" x="-20%" y="-20%" width="140%" height="140%"><feGaussianBlur stdDeviation="9" /></filter></defs>
);

// ---------- puppet & scenery library (from the HIKAYAT demo) ----------
export const WOMAN: Part[] = [
  {poly: [[-40, -470], [36, -476], [50, -458], [80, -452], [52, -432], [46, -404], [20, -394], [-6, -388], [-32, -400], [-44, -432]]},
  {poly: [[-44, -470], [-78, -500], [-96, -468], [-84, -436], [-46, -440]]},
  {poly: [[-36, -394], [28, -394], [44, -262], [-46, -256]], perforate: 14},
  {poly: [[-50, -262], [46, -264], [64, 0], [-64, 0]], perforate: 15},
  {poly: [[-34, -386], [-96, -300], [-112, -150], [-94, -146], [-80, -292], [-28, -360]]},
  {poly: [[14, -386], [70, -320], [96, -250], [80, -244], [56, -306], [8, -356]]},
];
export const CHILD: Part[] = KING.slice(1);
export const FISH: Pt[] = [[-200, -60], [-120, -118], [0, -140], [110, -110], [190, -40], [205, 0], [190, 40], [110, 110], [0, 140], [-120, 118], [-200, 60], [-300, 140], [-276, 0], [-300, -140]];
/** Rumah adat with an upswept saddle roof, on stilts. */
export const HOUSE: Part[] = [
  {poly: [[-300, -540], [-210, -440], [-140, -390], [0, -372], [140, -390], [210, -440], [300, -540], [250, -300], [-250, -300]], perforate: 15},
  {poly: [[-200, -300], [200, -300], [182, -150], [-182, -150]], perforate: 17},
  ...[-165, -60, 45, 140].map((x) => ({poly: [[x, -150], [x + 28, -150], [x + 28, 0], [x, 0]] as Pt[]})),
];
export const MOUNTAINS: Pt[] = [[-620, 0], [-470, -380], [-390, -300], [-230, -560], [-70, -320], [40, -430], [220, -190], [360, -480], [520, -260], [620, -340], [620, 0]];
export const VOLCANO: Pt[] = [[-560, 0], [-150, -660], [-80, -630], [0, -660], [80, -630], [150, -660], [560, 0]];
export const CALDERA: Pt[] = [[-560, 0], [-320, -390], [-230, -300], [0, -290], [230, -300], [320, -390], [560, 0]];
export const ISLAND: Part[] = [
  {poly: [[-280, 0], [-190, -120], [-60, -180], [70, -160], [190, -96], [280, 0]], perforate: 16},
  ...[-120, 0, 110].map((x, i) => ({poly: [[x - 40, -150 + i * 8], [x, -300 + i * 20], [x + 40, -150 + i * 8]] as Pt[]})),
];
export const circlePts = (r: number, n = 40): Pt[] => Array.from({length: n}, (_, i) => [Math.cos((i / n) * Math.PI * 2) * r, Math.sin((i / n) * Math.PI * 2) * r] as Pt);
export const SUN: Part[] = [{poly: circlePts(110), perforate: 15}];
export const lerpPts = (A: Pt[], B: Pt[], k: number): Pt[] => A.map(([x, y], i) => [x + (B[i][0] - x) * k, y + (B[i][1] - y) * k]);
export const wave = (w: number, amp: number, period: number, depth: number): Pt[] => {
  const pts: Pt[] = [];
  for (let x = -w; x <= w; x += 20) pts.push([x, -amp * Math.sin((x / period) * Math.PI * 2)]);
  pts.push([w, depth], [-w, depth]);
  return pts;
};
export const WATER = wave(1300, 22, 170, 900);


/** Lontar label styles: ALWAYS one line (nowrap). For two lines, give the Lontar h >= 150. */
export const lontarTitle = (size = 56, color: string = HIKAYAT.ink): React.CSSProperties => ({...incised, fontFamily: HEAD, fontWeight: 800, fontSize: size, letterSpacing: 4, color, whiteSpace: 'nowrap', lineHeight: 1.1});
export const lontarText = (size = 38): React.CSSProperties => ({...incised, fontFamily: TEXT, fontWeight: 700, fontSize: size, whiteSpace: 'nowrap'});
