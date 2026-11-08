// REFERENCE DEMO (AI BIJAN MOGRAPH v1.6) — read for structure only: stations, camera moves, word-pinned beats, SFX cues.
// Do NOT copy this story, its text or its layout into a buyer video. Written in the author's demo workspace, so two
// imports differ from the starter: `makeCues(words)` ≈ the starter's `at()/endOf()` from src/lib/words.ts, and
// `EpisodeAudio` ≈ Main.tsx audio (musicFile + auto-duck) + <SfxTrack /> fed by src/audio-plan.json sfxCues.
import React from 'react';
import {AbsoluteFill, Easing} from 'remotion';
import {makeCues} from '../../lib/cues';
import {camera, drawOn, Move, p, pagesFor, pb, rnd, shake, StyleFonts, useTime, World} from '../../styles/shared';
import {B, Callout, Caption, Dim, Figure, GRAIN, HEAD, MONO, Pt, Typed} from '../../styles/blueprint/kit';
import {EpisodeAudio, Sfx} from '../../styles/audio';
import wordsFile from './words.json';

// BLUEPRINT episode — "Kalau tali lift putus, kamu jatuh?". One cross-section drawing of a lift shaft;
// detail panels (A–E) sit beside it on the same sheet, and an 1854 archive sheet sits below. The camera travels.

const C = makeCues(wordsFile);
const PAGES = pagesFor(C.words);
export const BLUEPRINT_DURATION = C.duration + 1.4;
const a = C.at;

// shaft geometry (world px)
const ROOM = {x0: 170, x1: 910, y0: 200, y1: 640};
const SHEAVE = {x: 600, y: 430, r: 150};
const GOV = {x: 290, y: 470, r: 52};
const WALL = {l: 220, r: 860, top: 640, pit: 4700};
const CAR = {x0: 320, x1: 580, h: 360};
const CW = {x0: 705, x1: 795, top: 1880, h: 280};
const ROPES = [-12, -6, 0, 6, 12];
const CAR_TOP = 2200, CAR_STOP = 2600;

const line = (q: number, w = 3, color = B.line) => ({fill: 'none', stroke: color, strokeWidth: w, strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const, ...drawOn(q)});

const carTopAt = (t: number) => {
  const t0 = a('begitu kabin'), t1 = a('klak');
  if (t < t0) return CAR_TOP - 22 * p(t, a('kamu bakal'), a('kamu bakal') + 0.25) * (1 - p(t, a('jawabannya'), a('jawabannya') + 0.3));
  const k = Math.min(1, (t - t0) / (t1 - t0));
  return CAR_TOP + (CAR_STOP - CAR_TOP) * Easing.in(Easing.quad)(k);
};

const Hatch: React.FC<{x: number; y: number; w: number; h: number; q: number; id: string}> = ({x, y, w, h, q, id}) => (
  <g>
    <defs>
      <pattern id={id} width="22" height="22" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><line x1="0" y1="0" x2="0" y2="22" stroke={B.line2} strokeWidth="2" strokeOpacity=".55" /></pattern>
      <clipPath id={`${id}-c`}><rect x={x} y={y} width={w} height={h * q} /></clipPath>
    </defs>
    <rect x={x} y={y} width={w} height={h} fill={`url(#${id})`} clipPath={`url(#${id}-c)`} />
    <rect x={x} y={y} width={w} height={h} fill="none" stroke={B.line} strokeWidth={2.5} {...drawOn(q)} />
  </g>
);

const BreakMark: React.FC<{x: number; y: number; w: number; q: number}> = ({x, y, w, q}) => (
  <path d={`M ${x - w / 2} ${y} l ${w * 0.25} -16 l ${w * 0.18} 30 l ${w * 0.18} -30 l ${w * 0.18} 30 l ${w * 0.21} -14`} {...line(q, 5, B.red)} />
);

const Panel: React.FC<{x: number; y: number; w: number; h: number; title: string; q: number; children?: React.ReactNode}> = ({x, y, w, h, title, q, children}) => (
  <g>
    <rect x={x} y={y} width={w} height={h} fill={B.bg} fillOpacity={0.6 * q} stroke={B.line} strokeWidth={3} {...drawOn(q)} />
    <line x1={x} y1={y + 70} x2={x + w} y2={y + 70} stroke={B.line2} strokeWidth={2} {...drawOn(p(q, 0.3, 1))} />
    <text x={x + 26} y={y + 46} fill={B.cyan} opacity={p(q, 0.4, 1)} style={{fontFamily: MONO, fontWeight: 600, fontSize: 28, letterSpacing: 2}}>{title}</text>
    {q > 0.5 && children}
  </g>
);

// ---------- main drawing ----------
const Shaft: React.FC<{t: number}> = ({t}) => {
  const reveal = p(t, a('dan alasannya') - 0.3, a('dan alasannya') + 1.2);
  const always = 1;
  const carTop = carTopAt(t);
  const cutAt = a('anggap');
  const cut = t >= cutAt + 0.6;
  const slack = p(t, cutAt + 0.6, cutAt + 1.2);
  const hookCut = p(t, a('putus') - 0.05, a('putus') + 0.2) * (1 - p(t, a('jawabannya'), a('jawabannya') + 0.3));
  const govSpin = t * (t > a('begitu kabin') && t < a('klak') + 0.3 ? 16 : 1.2);
  const ropeQ = (i: number) => (t < 8.5 ? 1 : p(t, a('satu tali') + (i === 2 ? -0.4 : 0.9 + i * 0.12), a('satu tali') + (i === 2 ? 0 : 1.3 + i * 0.12)));
  return (
    <g>
      {/* machine room */}
      <rect x={ROOM.x0} y={ROOM.y0} width={ROOM.x1 - ROOM.x0} height={ROOM.y1 - ROOM.y0} {...line(always, 3)} />
      <text x={ROOM.x0 + 20} y={ROOM.y0 + 40} fill={B.line2} style={{fontFamily: MONO, fontWeight: 600, fontSize: 24}}>RUANG MESIN</text>
      <circle cx={SHEAVE.x} cy={SHEAVE.y} r={SHEAVE.r} {...line(always, 3)} />
      <circle cx={SHEAVE.x} cy={SHEAVE.y} r={SHEAVE.r - 26} fill="none" stroke={B.line2} strokeWidth={1.5} />
      <g transform={`rotate(${t * 20} ${SHEAVE.x} ${SHEAVE.y})`}>{[0, 60, 120].map((d) => <line key={d} x1={SHEAVE.x - 120} y1={SHEAVE.y} x2={SHEAVE.x + 120} y2={SHEAVE.y} stroke={B.line2} strokeWidth={2} transform={`rotate(${d} ${SHEAVE.x} ${SHEAVE.y})`} />)}</g>
      <rect x={790} y={370} width={100} height={150} {...line(always, 3)} />
      <text x={840} y={455} textAnchor="middle" fill={B.line2} style={{fontFamily: MONO, fontWeight: 600, fontSize: 20}}>MOTOR</text>
      <circle cx={GOV.x} cy={GOV.y} r={GOV.r} {...line(always, 3)} />
      <g transform={`rotate(${govSpin * 57} ${GOV.x} ${GOV.y})`}><line x1={GOV.x - 40} y1={GOV.y} x2={GOV.x + 40} y2={GOV.y} stroke={B.yellow} strokeWidth={4} /><circle cx={GOV.x + 40} cy={GOV.y} r={8} fill={B.yellow} /><circle cx={GOV.x - 40} cy={GOV.y} r={8} fill={B.yellow} /></g>
      {/* walls with concrete hatch */}
      <Hatch x={WALL.l - 50} y={WALL.top} w={50} h={WALL.pit - WALL.top + 60} q={t < 5.6 ? 1 : reveal} id="wl" />
      <Hatch x={WALL.r} y={WALL.top} w={50} h={WALL.pit - WALL.top + 60} q={t < 5.6 ? 1 : reveal} id="wr" />
      <Hatch x={WALL.l - 50} y={WALL.pit} w={WALL.r - WALL.l + 100} h={60} q={t < 5.6 ? 1 : reveal} id="wp" />
      {/* landings */}
      {[1000, 1600, 2200, 2800, 3400, 4000].map((y, i) => (
        <g key={y}>
          <line x1={WALL.l} y1={y} x2={WALL.r} y2={y} stroke={B.line2} strokeWidth={1.4} strokeDasharray="14 10" opacity={0.6} />
          <text x={WALL.l - 70} y={y - 10} textAnchor="end" fill={B.line2} style={{fontFamily: MONO, fontWeight: 600, fontSize: 30}}>LT {6 - i}</text>
        </g>
      ))}
      {/* rails */}
      {[CAR.x0 - 26, CAR.x1 + 16, CW.x0 - 22, CW.x1 + 12].map((x) => <rect key={x} x={x} y={WALL.top} width={10} height={WALL.pit - WALL.top} fill={B.face3} stroke={B.line2} strokeWidth={1.5} />)}
      {/* governor rope */}
      <line x1={GOV.x - GOV.r} y1={GOV.y} x2={GOV.x - GOV.r} y2={WALL.pit - 60} stroke={B.cyan} strokeWidth={2} strokeDasharray="2 6" />
      <circle cx={GOV.x - GOV.r + 20} cy={WALL.pit - 60} r={20} {...line(1, 2, B.cyan)} />
      <line x1={GOV.x - GOV.r} y1={carTop + 60} x2={CAR.x0} y2={carTop + 60} stroke={B.cyan} strokeWidth={3} />
      {/* hoist ropes: over the sheave, down to the counterweight */}
      {ROPES.map((d, i) => {
        const xl = SHEAVE.x - SHEAVE.r + 1 + d * 0.0 + (i - 2) * 6, xr = SHEAVE.x + SHEAVE.r - 1 + (i - 2) * 6;
        const rr = SHEAVE.r + (i - 2) * 0;
        const carX = 450 + (i - 2) * 6 * 1.0;
        const q = ropeQ(i);
        const hot = t > a('dan tiap') - 0.1 && t < a('kedua') && i === 2;
        const col = hot ? B.yellow : B.line;
        const over = `M ${xl} ${SHEAVE.y} A ${rr} ${rr} 0 0 1 ${xr} ${SHEAVE.y}`;
        const cutY = 1450;
        return (
          <g key={i}>
            <path d={over} {...line(q, 2.5, col)} />
            <line x1={xr} y1={SHEAVE.y} x2={xr} y2={CW.top} stroke={col} strokeWidth={2.5} {...drawOn(q)} />
            {!cut ? (
              <line x1={carX} y1={SHEAVE.y} x2={carX} y2={carTop} stroke={col} strokeWidth={hot ? 4 : 2.5} {...drawOn(q)} />
            ) : (
              <>
                <line x1={xl} y1={SHEAVE.y} x2={xl} y2={cutY - 20} stroke={B.line} strokeWidth={2.5} />
                <path d={`M ${carX} ${carTop} C ${carX + (i - 2) * 30 * slack} ${carTop - 300}, ${carX - (i - 2) * 40 * slack} ${carTop - 520}, ${carX + (i - 2) * 22 * slack} ${cutY + 30 + 400 * slack}`} fill="none" stroke={B.line} strokeWidth={2.5} opacity={0.75} />
              </>
            )}
          </g>
        );
      })}
      {hookCut > 0 && <BreakMark x={450} y={1900} w={110} q={hookCut} />}
      {t >= cutAt && <BreakMark x={450} y={1450} w={120} q={p(t, cutAt, cutAt + 0.5)} />}
      {/* counterweight */}
      <Hatch x={CW.x0} y={CW.top} w={CW.x1 - CW.x0} h={CW.h} q={t < 17 ? 1 : p(t, a('pemberat') - 0.2, a('pemberat') + 0.5)} id="cw" />
      {/* car (sling + cab) */}
      <g>
        <rect x={CAR.x0 - 16} y={carTop - 30} width={CAR.x1 - CAR.x0 + 32} height={30} fill={B.face2} stroke={B.line} strokeWidth={3} />
        <rect x={CAR.x0} y={carTop} width={CAR.x1 - CAR.x0} height={CAR.h} fill={B.face3} stroke={B.line} strokeWidth={3.5} />
        <line x1={(CAR.x0 + CAR.x1) / 2} y1={carTop + 20} x2={(CAR.x0 + CAR.x1) / 2} y2={carTop + CAR.h - 20} stroke={B.line2} strokeWidth={1.5} strokeDasharray="10 8" />
        <rect x={CAR.x0 - 22} y={carTop + CAR.h - 40} width={20} height={36} fill={t > a('klak') ? B.yellow : B.face1} stroke={B.line} strokeWidth={2} />
        <rect x={CAR.x1 + 2} y={carTop + CAR.h - 40} width={20} height={36} fill={t > a('klak') ? B.yellow : B.face1} stroke={B.line} strokeWidth={2} />
        <Figure x={400} base={carTop + CAR.h - 8} h={250} q={1} />
        {t > a('yang perlu') && <Figure x={500} base={carTop + CAR.h - 8} h={232} q={p(t, a('yang perlu'), a('yang perlu') + 0.6)} />}
      </g>
      {/* buffers in the pit */}
      {[[450, 1], [750, 0.8]].map(([x, s], i) => {
        const comp = i === 0 ? 0.35 * p(t, a('peredam'), a('peredam') + 0.4) * (1 - p(t, a('peredam') + 0.6, a('peredam') + 1.3)) : 0;
        const top = WALL.pit - 200 * (s as number) * (1 - comp);
        const zig = Array.from({length: 9}, (_, k) => `${k % 2 ? (x as number) + 34 : (x as number) - 34} ${top + 30 + (k * (WALL.pit - top - 50)) / 8}`).join(' L ');
        return (
          <g key={i}>
            <rect x={(x as number) - 50} y={top} width={100} height={22} fill={B.face1} stroke={B.line} strokeWidth={2.5} />
            <path d={`M ${zig}`} fill="none" stroke={i === 0 && t > a('peredam') - 0.2 ? B.yellow : B.line} strokeWidth={3} />
          </g>
        );
      })}
    </g>
  );
};

// ---------- detail panels ----------
const DetailA: React.FC<{t: number}> = ({t}) => {
  const x = 1180, y = 1620, w = 980, h = 1060;
  const q = p(t, a('biasanya') - 0.45, a('biasanya') + 0.3);
  const strand = p(t, a('kabel baja') - 0.1, a('kabel baja') + 0.6);
  const one = p(t, a('dan tiap') - 0.1, a('dan tiap') + 0.4);
  const full = p(t, a('beban penuh') - 0.2, a('beban penuh') + 0.3);
  return (
    <Panel x={x} y={y} w={w} h={h} title="DETAIL A — KABEL BAJA" q={q}>
      {/* section through one rope */}
      <g transform={`translate(${x + 230} ${y + 330})`} opacity={strand}>
        <circle r={150} {...line(strand, 3)} />
        {Array.from({length: 7}).map((_, i) => {
          const ang = (i / 6) * Math.PI * 2;
          const cx = i === 6 ? 0 : Math.cos(ang) * 92, cy = i === 6 ? 0 : Math.sin(ang) * 92;
          return (
            <g key={i}>
              <circle cx={cx} cy={cy} r={50} fill={B.face2} stroke={B.line} strokeWidth={2} opacity={p(strand, i * 0.1, i * 0.1 + 0.3)} />
              {Array.from({length: 6}).map((__, k) => <circle key={k} cx={cx + Math.cos((k / 6) * Math.PI * 2) * 28} cy={cy + Math.sin((k / 6) * Math.PI * 2) * 28} r={11} fill="none" stroke={B.line2} strokeWidth={1.5} opacity={p(strand, 0.5, 1)} />)}
            </g>
          );
        })}
        <text y={210} textAnchor="middle" fill={B.line2} style={{fontFamily: MONO, fontWeight: 600, fontSize: 24}}>POTONGAN 1 KABEL</text>
      </g>
      {/* one rope carries the whole car */}
      <g transform={`translate(${x + 690} ${y + 120})`}>
        {[-60, -30, 0, 30, 60].map((d, i) => (
          <line key={d} x1={d} y1={20} x2={d} y2={520} stroke={i === 2 ? (one > 0 ? B.yellow : B.line) : B.line2} strokeWidth={i === 2 ? 5 : 2.5}
            strokeDasharray={i !== 2 && one > 0 ? '8 10' : undefined} opacity={i !== 2 ? 1 - 0.55 * one : 1} />
        ))}
        <rect x={-120} y={520} width={240} height={260} fill={B.face3} stroke={B.line} strokeWidth={3} />
        <text x={0} y={640} textAnchor="middle" fill={B.line} opacity={full} style={{fontFamily: MONO, fontWeight: 700, fontSize: 26}}>BEBAN</text>
        <text x={0} y={676} textAnchor="middle" fill={B.line} opacity={full} style={{fontFamily: MONO, fontWeight: 700, fontSize: 26}}>PENUH</text>
      </g>
      <g opacity={p(t, a('sendirian') - 0.2, a('sendirian') + 0.2)} transform={`translate(${x + 60} ${y + 900})`}>
        <rect width={860} height={110} fill={B.deep} stroke={B.yellow} strokeWidth={3} />
        <text x={30} y={70} fill={B.yellow} style={{fontFamily: MONO, fontWeight: 700, fontSize: 38}}>1 KABEL ≥ BEBAN PENUH  ✓</text>
      </g>
    </Panel>
  );
};

const DetailB: React.FC<{t: number}> = ({t}) => {
  const x = 1180, y = 2760, w = 980, h = 1000;
  const q = p(t, a('jadi mesinnya') - 0.45, a('jadi mesinnya') + 0.25);
  const bars = [
    {label: 'BERAT KABIN', v: 1, at: a('seluruh kabin') - 0.2, col: B.line},
    {label: 'PEMBERAT', v: 0.86, at: a('seluruh kabin') + 0.3, col: B.line2},
    {label: 'SELISIH', v: 0.14, at: a('selisih') - 0.1, col: B.yellow},
  ];
  return (
    <Panel x={x} y={y} w={w} h={h} title="DETAIL B — PENYEIMBANG" q={q}>
      {bars.map((b, i) => {
        const bq = p(t, b.at, b.at + 0.45);
        const bx = x + 110 + i * 290, base = y + 820, bh = 600 * b.v * bq;
        return (
          <g key={b.label}>
            <rect x={bx} y={base - bh} width={180} height={bh} fill={i === 2 ? `${B.yellow}55` : B.face2} stroke={b.col} strokeWidth={3} />
            <text x={bx + 90} y={base + 50} textAnchor="middle" fill={b.col} opacity={bq} style={{fontFamily: MONO, fontWeight: 700, fontSize: 25}}>{b.label}</text>
          </g>
        );
      })}
      <text x={x + 690} y={y + 160} textAnchor="middle" fill={B.yellow} opacity={p(t, a('beratnya'), a('beratnya') + 0.3)} style={{fontFamily: HEAD, fontWeight: 700, fontSize: 54}}>mesin = selisih</text>
      <Dim a={[x + 640, y + 820]} b={[x + 640, y + 820 - 84]} text="" q={p(t, a('beratnya'), a('beratnya') + 0.3)} color={B.yellow} />
    </Panel>
  );
};

const DetailC: React.FC<{t: number}> = ({t}) => {
  const x = -1100, y = 1820, w = 980, h = 1120;
  const q = p(t, 31.2, 31.8);
  const clamp = p(t, a('klak') - 0.05, a('klak') + 0.12);
  const fast = t < a('klak') + 0.2 ? 1 : Math.max(0, 1 - (t - a('klak') - 0.2) * 2);
  const spin = t * 18 * fast;
  const fly = 40 + 34 * fast;
  return (
    <Panel x={x} y={y} w={w} h={h} title="DETAIL C — GOVERNOR + REM" q={q}>
      {/* governor: flyweights swing out with speed */}
      <g transform={`translate(${x + 250} ${y + 330})`}>
        <circle r={150} {...line(1, 3)} />
        <g transform={`rotate(${spin * 57})`}>
          <line x1={-fly - 60} y1={0} x2={fly + 60} y2={0} stroke={B.line} strokeWidth={4} />
          <circle cx={fly + 60} cy={0} r={22} fill={B.yellow} /><circle cx={-fly - 60} cy={0} r={22} fill={B.yellow} />
        </g>
        <text y={210} textAnchor="middle" fill={B.line2} style={{fontFamily: MONO, fontWeight: 600, fontSize: 24}}>RODA PENGAWAS</text>
        <text y={244} textAnchor="middle" fill={fast > 0.5 ? B.red : B.line2} style={{fontFamily: MONO, fontWeight: 700, fontSize: 24}}>{fast > 0.5 ? 'KECEPATAN: BAHAYA' : 'KECEPATAN: 0'}</text>
      </g>
      {/* safety gear: wedges climb and bite the rail on both sides */}
      <g transform={`translate(${x + 690} ${y + 560})`}>
        <rect x={-30} y={-400} width={60} height={760} fill={t > a('klak') ? `${B.yellow}44` : B.face3} stroke={t > a('menjepit') ? B.yellow : B.line} strokeWidth={3} />
        {[-1, 1].map((sd) => {
          const off = 60 - 30 * clamp;
          return (
            <path key={sd} d={`M ${sd * off} ${-80 * (1 - clamp)} L ${sd * (off + 90)} ${-150 - 80 * (1 - clamp)} L ${sd * (off + 90)} ${150 - 80 * (1 - clamp)} Z`} fill={clamp > 0.9 ? B.yellow : B.face1} stroke={B.line} strokeWidth={3} />
          );
        })}
        {t > a('menjepit') - 0.1 && [-1, 1].map((sd) => (
          <g key={sd} opacity={p(t, a('menjepit') - 0.1, a('menjepit') + 0.2)}>
            <path d={`M ${sd * 260} 0 L ${sd * 150} 0`} stroke={B.yellow} strokeWidth={5} />
            <path d={`M ${sd * 172} -18 L ${sd * 150} 0 L ${sd * 172} 18`} fill="none" stroke={B.yellow} strokeWidth={5} />
          </g>
        ))}
        <text x={70} y={340} fill={B.line2} style={{fontFamily: MONO, fontWeight: 600, fontSize: 24}}>REL</text>
      </g>
      <text x={x + 60} y={y + 1010} fill={B.yellow} opacity={p(t, a('klak'), a('klak') + 0.15)} style={{fontFamily: HEAD, fontWeight: 700, fontSize: 72}}>KLAK!</text>
      <Typed x={x + 60} y={y + 1080} text="REM MENJEPIT KEDUA SISI" t={t} start={a('kedua sisi') - 0.3} size={28} color={B.line} />
    </Panel>
  );
};

const DetailE: React.FC<{t: number}> = ({t}) => {
  const x = 1180, y = 3980, w = 980, h = 760;
  const q = p(t, a('di dasar') - 0.3, a('di dasar') + 0.3);
  const comp = p(t, a('peredam'), a('peredam') + 0.4) * (1 - p(t, a('peredam') + 0.6, a('peredam') + 1.3));
  const top = y + 230 + 140 * comp;
  const zig = Array.from({length: 11}, (_, k) => `${k % 2 ? x + 560 : x + 420} ${top + 40 + (k * (y + 640 - top - 40)) / 10}`).join(' L ');
  return (
    <Panel x={x} y={y} w={w} h={h} title="DETAIL E — PEREDAM" q={q}>
      <rect x={x + 380} y={top - 30} width={220} height={36} fill={B.face1} stroke={B.line} strokeWidth={3} />
      <path d={`M ${zig}`} fill="none" stroke={B.yellow} strokeWidth={5} />
      <rect x={x + 340} y={y + 640} width={300} height={40} fill={B.face3} stroke={B.line} strokeWidth={3} />
      <Typed x={x + 60} y={y + 160} text="JAGA-JAGA TERAKHIR" t={t} start={a('jaga-jaga') - 0.2} size={30} color={B.yellow} />
    </Panel>
  );
};

// ---------- 1854 archive sheet ----------
const OtisSheet: React.FC<{t: number}> = ({t}) => {
  const Y = 5400;
  const tYear = a('tahun');
  const q = p(t, tYear - 0.6, tYear + 0.2);
  const tower = p(t, tYear, tYear + 1.4);
  const man = p(t, a('dia berdiri') - 0.1, a('dia berdiri') + 0.6);
  const axe = p(t, a('terus nyuruh') - 0.1, a('terus nyuruh') + 0.5);
  const chop = p(t, a('dipotong') - 0.05, a('dipotong') + 0.12);
  const crowd = p(t, a('penonton') - 0.1, a('penonton') + 0.6);
  const drop = p(t, a('platformnya') - 0.05, a('platformnya') + 0.6, Easing.in(Easing.quad)) * 34;
  const stop = t > a('berhenti') - 0.1;
  const py = Y + 980 + drop;
  const postL = 300, postR = 780;
  return (
    <g opacity={q > 0 ? 1 : 0}>
      <rect x={60} y={Y} width={960} height={1700} fill={B.deep} fillOpacity={0.35} stroke={B.line} strokeWidth={3} {...drawOn(q)} />
      <Typed x={96} y={Y + 70} text="ARSIP · NEW YORK, 1854" t={t} start={tYear - 0.2} size={34} color={B.cyan} spacing={2} />
      <Typed x={96} y={Y + 118} text="DEMO REM PENGAMAN — E. G. OTIS" t={t} start={a('elisha')} size={26} color={B.line2} />
      {/* tower with toothed rails */}
      {[postL, postR].map((x, i) => (
        <g key={x}>
          <rect x={x - 18} y={Y + 300} width={36} height={1120} {...line(tower, 3)} />
          {Array.from({length: 26}).map((_, k) => {
            const ty = Y + 330 + k * 42;
            const dir = i === 0 ? 1 : -1;
            const hot = stop && Math.abs(ty - py) < 30;
            return <path key={k} d={`M ${x + 18 * dir} ${ty} l ${16 * dir} 14 l ${-16 * dir} 14`} fill="none" stroke={hot ? B.yellow : B.line2} strokeWidth={hot ? 4 : 2} opacity={p(tower, 0.4 + k * 0.02, 0.6 + k * 0.02)} />;
          })}
        </g>
      ))}
      <line x1={postL - 40} y1={Y + 300} x2={postR + 40} y2={Y + 300} {...line(tower, 4)} />
      <circle cx={540} cy={Y + 300} r={34} {...line(tower, 3)} />
      {/* rope */}
      {chop < 1 ? (
        <line x1={540} y1={Y + 334} x2={540} y2={py - 120} stroke={B.line} strokeWidth={3} {...drawOn(tower)} />
      ) : (
        <>
          <line x1={540} y1={Y + 334} x2={540} y2={Y + 600} stroke={B.line} strokeWidth={3} />
          <path d={`M 540 ${py - 120} C 520 ${py - 220}, 570 ${py - 300}, 548 ${Y + 680}`} fill="none" stroke={B.line} strokeWidth={3} opacity={0.6} />
        </>
      )}
      {chop > 0 && <BreakMark x={540} y={Y + 640} w={110} q={chop} />}
      {/* platform with its wagon spring and pawls */}
      <g>
        <path d={`M ${postL + 26} ${py - 120} Q 540 ${py - (stop ? 150 : 190)} ${postR - 26} ${py - 120}`} fill="none" stroke={B.yellow} strokeWidth={4} {...drawOn(tower)} />
        <rect x={postL + 30} y={py} width={postR - postL - 60} height={26} fill={B.face2} stroke={B.line} strokeWidth={3} opacity={tower} />
        <line x1={postL + 30} y1={py} x2={postL + 30} y2={py - 120} stroke={B.line} strokeWidth={3} opacity={tower} />
        <line x1={postR - 30} y1={py} x2={postR - 30} y2={py - 120} stroke={B.line} strokeWidth={3} opacity={tower} />
        <line x1={postL + 30} y1={py - 120} x2={postR - 30} y2={py - 120} stroke={B.line} strokeWidth={3} opacity={tower} />
        <Figure x={540} base={py} h={250} q={man} />
        <path d={`M 516 ${py - 238} L 564 ${py - 238} M 524 ${py - 238} L 526 ${py - 278} L 556 ${py - 278} L 558 ${py - 238}`} fill="none" stroke={B.line} strokeWidth={3} opacity={man} />
        {stop && <text x={540} y={py + 80} textAnchor="middle" fill={B.yellow} style={{fontFamily: MONO, fontWeight: 700, fontSize: 34}}>BERHENTI ✓</text>}
      </g>
      {drop > 1 && <Dim a={[postR + 120, Y + 980]} b={[postR + 120, py]} text="" q={1} color={B.yellow} />}
      {drop > 1 && <text x={postR + 140} y={Y + 1010} fill={B.yellow} style={{fontFamily: MONO, fontWeight: 700, fontSize: 24}}>BEBERAPA CM</text>}
      {/* axe man */}
      <g opacity={axe}>
        <Figure x={900} base={Y + 1420} h={230} q={axe} />
        <g transform={`rotate(${-70 + 70 * chop} 900 ${Y + 1290})`}>
          <line x1={900} y1={Y + 1290} x2={900} y2={Y + 1130} stroke={B.line} strokeWidth={4} />
          <path d={`M 900 ${Y + 1124} l 46 -22 l 10 58 l -56 -12 Z`} fill={B.face1} stroke={B.line} strokeWidth={3} />
        </g>
      </g>
      {/* the crowd */}
      {Array.from({length: 9}).map((_, i) => <Figure key={i} x={130 + i * 70} base={Y + 1620} h={120 + (rnd(i) * 30)} q={p(crowd, i * 0.06, i * 0.06 + 0.4)} />)}
      <line x1={90} y1={Y + 1420} x2={990} y2={Y + 1420} stroke={B.line2} strokeWidth={2} opacity={tower} />
    </g>
  );
};

// ---------- composition ----------
export const LiftEpisode: React.FC = () => {
  const {t} = useTime();
  const moves: Move[] = [
    {from: 0, to: 4.5, s: 1.0},
    {from: 5.6, to: a('dan alasannya') + 0.9, x: 530, y: 2470, s: 0.28},
    {from: 8.08, to: a('pertama') + 0.2, x: 540, y: 1720, s: 0.75},
    {from: 11.0, to: a('biasanya') + 0.15, x: 1670, y: 2160, s: 1},
    {from: 17.0, to: a('kedua') + 0.2, x: 680, y: 1950, s: 0.9},
    {from: 20.13, to: a('jadi mesinnya') + 0.1, x: 1670, y: 3260, s: 1},
    {from: 24.09, to: a('tapi oke') + 0.2, x: 540, y: 1900, s: 0.8},
    {from: 27.33, to: a('di atas') + 0.1, x: 480, y: 600, s: 1.05},
    {from: 29.77, to: a('begitu kabin') + 0.2, x: 520, y: 2300, s: 0.8},
    {from: 31.2, to: 31.8, x: -610, y: 2420, s: 0.95},
    {from: 35.23, to: a('tahun') + 0.1, x: 540, y: 6392, s: 0.79},
    {from: 49.67, to: a('dan kalau') + 0.3, x: 560, y: 4260, s: 0.8},
    {from: 52.0, to: a('di dasar') + 0.3, x: 1670, y: 4420, s: 1},
    {from: 55.53, to: a('jadi tenang') + 0.3, x: 530, y: 2700, s: 0.26},
    {from: 58.03, to: a('yang perlu') + 0.15, x: 460, y: 2830, s: 1.5},
  ];
  const pose = camera(t, {x: 540, y: 2250, s: 1.25, r: 0}, moves);
  const sh = shake(t, [a('klak') + 0.02, a('dipotong') + 0.1, a('berhenti')], 14);
  const sheet = t > 35.4 && t < 49.8 ? '02' : '01';
  const summary = p(t, a('jadi tenang') + 0.2, a('jadi tenang') + 0.6) * (1 - p(t, 58.0, 58.3));
  return (
    <AbsoluteFill style={{background: B.bg, overflow: 'hidden'}}>
      <StyleFonts />
      <EpisodeAudio vo="audio/vo/blueprint.mp3" words={C.words} music="audio/music/blueprint.wav" bed={0.5} duck={0.16} sfx={BLUEPRINT_SFX} />
      <World pose={pose} offset={sh} h={7400}>
        <svg width={1080} height={7400} style={{position: 'absolute', left: 0, top: 0, overflow: 'visible'}}>
          <defs>
            <pattern id="gmin" width="36" height="36" patternUnits="userSpaceOnUse"><path d="M 36 0 L 0 0 0 36" fill="none" stroke="#ffffff" strokeOpacity="0.07" strokeWidth="1" /></pattern>
            <pattern id="gmaj" width="180" height="180" patternUnits="userSpaceOnUse"><path d="M 180 0 L 0 0 0 180" fill="none" stroke="#ffffff" strokeOpacity="0.15" strokeWidth="1.5" /></pattern>
          </defs>
          <rect x={-3000} y={-2000} width={7200} height={12000} fill="url(#gmin)" />
          <rect x={-3000} y={-2000} width={7200} height={12000} fill="url(#gmaj)" />
          <Shaft t={t} />
          {/* hook labels */}
          <g opacity={p(t, a('kamu bakal'), a('kamu bakal') + 0.2) * (1 - p(t, a('jawabannya'), a('jawabannya') + 0.25))}>
            <path d="M 700 2300 L 700 2560 M 680 2530 L 700 2560 L 720 2530" fill="none" stroke={B.red} strokeWidth={5} />
            <text x={730} y={2470} fill={B.red} style={{fontFamily: HEAD, fontWeight: 700, fontSize: 84}}>JATUH?</text>
          </g>
          <g opacity={p(t, a('hampir') - 0.05, a('hampir') + 0.2) * (1 - p(t, 5.7, 6.0))}>
            <rect x={630} y={2330} width={420} height={170} fill={B.deep} stroke={B.yellow} strokeWidth={3} />
            <text x={660} y={2400} fill={B.yellow} style={{fontFamily: HEAD, fontWeight: 700, fontSize: 60}}>HAMPIR</text>
            <text x={660} y={2470} fill={B.yellow} style={{fontFamily: HEAD, fontWeight: 700, fontSize: 60}}>MUSTAHIL.</text>
          </g>
          <g opacity={1 - p(t, 8.1, 8.4)}><Typed x={540} y={120} text="PENAMPANG LORONG LIFT" t={t} start={a('dan alasannya')} size={130} color={B.cyan} spacing={4} anchor="middle" /></g>
          {/* numbered callouts on the main drawing */}
          <Callout a={[450, 1200]} l={[150, 1120]} n="1" label="KABEL" side="left" t={t} start={a('biasanya') - 0.6} />
          <Callout a={[795, CW.top + 120]} l={[890, CW.top + 40]} n="2" label="PEMBERAT" t={t} start={a('pemberat')} />
          <Callout a={[GOV.x, GOV.y - GOV.r]} l={[140, 180]} n="3" label="GOVERNOR" side="left" t={t} start={a('roda')} color={B.yellow} />
          <Callout a={[CAR.x1 + 22, CAR_STOP + CAR.h - 20]} l={[960, CAR_STOP + CAR.h + 120]} n="4" label="REM" t={t} start={a('rem pengaman')} color={B.yellow} />
          <Callout a={[450, WALL.pit - 180]} l={[960, WALL.pit - 300]} n="5" label="PEREDAM" t={t} start={a('peredam')} color={B.yellow} />
          {t > 24.4 && t < 29.8 && (
            <g opacity={p(t, a('anggap'), a('anggap') + 0.2)}>
              <rect x={600} y={1260} width={460} height={120} fill={B.deep} stroke={B.red} strokeWidth={3} />
              <text x={626} y={1312} fill={B.red} style={{fontFamily: MONO, fontWeight: 700, fontSize: 28}}>SKENARIO TERBURUK:</text>
              <text x={626} y={1352} fill={B.line} style={{fontFamily: MONO, fontWeight: 600, fontSize: 26}}>SEMUA KABEL PUTUS</text>
            </g>
          )}
          {t > a('begitu kabin') && t < a('klak') + 0.2 && Array.from({length: 6}).map((_, i) => (
            <line key={i} x1={CAR.x0 + 20 + i * 44} y1={carTopAt(t) - 60 - (i % 2) * 40} x2={CAR.x0 + 20 + i * 44} y2={carTopAt(t) - 180 - (i % 2) * 60} stroke={B.cyan} strokeWidth={3} opacity={0.7} />
          ))}
          {/* the ending: who you meet inside */}
          <g opacity={p(t, a('ketemu'), a('ketemu') + 0.3)}>
            <Callout a={[400, CAR_STOP + 140]} l={[300, CAR_STOP + 30]} n="A" label="KAMU" side="left" t={t} start={a('ketemu')} />
            <Callout a={[500, CAR_STOP + 150]} l={[580, CAR_STOP + 30]} n="B" label="MANTAN" t={t} start={a('mantan') - 0.1} color={B.red} />
          </g>
          <g opacity={p(t, a('dalamnya'), a('dalamnya') + 0.3)} transform={`translate(250 ${CAR_STOP + CAR.h + 30})`}>
            <rect width={420} height={78} fill={B.deep} stroke={B.red} strokeWidth={3} />
            <text x={24} y={52} fill={B.red} style={{fontFamily: MONO, fontWeight: 700, fontSize: 30}}>⚠ RISIKO TERBESAR</text>
          </g>
          <DetailA t={t} />
          <DetailB t={t} />
          <DetailC t={t} />
          <DetailE t={t} />
          <OtisSheet t={t} />
        </svg>
      </World>
      {/* summary checklist (screen-fixed) */}
      {summary > 0 && (
        <div style={{position: 'absolute', left: 90, right: 90, top: 200, opacity: summary, background: `${B.deep}ee`, border: `2px solid ${B.yellow}`, padding: '22px 30px',
          fontFamily: MONO, fontWeight: 700, fontSize: 32, color: B.line, lineHeight: 1.5}}>
          {['KABEL BAJA', 'PEMBERAT', 'GOVERNOR', 'REM PENGAMAN', 'PEREDAM'].map((s, i) => (
            <div key={s} style={{opacity: p(t, a('jadi tenang') + 0.2 + i * 0.18, a('jadi tenang') + 0.4 + i * 0.18)}}><span style={{color: B.yellow}}>✓</span> {s}</div>
          ))}
        </div>
      )}
      <div style={{position: 'absolute', left: 96, right: 96, top: 96, height: 62, borderTop: `2px solid ${B.line2}`, borderBottom: `2px solid ${B.line2}`,
        display: 'flex', alignItems: 'center', justifyContent: 'space-between', fontFamily: MONO, fontWeight: 600, fontSize: 22, color: B.line2, letterSpacing: 2}}>
        <span>LIFT · CARA KERJA</span>
        <span>LEMBAR {sheet}/02</span>
        <span style={{color: B.yellow, border: `2px solid ${B.yellow}`, padding: '3px 12px'}}>CARA KERJA</span>
      </div>
      <Caption t={t} pages={PAGES} />
      <AbsoluteFill style={{backgroundImage: GRAIN, backgroundSize: '400px', opacity: 0.07, mixBlendMode: 'overlay'}} />
      <AbsoluteFill style={{background: `radial-gradient(ellipse 110% 80% at 50% 45%, transparent 60%, ${B.deep}cc)`}} />
    </AbsoluteFill>
  );
};

const s = (t: number, src: string, vol = 0.55): Sfx => ({t, src: `audio/sfx/blueprint/${src}.wav`, vol});
export const BLUEPRINT_SFX: Sfx[] = [
  s(a('putus') - 0.02, 'snap', 0.6), s(a('kamu bakal'), 'drop', 0.45), s(a('hampir'), 'blip', 0.5),
  s(5.6, 'whoosh', 0.4), s(a('dan alasannya'), 'scribe', 0.45),
  s(8.1, 'whoosh', 0.35), s(a('satu tali') - 0.4, 'scribe', 0.35), s(11.0, 'whoosh', 0.35), s(a('biasanya'), 'blip', 0.45), s(a('kabel baja'), 'scribe', 0.4),
  s(a('dan tiap'), 'blip', 0.45), s(a('sendirian') - 0.2, 'confirm', 0.55),
  s(17.0, 'whoosh', 0.35), s(a('pemberat'), 'blip', 0.45), s(20.13, 'whoosh', 0.35), s(a('seluruh kabin') - 0.2, 'scribe', 0.35), s(a('selisih') - 0.1, 'confirm', 0.5),
  s(24.1, 'whoosh', 0.35), s(a('anggap'), 'alarm', 0.35), s(a('anggap') + 0.6, 'snap', 0.6), s(27.33, 'whoosh', 0.35), s(a('roda'), 'blip', 0.45),
  s(a('begitu kabin'), 'whir', 0.55), s(31.2, 'whoosh', 0.35), s(a('klak') - 0.02, 'clank', 0.85), s(a('menjepit'), 'confirm', 0.45),
  s(35.23, 'whoosh', 0.4), s(a('tahun'), 'scribe', 0.45), s(a('dia berdiri'), 'blip', 0.4), s(a('dipotong') + 0.05, 'chop', 0.75),
  s(a('platformnya'), 'drop', 0.4), s(a('berhenti') - 0.05, 'clank', 0.6), s(a('berhenti') + 0.2, 'confirm', 0.5),
  s(49.67, 'whoosh', 0.4), s(52.0, 'whoosh', 0.35), s(a('peredam'), 'spring', 0.6),
  s(55.53, 'whoosh', 0.4), ...[0, 1, 2, 3, 4].map((i) => s(a('jadi tenang') + 0.2 + i * 0.18, 'blip', 0.35)),
  s(58.03, 'whoosh', 0.4), s(a('mantan') - 0.1, 'alarm', 0.4), s(a('dalamnya') + 0.1, 'blip', 0.45),
];
