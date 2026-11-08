// REFERENCE DEMO (AI BIJAN MOGRAPH v1.6) — read for structure only: stations, camera moves, word-pinned beats, SFX cues.
// Do NOT copy this story, its text or its layout into a buyer video. Written in the author's demo workspace, so two
// imports differ from the starter: `makeCues(words)` ≈ the starter's `at()/endOf()` from src/lib/words.ts, and
// `EpisodeAudio` ≈ Main.tsx audio (musicFile + auto-duck) + <SfxTrack /> fed by src/audio-plan.json sfxCues.
import React from 'react';
import {AbsoluteFill} from 'remotion';
import {makeCues} from '../../lib/cues';
import {camera, drawOn, Move, noise, p, pagesFor, pb, rnd, roughEllipse, roughLine, shake, StyleFonts, useTime, World} from '../../styles/shared';
import {A, Caption, DESK, EvidenceTag, GRAIN, HEAD, Polaroid, Sheet, Stamp, Tape, TextBars, TYPE, TypeOn} from '../../styles/arsip/kit';
import {EpisodeAudio, Sfx} from '../../styles/audio';
import wordsFile from './words.json';

// ARSIP episode — "Februari Dirampok". A case file: the victim (February), four suspects on a cork board,
// the evidence, the verdict. One desk + one long investigation wall; the camera travels between stations.

const C = makeCues(wordsFile);
const PAGES = pagesFor(C.words);
export const ARSIP_DURATION = C.duration + 1.4;
const CORK = noise(0.45, 4, 12, 512);

// ---------- art ----------
type Variant = 'helmet' | 'beard' | 'laurel' | 'fringe';
const Bust: React.FC<{v: Variant}> = ({v}) => (
  <svg width="100%" height="100%" viewBox="0 0 400 440" preserveAspectRatio="xMidYMid slice">
    <defs>
      <linearGradient id={`mg-${v}`} x1="0" y1="0" x2="0" y2="1"><stop offset="0" stopColor="#C9B48E" /><stop offset="1" stopColor="#9C8661" /></linearGradient>
    </defs>
    <rect width="400" height="440" fill={`url(#mg-${v})`} />
    {Array.from({length: 11}).map((_, i) => (
      <g key={i}>
        <line x1="0" x2="400" y1={22 + i * 40} y2={22 + i * 40} stroke="#3B2F22" strokeOpacity=".35" strokeWidth="2" />
        <text x="8" y={18 + i * 40} fill="#3B2F22" fillOpacity=".55" style={{fontFamily: TYPE, fontWeight: 700, fontSize: 15}}>{190 - i * 10}</text>
      </g>
    ))}
    <g fill="#2A2119">
      <path d="M 120 200 C 110 140, 140 90, 200 88 C 250 86, 285 120, 288 160 L 302 190 L 290 196 C 292 208, 288 220, 282 226 L 286 236 C 280 244, 270 250, 258 250 C 250 268, 240 280, 228 288 L 230 322 C 280 332, 330 362, 352 440 L 48 440 C 70 362, 120 332, 160 322 L 162 282 C 130 262, 122 236, 120 200 Z" />
      {v === 'beard' && <path d="M 168 226 C 172 300, 246 318, 290 238 L 266 250 C 244 262, 212 252, 196 230 Z" fill="#17110c" />}
      {v === 'fringe' && <path d="M 150 120 C 180 92, 240 88, 272 118 L 262 124 C 240 108, 190 108, 160 132 Z" fill="#17110c" />}
    </g>
    {v === 'helmet' && (
      <g>
        <path d="M 108 196 C 96 112, 156 66, 214 68 C 272 70, 304 118, 298 178 L 250 170 L 236 196 Z" fill="#55432f" />
        <path d="M 134 96 C 160 18, 262 14, 290 84" fill="none" stroke="#7A2B1C" strokeWidth="28" strokeLinecap="round" />
      </g>
    )}
    {(v === 'laurel' || v === 'fringe') && Array.from({length: 11}).map((_, i) => {
      const a = Math.PI * (1.05 + i * 0.08);
      const x = 205 + Math.cos(a) * 98, y = 168 + Math.sin(a) * 86;
      return <ellipse key={i} cx={x} cy={y} rx="20" ry="9" fill="#5E6B33" transform={`rotate(${(a * 180) / Math.PI + 70} ${x} ${y})`} />;
    })}
    <rect x="100" y="362" width="200" height="62" fill="#14110d" />
  </svg>
);

const Mugshot: React.FC<{x: number; y: number; rot: number; q: number; n: string; v: Variant; plate: string; tape?: number}> = ({x, y, rot, q, n, v, plate, tape = -6}) => (
  <Polaroid x={x} y={y} w={430} h={560} rot={rot} q={q} label={`TERSANGKA ${n}`} tapeRot={tape}>
    <div style={{position: 'absolute', inset: 0, filter: 'sepia(.25) contrast(1.05)'}}>
      <Bust v={v} />
      <div style={{position: 'absolute', left: 0, right: 0, bottom: 18, textAlign: 'center', fontFamily: TYPE, fontWeight: 700, fontSize: 25, color: '#EDE3CC', letterSpacing: 2}}>{plate}</div>
    </div>
  </Polaroid>
);

const NameCard: React.FC<{x: number; y: number; rot: number; t: number; at: number; name: string; sub: string; subAt: number}> = ({x, y, rot, t, at, name, sub, subAt}) => {
  const q = p(t, at, at + 0.3);
  if (q <= 0) return null;
  return (
    <div style={{position: 'absolute', left: x, top: y, transform: `rotate(${rot}deg) translateY(${(1 - q) * 20}px)`, opacity: q}}>
      <Sheet x={0} y={0} w={430} h={132} bg={A.card}>
        <div style={{position: 'absolute', left: 22, top: 14, fontFamily: HEAD, fontWeight: 900, fontSize: 52, color: A.ink}}>{name}</div>
        <div style={{position: 'absolute', left: 22, top: 84, fontFamily: TYPE, fontWeight: 700, fontSize: 23, color: A.inkSoft}}>
          <TypeOn text={sub} t={t} start={subAt} cps={32} />
        </div>
      </Sheet>
    </div>
  );
};

const Card: React.FC<{x: number; y: number; w: number; h: number; rot: number; q: number; children: React.ReactNode; bg?: string}> = ({x, y, w, h, rot, q, children, bg = A.card}) =>
  q > 0 ? (
    <div style={{position: 'absolute', left: x, top: y, transform: `rotate(${rot + (1 - q) * 6}deg) translateY(${(1 - q) * -50}px) scale(${1.08 - 0.08 * q})`, opacity: Math.min(1, q * 2.2)}}>
      <Sheet x={0} y={0} w={w} h={h} bg={bg}>{children}</Sheet>
    </div>
  ) : null;

const Pin: React.FC<{x: number; y: number; q?: number}> = ({x, y, q = 1}) =>
  q > 0 ? (
    <svg style={{position: 'absolute', left: x - 20, top: y - 20, overflow: 'visible'}} width={40} height={40}>
      <circle cx={23} cy={25} r={13} fill="#0006" /><circle cx={20} cy={20} r={13} fill="#C9302C" /><circle cx={16} cy={16} r={4} fill="#fff9" />
    </svg>
  ) : null;

const MarkerX: React.FC<{cx: number; cy: number; q: number; s?: number}> = ({cx, cy, q, s = 46}) => (
  <svg style={{position: 'absolute', left: 0, top: 0, overflow: 'visible'}} width={1} height={1}>
    <path d={roughLine([cx - s, cy - s], [cx + s, cy + s], cx, 5)} fill="none" stroke={A.red} strokeWidth={9} strokeLinecap="round" {...drawOn(p(q, 0, 0.5))} />
    <path d={roughLine([cx + s, cy - s], [cx - s, cy + s], cy, 5)} fill="none" stroke={A.red} strokeWidth={9} strokeLinecap="round" {...drawOn(p(q, 0.5, 1))} />
  </svg>
);

const Ring: React.FC<{cx: number; cy: number; rx: number; ry: number; q: number; color?: string; seed?: number; w?: number}> = ({cx, cy, rx, ry, q, color = A.red, seed = 3, w = 8}) => (
  <svg style={{position: 'absolute', left: 0, top: 0, overflow: 'visible'}} width={1} height={1}>
    <path d={roughEllipse(cx, cy, rx, ry, seed, 0.05)} fill="none" stroke={color} strokeWidth={w} strokeLinecap="round" strokeOpacity={0.92} {...drawOn(q)} />
  </svg>
);

/** Classic wall calendar page: 7 columns, the month starts on Sunday so 28 days are exactly four rows. */
const CalendarPage: React.FC<{t: number; x: number; y: number; w: number; rot: number; shown: number; title: string; sub: string; extra?: number; extraLabel?: (i: number) => string; markers?: number; markerAt?: number; circle28?: number}> = ({
  t, x, y, w, rot, shown, title, sub, extra = 0, extraLabel, markers = 0, markerAt = 0, circle28 = 0,
}) => {
  const cw = (w - 60) / 7;
  const ch = cw * 1.08;
  const gridTop = 220;
  const h = gridTop + ch * 5 + 50;
  const days = ['MIN', 'SEN', 'SEL', 'RAB', 'KAM', 'JUM', 'SAB'];
  return (
    <Sheet x={x} y={y} w={w} h={h} rot={rot} bg="#F4EEDF">
      <div style={{position: 'absolute', left: 0, right: 0, top: 0, height: 26, background: A.red}} />
      <div style={{position: 'absolute', left: 30, top: 44, fontFamily: HEAD, fontWeight: 900, fontSize: w * 0.13, lineHeight: 1, color: A.ink}}>{title}</div>
      <div style={{position: 'absolute', right: 30, top: 70, fontFamily: TYPE, fontWeight: 700, fontSize: w * 0.03, color: A.red}}>{sub}</div>
      {days.map((d, i) => (
        <div key={d} style={{position: 'absolute', left: 30 + i * cw, top: gridTop - 40, width: cw, textAlign: 'center', fontFamily: TYPE, fontWeight: 700, fontSize: w * 0.024, color: i === 0 ? A.red : A.inkSoft}}>{d}</div>
      ))}
      {Array.from({length: 35}).map((_, i) => {
        const col = i % 7, row = Math.floor(i / 7);
        const n = i + 1;
        const real = n <= 28 || n - 28 <= extra;
        const visible = n <= 28 ? n <= shown : n - 28 <= extra;
        return (
          <div key={i} style={{position: 'absolute', left: 30 + col * cw, top: gridTop + row * ch, width: cw, height: ch,
            border: real && visible ? `1.5px solid ${A.ink}55` : `2.5px dashed ${A.inkSoft}66`, background: n > 28 && real && visible ? `${A.marker}aa` : 'transparent'}}>
            {visible && real && <div style={{position: 'absolute', left: 8, top: 4, fontFamily: HEAD, fontWeight: 700, fontSize: w * 0.05, color: col === 0 ? A.red : A.ink}}>
              {n > 28 && extraLabel ? extraLabel(n) : n}</div>}
          </div>
        );
      })}
      {Array.from({length: markers}).map((_, i) => {
        const q = pb(t, markerAt + i * 0.15, markerAt + i * 0.15 + 0.35);
        if (q <= 0) return null;
        const cx = 30 + i * cw + cw / 2, cy = gridTop + 4 * ch + ch / 2;
        return (
          <svg key={i} style={{position: 'absolute', left: cx - 40, top: cy - 34, overflow: 'visible', transform: `translateY(${(1 - q) * -60}px)`}} width={80} height={70}>
            <path d="M 10 66 L 22 6 L 58 6 L 70 66 Z" fill={A.marker} stroke="#00000055" strokeWidth={2} />
            <text x={40} y={52} textAnchor="middle" fill={A.ink} style={{fontFamily: TYPE, fontWeight: 700, fontSize: 34}}>{i + 1}</text>
          </svg>
        );
      })}
      {circle28 > 0 && <Ring cx={30 + 6 * cw + cw / 2} cy={gridTop + 3 * ch + ch / 2} rx={cw * 0.62} ry={ch * 0.55} q={circle28} seed={28} />}
    </Sheet>
  );
};

const MONTHS10 = ['MARTIUS', 'APRILIS', 'MAIUS', 'IUNIUS', 'QUINTILIS', 'SEXTILIS', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER'];
const NUMA: Array<[string, number]> = [['MARTIUS', 31], ['APRILIS', 29], ['MAIUS', 31], ['IUNIUS', 29], ['QUINTILIS', 31], ['SEXTILIS', 29],
  ['SEPTEMBER', 29], ['OCTOBER', 31], ['NOVEMBER', 29], ['DECEMBER', 29], ['IANUARIUS', 29], ['FEBRUARIUS', 28]];

const Scroll: React.FC<{t: number; x: number; y: number}> = ({t, x, y}) => {
  const t0 = C.at('kalendernya');
  const q = p(t, t0 - 0.1, t0 + 0.4);
  if (q <= 0) return null;
  const tm = C.at('musim');
  const red = p(t, tm, tm + 0.4);
  return (
    <div style={{position: 'absolute', left: x, top: y, width: 430, height: 800 * q + 60, overflow: 'visible', transform: 'rotate(2deg)'}}>
      <div style={{position: 'absolute', left: 0, right: 0, top: 30, bottom: 30, background: 'linear-gradient(90deg,#CDB98C,#E8DAB3 18%,#EADDB8 82%,#C9B385)', boxShadow: '0 24px 40px #0007', overflow: 'hidden'}}>
        <div style={{position: 'absolute', inset: 0, backgroundImage: GRAIN, backgroundSize: '400px', opacity: 0.3, mixBlendMode: 'multiply'}} />
        <div style={{position: 'absolute', left: 30, top: 26, fontFamily: TYPE, fontWeight: 700, fontSize: 24, color: A.red, letterSpacing: 1}}>KALENDER ROMULUS</div>
        {MONTHS10.map((m, i) => (
          <div key={m} style={{position: 'absolute', left: 30, top: 70 + i * 52, fontFamily: HEAD, fontWeight: 700, fontSize: 34, color: A.ink, opacity: p(t, t0 + 0.1 + i * 0.09, t0 + 0.25 + i * 0.09)}}>
            <span style={{fontFamily: TYPE, fontSize: 22, color: A.inkSoft, marginRight: 12}}>{String(i + 1).padStart(2, '0')}</span>{m}
          </div>
        ))}
        <div style={{position: 'absolute', left: 22, right: 22, top: 600, height: 96, background: A.ink, transformOrigin: '0 50%', transform: `scaleX(${red})`}}>
          <div style={{position: 'absolute', left: 18, top: 16, fontFamily: TYPE, fontWeight: 700, fontSize: 26, color: '#EDE3CC', opacity: p(t, tm + 0.3, tm + 0.5)}}>MUSIM DINGIN</div>
          <div style={{position: 'absolute', left: 18, top: 52, fontFamily: TYPE, fontSize: 20, color: '#B9AE98', opacity: p(t, tm + 0.4, tm + 0.6)}}>(tanpa nama bulan)</div>
        </div>
      </div>
      <div style={{position: 'absolute', left: -12, right: -12, top: 0, height: 44, borderRadius: 22, background: 'linear-gradient(#8A7148,#D9C79C 45%,#7A6340)'}} />
      <div style={{position: 'absolute', left: -12, right: -12, bottom: 0, height: 44, borderRadius: 22, background: 'linear-gradient(#8A7148,#D9C79C 45%,#7A6340)'}} />
    </div>
  );
};

const Candles: React.FC<{t: number}> = ({t}) => (
  <svg width="100%" height="100%" viewBox="0 0 386 380" preserveAspectRatio="xMidYMid slice" style={{filter: 'sepia(.6)'}}>
    <rect width="386" height="380" fill="#241b14" />
    <ellipse cx="193" cy="210" rx="190" ry="160" fill="#F6C76A" opacity=".18" />
    {[[110, 170, 120], [193, 130, 160], [276, 180, 110]].map(([x, top, h], i) => {
      const fl = Math.sin(t * 13 + i * 2) * 3 + Math.sin(t * 29 + i) * 2;
      return (
        <g key={i}>
          <rect x={x - 20} y={top} width={40} height={h} rx={4} fill="#E9DCC0" />
          <path d={`M ${x} ${top - 52 + fl} C ${x + 16} ${top - 26}, ${x + 10} ${top - 6}, ${x} ${top - 4} C ${x - 10} ${top - 6}, ${x - 16} ${top - 26}, ${x} ${top - 52 + fl} Z`} fill="#FFD27A" />
          <ellipse cx={x} cy={top - 22} rx={34} ry={44} fill="#FFD27A" opacity=".22" />
        </g>
      );
    })}
    <rect x="0" y="300" width="386" height="80" fill="#3b2c20" />
    {[60, 120, 270, 330].map((x, i) => <circle key={i} cx={x} cy={300} r={16} fill="#8f3b2c" />)}
  </svg>
);

// ---------- stations ----------
const S0: React.FC<{t: number}> = ({t}) => {
  const tD = C.at('dirampok');
  const tF = C.at('februari');
  const cover = p(t, tF - 0.45, tF + 0.15);
  const shown = Math.floor(28 * p(t, C.at('cuma 28') - 0.3, C.at('28') + 0.15));
  return (
    <>
      <div style={{position: 'absolute', left: 0, top: 0, opacity: p(t, tF - 0.5, tF - 0.2)}}>
        <CalendarPage t={t} x={110} y={330} w={860} rot={-1.5} shown={shown} title="FEBRUARI" sub="KORBAN" markers={3} markerAt={C.at('hari') - 0.05}
          circle28={p(t, C.at('28'), C.at('28') + 0.4)} />
      </div>
      <Card x={-20} y={1300} w={500} h={300} rot={-6} q={p(t, C.at('padahal'), C.at('padahal') + 0.4)}>
        <div style={{position: 'absolute', left: 26, top: 18, fontFamily: HEAD, fontWeight: 900, fontSize: 66, color: A.ink}}>APRIL</div>
        <div style={{position: 'absolute', left: 26, top: 112, fontFamily: TYPE, fontWeight: 700, fontSize: 86, color: A.red, opacity: p(t, C.at('30') - 0.05, C.at('30') + 0.15)}}>30</div>
        <div style={{position: 'absolute', left: 170, top: 150, fontFamily: TYPE, fontWeight: 700, fontSize: 30, color: A.inkSoft, opacity: p(t, C.at('30'), C.at('30') + 0.2)}}>HARI</div>
      </Card>
      <Card x={600} y={1290} w={500} h={300} rot={5} q={p(t, C.at('padahal') + 0.25, C.at('padahal') + 0.65)}>
        <div style={{position: 'absolute', left: 26, top: 18, fontFamily: HEAD, fontWeight: 900, fontSize: 66, color: A.ink}}>MARET</div>
        <div style={{position: 'absolute', left: 26, top: 112, fontFamily: TYPE, fontWeight: 700, fontSize: 86, color: A.red, opacity: p(t, C.at('31') - 0.05, C.at('31') + 0.15)}}>31</div>
        <div style={{position: 'absolute', left: 170, top: 150, fontFamily: TYPE, fontWeight: 700, fontSize: 30, color: A.inkSoft, opacity: p(t, C.at('31'), C.at('31') + 0.2)}}>HARI</div>
      </Card>
      {/* the case folder lies on top until the victim is named */}
      {cover < 1 && (
        <div style={{position: 'absolute', left: 0, top: 0, transform: `translateY(${-1700 * cover}px) rotate(${-9 * cover}deg)`}}>
          <div style={{position: 'absolute', left: 110, top: 214, width: 470, height: 76, background: A.manilaDark, borderRadius: '14px 14px 0 0',
            fontFamily: TYPE, fontWeight: 700, fontSize: 27, color: A.ink, padding: '16px 26px'}}>BERKAS KASUS · No. 02/28</div>
          <Sheet x={80} y={270} w={920} h={1140} bg={A.manila} style={{borderRadius: 10}}>
            <div style={{position: 'absolute', left: 120, right: 120, top: 300, height: 400, background: A.card, boxShadow: '0 6px 16px #0004', padding: '40px 46px'}}>
              <div style={{fontFamily: TYPE, fontWeight: 700, fontSize: 26, color: A.inkSoft, letterSpacing: 2}}>PERKARA</div>
              <div style={{fontFamily: HEAD, fontWeight: 900, fontSize: 92, lineHeight: 1, color: A.ink, marginTop: 14}}>Hari yang<br />Hilang</div>
              <div style={{fontFamily: TYPE, fontWeight: 700, fontSize: 28, color: A.ink, marginTop: 30}}>
                <TypeOn text="Korban: satu bulan." t={t} start={0.25} cps={24} />
              </div>
            </div>
            <Stamp id="dirampok" t={t} at={tD} x={460} y={850} w={560} h={170} rot={-8} lines={['DIRAMPOK']} size={92} />
          </Sheet>
        </div>
      )}
    </>
  );
};

const STRING_PINS: Array<[number, number]> = [[305, 2160], [775, 3390], [305, 6830], [775, 8130]];

export const FebruariEpisode: React.FC = () => {
  const {t} = useTime();
  const a = C.at;
  const Y1 = 1800, Y2 = 3300, Y3 = 4700, Y4 = 6750, Y5 = 8050, Y6 = 9800;
  const moves: Move[] = [
    {from: 0, to: 3.2, s: 1.03},
    {from: 3.3, to: 3.9, y: 860, s: 1},
    {from: 6.2, to: a('padahal') + 0.1, y: 1090, s: 0.76},
    {from: 9.5, to: a('jadi siapa') - 0.05, y: Y1 + 470, s: 1},
    {from: 12.05, to: a('tersangka') - 0.02, y: Y1 + 640, s: 0.97},
    {from: 21.97, to: a('tersangka', 1) - 0.03, y: Y2 + 520, s: 1},
    {from: 29.13, to: a('tapi') - 0.02, y: Y3 + 450, s: 1},
    {from: 35.0, to: a('masalahnya') + 0.1, y: Y3 + 720, s: 0.95},
    {from: 43.5, to: a('menghormati') - 0.2, y: Y3 + 960, s: 0.97},
    {from: 46.69, to: a('julius') - 0.02, y: Y4 + 520, s: 1},
    {from: 51.57, to: a('terus tuduhan') - 0.02, y: Y5 + 560, s: 0.98},
    {from: 58.9, to: a('ceritanya') + 0.2, y: Y5 + 680},
    {from: 61.93, to: a('kasus') + 0.05, y: 5650, s: 0.235},
    {from: 63.0, to: a('februari nggak dirampok') + 0.05, y: Y6 + 690, s: 0.88},
  ];
  const pose = camera(t, {x: 540, y: 880, s: 1, r: 0}, moves);
  const impacts = [a('dirampok') + 0.13, a('sial') + 0.13, a('dikorbankan') + 0.13, a('mitos') + 0.13, a('kasus') + 0.3];
  const sh = shake(t, impacts, 12);
  const overview = p(t, 61.95, 62.6);

  // red string between suspects: segment i draws when suspect i+1 is introduced
  const segAt = [a('tersangka', 1), a('julius'), a('terus tuduhan')];
  const stringPath = (i: number) => {
    const [x0, y0] = STRING_PINS[i], [x1, y1] = STRING_PINS[i + 1];
    return `M ${x0} ${y0} Q ${(x0 + x1) / 2} ${(y0 + y1) / 2 + 120} ${x1} ${y1}`;
  };

  return (
    <AbsoluteFill style={{background: A.desk, overflow: 'hidden'}}>
      <StyleFonts />
      <EpisodeAudio vo="audio/vo/arsip.mp3" words={C.words} music="audio/music/arsip.wav" bed={0.55} duck={0.17} sfx={ARSIP_SFX} />
      <World pose={pose} offset={sh} h={11400}>
        {/* desk */}
        <div style={{position: 'absolute', left: -2600, top: -1200, width: 6280, height: 13800, background: A.desk}} />
        <div style={{position: 'absolute', left: -2600, top: -1200, width: 6280, height: 13800, backgroundImage: DESK, backgroundSize: '1024px 300px', opacity: 0.3, mixBlendMode: 'overlay'}} />
        <div style={{position: 'absolute', left: -400, top: -200, width: 1880, height: 1900, background: 'radial-gradient(ellipse 900px 1000px at 50% 50%, #45392c, transparent 70%)'}} />
        {/* cork wall for the investigation */}
        <div style={{position: 'absolute', left: -60, top: 1720, width: 1200, height: 7820, background: '#9A6E44', boxShadow: '0 30px 80px #000a, inset 0 0 120px #3a2414'}}>
          <div style={{position: 'absolute', inset: 0, backgroundImage: CORK, backgroundSize: '512px', opacity: 0.55, mixBlendMode: 'multiply'}} />
          <div style={{position: 'absolute', inset: 0, border: '26px solid #5A3C22'}} />
        </div>
        <div style={{position: 'absolute', left: -200, top: 9620, width: 1480, height: 1700, background: 'radial-gradient(ellipse 800px 900px at 50% 50%, #45392c, transparent 70%)'}} />

        <S0 t={t} />

        {/* S1 — who did it + Romulus */}
        <Card x={110} y={Y1 + 90} w={860} h={190} rot={-1.5} q={p(t, a('jadi siapa'), a('jadi siapa') + 0.35)}>
          <div style={{position: 'absolute', left: 30, top: 18, fontFamily: TYPE, fontWeight: 700, fontSize: 24, color: A.red, letterSpacing: 2}}>DAFTAR TERSANGKA</div>
          <div style={{position: 'absolute', left: 30, top: 56, fontFamily: HEAD, fontWeight: 900, fontSize: 84, color: A.ink, lineHeight: 1, whiteSpace: 'nowrap'}}>
            Siapa pelaku<span style={{opacity: p(t, a('pelakunya') + 0.2, a('pelakunya') + 0.4)}}>nya?</span>
          </div>
        </Card>
        <Pin x={540} y={Y1 + 100} q={p(t, a('jadi siapa') + 0.3, a('jadi siapa') + 0.4)} />
        <Mugshot x={90} y={Y1 + 360} rot={-3} q={p(t, a('tersangka') - 0.05, a('tersangka') + 0.35)} n="#1" v="helmet" plate="ROMA · 01" />
        <NameCard x={90} y={Y1 + 950} rot={-1} t={t} at={a('romulus') - 0.1} name="Romulus" sub="Raja pertama Roma (legenda)" subAt={a('menurut')} />
        <Scroll t={t} x={580} y={Y1 + 330} />

        {/* S2 — Numa */}
        <Mugshot x={560} y={Y2 + 90} rot={3} q={p(t, a('tersangka', 1) - 0.05, a('tersangka', 1) + 0.35)} n="#2" v="beard" plate="ROMA · 02" tape={5} />
        <NameCard x={560} y={Y2 + 680} rot={1.5} t={t} at={a('numa') - 0.15} name="Numa" sub="Raja kedua Roma" subAt={a('raja', 1)} />
        {[['IANUARIUS', 'Januari', a('januari')], ['FEBRUARIUS', 'Februari', a('februari', 1)]].map(([lat, id, at], i) => (
          <Card key={lat as string} x={70} y={Y2 + 140 + i * 330} w={450} h={280} rot={i ? 2 : -2.5} q={p(t, (at as number) - 0.25, (at as number) + 0.15)}>
            <div style={{position: 'absolute', left: 26, top: 18, fontFamily: TYPE, fontWeight: 700, fontSize: 22, color: A.red, letterSpacing: 2}}>BULAN BARU #{i + 1}</div>
            <div style={{position: 'absolute', left: 26, top: 60, fontFamily: HEAD, fontWeight: 900, fontSize: 64, color: A.ink}}>{lat as string}</div>
            <div style={{position: 'absolute', left: 26, top: 160, fontFamily: TYPE, fontWeight: 700, fontSize: 34, color: A.inkSoft}}>= {id as string}</div>
          </Card>
        ))}
        <Stamp id="baru" t={t} at={a('dua bulan') + 0.1} x={300} y={Y2 + 820} w={360} h={130} rot={-7} lines={['+2 BULAN']} size={64} />

        {/* S3 — even numbers, 355, the sacrifice */}
        <Card x={70} y={Y3 + 80} w={480} h={560} rot={-2} q={p(t, a('tapi') - 0.05, a('tapi') + 0.3)}>
          <div style={{position: 'absolute', left: 28, top: 20, fontFamily: TYPE, fontWeight: 700, fontSize: 22, color: A.red, letterSpacing: 2}}>KEPERCAYAAN ROMAWI</div>
          {Array.from({length: 8}).map((_, i) => {
            const n = i + 1, cx = 70 + (i % 4) * 108, cy = 150 + Math.floor(i / 4) * 170;
            const even = n % 2 === 0;
            return (
              <React.Fragment key={n}>
                <div style={{position: 'absolute', left: cx - 40, top: cy - 60, width: 80, textAlign: 'center', fontFamily: HEAD, fontWeight: 900, fontSize: 108, color: A.ink, lineHeight: 1}}>{n}</div>
                {even && <MarkerX cx={cx} cy={cy - 6} q={p(t, a('genap') + i * 0.06, a('genap') + 0.3 + i * 0.06)} s={40} />}
                {!even && <Ring cx={cx} cy={cy - 4} rx={52} ry={62} q={p(t, a('ganjil') - 0.2 + i * 0.05, a('ganjil') + 0.2 + i * 0.05)} color="#E3A200" seed={n} w={7} />}
              </React.Fragment>
            );
          })}
          <div style={{position: 'absolute', left: 28, top: 440, fontFamily: TYPE, fontWeight: 700, fontSize: 28, color: A.ink}}>
            <TypeOn text="genap = sial" t={t} start={a('sial') - 0.4} cps={26} /><br />
            <TypeOn text="ganjil = aman" t={t} start={a('ganjil') - 0.3} cps={26} />
          </div>
        </Card>
        <Stamp id="sial" t={t} at={a('sial')} x={330} y={Y3 + 330} w={300} h={130} rot={-14} lines={['SIAL']} size={78} />
        <Card x={600} y={Y3 + 60} w={400} h={1240} rot={1.5} q={p(t, a('masalahnya') - 0.1, a('masalahnya') + 0.3)} bg="#F7F4EC">
          <div style={{position: 'absolute', left: 24, top: 22, fontFamily: TYPE, fontWeight: 700, fontSize: 22, color: A.red, letterSpacing: 1}}>KALENDER NUMA</div>
          {NUMA.map(([m, d], i) => {
            const tq = p(t, a('masalahnya') + 0.2 + i * 0.13, a('masalahnya') + 0.3 + i * 0.13);
            const feb = i === 11;
            const febQ = p(t, a('genap', 1) - 0.1, a('genap', 1) + 0.1);
            return (
              <div key={m} style={{position: 'absolute', left: 24, right: 24, top: 70 + i * 74, display: 'flex', justifyContent: 'space-between', fontFamily: TYPE, fontWeight: 700,
                fontSize: 29, color: feb ? A.red : A.ink, opacity: tq, borderBottom: '2px dotted #00000030', paddingBottom: 6}}>
                <span>{m}</span><span>{feb ? (febQ > 0 ? '28' : '??') : d}</span>
              </div>
            );
          })}
          <div style={{position: 'absolute', left: 24, right: 24, top: 975, borderTop: `3px solid ${A.ink}`, paddingTop: 12, display: 'flex', justifyContent: 'space-between',
            fontFamily: TYPE, fontWeight: 700, fontSize: 40, color: A.ink, opacity: p(t, a('355') - 0.05, a('355') + 0.15)}}>
            <span>TOTAL</span><span>355</span>
          </div>
          <div style={{position: 'absolute', left: 24, right: 24, top: 1060, fontFamily: TYPE, fontSize: 22, lineHeight: 1.3, color: A.inkSoft, opacity: p(t, a('satu bulan harus'), a('satu bulan harus') + 0.3)}}>
            11 bulan ganjil + 1 bulan genap = 355
          </div>
        </Card>
        <Ring cx={930} cy={Y3 + 60 + 70 + 11 * 74 + 20} rx={66} ry={44} q={p(t, a('genap', 1), a('genap', 1) + 0.4)} seed={12} />
        <Stamp id="korban" t={t} at={a('dikorbankan')} x={790} y={Y3 + 905} w={520} h={150} rot={-9} lines={['DIKORBANKAN']} size={70} />
        <Polaroid x={80} y={Y3 + 700} w={450} h={500} rot={-3} q={p(t, a('bulan buat') - 0.1, a('bulan buat') + 0.3)} label="Bulan penghormatan arwah">
          <Candles t={t} />
        </Polaroid>

        {/* S4 — Julius Caesar */}
        <Mugshot x={90} y={Y4 + 80} rot={-2.5} q={p(t, a('julius') - 0.05, a('julius') + 0.35)} n="#3" v="laurel" plate="ROMA · 03" />
        <NameCard x={90} y={Y4 + 670} rot={-1} t={t} at={a('julius') + 0.2} name="Julius Caesar" sub="Kalender Julian" subAt={a('malah')} />
        <EvidenceTag x={360} y={Y4 + 60} rot={14} text="BONUS +1" q={pb(t, a('bonus') - 0.05, a('bonus') + 0.3)} />
        <div style={{position: 'absolute', left: 0, top: 0, opacity: p(t, a('satu hari tambahan') - 0.3, a('satu hari tambahan'))}}>
          <CalendarPage t={t} x={560} y={Y4 + 120} w={440} rot={2} shown={28} title="FEBRUARI" sub="KABISAT" extra={p(t, a('satu hari tambahan'), a('satu hari tambahan') + 0.3) > 0.5 ? 1 : 0} />
        </div>
        <Card x={570} y={Y4 + 720} w={420} h={130} rot={-1.5} q={p(t, a('tiap empat') - 0.1, a('tiap empat') + 0.25)} bg={A.marker}>
          <div style={{position: 'absolute', left: 22, top: 18, fontFamily: TYPE, fontWeight: 700, fontSize: 30, color: A.ink, lineHeight: 1.25}}>+1 HARI (29)<br />TIAP 4 TAHUN</div>
        </Card>

        {/* S5 — Augustus and the myth */}
        <Mugshot x={560} y={Y5 + 80} rot={3} q={p(t, a('terus tuduhan') - 0.05, a('terus tuduhan') + 0.35)} n="#4" v="fringe" plate="ROMA · 04" tape={6} />
        <NameCard x={560} y={Y5 + 670} rot={1.5} t={t} at={a('augustus') - 0.2} name="Augustus" sub="Kaisar pertama Roma" subAt={a('augustus') + 0.2} />
        <Card x={60} y={Y5 + 100} w={480} h={780} rot={-2.5} q={p(t, a('katanya') - 0.1, a('katanya') + 0.3)} bg={A.paper}>
          <div style={{position: 'absolute', left: 24, right: 24, top: 20, borderTop: `3px solid ${A.ink}`, borderBottom: `1px solid ${A.ink}`, height: 5}} />
          <div style={{position: 'absolute', left: 0, right: 0, top: 34, textAlign: 'center', fontFamily: HEAD, fontWeight: 900, fontSize: 50, color: A.ink}}>Kabar Roma</div>
          <div style={{position: 'absolute', left: 24, right: 24, top: 104, borderTop: `3px solid ${A.ink}`}} />
          <div style={{position: 'absolute', left: 26, right: 22, top: 124, fontFamily: HEAD, fontWeight: 900, fontSize: 58, lineHeight: 1.02, color: A.ink}}>
            Augustus Curi Hari dari Februari!
          </div>
          <div style={{position: 'absolute', left: 26, top: 400, width: 200, height: 200, background: A.paperDark, overflow: 'hidden'}}><Bust v="fringe" /></div>
          <div style={{position: 'absolute', left: 246, top: 400, width: 210}}><TextBars lines={9} w={200} seed={41} /></div>
          <div style={{position: 'absolute', left: 26, top: 630, right: 22, display: 'flex', alignItems: 'baseline', gap: 14, opacity: p(t, a('agustus') - 0.05, a('agustus') + 0.2)}}>
            <span style={{fontFamily: HEAD, fontWeight: 900, fontSize: 46, color: A.ink}}>AGUSTUS</span>
            <span style={{fontFamily: TYPE, fontWeight: 700, fontSize: 60, color: A.red}}>31</span>
          </div>
        </Card>
        <Stamp id="mitos" t={t} at={a('mitos')} x={310} y={Y5 + 470} w={460} h={190} rot={-12} lines={['MITOS']} size={110} />
        <Card x={110} y={Y5 + 960} w={860} h={200} rot={-1} q={p(t, a('ceritanya') - 0.1, a('ceritanya') + 0.25)}>
          <div style={{position: 'absolute', left: 30, top: 22, fontFamily: TYPE, fontWeight: 700, fontSize: 22, color: A.red, letterSpacing: 2}}>CATATAN PENYIDIK</div>
          <div style={{position: 'absolute', left: 30, top: 62, right: 30, fontFamily: TYPE, fontWeight: 700, fontSize: 33, lineHeight: 1.3, color: A.ink}}>
            <TypeOn text="Kisah ini baru populer lebih dari 1.000 tahun kemudian." t={t} start={a('ceritanya') + 0.1} cps={30} />
          </div>
        </Card>

        {/* red string across the whole wall */}
        <svg style={{position: 'absolute', left: 0, top: 0, overflow: 'visible'}} width={1080} height={10000}>
          {[0, 1, 2].map((i) => (
            <path key={i} d={stringPath(i)} fill="none" stroke={A.string} strokeWidth={5} strokeLinecap="round" {...drawOn(p(t, segAt[i] + 0.1, segAt[i] + 0.9))} />
          ))}
        </svg>
        {STRING_PINS.map(([x, y], i) => <Pin key={i} x={x} y={y} q={p(t, [a('tersangka'), a('tersangka', 1), a('julius'), a('terus tuduhan')][i] + 0.2, 99)} />)}

        {/* S6 — verdict */}
        <Sheet x={60} y={Y6 + 20} w={960} h={1280} bg={A.manila} style={{borderRadius: 10}} />
        <Card x={100} y={Y6 + 70} w={440} h={200} rot={-1} q={p(t, 63.0, 63.3)} bg="#F4EEDF">
          <div style={{position: 'absolute', left: 0, right: 0, top: 0, height: 18, background: A.red}} />
          <div style={{position: 'absolute', left: 28, top: 34, fontFamily: HEAD, fontWeight: 900, fontSize: 66, color: A.ink}}>FEBRUARI</div>
          <div style={{position: 'absolute', left: 30, top: 136, fontFamily: TYPE, fontWeight: 700, fontSize: 28, color: A.inkSoft}}>28 HARI</div>
        </Card>
        <Stamp id="dirampok2" t={t} at={63.0} x={790} y={Y6 + 190} w={400} h={120} rot={-6} lines={['DIRAMPOK']} size={66} />
        <svg style={{position: 'absolute', left: 0, top: 0, overflow: 'visible'}} width={1} height={1}>
          <path d={roughLine([600, Y6 + 214], [985, Y6 + 168], 7, 6)} fill="none" stroke={A.ink} strokeWidth={12} strokeLinecap="round" {...drawOn(p(t, a('dirampok', 1) - 0.1, a('dirampok', 1) + 0.25))} />
        </svg>
        <div style={{position: 'absolute', left: 640, top: Y6 + 64, transform: 'rotate(-6deg)', fontFamily: TYPE, fontWeight: 700, fontSize: 44, color: A.red, opacity: p(t, a('dirampok', 1), a('dirampok', 1) + 0.2)}}>TIDAK</div>
        {NUMA.map(([m, d], i) => {
          const col = i % 3, row = Math.floor(i / 3);
          const q = p(t, a('dia cuma') + i * 0.05, a('dia cuma') + 0.25 + i * 0.05);
          const feb = i === 11;
          return (
            <Card key={m} x={100 + col * 300} y={Y6 + 330 + row * 196} w={280} h={172} rot={(rnd(i) - 0.5) * 4} q={q} bg={feb ? A.card : '#E9E2D2'}>
              <div style={{position: 'absolute', left: 16, top: 12, fontFamily: TYPE, fontWeight: 700, fontSize: 18, color: A.inkSoft}}>{String(i + 1).padStart(2, '0')}</div>
              <div style={{position: 'absolute', left: 16, top: 40, fontFamily: HEAD, fontWeight: 900, fontSize: m.length > 9 ? 31 : 36, color: feb ? A.red : A.ink}}>{m}</div>
              <div style={{position: 'absolute', right: 18, bottom: 6, fontFamily: TYPE, fontWeight: 700, fontSize: 54, color: feb ? A.red : A.ink}}>{d}</div>
            </Card>
          );
        })}
        <Ring cx={840} cy={Y6 + 330 + 3 * 196 + 86} rx={160} ry={108} q={p(t, a('terakhir'), a('terakhir') + 0.45)} seed={44} />
        <Card x={150} y={Y6 + 1140} w={780} h={110} rot={-0.8} q={p(t, a('jatahnya') - 0.2, a('jatahnya') + 0.15)} bg="#F7F4EC">
          <div style={{position: 'absolute', left: 26, top: 26, fontFamily: TYPE, fontWeight: 700, fontSize: 40, color: A.ink}}>
            <TypeOn text="JATAH: 355 − 327 = 28" t={t} start={a('jatahnya') - 0.1} cps={30} />
          </div>
        </Card>
        <Stamp id="habis" t={t} at={a('habis')} x={820} y={Y6 + 1190} w={300} h={120} rot={-10} lines={['HABIS']} size={70} />
      </World>

      {/* overview: case closed */}
      {overview > 0 && t < 63.3 && (
        <Stamp id="ditutup" t={t} at={a('kasus') + 0.15} x={540} y={820} w={860} h={250} rot={-8} lines={['KASUS DITUTUP']} size={98} />
      )}
      <div style={{position: 'absolute', right: 70, top: 92, transform: 'rotate(4deg)', border: `4px solid ${A.red}`, padding: '6px 16px', fontFamily: TYPE, fontWeight: 700,
        fontSize: 28, letterSpacing: 4, color: A.red, background: '#1b171299', opacity: 0.85}}>BERKAS · FAKTA</div>
      <Caption t={t} pages={PAGES} />
      <AbsoluteFill style={{background: 'radial-gradient(ellipse 120% 90% at 50% 45%, transparent 55%, rgba(0,0,0,.6))', pointerEvents: 'none'}} />
    </AbsoluteFill>
  );
};

// ---------- sound design: one-shots on visual events ----------
const s = (t: number, src: string, vol = 0.6): Sfx => ({t, src: `audio/sfx/arsip/${src}.wav`, vol});
const a = C.at;
export const ARSIP_SFX: Sfx[] = [
  s(0.25, 'type', 0.35), s(a('dirampok'), 'stamp', 0.75),
  s(a('februari') - 0.45, 'paper', 0.55), s(a('cuma 28') - 0.3, 'type', 0.3), s(a('28'), 'marker', 0.4), s(a('hari'), 'tick', 0.5), s(a('hari') + 0.15, 'tick', 0.45), s(a('hari') + 0.3, 'tick', 0.4),
  s(a('padahal'), 'paper', 0.45), s(a('padahal') + 0.25, 'paper', 0.4),
  s(9.5, 'whoosh', 0.35), s(a('jadi siapa'), 'paper', 0.5), s(a('pelakunya') + 0.25, 'pin', 0.5),
  s(a('tersangka'), 'shutter', 0.6), s(a('romulus') - 0.1, 'paper', 0.4), s(a('menurut'), 'type', 0.3),
  s(a('kalendernya') - 0.1, 'scroll', 0.55), s(a('musim'), 'marker', 0.45), s(a('dihitung'), 'thud', 0.4),
  s(21.97, 'whoosh', 0.35), s(a('tersangka', 1), 'shutter', 0.6), s(a('tersangka', 1) + 0.2, 'string', 0.5), s(a('raja', 1), 'type', 0.3),
  s(a('januari') - 0.25, 'paper', 0.5), s(a('februari', 1) - 0.25, 'paper', 0.5), s(a('dua bulan') + 0.1, 'stamp', 0.55),
  s(29.13, 'whoosh', 0.35), s(a('tapi'), 'paper', 0.45), s(a('genap'), 'marker', 0.5), s(a('sial'), 'stamp', 0.7), s(a('ganjil') - 0.2, 'marker', 0.4),
  s(a('masalahnya') - 0.1, 'paper', 0.45), s(a('masalahnya') + 0.2, 'type', 0.35), s(a('masalahnya') + 0.9, 'type', 0.3), s(a('355'), 'bell', 0.5),
  s(a('genap', 1), 'marker', 0.5), s(a('dikorbankan'), 'stamp', 0.75), s(a('bulan buat') - 0.1, 'shutter', 0.5),
  s(46.7, 'whoosh', 0.35), s(a('julius'), 'shutter', 0.6), s(a('julius') + 0.15, 'string', 0.45), s(a('bonus'), 'pin', 0.5),
  s(a('satu hari tambahan'), 'tick', 0.55), s(a('tiap empat') - 0.1, 'paper', 0.4),
  s(51.58, 'whoosh', 0.35), s(a('terus tuduhan'), 'shutter', 0.6), s(a('terus tuduhan') + 0.15, 'string', 0.45), s(a('katanya') - 0.1, 'paper', 0.5),
  s(a('mitos'), 'stamp', 0.8), s(a('ceritanya'), 'type', 0.35), s(a('ceritanya') + 0.9, 'type', 0.3),
  s(61.95, 'whoosh', 0.45), s(a('kasus') + 0.15, 'stamp', 0.85),
  s(63.0, 'whoosh', 0.4), s(63.0, 'stamp', 0.4), s(a('dirampok', 1) - 0.1, 'marker', 0.55),
  s(a('dia cuma'), 'paper', 0.45), s(a('terakhir'), 'marker', 0.5), s(a('jatahnya') - 0.1, 'type', 0.4), s(a('habis'), 'stamp', 0.7), s(a('habis') + 0.25, 'bell', 0.45),
];
