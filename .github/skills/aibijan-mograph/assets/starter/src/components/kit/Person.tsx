import React from 'react';
import {C} from '../../theme';
import {easeBack, easeInOut, lerp, prog} from './util';

export type Pose = 'stand' | 'wave' | 'point' | 'think' | 'shock' | 'phone' | 'shrug' | 'celebrate';
export type Mood = 'happy' | 'neutral' | 'surprised' | 'worried';

// [outward angle of the upper arm from hanging, inward bend of the forearm] in degrees, per arm (left, right)
const POSES: Record<Pose, {l: [number, number]; r: [number, number]; tilt: number}> = {
  stand: {l: [10, 6], r: [10, 6], tilt: 0},
  wave: {l: [10, 6], r: [150, -25], tilt: -3},
  point: {l: [10, 6], r: [88, -4], tilt: 2},
  think: {l: [22, 100], r: [-12, 168], tilt: 7},
  shock: {l: [140, 40], r: [140, 40], tilt: 0},
  phone: {l: [10, 6], r: [-2, 128], tilt: 6},
  shrug: {l: [28, -78], r: [28, -78], tilt: -4},
  celebrate: {l: [160, 8], r: [160, 8], tilt: 0},
};

const Arm: React.FC<{sx: number; sy: number; side: 1 | -1; a1: number; a2: number; sleeve: string; skin: string; phone?: boolean}> =
  ({sx, sy, side, a1, a2, sleeve, skin, phone}) => (
    <g transform={`translate(${sx} ${sy}) rotate(${-side * a1})`}>
      <rect x={-16} y={-10} width={32} height={112} rx={16} fill={sleeve} />
      <g transform={`translate(0 98) rotate(${side * a2})`}>
        <rect x={-13} y={-8} width={26} height={98} rx={13} fill={skin} />
        <circle cx={0} cy={92} r={17} fill={skin} />
        {phone && <rect x={-24} y={70} width={48} height={80} rx={9} fill="#1b1d24" stroke="#4a4f5c" strokeWidth={3} />}
      </g>
    </g>
  );

/**
 * Flat, friendly character for everyday micro-stories (buyer, shop owner, viewer). `x` = centre, `y` = top.
 * Poses change smoothly from `fromPose` at `poseAt`; eyes blink on their own; `hijab` takes a colour.
 */
export const PersonFigure: React.FC<{
  t: number; x: number; y: number; height?: number; pose?: Pose; fromPose?: Pose; poseAt?: number; mood?: Mood; skin?: string;
  hair?: string; shirt?: string; pants?: string; hijab?: string; flip?: boolean; enterAt?: number; seed?: number;
}> = ({t, x, y, height = 620, pose = 'stand', fromPose, poseAt = 0, mood = 'neutral', skin = '#C98E66', hair = '#2B1D16',
  shirt = C.blue, pants = '#2F3A56', hijab, flip = false, enterAt, seed = 0}) => {
  const k = prog(t, poseAt, 0.4, easeInOut);
  const A = POSES[fromPose ?? pose], B = POSES[pose];
  const wave = pose === 'wave' ? Math.sin((t - poseAt) * 9) * 16 * k : 0;
  const L: [number, number] = [lerp(A.l[0], B.l[0], k), lerp(A.l[1], B.l[1], k)];
  const R: [number, number] = [lerp(A.r[0], B.r[0], k), lerp(A.r[1], B.r[1], k) + wave];
  const tilt = lerp(A.tilt, B.tilt, k);
  const blink = ((t + seed * 1.37) % 3.7) < 0.12 ? 0.12 : 1;
  const enter = enterAt === undefined ? 1 : prog(t, enterAt, 0.5, easeBack);
  const s = height / 640;
  const dark = '#1E1A22';
  const brows = {happy: [-6, 6], neutral: [0, 0], surprised: [-12, -12], worried: [10, -10]}[mood];
  return (
    <div style={{position: 'absolute', left: x - 150 * s, top: y, width: 300 * s, height, opacity: Math.min(1, enter * 1.3),
      transform: `translateY(${(1 - enter) * 60}px) scaleX(${flip ? -1 : 1})`}}>
      <svg width={300 * s} height={height} viewBox="0 0 300 640" style={{overflow: 'visible'}}>
        <ellipse cx={150} cy={626} rx={92} ry={11} fill="#0d123322" />
        <rect x={112} y={392} width={36} height={214} rx={16} fill={pants} />
        <rect x={152} y={392} width={36} height={214} rx={16} fill={pants} />
        <ellipse cx={126} cy={608} rx={32} ry={14} fill={dark} />
        <ellipse cx={176} cy={608} rx={32} ry={14} fill={dark} />
        {hijab ? <path d="M78 120 Q76 26 150 26 Q224 26 222 120 L236 222 Q150 250 64 222 Z" fill={hijab} /> : null}
        <path d="M100 202 Q150 184 200 202 L212 332 Q214 412 190 412 L110 412 Q86 412 88 332 Z" fill={shirt} />
        {!hijab && <rect x={137} y={150} width={26} height={50} rx={10} fill={skin} />}
        <g transform={`rotate(${tilt} 150 170)`}>
          {hijab ? (
            <ellipse cx={150} cy={116} rx={50} ry={58} fill={skin} />
          ) : (
            <>
              <circle cx={91} cy={118} r={12} fill={skin} />
              <circle cx={209} cy={118} r={12} fill={skin} />
              <circle cx={150} cy={110} r={60} fill={skin} />
              <path d="M88 116 Q84 38 150 38 Q216 38 212 116 Q204 74 158 70 Q120 72 104 92 Q94 100 88 116 Z" fill={hair} />
            </>
          )}
          <ellipse cx={130} cy={114} rx={6.5} ry={8 * blink} fill={dark} />
          <ellipse cx={170} cy={114} rx={6.5} ry={8 * blink} fill={dark} />
          <path d={`M118 ${96 + brows[0]} L140 ${96 - brows[0] * 0.3 + (mood === 'surprised' ? -12 : 0)}`} stroke={dark} strokeWidth={5} strokeLinecap="round" />
          <path d={`M160 ${96 - brows[1] * 0.3 + (mood === 'surprised' ? -12 : 0)} L182 ${96 + brows[1]}`} stroke={dark} strokeWidth={5} strokeLinecap="round" />
          {mood === 'happy' && <>
            <circle cx={118} cy={136} r={10} fill={C.coral} opacity={0.3} />
            <circle cx={182} cy={136} r={10} fill={C.coral} opacity={0.3} />
            <path d="M134 140 Q150 158 166 140" fill="none" stroke={dark} strokeWidth={5} strokeLinecap="round" />
          </>}
          {mood === 'neutral' && <path d="M139 146 L161 146" stroke={dark} strokeWidth={5} strokeLinecap="round" />}
          {mood === 'surprised' && <ellipse cx={150} cy={148} rx={9} ry={12} fill={dark} />}
          {mood === 'worried' && <path d="M136 152 Q150 140 164 152" fill="none" stroke={dark} strokeWidth={5} strokeLinecap="round" />}
        </g>
        <Arm sx={102} sy={210} side={-1} a1={L[0]} a2={L[1]} sleeve={shirt} skin={skin} />
        <Arm sx={198} sy={210} side={1} a1={R[0]} a2={R[1]} sleeve={shirt} skin={skin} phone={pose === 'phone' && k > 0.5} />
      </svg>
    </div>
  );
};
