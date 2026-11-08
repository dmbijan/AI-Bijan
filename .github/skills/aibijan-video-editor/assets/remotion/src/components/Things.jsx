// Flat object illustrations for the scene kit (SceneBuild "thing" items). Drawn for this skill: simple geometric shapes
// on a 120x120 grid, three colours only (c1 = main fill, c2 = secondary / paper, ln = line), so every object wears the
// current style: solid ink outlines on flat and light styles, white lines on dark styles. Generic on purpose - no brand
// marks, no currency symbols, no text.
import React from 'react';

const gearPath = (n = 8, r1 = 44, r2 = 34) => {
  let d = '';
  for (let i = 0; i < n * 2; i++) {
    const a0 = (i / (n * 2)) * Math.PI * 2, a1 = ((i + 1) / (n * 2)) * Math.PI * 2, r = i % 2 ? r2 : r1;
    const p = (a) => `${(60 + Math.cos(a) * r).toFixed(1)} ${(60 + Math.sin(a) * r).toFixed(1)}`;
    d += (i ? ' L' : 'M') + p(a0) + ' L' + p(a1);
  }
  return d + ' Z';
};
const starPath = (r1 = 46, r2 = 20) => Array.from({length: 10}, (_, i) => {
  const a = -Math.PI / 2 + (i * Math.PI) / 5, r = i % 2 ? r2 : r1;
  return `${i ? 'L' : 'M'}${(60 + Math.cos(a) * r).toFixed(1)} ${(62 + Math.sin(a) * r).toFixed(1)}`;
}).join(' ') + ' Z';

// each entry: ({c1, c2, c3, ln, w, t, it}) => svg children. w = line width. t = seconds since the item appeared.
export const THINGS = {
  house: ({c1, c2, ln, w}) => (<>
    <rect x={24} y={56} width={72} height={50} fill={c2} stroke={ln} strokeWidth={w} />
    <path d="M12 60 L60 20 L108 60 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <rect x={52} y={76} width={16} height={30} fill={c1} stroke={ln} strokeWidth={w * 0.8} />
    <rect x={32} y={66} width={13} height={13} fill={ln} opacity={0.85} />
  </>),
  store: ({c1, c2, ln, w}) => (<>
    <rect x={18} y={46} width={84} height={58} fill={c2} stroke={ln} strokeWidth={w} />
    <rect x={14} y={26} width={92} height={22} fill={c1} stroke={ln} strokeWidth={w} />
    {[0, 2, 4, 6].map((k) => <rect key={k} x={14 + k * 11.5} y={26} width={11.5} height={22} fill={c2} opacity={0.9} />)}
    <rect x={14} y={26} width={92} height={22} fill="none" stroke={ln} strokeWidth={w} />
    <rect x={50} y={68} width={20} height={36} fill={c1} stroke={ln} strokeWidth={w * 0.8} />
    <rect x={26} y={60} width={17} height={16} fill={ln} opacity={0.85} />
    <rect x={77} y={60} width={17} height={16} fill={ln} opacity={0.85} />
  </>),
  building: ({c1, c2, ln, w}) => (<>
    <rect x={30} y={12} width={60} height={96} fill={c2} stroke={ln} strokeWidth={w} />
    {[0, 1, 2, 3, 4].map((r) => [0, 1, 2].map((c) => (
      <rect key={`${r}${c}`} x={38 + c * 16} y={22 + r * 15} width={10} height={9} fill={(r + c) % 3 === 0 ? c1 : ln} opacity={(r + c) % 3 === 0 ? 1 : 0.8} />)))}
    <rect x={52} y={92} width={16} height={16} fill={c1} stroke={ln} strokeWidth={w * 0.8} />
  </>),
  money: ({c1, c2, ln, w}) => (<>
    <rect x={20} y={28} width={88} height={52} rx={6} fill={c1} stroke={ln} strokeWidth={w} opacity={0.55} />
    <rect x={12} y={40} width={88} height={52} rx={6} fill={c1} stroke={ln} strokeWidth={w} />
    <rect x={20} y={48} width={72} height={36} rx={4} fill="none" stroke={c2} strokeWidth={3} />
    <circle cx={56} cy={66} r={10} fill={c2} />
  </>),
  coin: ({c1, c2, ln, w}) => (<>
    <circle cx={60} cy={60} r={42} fill={c1} stroke={ln} strokeWidth={w} />
    <circle cx={60} cy={60} r={29} fill="none" stroke={c2} strokeWidth={4} />
    <rect x={56} y={44} width={8} height={32} rx={3} fill={c2} />
  </>),
  wallet: ({c1, c2, ln, w}) => (<>
    <rect x={24} y={22} width={60} height={26} rx={4} fill={c2} stroke={ln} strokeWidth={w * 0.8} />
    <rect x={12} y={34} width={96} height={64} rx={10} fill={c1} stroke={ln} strokeWidth={w} />
    <rect x={70} y={54} width={38} height={26} rx={6} fill={c2} stroke={ln} strokeWidth={w * 0.8} />
    <circle cx={82} cy={67} r={4} fill={ln} />
  </>),
  clock: ({c1, c2, ln, w, t}) => {
    const m = (t * 60) % 360, h = (t * 5) % 360;
    return (<>
      <circle cx={60} cy={60} r={46} fill={c2} stroke={ln} strokeWidth={w * 1.2} />
      {Array.from({length: 12}, (_, i) => <rect key={i} x={58.5} y={18} width={3} height={i % 3 ? 5 : 9} fill={ln} transform={`rotate(${i * 30} 60 60)`} />)}
      <rect x={57} y={34} width={6} height={28} rx={3} fill={ln} transform={`rotate(${h} 60 60)`} />
      <rect x={58} y={22} width={4} height={40} rx={2} fill={c1} transform={`rotate(${m} 60 60)`} />
      <circle cx={60} cy={60} r={6} fill={c1} stroke={ln} strokeWidth={2} />
    </>);
  },
  hourglass: ({c1, c2, ln, w, t}) => {
    const k = Math.min(1, (t % 6) / 5);
    return (<>
      <rect x={28} y={10} width={64} height={8} rx={3} fill={ln} /><rect x={28} y={102} width={64} height={8} rx={3} fill={ln} />
      <path d="M34 18 L86 18 Q86 44 64 60 Q86 76 86 102 L34 102 Q34 76 56 60 Q34 44 34 18 Z" fill={c2} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
      <path d={`M${40 + k * 12} ${22 + k * 26} L${80 - k * 12} ${22 + k * 26} Q72 50 60 58 Q48 50 ${40 + k * 12} ${22 + k * 26} Z`} fill={c1} />
      <path d={`M40 98 L80 98 L${80 - (1 - k) * 18} ${98 - k * 26} L${40 + (1 - k) * 18} ${98 - k * 26} Z`} fill={c1} />
    </>);
  },
  calendar: ({c1, c2, ln, w}) => (<>
    <rect x={16} y={24} width={88} height={80} rx={8} fill={c2} stroke={ln} strokeWidth={w} />
    <path d="M16 32 Q16 24 24 24 L96 24 Q104 24 104 32 L104 46 L16 46 Z" fill={c1} stroke={ln} strokeWidth={w} />
    <rect x={34} y={14} width={7} height={18} rx={3} fill={ln} /><rect x={79} y={14} width={7} height={18} rx={3} fill={ln} />
    {[0, 1, 2].map((r) => [0, 1, 2, 3].map((c) => (
      <rect key={`${r}${c}`} x={27 + c * 18} y={55 + r * 15} width={11} height={9} rx={2} fill={r === 1 && c === 2 ? c1 : ln} opacity={r === 1 && c === 2 ? 1 : 0.35} />)))}
  </>),
  phone: ({c1, c2, ln}) => (<>
    <rect x={32} y={8} width={56} height={104} rx={12} fill={ln} />
    <rect x={38} y={18} width={44} height={84} rx={5} fill={c2} />
    <rect x={52} y={11} width={16} height={4} rx={2} fill={c2} opacity={0.5} />
    <rect x={44} y={28} width={26} height={12} rx={5} fill={c1} />
    <rect x={50} y={46} width={26} height={12} rx={5} fill={ln} opacity={0.25} />
    <rect x={44} y={64} width={20} height={12} rx={5} fill={c1} />
  </>),
  laptop: ({c1, c2, ln, w}) => (<>
    <rect x={22} y={22} width={76} height={54} rx={5} fill={ln} />
    <rect x={28} y={28} width={64} height={42} fill={c2} />
    <rect x={34} y={36} width={30} height={6} rx={3} fill={c1} /><rect x={34} y={48} width={46} height={5} rx={2} fill={ln} opacity={0.3} />
    <rect x={34} y={58} width={38} height={5} rx={2} fill={ln} opacity={0.3} />
    <path d="M8 80 L112 80 L104 94 L16 94 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
  </>),
  chart: ({c1, c2, ln, w, it}) => {
    const down = it.trend === 'down', hs = down ? [66, 48, 28] : [28, 48, 68];
    return (<>
      <path d="M18 14 L18 102 L106 102" fill="none" stroke={ln} strokeWidth={w} strokeLinecap="round" strokeLinejoin="round" />
      {hs.map((h, i) => <rect key={i} x={30 + i * 24} y={100 - h} width={16} height={h} fill={i === (down ? 2 : 2) ? c1 : c2} stroke={ln} strokeWidth={w * 0.7} />)}
      <path d={down ? 'M30 28 L58 50 L90 76' : 'M30 76 L58 54 L90 26'} fill="none" stroke={c1} strokeWidth={w} strokeLinecap="round" />
      <path d={down ? 'M80 76 L92 78 L90 66' : 'M80 24 L92 24 L90 36'} fill="none" stroke={c1} strokeWidth={w} strokeLinecap="round" strokeLinejoin="round" />
    </>);
  },
  scale: ({c1, ln, w, it}) => {
    const tilt = it.tilt ?? 0;
    return (<>
      <rect x={57} y={30} width={6} height={64} fill={ln} />
      <rect x={34} y={92} width={52} height={10} rx={4} fill={ln} />
      <g transform={`rotate(${tilt} 60 30)`}>
        <rect x={14} y={27} width={92} height={6} rx={3} fill={ln} />
        <g transform={`rotate(${-tilt} 20 30)`}><path d="M20 30 L8 62 M20 30 L32 62" stroke={ln} strokeWidth={w * 0.6} /><path d="M4 62 Q20 78 36 62 Z" fill={c1} stroke={ln} strokeWidth={w * 0.8} /></g>
        <g transform={`rotate(${-tilt} 100 30)`}><path d="M100 30 L88 62 M100 30 L112 62" stroke={ln} strokeWidth={w * 0.6} /><path d="M84 62 Q100 78 116 62 Z" fill={c1} stroke={ln} strokeWidth={w * 0.8} /></g>
      </g>
      <circle cx={60} cy={30} r={6} fill={c1} stroke={ln} strokeWidth={2} />
    </>);
  },
  lock: ({c1, ln, w, it}) => {
    const open = it.open ? -14 : 0;
    return (<>
      <path d={`M40 ${56 + open} L40 38 A20 20 0 0 1 80 38 L80 ${it.open ? 30 : 54}`} fill="none" stroke={ln} strokeWidth={w * 1.6} strokeLinecap="round" />
      <rect x={28} y={52} width={64} height={52} rx={9} fill={c1} stroke={ln} strokeWidth={w} />
      <circle cx={60} cy={72} r={7} fill={ln} /><rect x={57} y={72} width={6} height={16} rx={2} fill={ln} />
    </>);
  },
  key: ({c1, ln, w}) => (<>
    <circle cx={36} cy={60} r={20} fill={c1} stroke={ln} strokeWidth={w} /><circle cx={36} cy={60} r={7} fill={ln} />
    <rect x={54} y={54} width={54} height={12} rx={3} fill={c1} stroke={ln} strokeWidth={w * 0.8} />
    <rect x={88} y={64} width={7} height={14} fill={c1} stroke={ln} strokeWidth={w * 0.6} /><rect x={99} y={64} width={7} height={10} fill={c1} stroke={ln} strokeWidth={w * 0.6} />
  </>),
  bag: ({c1, c2, ln, w}) => (<>
    <path d="M44 44 Q44 18 60 18 Q76 18 76 44" fill="none" stroke={ln} strokeWidth={w} strokeLinecap="round" />
    <path d="M24 42 L96 42 L102 106 L18 106 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <rect x={40} y={60} width={40} height={6} rx={3} fill={c2} opacity={0.9} />
  </>),
  box: ({c1, c2, ln, w}) => (<>
    <path d="M18 46 L40 28 L104 28 L82 46 Z" fill={c2} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <path d="M82 46 L104 28 L104 82 L82 100 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <path d="M82 46 L104 28 L104 82 L82 100 Z" fill={ln} opacity={0.18} />
    <rect x={18} y={46} width={64} height={54} fill={c1} stroke={ln} strokeWidth={w} />
    <rect x={43} y={46} width={14} height={22} fill={c2} />
  </>),
  cart: ({c1, ln, w}) => (<>
    <path d="M34 42 L106 42 L98 78 L42 78 Z" fill={c1} />
    <path d="M10 26 L28 26 L42 78 L98 78 L106 42 L32 42" fill="none" stroke={ln} strokeWidth={w} strokeLinecap="round" strokeLinejoin="round" />
    <circle cx={48} cy={94} r={9} fill={ln} /><circle cx={90} cy={94} r={9} fill={ln} />
  </>),
  truck: ({c1, c2, ln, w}) => (<>
    <rect x={8} y={32} width={68} height={52} rx={3} fill={c1} stroke={ln} strokeWidth={w} />
    <path d="M76 48 L98 48 L112 66 L112 84 L76 84 Z" fill={c2} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <path d="M82 54 L96 54 L104 66 L82 66 Z" fill={ln} opacity={0.75} />
    <circle cx={30} cy={88} r={10} fill={ln} /><circle cx={30} cy={88} r={4} fill={c2} />
    <circle cx={92} cy={88} r={10} fill={ln} /><circle cx={92} cy={88} r={4} fill={c2} />
  </>),
  car: ({c1, c2, ln, w}) => (<>
    <path d="M22 62 L32 42 Q36 36 44 36 L76 36 Q84 36 88 42 L98 62 Z" fill={c2} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <path d="M40 58 L46 44 L58 44 L58 58 Z M64 58 L64 44 L74 44 L82 58 Z" fill={ln} opacity={0.75} />
    <rect x={10} y={58} width={100} height={26} rx={10} fill={c1} stroke={ln} strokeWidth={w} />
    <circle cx={34} cy={86} r={11} fill={ln} /><circle cx={34} cy={86} r={4} fill={c2} />
    <circle cx={86} cy={86} r={11} fill={ln} /><circle cx={86} cy={86} r={4} fill={c2} />
  </>),
  chat: ({c1, c2, ln, w}) => (<>
    <path d="M30 86 L24 106 L50 86 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <rect x={12} y={20} width={96} height={68} rx={20} fill={c1} stroke={ln} strokeWidth={w} />
    <path d="M30 86 L24 104 L48 86" fill={c1} />
    {[40, 60, 80].map((x) => <circle key={x} cx={x} cy={54} r={6.5} fill={c2} />)}
  </>),
  doc: ({c1, c2, ln, w}) => (<>
    <path d="M26 10 L78 10 L96 28 L96 110 L26 110 Z" fill={c2} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <path d="M78 10 L78 28 L96 28 Z" fill={c1} stroke={ln} strokeWidth={w * 0.8} strokeLinejoin="round" />
    {[42, 56, 70].map((y, i) => <rect key={y} x={38} y={y} width={i === 2 ? 30 : 46} height={5} rx={2} fill={ln} opacity={0.4} />)}
    <rect x={38} y={86} width={28} height={10} rx={3} fill={c1} />
  </>),
  bulb: ({c1, ln, w, t}) => (<>
    {[-150, -115, -65, -30].map((a, i) => {
      const r = (a * Math.PI) / 180, k = 0.85 + 0.15 * Math.sin(t * 4 + i);
      return <line key={a} x1={60 + Math.cos(r) * 40} y1={46 + Math.sin(r) * 40} x2={60 + Math.cos(r) * (40 + 14 * k)} y2={46 + Math.sin(r) * (40 + 14 * k)} stroke={c1} strokeWidth={w} strokeLinecap="round" />;
    })}
    <path d="M60 14 A30 30 0 0 1 78 68 L74 80 L46 80 L42 68 A30 30 0 0 1 60 14 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <rect x={46} y={84} width={28} height={7} rx={3} fill={ln} /><rect x={49} y={94} width={22} height={7} rx={3} fill={ln} />
  </>),
  target: ({c1, c2, ln, w}) => (<>
    <circle cx={56} cy={64} r={44} fill={c2} stroke={ln} strokeWidth={w} />
    <circle cx={56} cy={64} r={30} fill={c1} stroke={ln} strokeWidth={w * 0.6} />
    <circle cx={56} cy={64} r={16} fill={c2} stroke={ln} strokeWidth={w * 0.6} />
    <circle cx={56} cy={64} r={6} fill={ln} />
    <line x1={58} y1={62} x2={104} y2={16} stroke={ln} strokeWidth={w} strokeLinecap="round" />
    <path d="M96 14 L108 12 L106 24" fill="none" stroke={ln} strokeWidth={w} strokeLinejoin="round" />
  </>),
  trophy: ({c1, ln, w}) => (<>
    <path d="M34 22 Q14 22 18 40 Q22 54 40 54 M86 22 Q106 22 102 40 Q98 54 80 54" fill="none" stroke={ln} strokeWidth={w} strokeLinecap="round" />
    <path d="M32 16 L88 16 L84 52 Q60 74 36 52 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <rect x={54} y={66} width={12} height={18} fill={ln} /><rect x={36} y={84} width={48} height={14} rx={4} fill={ln} />
  </>),
  heart: ({c1, ln, w}) => <path d="M60 104 C20 78 10 56 14 40 C18 22 40 14 60 34 C80 14 102 22 106 40 C110 56 100 78 60 104 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />,
  star: ({c1, ln, w}) => <path d={starPath()} fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />,
  magnifier: ({c2, ln, w}) => (<>
    <circle cx={50} cy={50} r={32} fill={c2} stroke={ln} strokeWidth={w * 1.6} />
    <line x1={74} y1={74} x2={104} y2={104} stroke={ln} strokeWidth={w * 2.6} strokeLinecap="round" />
  </>),
  gear: ({c1, c2, ln, w, t, it}) => (
    <g transform={`rotate(${it.spin === false ? 0 : (t * 30) % 360} 60 60)`}>
      <path d={gearPath()} fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
      <circle cx={60} cy={60} r={14} fill={c2} stroke={ln} strokeWidth={w} />
    </g>
  ),
  globe: ({c1, c2, ln, w}) => (<>
    <circle cx={60} cy={60} r={44} fill={c1} stroke={ln} strokeWidth={w} />
    <ellipse cx={60} cy={60} rx={18} ry={44} fill="none" stroke={c2} strokeWidth={4} />
    <path d="M18 46 Q60 56 102 46 M18 74 Q60 64 102 74" fill="none" stroke={c2} strokeWidth={4} />
    <line x1={60} y1={16} x2={60} y2={104} stroke={c2} strokeWidth={4} />
  </>),
  pin: ({c1, c2, ln, w}) => (<>
    <path d="M60 110 C40 82 24 64 24 46 A36 36 0 0 1 96 46 C96 64 80 82 60 110 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <circle cx={60} cy={46} r={14} fill={c2} stroke={ln} strokeWidth={w * 0.7} />
  </>),
  camera: ({c1, c2, ln}) => (<>
    <rect x={40} y={26} width={30} height={14} rx={4} fill={ln} />
    <rect x={12} y={36} width={96} height={64} rx={12} fill={ln} />
    <circle cx={60} cy={68} r={24} fill={c2} /><circle cx={60} cy={68} r={15} fill={c1} /><circle cx={60} cy={68} r={6} fill={ln} />
    <rect x={88} y={44} width={12} height={8} rx={2} fill={c1} />
  </>),
  mic: ({c1, ln, w}) => (<>
    <rect x={44} y={10} width={32} height={56} rx={16} fill={c1} stroke={ln} strokeWidth={w} />
    <path d="M32 50 Q32 80 60 80 Q88 80 88 50" fill="none" stroke={ln} strokeWidth={w} strokeLinecap="round" />
    <rect x={57} y={80} width={6} height={16} fill={ln} /><rect x={40} y={96} width={40} height={9} rx={4} fill={ln} />
  </>),
  play: ({c1, c2, ln, w}) => (<>
    <rect x={10} y={22} width={100} height={76} rx={16} fill={c1} stroke={ln} strokeWidth={w} />
    <path d="M50 42 L78 60 L50 78 Z" fill={c2} strokeLinejoin="round" />
  </>),
  cup: ({c1, c2, ln, w, t}) => (<>
    {[44, 60, 76].map((x, i) => <path key={x} d={`M${x} 34 q-6 -8 0 -14 q6 -6 0 -14`} fill="none" stroke={ln} strokeWidth={w * 0.7} strokeLinecap="round"
      opacity={0.25 + 0.35 * (0.5 + 0.5 * Math.sin(t * 3 + i * 1.7))} />)}
    <path d="M86 52 Q108 54 100 76 Q94 88 82 84" fill="none" stroke={ln} strokeWidth={w} strokeLinecap="round" />
    <path d="M24 40 L92 40 L84 104 Q58 112 32 104 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <rect x={30} y={56} width={58} height={10} fill={c2} opacity={0.9} />
  </>),
  rocket: ({c1, c2, ln, w, t}) => {
    const fl = 1 + 0.18 * Math.sin(t * 22);
    return (<>
      <path d={`M48 88 L60 ${88 + 22 * fl} L72 88 Z`} fill={c1} />
      <path d="M40 72 L24 92 L44 88 Z M80 72 L96 92 L76 88 Z" fill={c1} stroke={ln} strokeWidth={w * 0.8} strokeLinejoin="round" />
      <path d="M60 8 Q88 32 80 88 L40 88 Q32 32 60 8 Z" fill={c2} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
      <circle cx={60} cy={44} r={10} fill={c1} stroke={ln} strokeWidth={w * 0.8} />
    </>);
  },
  eye: ({c1, c2, ln, w}) => (<>
    <path d="M8 60 Q60 12 112 60 Q60 108 8 60 Z" fill={c2} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <circle cx={60} cy={60} r={20} fill={c1} stroke={ln} strokeWidth={w * 0.6} /><circle cx={60} cy={60} r={8} fill={ln} />
    <circle cx={66} cy={53} r={4} fill={c2} />
  </>),
  bell: ({c1, ln, w, t}) => (
    <g transform={`rotate(${Math.sin(t * 9) * 10 * Math.exp(-((t % 3) * 1.2))} 60 22)`}>
      <circle cx={60} cy={22} r={6} fill={ln} />
      <path d="M32 82 Q32 34 60 28 Q88 34 88 82 L98 92 L22 92 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
      <circle cx={60} cy={100} r={8} fill={ln} />
    </g>
  ),
  warning: ({c1, ln, w}) => (<>
    <path d="M60 12 L110 100 L10 100 Z" fill={c1} stroke={ln} strokeWidth={w * 1.2} strokeLinejoin="round" />
    <rect x={55} y={40} width={10} height={34} rx={4} fill={ln} /><circle cx={60} cy={86} r={6} fill={ln} />
  </>),
  check: ({c1, c2, ln, w}) => (<>
    <circle cx={60} cy={60} r={46} fill={c1} stroke={ln} strokeWidth={w} />
    <path d="M36 62 L53 79 L86 44" fill="none" stroke={c2} strokeWidth={w * 2.2} strokeLinecap="round" strokeLinejoin="round" />
  </>),
  cross: ({c3, c2, ln, w}) => (<>
    <circle cx={60} cy={60} r={46} fill={c3} stroke={ln} strokeWidth={w} />
    <path d="M40 40 L80 80 M80 40 L40 80" fill="none" stroke={c2} strokeWidth={w * 2.2} strokeLinecap="round" />
  </>),
  question: ({c1, c2, ln, w}) => (<>
    <circle cx={60} cy={60} r={46} fill={c1} stroke={ln} strokeWidth={w} />
    <path d="M44 46 Q44 28 60 28 Q76 28 76 44 Q76 54 64 60 Q60 62 60 72" fill="none" stroke={c2} strokeWidth={w * 2} strokeLinecap="round" />
    <circle cx={60} cy={88} r={6} fill={c2} />
  </>),
  book: ({c1, c2, ln, w}) => (<>
    <path d="M60 32 Q36 20 10 26 L10 100 Q36 94 60 106 Q84 94 110 100 L110 26 Q84 20 60 32 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <path d="M60 30 Q38 18 16 22 L16 92 Q38 88 60 100 Z M60 30 Q82 18 104 22 L104 92 Q82 88 60 100 Z" fill={c2} stroke={ln} strokeWidth={w * 0.8} strokeLinejoin="round" />
    {[40, 52, 64].map((y) => <path key={y} d={`M26 ${y} Q40 ${y - 3} 52 ${y + 3} M68 ${y + 3} Q80 ${y - 3} 94 ${y}`} fill="none" stroke={ln} strokeWidth={3} opacity={0.35} />)}
  </>),
  door: ({c1, c2, ln, w, it}) => (<>
    <rect x={26} y={10} width={68} height={102} fill={it.open ? ln : c2} stroke={ln} strokeWidth={w} />
    <path d={it.open ? 'M32 16 L66 24 L66 104 L32 112 Z' : 'M32 16 L88 16 L88 112 L32 112 Z'} fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
    <circle cx={it.open ? 58 : 78} cy={64} r={4.5} fill={ln} />
  </>),
  plant: ({c1, c3, ln, w}) => (<>
    <path d="M60 74 L60 40" stroke={ln} strokeWidth={w} strokeLinecap="round" />
    <path d="M60 52 Q36 52 30 30 Q54 28 60 52 Z" fill={c3} stroke={ln} strokeWidth={w * 0.8} strokeLinejoin="round" />
    <path d="M60 44 Q84 42 90 20 Q66 18 60 44 Z" fill={c3} stroke={ln} strokeWidth={w * 0.8} strokeLinejoin="round" />
    <path d="M36 72 L84 72 L78 108 L42 108 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
  </>),
  fire: ({c1, c2, ln, w, t}) => {
    const k = 1 + 0.05 * Math.sin(t * 9);
    return (
      <g transform={`translate(60 108) scale(1 ${k}) translate(-60 -108)`}>
        <path d="M60 108 C30 108 18 86 26 64 C32 48 44 42 44 24 C60 34 66 46 64 58 C72 52 74 44 74 36 C92 52 100 74 92 90 C86 102 76 108 60 108 Z" fill={c1} stroke={ln} strokeWidth={w} strokeLinejoin="round" />
        <path d="M60 104 C46 104 42 92 46 82 C50 72 58 70 58 60 C68 68 74 78 72 88 C70 98 66 104 60 104 Z" fill={c2} />
      </g>
    );
  },
  battery: ({c1, c2, ln, w, it}) => {
    const lv = Math.max(0, Math.min(1, it.level ?? 0.7));
    return (<>
      <rect x={10} y={34} width={90} height={52} rx={9} fill={c2} stroke={ln} strokeWidth={w} />
      <rect x={100} y={50} width={10} height={20} rx={3} fill={ln} />
      <rect x={18} y={42} width={74 * lv} height={36} rx={4} fill={lv < 0.25 ? (it.lowTone || c1) : c1} />
    </>);
  },
  gift: ({c1, c2, ln, w}) => (<>
    <rect x={18} y={50} width={84} height={56} fill={c1} stroke={ln} strokeWidth={w} />
    <rect x={12} y={36} width={96} height={18} fill={c1} stroke={ln} strokeWidth={w} />
    <rect x={53} y={36} width={14} height={70} fill={c2} />
    <path d="M60 36 Q40 14 34 26 Q30 36 60 36 Q90 36 86 26 Q80 14 60 36 Z" fill="none" stroke={ln} strokeWidth={w * 0.8} strokeLinejoin="round" />
  </>),
};
export const THING_NAMES = Object.keys(THINGS);
