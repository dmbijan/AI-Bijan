// REFERENCE DEMO (AI BIJAN MOGRAPH v1.6) — read for structure only: stations, camera moves, word-pinned beats, SFX cues.
// Do NOT copy this story, its text or its layout into a buyer video. Written in the author's demo workspace, so two
// imports differ from the starter: `makeCues(words)` ≈ the starter's `at()/endOf()` from src/lib/words.ts, and
// `EpisodeAudio` ≈ Main.tsx audio (musicFile + auto-duck) + <SfxTrack /> fed by src/audio-plan.json sfxCues.
import React from 'react';
import {AbsoluteFill} from 'remotion';
import {makeCues} from '../../lib/cues';
import {drawOn, p, pagesFor, pb, pio, rnd, roughEllipse, roughLine, roughRect, spline, StyleFonts, useTime} from '../../styles/shared';
import {Boil, Caption, HAIR, HAND, HEAD, Page, pen, PENCIL, Pt, S, Stick, Swipe, Write} from '../../styles/sketsa/kit';
import {EpisodeAudio, Sfx} from '../../styles/audio';
import wordsFile from './words.json';

// SKETSA episode — "Lampu Sorot" (the spotlight effect). Five notebook pages, each flipped up over the last:
// the feeling → the experiment → guess vs reality → the name + why → tonight.

const C = makeCues(wordsFile);
const PAGES = pagesFor(C.words);
export const SKETSA_DURATION = C.duration + 1.4;
const a = C.at;

// ---------- doodle kit for this episode ----------
const Spotlight: React.FC<{lamp: Pt; target: Pt; spread: number; q: number; power?: number; seed?: number; lampScale?: number}> = ({lamp, target, spread, q, power = 1, seed = 1, lampScale = 1}) => {
  if (q <= 0) return null;
  const [lx, ly] = lamp, [tx, ty] = target;
  const ang = Math.atan2(tx - lx, ty - ly);
  const deg = (-ang * 180) / Math.PI;
  const cone = `M ${lx - 26 * lampScale} ${ly + 30 * lampScale} L ${tx - spread} ${ty} Q ${tx} ${ty + 40} ${tx + spread} ${ty} L ${lx + 26 * lampScale} ${ly + 30 * lampScale} Z`;
  return (
    <g>
      <path d={cone} fill={S.yellow} opacity={0.5 * q * power} style={{mixBlendMode: 'multiply'}} />
      <path d={`${roughLine([lx - 26 * lampScale, ly + 30 * lampScale], [tx - spread, ty], seed, 4)} ${roughLine([lx + 26 * lampScale, ly + 30 * lampScale], [tx + spread, ty], seed + 3, 4)}`} {...pen(q, 3, '#E0B400')} />
      <path d={roughEllipse(tx, ty + 6, spread, 26, seed + 5, 0.04)} {...pen(q, 3, '#E0B400')} />
      <g transform={`translate(${lx} ${ly}) rotate(${deg}) scale(${lampScale})`}>
        <path d="M -30 -50 L 30 -50 L 44 30 L -44 30 Z" fill={S.paper} stroke={S.ink} strokeWidth={5} strokeLinejoin="round" />
        <path d="M -44 30 L 44 30" {...pen(1, 7, S.ink)} />
        <path d="M 0 -50 L 0 -90" {...pen(1, 5, S.ink)} />
      </g>
    </g>
  );
};

const Bubble: React.FC<{x: number; y: number; w: number; h: number; q: number; seed: number; tail?: Pt; children?: React.ReactNode; thought?: boolean}> = ({x, y, w, h, q, seed, tail, children, thought = false}) => {
  if (q <= 0) return null;
  const s = 0.6 + 0.4 * q;
  return (
    <g transform={`translate(${x + w / 2} ${y + h / 2}) scale(${s}) translate(${-x - w / 2} ${-y - h / 2})`} opacity={Math.min(1, q * 2)}>
      <ellipse cx={x + w / 2} cy={y + h / 2} rx={w / 2} ry={h / 2} fill={S.paper} />
      <path d={roughEllipse(x + w / 2, y + h / 2, w / 2, h / 2, seed, 0.04)} {...pen(1, 4.5)} />
      {tail && !thought && <path d={`M ${x + w / 2 - 20} ${y + h - 6} L ${tail[0]} ${tail[1]} L ${x + w / 2 + 16} ${y + h - 10}`} fill={S.paper} stroke={S.ink} strokeWidth={4.5} strokeLinejoin="round" />}
      {tail && thought && [0.35, 0.65].map((k, i) => (
        <path key={i} d={roughEllipse(x + w / 2 + (tail[0] - x - w / 2) * k, y + h + (tail[1] - y - h) * k, 14 - i * 5, 12 - i * 4, seed + i)} {...pen(1, 4)} fill={S.paper} />
      ))}
      {children}
    </g>
  );
};

/** Hand-drawn pie chart; `part` of it hatched with marker strokes. */
const Pie: React.FC<{cx: number; cy: number; r: number; part: number; q: number; fill: number; color: string; seed: number; id: string}> = ({cx, cy, r, part, q, fill, color, seed, id}) => {
  const end = -Math.PI / 2 + Math.PI * 2 * part * fill;
  const large = part * fill > 0.5 ? 1 : 0;
  const wedge = fill > 0 ? `M ${cx} ${cy} L ${cx} ${cy - r} A ${r} ${r} 0 ${large} 1 ${cx + Math.cos(end) * r} ${cy + Math.sin(end) * r} Z` : '';
  return (
    <g>
      <clipPath id={id}><path d={wedge} /></clipPath>
      <g clipPath={`url(#${id})`}>
        {Array.from({length: 16}).map((_, i) => (
          <path key={i} d={roughLine([cx - r + i * (2 * r / 15) - 40, cy + r], [cx - r + i * (2 * r / 15) + 40, cy - r], seed + i, 3)} stroke={color} strokeWidth={26} strokeLinecap="round" fill="none" opacity={0.75} />
        ))}
      </g>
      <path d={roughEllipse(cx, cy, r, r, seed, 0.03)} {...pen(q, 6)} />
      {fill > 0 && <path d={`M ${cx} ${cy - r} L ${cx} ${cy} L ${cx + Math.cos(end) * r} ${cy + Math.sin(end) * r}`} {...pen(1, 4.5)} />}
    </g>
  );
};

/** A small head that either looks at the student ("!") or at their own phone. */
const Head: React.FC<{x: number; y: number; q: number; looks: boolean; seed: number}> = ({x, y, q, looks, seed}) => {
  if (q <= 0) return null;
  return (
    <g opacity={Math.min(1, q * 2)}>
      <path d={roughEllipse(x, y, 30, 31, seed, 0.04)} {...pen(1, 4)} fill={S.paper} />
      {looks ? (
        <g>
          <circle cx={x - 14} cy={y - 4} r={4} fill={S.ink} /><circle cx={x + 6} cy={y - 4} r={4} fill={S.ink} />
          <text x={x + 26} y={y - 30} fill={S.coral} style={{fontFamily: HEAD, fontSize: 44}}>!</text>
        </g>
      ) : (
        <g>
          <path d={`M ${x - 14} ${y + 4} q 5 4 10 0 M ${x + 4} ${y + 4} q 5 4 10 0`} {...pen(1, 3)} />
          <rect x={x - 16} y={y + 30} width={32} height={46} rx={6} fill={S.blueLight} stroke={S.ink} strokeWidth={3.5} />
        </g>
      )}
    </g>
  );
};

const Cheeks: React.FC<{x: number; y: number; q: number}> = ({x, y, q}) =>
  q > 0 ? (
    <g opacity={q}>
      {[-1, 1].map((s) => <path key={s} d={`M ${x + s * 22 - 8} ${y + 8} l 6 -6 M ${x + s * 22} ${y + 8} l 6 -6`} {...pen(1, 3.5, S.coral)} />)}
      <path d={`M ${x + 40} ${y - 26} q 8 14 0 20 q -8 -6 0 -20`} {...pen(1, 3, S.blue)} />
    </g>
  ) : null;

/** The embarrassing T-shirt: a goofy face with sparkles across the chest. */
const Shirt: React.FC<{x: number; y: number; q: number}> = ({x, y, q}) =>
  q > 0 ? (
    <g opacity={Math.min(1, q * 2)}>
      <path d={`M ${x - 56} ${y} L ${x - 24} ${y - 14} Q ${x} ${y} ${x + 24} ${y - 14} L ${x + 56} ${y} L ${x + 78} ${y + 34} L ${x + 50} ${y + 46} L ${x + 46} ${y + 120} L ${x - 46} ${y + 120} L ${x - 50} ${y + 46} L ${x - 78} ${y + 34} Z`}
        fill="#FFB8A8" stroke={S.ink} strokeWidth={4.5} strokeLinejoin="round" />
      <path d={roughEllipse(x, y + 58, 26, 26, 7)} {...pen(1, 3.5)} />
      <path d={`M ${x - 10} ${y + 52} l 0 2 M ${x + 10} ${y + 52} l 0 2 M ${x - 12} ${y + 66} Q ${x} ${y + 78} ${x + 12} ${y + 66}`} {...pen(1, 3.5)} />
      {[[-34, 30], [36, 36], [-30, 96]].map(([dx, dy], i) => <path key={i} d={`M ${x + dx} ${y + dy - 9} L ${x + dx} ${y + dy + 9} M ${x + dx - 9} ${y + dy} L ${x + dx + 9} ${y + dy}`} {...pen(1, 3, S.coral)} />)}
    </g>
  ) : null;

const Bed: React.FC<{x: number; y: number; q: number; awake: boolean; smile: number}> = ({x, y, q, awake, smile}) => (
  <g>
    <path d={roughRect(x, y, 440, 120, 5)} {...pen(q, 5)} />
    <path d={`${roughLine([x, y + 120], [x, y + 180], 7)} ${roughLine([x + 440, y + 120], [x + 440, y + 180], 8)} ${roughLine([x, y - 90], [x, y + 10], 9)}`} {...pen(q, 5)} />
    <path d={roughEllipse(x + 80, y - 22, 64, 26, 11)} {...pen(q, 4.5)} />
    <path d={roughEllipse(x + 100, y - 52, 34, 33, 12)} {...pen(q, 5)} fill={S.paper} />
    {awake ? (
      <g><circle cx={x + 88} cy={y - 56} r={5} fill={S.ink} /><circle cx={x + 112} cy={y - 56} r={5} fill={S.ink} /></g>
    ) : (
      <path d={`M ${x + 80} ${y - 54} q 7 6 14 0 M ${x + 104} ${y - 54} q 7 6 14 0`} {...pen(1, 3.5)} />
    )}
    <path d={`M ${x + 90} ${y - 38} Q ${x + 100} ${y - 38 + 8 * smile} ${x + 112} ${y - 38}`} {...pen(q, 3.5)} />
    <path d={spline([[x + 130, y], [x + 230, y - 34], [x + 340, y - 18], [x + 440, y - 4]])} {...pen(q, 5, S.blue)} />
    {Array.from({length: 6}).map((_, i) => (
      <path key={i} d={roughLine([x + 150 + i * 48, y + 110], [x + 190 + i * 48, y + 20], 120 + i, 3)} stroke={S.blueLight} strokeWidth={26} strokeLinecap="round" fill="none" opacity={0.85 * p(q, 0.5 + i * 0.05, 0.7 + i * 0.05)} />
    ))}
  </g>
);

// ---------- pages ----------
/** Scale a doodle group about its own anchor (ox, oy) and place that anchor at (x, y). */
const Sc: React.FC<{x: number; y: number; ox: number; oy: number; k: number; children: React.ReactNode; o?: number}> = ({x, y, ox, oy, k, children, o = 1}) => (
  <g transform={`translate(${x} ${y}) scale(${k}) translate(${-ox} ${-oy})`} opacity={o}>{children}</g>
);

const P1: React.FC<{t: number}> = ({t}) => {
  const walk = pio(t, a('ngikutin') - 0.2, a('ngikutin') + 1.1);
  const kx = 330 + 400 * walk;
  const bob = Math.abs(Math.sin(t * 9)) * 14 * (walk > 0 && walk < 1 ? 1 : 0);
  const base = 1240;
  const crowd: Array<[number, number, string, (typeof HAIR)[number]]> = [[130, 1240, 'psst…', 'bun'], [950, 1240, 'hihi', 'cap'], [120, 840, '…', 'long'], [960, 820, 'eh tau gak', 'curl']];
  const bubblesOut = 1 - p(t, a('rasanya') - 0.2, a('rasanya'));
  return (
    <g>
      {/* pencil underdrawing: the page is sketched before the voice starts */}
      <g opacity={0.7 - 0.6 * p(t, 1.0, 2.0)}>
        <Sc x={330} y={base} ox={330} oy={base} k={1.6}><Stick x={330} base={base} q={1} seed={3} style="spiky" color={PENCIL} pencil /></Sc>
        <path d={roughRect(110, 400, 560, 320, 31, 3)} {...pen(1, 2.6, PENCIL)} />
      </g>
      <Swipe x={136} y={232} w={540} h={62} q={p(t, 0, 0.7)} />
      <text x={140} y={292} fill={S.ink} style={{fontFamily: HEAD, fontSize: 100}}>Pernah nggak…</text>
      {/* the classroom slip */}
      <path d={roughRect(110, 400, 560, 320, 31, 3)} {...pen(p(t, 1.0, 1.6), 6)} />
      <text x={160} y={520} fill={S.inkSoft} opacity={p(t, 1.4, 1.8)} style={{fontFamily: HAND, fontSize: 64}}>2 + 2 = …</text>
      <path d={roughLine([160, 600], [600, 600], 51, 3)} {...pen(p(t, 1.5, 1.9), 3, S.inkSoft)} />
      <Spotlight lamp={[560 + (kx - 330) * 0.3, 480]} target={[kx, base + 8]} spread={230} q={p(t, a('lampu') - 0.2, a('lampu') + 0.4)} seed={9} lampScale={1.5} />
      <g transform={`translate(${kx - 330} ${-bob})`}>
        <Sc x={330} y={base} ox={330} oy={base} k={1.6}>
          <Stick x={330} base={base} q={p(t, 1.0, 2.2)} seed={3} style="spiky" color={S.coral} />
          <Cheeks x={330} y={base - 170} q={p(t, a('kepikiran'), a('kepikiran') + 0.3)} />
        </Sc>
        <Bubble x={500} y={700} w={360} h={170} q={pb(t, a('ngomong') - 0.05, a('ngomong') + 0.3) * (1 - p(t, a('kepikiran') - 0.3, a('kepikiran') - 0.1))} seed={71} tail={[420, 930]}>
          <text x={680} y={812} textAnchor="middle" fill={S.ink} style={{fontFamily: HEAD, fontSize: 90}}>5!</text>
        </Bubble>
        <Bubble x={470} y={660} w={470} h={230} q={pb(t, a('kepikiran') - 0.05, a('kepikiran') + 0.35) * bubblesOut} seed={73} tail={[420, 930]} thought>
          {[0, 1, 2].map((i) => (
            <g key={i} opacity={p(t, a('tiga') + i * 0.15, a('tiga') + 0.15 + i * 0.15)}>
              <path d={roughRect(522 + i * 128, 712, 110, 124, 80 + i, 2)} {...pen(1, 4)} />
              <text x={577 + i * 128} y={800} textAnchor="middle" fill={S.coral} style={{fontFamily: HEAD, fontSize: 74}}>{i + 1}</text>
            </g>
          ))}
        </Bubble>
      </g>
      <Write id="hari3" x={540} y={1390} q={p(t, a('tiga') + 0.2, a('tiga') + 0.6) * (1 - p(t, a('semua orang') - 0.3, a('semua orang')))} size={76} color={S.coral} anchor="middle" w={640}>kepikiran 3 hari!</Write>
      {/* everyone talking about it (in your head) */}
      {crowd.map(([x, b, w, hair], i) => {
        const q = p(t, a('semua orang') + i * 0.18, a('semua orang') + 0.45 + i * 0.18);
        if (q <= 0) return null;
        return (
          <g key={i}>
            <Sc x={x} y={b} ox={x} oy={b} k={1.05}><Stick x={x} base={b} q={q} seed={20 + i} style={hair} color={S.ink} /></Sc>
            <text x={Math.min(Math.max(x, 130), 950)} y={b - 232} textAnchor="middle" fill={S.blue} opacity={q} style={{fontFamily: HAND, fontSize: 46}}>{w}</text>
            {[0, 1].map((k) => <path key={k} d={`M ${x + (x < 540 ? 40 : -40)} ${b - 200 + k * 18} l ${x < 540 ? 26 : -26} ${-8 + k * 16}`} {...pen(q, 3.5, S.blue)} />)}
          </g>
        );
      })}
    </g>
  );
};

const P2: React.FC<{t: number}> = ({t}) => {
  const tY = a('tahun');
  const room = p(t, a('terus dia') - 0.1, a('terus dia') + 0.6);
  const enter = pio(t, a('masuk') - 0.3, a('masuk') + 0.7);
  return (
    <g>
      <Swipe x={136} y={232} w={440} h={62} q={p(t, a('ternyata') - 0.1, a('ternyata') + 0.4)} seed={3} />
      <Write id="eks" x={140} y={292} q={p(t, a('ternyata') - 0.2, a('ternyata') + 0.5)} size={100} w={600}>Eksperimen!</Write>
      <Sc x={720} y={300} ox={720} oy={280} k={1.3}>
        <path d="M 700 210 L 700 260 L 660 330 Q 650 352 676 352 L 764 352 Q 790 352 780 330 L 740 260 L 740 210 Z" {...pen(p(t, a('diuji') - 0.3, a('diuji') + 0.3), 4.5)} />
        <path d="M 668 318 L 772 318" {...pen(p(t, a('diuji'), a('diuji') + 0.3), 12, S.green)} />
      </Sc>
      <Write id="y2000" x={150} y={560} q={p(t, tY - 0.05, tY + 0.4)} size={160} color={S.coral} w={420}>2000</Write>
      <path d={roughEllipse(310, 506, 210, 96, 33)} {...pen(p(t, tY + 0.3, tY + 0.8), 5, S.coral)} />
      {/* researcher (designed around 600,760; drawn bigger on the right) */}
      <Sc x={820} y={1000} ox={600} oy={760} k={1.45} o={p(t, a('peneliti') - 0.1, a('peneliti') + 0.4)}>
        <Stick x={600} base={760} q={p(t, a('peneliti') - 0.1, a('peneliti') + 0.6)} seed={41} style="curl" color={S.ink} />
        <path d="M 570 620 L 560 740 L 640 740 L 630 620" {...pen(1, 4, S.blue)} />
        <path d={`${roughEllipse(590, 588, 11, 9, 4)} ${roughEllipse(612, 588, 11, 9, 5)}`} {...pen(1, 3)} />
        <path d={roughRect(640, 640, 54, 70, 42, 2)} {...pen(1, 4)} />
        <text x={600} y={812} textAnchor="middle" fill={S.inkSoft} style={{fontFamily: HAND, fontSize: 30}}>peneliti</text>
      </Sc>
      <path d={spline([[700, 820], [560, 780], [470, 840]])} {...pen(p(t, a('nyuruh') - 0.1, a('nyuruh') + 0.4), 4.5)} />
      <path d="M 488 812 L 466 842 L 502 852" {...pen(p(t, a('nyuruh') + 0.3, a('nyuruh') + 0.45), 4.5)} />
      {/* student + the shirt, then into the room */}
      <Sc x={320 - 150 * enter} y={1090 + 280 * enter} ox={300} oy={1120} k={1.55 - 0.85 * enter} o={p(t, a('mahasiswa') - 0.1, a('mahasiswa') + 0.4)}>
        <Stick x={300} base={1120} q={p(t, a('mahasiswa') - 0.1, a('mahasiswa') + 0.7)} seed={57} style="spiky" color={S.ink} />
        <Shirt x={300} y={990} q={p(t, a('kaos') - 0.1, a('kaos') + 0.3)} />
        <Cheeks x={300} y={950} q={p(t, a('malu') - 0.4, a('malu'))} />
        <text x={300} y={1180} textAnchor="middle" fill={S.inkSoft} opacity={1 - enter} style={{fontFamily: HAND, fontSize: 30}}>mahasiswa</text>
      </Sc>
      <Write id="malu" x={470} y={1090} q={p(t, a('lumayan'), a('malu') + 0.3) * (1 - room)} size={72} color={S.coral} rot={-6} w={460}>malu bangett</Write>
      {room > 0 && (
        <g>
          <path d={roughRect(110, 1170, 860, 260, 91, 4)} {...pen(room, 5)} />
          <path d={roughLine([128, 1430], [128, 1250], 92, 3)} {...pen(room, 9, S.coral)} />
          {Array.from({length: 6}).map((_, i) => (
            <Sc key={i} x={300 + i * 118} y={1300 + (i % 2) * 40} ox={0} oy={0} k={1.35}>
              <Head x={0} y={0} q={p(t, a('penuh') - 0.1 + i * 0.05, a('penuh') + 0.2 + i * 0.05)} looks={false} seed={i + 3} />
            </Sc>
          ))}
          <Write id="ruang" x={540} y={1150} q={p(t, a('ruangan') - 0.1, a('ruangan') + 0.4)} size={52} color={S.inkSoft} font={HAND} anchor="middle" w={520}>ruangan penuh orang</Write>
        </g>
      )}
    </g>
  );
};

const LOOKS_GUESS = [0, 2, 3, 5, 8, 10];
const LOOKS_REAL = [3, 8, 10];
const P3: React.FC<{t: number}> = ({t}) => {
  const guess = p(t, a('setengah') - 0.1, a('setengah') + 0.6);
  const real = p(t, a('seperempat') - 0.2, a('seperempat') + 0.5);
  const realMode = t > a('kenyataannya') - 0.1;
  return (
    <g>
      <Bubble x={110} y={170} w={820} h={250} q={pb(t, a('sebelumnya') - 0.1, a('sebelumnya') + 0.4)} seed={13} tail={[170, 540]} thought>
        <Write id="nebak" x={520} y={318} q={p(t, a('sebelumnya'), a('nebak') + 0.4)} size={72} anchor="middle" w={700}>&quot;Pasti pada lihat…&quot;</Write>
      </Bubble>
      {Array.from({length: 12}).map((_, i) => {
        const looks = realMode ? LOOKS_REAL.includes(i) : LOOKS_GUESS.includes(i) && guess > 0.2;
        return (
          <Sc key={i} x={170 + (i % 6) * 148} y={610 + Math.floor(i / 6) * 190} ox={0} oy={0} k={1.4}>
            <Head x={0} y={0} q={p(t, a('sebelumnya') + 0.2 + i * 0.04, a('sebelumnya') + 0.5 + i * 0.04)} looks={looks} seed={i * 3 + 1} />
          </Sc>
        );
      })}
      <Pie cx={285} cy={1170} r={165} part={0.5} q={p(t, a('setengah') - 0.2, a('setengah') + 0.3)} fill={guess} color={S.coral} seed={61} id="pie1" />
      <text x={285} y={1410} textAnchor="middle" fill={S.ink} opacity={guess} style={{fontFamily: HEAD, fontSize: 60}}>tebakan: ½</text>
      <Pie cx={795} cy={1170} r={165} part={0.25} q={p(t, a('kenyataannya') - 0.1, a('kenyataannya') + 0.4)} fill={real} color="#F2C200" seed={63} id="pie2" />
      <text x={795} y={1410} textAnchor="middle" fill={S.ink} opacity={real} style={{fontFamily: HEAD, fontSize: 60}}>nyata: ±¼</text>
      <path d={roughEllipse(795, 1250, 245, 215, 65)} {...pen(p(t, a('seperempat'), a('seperempat') + 0.5), 5, S.coral)} />
      <text x={540} y={1200} textAnchor="middle" fill={S.inkSoft} opacity={p(t, a('kenyataannya'), a('kenyataannya') + 0.3)} style={{fontFamily: HEAD, fontSize: 70}}>vs</text>
    </g>
  );
};

const P4: React.FC<{t: number}> = ({t}) => {
  const dim = p(t, a('padahal') - 0.1, a('padahal') + 0.8);
  const pull = pio(t, a('karena') - 0.2, a('karena') + 0.9);
  const k = 1.55 - 0.6 * pull;
  const others: Array<[number, number, string, (typeof HAIR)[number]]> = [[190, 790, 'tadi aku aneh?', 'long'], [890, 790, 'bajuku…', 'curl'], [190, 1300, 'jerawatku…', 'bun'], [890, 1300, 'rambutku…', 'cap']];
  return (
    <g>
      <Swipe x={136} y={228} w={700} h={70} q={p(t, a('spotlight') - 0.2, a('spotlight') + 0.5)} seed={5} />
      <Write id="se" x={140} y={292} q={p(t, a('nyebutnya') - 0.1, a('effect') + 0.4)} size={104} w={760}>Spotlight Effect</Write>
      <path d={spline([[140, 320], [400, 330], [640, 314], [820, 326]])} {...pen(p(t, a('effect') + 0.2, a('effect') + 0.6), 5, S.coral)} />
      <Spotlight lamp={[540, 420 + 120 * pull]} target={[540, 1250]} spread={(250 - 90 * dim) * (1 - 0.4 * pull)} q={p(t, a('spotlight') - 0.1, a('spotlight') + 0.5)} power={1 - 0.6 * dim} seed={21} lampScale={1.7 - 0.6 * pull} />
      <Sc x={540} y={1250} ox={540} oy={1250} k={k}><Stick x={540} base={1250} q={p(t, a('spotlight'), a('spotlight') + 0.7)} seed={3} style="spiky" color={S.coral} /></Sc>
      <g opacity={p(t, a('padahal'), a('padahal') + 0.3) * (1 - pull)} transform="translate(860 560)">
        <path d={roughEllipse(0, 0, 76, 76, 81)} {...pen(1, 5)} />
        <path d={`M 0 0 L ${Math.cos(-2.4 + 1.9 * dim) * 60} ${Math.sin(-2.4 + 1.9 * dim) * 60}`} {...pen(1, 8, S.coral)} />
        <text x={0} y={130} textAnchor="middle" fill={S.inkSoft} style={{fontFamily: HAND, fontSize: 44}}>diredupin</text>
      </g>
      <text x={540} y={760} textAnchor="middle" fill={S.coral} opacity={p(t, a('kenapa') - 0.1, a('kenapa') + 0.2) * (1 - pull)} style={{fontFamily: HEAD, fontSize: 190}}>?</text>
      {others.map(([x, base, worry, hair], i) => {
        const q = p(t, a('orang lain') + i * 0.18, a('orang lain') + 0.5 + i * 0.18);
        if (q <= 0) return null;
        return (
          <g key={i}>
            <Spotlight lamp={[x, base - 300]} target={[x, base]} spread={100} q={q} seed={30 + i} lampScale={0.8} />
            <Sc x={x} y={base} ox={x} oy={base} k={0.95}><Stick x={x} base={base} q={q} seed={40 + i} style={hair} color={S.ink} /></Sc>
            <text x={x} y={base + 58} textAnchor="middle" fill={S.blue} opacity={p(t, a('sibuk') + i * 0.15, a('sibuk') + 0.3 + i * 0.15)} style={{fontFamily: HAND, fontSize: 46}}>{worry}</text>
          </g>
        );
      })}
    </g>
  );
};

const P5: React.FC<{t: number}> = ({t}) => {
  const tJ = a('jadi');
  const calm = p(t, a('tenang') - 0.1, a('tenang') + 0.5);
  const replay = pb(t, a('memalukan') - 0.2, a('memalukan') + 0.3) * (1 - p(t, a('tenang'), a('tenang') + 0.3));
  const head: Pt = [366, 767];
  return (
    <g>
      <Write id="malam" x={140} y={292} q={p(t, tJ - 0.1, tJ + 0.5)} size={100} w={600} color={S.blue}>Malam ini…</Write>
      <path d={roughRect(720, 160, 240, 220, 101, 4)} {...pen(p(t, tJ, tJ + 0.5), 5)} />
      <path d={roughLine([840, 160], [840, 380], 102, 3)} {...pen(p(t, tJ + 0.2, tJ + 0.6), 4)} />
      <g opacity={p(t, tJ + 0.3, tJ + 0.6)}>
        <circle cx={790} cy={262} r={40} fill={S.yellow} />
        <circle cx={808} cy={248} r={34} fill={S.paper} />
        <path d={roughEllipse(790, 262, 40, 40, 103, 0.03, 0.62)} {...pen(1, 4)} />
      </g>
      <Sc x={540} y={900} ox={540} oy={900} k={1.45}><Bed x={320} y={860} q={p(t, tJ - 0.1, tJ + 0.8)} awake={calm < 0.5} smile={calm} /></Sc>
      <Bubble x={420} y={400} w={520} h={250} q={replay} seed={111} tail={[head[0] + 20, head[1] - 90]} thought>
        <path d={roughRect(480, 450, 190, 140, 112, 2)} {...pen(1, 4)} />
        <text x={575} y={545} textAnchor="middle" fill={S.inkSoft} style={{fontFamily: HAND, fontSize: 48}}>kelas</text>
        <text x={810} y={555} textAnchor="middle" fill={S.coral} style={{fontFamily: HEAD, fontSize: 96}}>5!?</text>
      </Bubble>
      {calm > 0 && ['z', 'z', 'Z'].map((z, i) => {
        const kk = ((t - a('tenang') + i * 0.35) % 1.05) / 1.05;
        return <text key={i} x={head[0] + 60 + i * 44 + kk * 24} y={head[1] - 80 - i * 44 - kk * 36} fill={S.blue} opacity={calm * (1 - kk)} style={{fontFamily: HEAD, fontSize: 64 + i * 12}}>{z}</text>;
      })}
      {[180, 420, 660, 900].map((x, i) => {
        const q = p(t, a('kemungkinan') + i * 0.15, a('kemungkinan') + 0.45 + i * 0.15);
        if (q <= 0) return null;
        return (
          <g key={x}>
            <path d={roughRect(x - 100, 1190, 200, 140, 120 + i, 3)} {...pen(q, 4.5)} />
            <path d={`M ${x - 112} 1194 L ${x} 1120 L ${x + 112} 1194`} {...pen(q, 4.5)} />
            <text x={x} y={1280} textAnchor="middle" fill={S.blue} opacity={q} style={{fontFamily: HEAD, fontSize: 54}}>zzz</text>
          </g>
        );
      })}
      <Swipe x={190} y={1368} w={700} h={64} q={p(t, a('cuma kamu') - 0.1, a('cuma kamu') + 0.4)} seed={8} />
      <Write id="ingat" x={540} y={1430} q={p(t, a('cuma kamu') - 0.2, a('ingat') + 0.3)} size={76} anchor="middle" w={760}>cuma kamu yang ingat :)</Write>
    </g>
  );
};

const PAGE_LIST: Array<{start: number; C: React.FC<{t: number}>}> = [
  {start: -1, C: P1},
  {start: 11.62, C: P2},
  {start: 24.36, C: P3},
  {start: 31.82, C: P4},
  {start: 44.83, C: P5},
];

export const LampuSorotEpisode: React.FC = () => {
  const {t, frame} = useTime();
  const idx = PAGE_LIST.filter((pg) => t >= pg.start).length - 1;
  return (
    <AbsoluteFill style={{background: '#E9E2CF', overflow: 'hidden'}}>
      <StyleFonts />
      <EpisodeAudio vo="audio/vo/sketsa.mp3" words={C.words} music="audio/music/sketsa.wav" bed={0.5} duck={0.17} sfx={SKETSA_SFX} />
      {PAGE_LIST.map((pg, i) => {
        if (i < idx - 1 || i > idx) return null;
        const flip = i === 0 ? 1 : pio(t, pg.start, pg.start + 0.55);
        const under = i < idx ? pio(t, PAGE_LIST[i + 1].start, PAGE_LIST[i + 1].start + 0.55) : 0;
        const Comp = pg.C;
        const s = 1;
        return (
          <AbsoluteFill key={i} style={{transform: `translateY(${(1 - flip) * 1980}px) rotate(${(1 - flip) * 4}deg)`, transformOrigin: '0 0', boxShadow: i ? '0 -30px 60px rgba(0,0,0,.25)' : 'none'}}>
            <AbsoluteFill style={{transform: `scale(${s})`, transformOrigin: '50% 45%'}}>
              <Page>
                <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
                  <defs><Boil id={`boil-p${i}`} frame={frame} /></defs>
                  <g filter={`url(#boil-p${i})`}><Comp t={t} /></g>
                </svg>
                <div style={{position: 'absolute', right: 80, bottom: 300, fontFamily: HAND, fontSize: 30, color: S.inkSoft}}>hal. {i + 1}</div>
              </Page>
            </AbsoluteFill>
            <AbsoluteFill style={{background: '#000', opacity: 0.22 * under}} />
          </AbsoluteFill>
        );
      })}
      <div style={{position: 'absolute', right: 70, top: 96, transform: 'rotate(5deg)', background: '#7CCB9Ab0', padding: '8px 30px', fontFamily: HAND, fontSize: 36, color: S.ink,
        clipPath: 'polygon(0 6%,4% 0,96% 4%,100% 0,98% 50%,100% 100%,94% 96%,6% 100%,0 94%,2% 50%)'}}>PSIKOLOGI</div>
      <Caption t={t} pages={PAGES} />
    </AbsoluteFill>
  );
};

const s = (t: number, src: string, vol = 0.55): Sfx => ({t, src: `audio/sfx/sketsa/${src}.wav`, vol});
export const SKETSA_SFX: Sfx[] = [
  s(0.0, 'scribble', 0.4), s(1.0, 'scribble', 0.35), s(a('ngomong') - 0.05, 'pop', 0.45), s(a('kepikiran') - 0.05, 'pop', 0.4), s(a('tiga'), 'tick', 0.4), s(a('tiga') + 0.15, 'tick', 0.4), s(a('tiga') + 0.3, 'tick', 0.4),
  s(a('lampu') - 0.2, 'switch', 0.6), s(a('lampu') - 0.1, 'hum', 0.35), s(a('semua orang'), 'pop', 0.35), s(a('semua orang') + 0.36, 'pop', 0.35), s(a('semua orang') + 0.72, 'pop', 0.35),
  s(11.6, 'flip', 0.6), s(a('ternyata'), 'scribble', 0.4), s(a('diuji'), 'bubble', 0.45), s(a('tahun'), 'squeak', 0.5), s(a('peneliti'), 'scribble', 0.35),
  s(a('kaos') - 0.1, 'pop', 0.45), s(a('lumayan'), 'boing', 0.45), s(a('terus dia'), 'scribble', 0.4), s(a('penuh'), 'pop', 0.3),
  s(24.35, 'flip', 0.6), s(a('sebelumnya') - 0.1, 'pop', 0.45), s(a('setengah') - 0.1, 'squeak', 0.5), s(a('kenyataannya'), 'whoosh', 0.35), s(a('seperempat'), 'ding', 0.55),
  s(31.8, 'flip', 0.6), s(a('spotlight') - 0.2, 'squeak', 0.5), s(a('spotlight') - 0.1, 'switch', 0.55), s(a('padahal') + 0.1, 'dimmer', 0.5), s(a('kenapa'), 'boing', 0.4),
  ...[0, 1, 2, 3].map((i) => s(a('orang lain') + i * 0.18, 'switch', 0.35)),
  s(44.82, 'flip', 0.6), s(a('memalukan') - 0.2, 'pop', 0.4), s(a('tenang'), 'chime', 0.5), s(a('kemungkinan'), 'scribble', 0.3), s(a('cuma kamu') - 0.1, 'squeak', 0.45), s(a('ingat') + 0.3, 'ding', 0.45),
];
