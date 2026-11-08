// REFERENCE DEMO (AI BIJAN MOGRAPH v1.6) — read for structure only: stations, camera moves, word-pinned beats, SFX cues.
// Do NOT copy this story, its text or its layout into a buyer video. Written in the author's demo workspace, so two
// imports differ from the starter: `makeCues(words)` ≈ the starter's `at()/endOf()` from src/lib/words.ts, and
// `EpisodeAudio` ≈ Main.tsx audio (musicFile + auto-duck) + <SfxTrack /> fed by src/audio-plan.json sfxCues.
import React from 'react';
import {AbsoluteFill} from 'remotion';
import {makeCues} from '../../lib/cues';
import {p, pagesFor, pb, rnd, StyleFonts, useTime} from '../../styles/shared';
import {Big, Caption, G, GridPage, HEAD, ps, TEXT} from '../../styles/geometrik/kit';
import {EpisodeAudio, Sfx} from '../../styles/audio';
import wordsFile from './words.json';

// GEOMETRIK episode — "Aturan 72": shapes are the data. Squares are money, rows of blocks are years,
// a red flip is inflation, a bed with a halved square is cash under the mattress.

const C = makeCues(wordsFile);
const PAGES = pagesFor(C.words);
export const GEOMETRIK_DURATION = C.duration + 1.4;
const a = C.at;

const label = (size = 30, color: string = G.ink): React.CSSProperties => ({fontFamily: TEXT, fontWeight: 800, fontSize: size, letterSpacing: size * 0.12, color});
const Abs: React.FC<{x: number; y: number; children: React.ReactNode; style?: React.CSSProperties; o?: number}> = ({x, y, children, style, o = 1}) =>
  o > 0 ? <div style={{position: 'absolute', left: x, top: y, opacity: o, ...style}}>{children}</div> : null;
const Block: React.FC<{x: number; y: number; w: number; h: number; color: string; q: number; children?: React.ReactNode; round?: boolean; from?: 'left' | 'up' | 'scale'}> = ({x, y, w, h, color, q, children, round, from = 'scale'}) => {
  if (q <= 0) return null;
  const tf = from === 'scale' ? `scale(${q})` : from === 'left' ? `translateX(${(1 - q) * -400}px)` : `translateY(${(1 - q) * 300}px)`;
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, height: h, background: color, borderRadius: round ? '50%' : 0, transform: tf, opacity: Math.min(1, q * 3),
      display: 'flex', alignItems: 'center', justifyContent: 'center', flexDirection: 'column'}}>{children}</div>
  );
};

export const Aturan72Episode: React.FC = () => {
  const {t, frame} = useTime();
  const win = (from: number, to: number) => t >= from && t < to;
  const t72 = a('72');
  const tNama = a('namanya'), tCara = a('caranya'), tHasil = a('hasilnya'), tB6 = a('bunga 6'), tB12 = a('bunga 12'), tDan = a('dan karena');
  const tTapi = a('tapi aturan'), tDalam = a('dalam 24'), tJadi = a('jadi pertanyaannya'), tTapi2 = a('tapi uangmu');

  // P1: the digit slot machine locks into 72
  const lock = ps(t, t72 - 0.15, t72 + 0.2);
  // P7: red flip for inflation
  const red = ps(t, tTapi - 0.2, tTapi + 0.3) * (1 - ps(t, tDalam - 0.35, tDalam + 0.05));
  const ink = red > 0.5 ? G.paper : G.ink;

  return (
    <AbsoluteFill style={{background: G.paper, overflow: 'hidden'}}>
      <StyleFonts />
      <EpisodeAudio vo="audio/vo/geometrik.mp3" words={C.words} music="audio/music/geometrik.wav" bed={0.5} duck={0.17} sfx={GEOMETRIK_SFX} />
      <GridPage />
      {red > 0 && <div style={{position: 'absolute', left: 0, right: 0, top: 0, height: `${100 * red}%`, background: G.red}} />}

      {/* P1 — slot grid */}
      {t < t72 + 0.4 && (
        <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
          {Array.from({length: 15}).map((_, i) => {
            const col = i % 5, row = Math.floor(i / 5);
            const x = 100 + col * 180, y = 520 + row * 190;
            const d = Math.floor(rnd(i * 13 + Math.floor(t * 14)) * 10);
            const color = [G.blue, G.red, G.ink, G.yellow, G.blue][(col + row) % 5];
            const fly = lock;
            return (
              <g key={i} transform={`translate(${x + 80 + (col - 2) * 500 * fly} ${y + 80 + (row - 1) * 600 * fly}) rotate(${fly * (i * 37 % 90 - 45)})`} opacity={1 - fly}>
                <rect x={-80} y={-80} width={160} height={160} fill={color} />
                <text y={44} textAnchor="middle" fill={color === G.yellow ? G.ink : '#fff'} style={{fontFamily: HEAD, fontWeight: 900, fontSize: 130}}>{d}</text>
              </g>
            );
          })}
        </svg>
      )}
      <Abs x={72} y={190} o={1 - ps(t, t72 - 0.2, t72)}>
        <Big size={120} stretch={70}>SATU ANGKA</Big>
        <div style={{...label(30), marginTop: 14}}>BISA MENEBAK MASA DEPAN UANGMU</div>
      </Abs>
      {/* P1–P2 — the giant 72 */}
      {win(t72 - 0.1, tCara + 0.4) && (
        <>
          <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
            <circle cx={560} cy={930} r={430 * pb(t, t72, t72 + 0.45)} fill={G.yellow} />
          </svg>
          <Abs x={0} y={560} style={{width: 1080, textAlign: 'center', transform: `scale(${0.6 + 0.4 * pb(t, t72 - 0.05, t72 + 0.35)})`}} o={1 - ps(t, tCara, tCara + 0.3)}>
            <Big size={760} stretch={85}>72</Big>
          </Abs>
          <div style={{position: 'absolute', left: 72, top: 230, background: G.red, color: '#fff', padding: '12px 24px', transformOrigin: '0 50%', transform: `scaleX(${ps(t, a('aturan') - 0.1, a('aturan') + 0.25)})`, opacity: 1 - ps(t, tCara, tCara + 0.3)}}>
            <Big size={120} stretch={70} color="#fff">ATURAN 72</Big>
          </div>
        </>
      )}

      {/* P3–P4 — the formula, built from blocks */}
      {win(tCara - 0.1, tB6 - 0.1) && (
        <>
          <Abs x={72} y={200} o={ps(t, tCara - 0.1, tCara + 0.2)}><Big size={110} stretch={70}>RUMUSNYA</Big></Abs>
          <Block x={72} y={600} w={260} h={260} color={G.ink} q={pb(t, a('72', 2) - 0.1, a('72', 2) + 0.3)}><Big size={170} color="#fff" stretch={85}>72</Big></Block>
          <Abs x={350} y={640} o={ps(t, a('dibagi') - 0.1, a('dibagi') + 0.15)}><Big size={170}>÷</Big></Abs>
          <Block x={480} y={600} w={260} h={260} color={G.red} q={pb(t, a('persen') - 0.1, a('persen') + 0.3)} round>
            <Big size={130} color="#fff">%</Big><div style={{...label(20, '#fff')}}>BUNGA/TAHUN</div>
          </Block>
          <Abs x={760} y={640} o={ps(t, tHasil - 0.1, tHasil + 0.15)}><Big size={170}>{t > a('kira-kira') ? '≈' : '='}</Big></Abs>
          <Block x={72} y={920} w={936} h={170} color={G.blue} q={ps(t, tHasil, tHasil + 0.35)} from="left">
            <div style={{...label(44, '#fff')}}>TAHUN SAMPAI UANGMU ×2</div>
          </Block>
          {/* money doubles */}
          <Block x={300} y={1170} w={200} h={200} color={G.yellow} q={pb(t, a('uangmu', 1) - 0.1, a('uangmu', 1) + 0.3)}><Big size={70}>Rp</Big></Block>
          <Block x={580} y={1170} w={200} h={200} color={G.yellow} q={pb(t, a('dua kali') - 0.05, a('dua kali') + 0.35)}><Big size={70}>Rp</Big></Block>
          <Abs x={792} y={1200} o={ps(t, a('lipat') - 0.1, a('lipat') + 0.2)}><Big size={120} color={G.red}>×2</Big></Abs>
        </>
      )}

      {/* P5 — two examples */}
      {win(tB6 - 0.1, tDan - 0.1) && (
        <>
          {[{y: 300, pct: '6%', div: 6, res: 12, tp: tB6, tr: a('12 tahun'), color: G.blue}, {y: 900, pct: '12%', div: 12, res: 6, tp: tB12, tr: a('cuma 6'), color: G.red}].map((r) => (
            <React.Fragment key={r.pct}>
              <Block x={72} y={r.y} w={230} h={230} color={r.color} q={pb(t, r.tp - 0.1, r.tp + 0.3)} round><Big size={90} color="#fff" stretch={80}>{r.pct}</Big></Block>
              <Abs x={330} y={r.y + 50} o={ps(t, r.tp + 0.6, r.tp + 0.9)}><Big size={120} stretch={75}>72 ÷ {r.div}</Big></Abs>
              <Abs x={330} y={r.y + 170} o={ps(t, r.tr - 0.1, r.tr + 0.2)}>
                <div style={{display: 'flex', alignItems: 'baseline', gap: 16}}><Big size={210} color={r.color} stretch={85}>{r.res}</Big><div style={label(40)}>TAHUN</div></div>
              </Abs>
              <svg width={1080} height={80} style={{position: 'absolute', left: 0, top: r.y + 420}}>
                {Array.from({length: 12}).map((_, i) => (
                  <rect key={i} x={72 + i * 78} y={10} width={66} height={56} fill={i < r.res ? r.color : G.grey} opacity={i < r.res ? ps(t, r.tr + i * 0.05, r.tr + 0.2 + i * 0.05) : 0.5} />
                ))}
              </svg>
            </React.Fragment>
          ))}
        </>
      )}

      {/* P6 — compounding staircase */}
      {win(tDan - 0.1, tTapi - 0.1) && (
        <>
          <Abs x={72} y={200} o={ps(t, tDan, tDan + 0.3)}><Big size={100} stretch={70}>BUNGA BERBUNGA</Big><div style={{...label(28), marginTop: 10}}>BUNGA 6% · TIAP 12 TAHUN ×2</div></Abs>
          <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
            <line x1={72} y1={1380} x2={1008} y2={1380} stroke={G.ink} strokeWidth={6} />
            {['0', '12', '24'].map((y, i) => <text key={y} x={160 + i * 330} y={1440} textAnchor="middle" fill={G.ink} style={{fontFamily: TEXT, fontWeight: 800, fontSize: 34}}>{y} TH</text>)}
            {[{n: 1, at: a('bunganya'), x: 160}, {n: 2, at: a('12 tahun berikutnya'), x: 490}, {n: 4, at: a('4 kali'), x: 820}].map((s, k) => (
              <g key={k}>
                {Array.from({length: s.n}).map((_, i) => {
                  const q = pb(t, s.at + i * 0.08, s.at + 0.35 + i * 0.08);
                  const col = s.n === 4 ? i % 2 : 0, row = s.n === 4 ? Math.floor(i / 2) : i;
                  const size = 140;
                  const x = s.x - (s.n === 4 ? size + 4 : size / 2) + col * (size + 8), y = 1380 - (row + 1) * (size + 8);
                  return <rect key={i} x={x} y={y} width={size} height={size} fill={G.yellow} stroke={G.ink} strokeWidth={4} transform={`translate(0 ${(1 - q) * 200})`} opacity={Math.min(1, q * 3)} />;
                })}
                <text x={s.x} y={1380 - (s.n === 4 ? 2 : s.n) * 148 - 40} textAnchor="middle" fill={k === 2 ? G.red : G.ink} opacity={ps(t, s.at + 0.2, s.at + 0.4)} style={{fontFamily: HEAD, fontWeight: 900, fontSize: 110}}>×{s.n}</text>
              </g>
            ))}
            <path d={`M 160 1230 Q 600 1200 860 760`} fill="none" stroke={G.red} strokeWidth={8} strokeDasharray="1 0" opacity={ps(t, a('4 kali'), a('4 kali') + 0.5)} />
          </svg>
        </>
      )}

      {/* P7 — inflation, the other way */}
      {win(tTapi - 0.2, tDalam) && (
        <>
          <Abs x={72} y={200} o={ps(t, tTapi, tTapi + 0.3)}><Big size={110} stretch={70} color={ink}>ARAH SEBALIKNYA</Big></Abs>
          <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
            <g transform={`translate(540 560) rotate(${180 * ps(t, a('sebaliknya') - 0.2, a('sebaliknya') + 0.4)})`} opacity={ps(t, tTapi + 0.4, tTapi + 0.7)}>
              <rect x={-260} y={-34} width={380} height={68} fill={G.paper} />
              <path d="M 110 -110 L 280 0 L 110 110 Z" fill={G.paper} />
            </g>
          </svg>
          <Block x={72} y={760} w={936} h={200} color={G.paper} q={ps(t, a('inflasi') - 0.1, a('inflasi') + 0.3)} from="left">
            <Big size={130} color={G.red} stretch={75}>INFLASI 3%/TH</Big>
          </Block>
          <Abs x={0} y={1040} style={{width: 1080, textAlign: 'center'}} o={ps(t, a('72', 4) - 0.1, a('72', 4) + 0.2)}>
            <Big size={190} color={G.paper} stretch={80}>
              72 ÷ <span style={{opacity: ps(t, a('3', 1) - 0.1, a('3', 1) + 0.15)}}>3</span> <span style={{opacity: ps(t, a('24') - 0.1, a('24') + 0.15)}}>= 24</span>
            </Big>
            <div style={{...label(40, G.paper), marginTop: 14, opacity: ps(t, a('tahun', 5) - 0.1, a('tahun', 5) + 0.2)}}>TAHUN</div>
          </Abs>
        </>
      )}

      {/* P8 — cash under the mattress */}
      {win(tDalam - 0.1, tJadi - 0.1) && (
        <>
          <Abs x={72} y={200} o={ps(t, tDalam, tDalam + 0.3)}><Big size={110} stretch={70}>24 TAHUN KEMUDIAN</Big></Abs>
          <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
            <g opacity={ps(t, a('uang yang') - 0.2, a('uang yang') + 0.2)}>
              <rect x={150} y={640} width={780} height={160} fill={G.blue} />
              <circle cx={270} cy={600} r={80} fill={G.paper} stroke={G.ink} strokeWidth={6} />
              <rect x={150} y={800} width={34} height={150} fill={G.ink} /><rect x={896} y={800} width={34} height={150} fill={G.ink} />
            </g>
            {(() => {
              const show = pb(t, a('di bawah') - 0.1, a('kasur') + 0.2);
              const half = ps(t, a('daya') - 0.1, a('setengah') + 0.3);
              return show > 0 ? (
                <g transform={`translate(540 ${880 + (1 - show) * 200})`}>
                  <rect x={-150} y={-60} width={300} height={300} fill={G.yellow} stroke={G.ink} strokeWidth={6} opacity={1 - 0.75 * half} />
                  <rect x={-150} y={-60} width={300 * (1 - 0.5 * half)} height={300} fill={G.yellow} stroke={G.ink} strokeWidth={6} />
                  <text x={-150 + 150 * (1 - 0.5 * half)} y={130} textAnchor="middle" fill={G.ink} style={{fontFamily: HEAD, fontWeight: 900, fontSize: 110}}>Rp</text>
                </g>
              ) : null;
            })()}
          </svg>
          <Abs x={640} y={1080} o={ps(t, a('setengah') - 0.1, a('setengah') + 0.2)}>
            <div style={{display: 'flex', alignItems: 'baseline', gap: 10}}><Big size={260} color={G.red}>½</Big><div style={label(30)}>DAYA BELI</div></div>
          </Abs>
        </>
      )}

      {/* P9 — the question */}
      {win(tJadi - 0.1, tTapi2 - 0.1) && (
        <>
          <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
            <circle cx={540} cy={860} r={380 * pb(t, tJadi, tJadi + 0.5)} fill={G.yellow} />
          </svg>
          <Abs x={0} y={560} style={{width: 1080, textAlign: 'center'}} o={ps(t, tJadi + 0.1, tJadi + 0.4)}><Big size={640}>?</Big></Abs>
          <Block x={240} y={1220} w={600} h={160} color={G.ink} q={ps(t, a('berapa uangku') - 0.1, a('berapa uangku') + 0.3)} from="left"><Big size={110} color="#fff" stretch={80}>Rp ???</Big></Block>
        </>
      )}

      {/* P10 — two futures */}
      {t >= tTapi2 - 0.1 && (
        <>
          <div style={{position: 'absolute', left: 0, top: 0, width: 540, height: 1920, background: G.blue, transform: `translateY(${(1 - ps(t, tTapi2 - 0.1, tTapi2 + 0.3)) * -1920}px)`}} />
          <div style={{position: 'absolute', left: 540, top: 0, width: 540, height: 1920, background: G.red, transform: `translateY(${(1 - ps(t, a('atau') - 0.15, a('atau') + 0.25)) * 1920}px)`}} />
          <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
            {(() => {
              const grow = ps(t, a('kerja') - 0.1, a('kamu') + 0.6);
              const shrink = ps(t, a('diam-diam') - 0.1, a('menyusut') + 0.6);
              return (
                <>
                  <rect x={270 - (90 + 90 * grow)} y={980 - (180 + 180 * grow)} width={180 + 180 * grow} height={180 + 180 * grow} fill={G.yellow} opacity={ps(t, tTapi2, tTapi2 + 0.3)} />
                  <path d={`M 270 ${560 - 60 * grow} L 340 ${660 - 60 * grow} L 200 ${660 - 60 * grow} Z`} fill={G.paper} opacity={grow} />
                  {t > a('atau') && <rect x={810 - (180 - 90 * shrink)} y={980 - (360 - 180 * shrink)} width={360 - 180 * shrink} height={360 - 180 * shrink} fill={G.yellow} opacity={1 - 0.4 * shrink} />}
                  <path d={`M 810 ${1180 + 40 * shrink} L 880 ${1080 + 40 * shrink} L 740 ${1080 + 40 * shrink} Z`} fill={G.paper} opacity={shrink} />
                </>
              );
            })()}
          </svg>
          <Abs x={60} y={1120} o={ps(t, a('kerja') - 0.1, a('kerja') + 0.2)}><Big size={110} color={G.paper} stretch={70}>KERJA</Big></Abs>
          <Abs x={600} y={1240} o={ps(t, a('menyusut') - 0.1, a('menyusut') + 0.2)}><Big size={100} color={G.paper} stretch={62}>MENYUSUT</Big></Abs>
        </>
      )}

      <div style={{position: 'absolute', right: 72, top: 96, textAlign: 'right'}}>
        <div style={{display: 'flex', alignItems: 'center', justifyContent: 'flex-end', gap: 10, ...label(24, t >= tTapi2 || red > 0.5 ? G.paper : G.ink)}}>
          <div style={{width: 22, height: 22, background: red > 0.5 ? G.paper : G.red}} /><div style={{width: 22, height: 22, borderRadius: 11, background: G.yellow}} />FAKTA &amp; ANGKA
        </div>
        <div style={{...label(18, t >= tTapi2 || red > 0.5 ? G.paper : G.ink), opacity: 0.75, marginTop: 6}}>ILUSTRASI · BUKAN SARAN KEUANGAN</div>
      </div>
      <Caption t={t} pages={PAGES} />
      <div style={{display: 'none'}}>{frame}</div>
    </AbsoluteFill>
  );
};

const s = (t: number, src: string, vol = 0.55): Sfx => ({t, src: `audio/sfx/geometrik/${src}.wav`, vol});
export const GEOMETRIK_SFX: Sfx[] = [
  s(0.0, 'tick', 0.35), s(0.6, 'tick', 0.3), s(1.2, 'tick', 0.3), s(1.8, 'tick', 0.3), s(2.4, 'tick', 0.3), s(3.0, 'tick', 0.3),
  s(a('72') - 0.05, 'slam', 0.85), s(a('aturan') - 0.1, 'swipe', 0.5),
  s(a('caranya') - 0.1, 'wipe', 0.45), s(a('72', 2) - 0.1, 'pop', 0.5), s(a('dibagi') - 0.1, 'click', 0.45), s(a('persen') - 0.1, 'pop', 0.5),
  s(a('hasilnya'), 'swipe', 0.45), s(a('uangmu', 1) - 0.1, 'pop', 0.45), s(a('dua kali') - 0.05, 'pop', 0.5), s(a('lipat'), 'ding', 0.5),
  s(a('bunga 6') - 0.15, 'wipe', 0.45), s(a('bunga 6'), 'pop', 0.5), s(a('12 tahun') - 0.05, 'slam', 0.55), s(a('bunga 12'), 'pop', 0.5), s(a('cuma 6') - 0.05, 'slam', 0.55),
  s(a('dan karena') - 0.15, 'wipe', 0.45), s(a('bunganya'), 'pop', 0.45), s(a('12 tahun berikutnya'), 'pop', 0.5), s(a('4 kali'), 'rise', 0.55), s(a('lipat', 1) - 0.1, 'ding', 0.5),
  s(a('tapi aturan') - 0.2, 'flip', 0.7), s(a('sebaliknya') - 0.2, 'swipe', 0.5), s(a('inflasi') - 0.1, 'slam', 0.6), s(a('24') - 0.05, 'slam', 0.6),
  s(a('dalam 24') - 0.3, 'wipe', 0.5), s(a('di bawah') - 0.1, 'thud', 0.5), s(a('daya') - 0.1, 'cut', 0.6), s(a('setengah'), 'down', 0.55),
  s(a('jadi pertanyaannya') - 0.05, 'pop', 0.5), s(a('berapa uangku') - 0.1, 'swipe', 0.45),
  s(a('tapi uangmu') - 0.1, 'wipe', 0.5), s(a('kerja') - 0.1, 'rise', 0.5), s(a('atau') - 0.15, 'wipe', 0.5), s(a('menyusut') - 0.1, 'down', 0.55),
];
