// REFERENCE DEMO (AI BIJAN MOGRAPH v1.6) — read for structure only: stations, camera moves, word-pinned beats, SFX cues.
// Do NOT copy this story, its text or its layout into a buyer video. Written in the author's demo workspace, so two
// imports differ from the starter: `makeCues(words)` ≈ the starter's `at()/endOf()` from src/lib/words.ts, and
// `EpisodeAudio` ≈ Main.tsx audio (musicFile + auto-duck) + <SfxTrack /> fed by src/audio-plan.json sfxCues.
import React from 'react';
import {AbsoluteFill} from 'remotion';
import {makeCues} from '../../lib/cues';
import {p, pagesFor, pb, pio, rnd, shake, StyleFonts, useTime} from '../../styles/shared';
import {Caption, GUNUNGAN, GununganOrnament, H, HEAD, HikDefs, incised, Kelir, KING, Lontar, Part, Pt, Silhouette, Stage, TEXT, THIEF} from '../../styles/hikayat/kit';
import {EpisodeAudio, Sfx} from '../../styles/audio';
import wordsFile from './words.json';

// HIKAYAT episode — "Legenda Danau Toba": the legend played as wayang on a lit kelir, then the science twist
// (a supervolcano, a collapsed caldera filling with water), then the choice.

const C = makeCues(wordsFile);
const PAGES = pagesFor(C.words);
export const HIKAYAT_DURATION = C.duration + 1.4;
const a = C.at;

// ---------- extra puppets ----------
const WOMAN: Part[] = [
  {poly: [[-40, -470], [36, -476], [50, -458], [80, -452], [52, -432], [46, -404], [20, -394], [-6, -388], [-32, -400], [-44, -432]]},
  {poly: [[-44, -470], [-78, -500], [-96, -468], [-84, -436], [-46, -440]]},
  {poly: [[-36, -394], [28, -394], [44, -262], [-46, -256]], perforate: 14},
  {poly: [[-50, -262], [46, -264], [64, 0], [-64, 0]], perforate: 15},
  {poly: [[-34, -386], [-96, -300], [-112, -150], [-94, -146], [-80, -292], [-28, -360]]},
  {poly: [[14, -386], [70, -320], [96, -250], [80, -244], [56, -306], [8, -356]]},
];
const CHILD: Part[] = KING.slice(1);
const FISH: Pt[] = [[-200, -60], [-120, -118], [0, -140], [110, -110], [190, -40], [205, 0], [190, 40], [110, 110], [0, 140], [-120, 118], [-200, 60], [-300, 140], [-276, 0], [-300, -140]];
const HOUSE: Part[] = [
  {poly: [[-300, -540], [-210, -440], [-140, -390], [0, -372], [140, -390], [210, -440], [300, -540], [250, -300], [-250, -300]], perforate: 15},
  {poly: [[-200, -300], [200, -300], [182, -150], [-182, -150]], perforate: 17},
  ...[-165, -60, 45, 140].map((x) => ({poly: [[x, -150], [x + 28, -150], [x + 28, 0], [x, 0]] as Pt[]})),
];
const MOUNTAINS: Pt[] = [[-620, 0], [-470, -380], [-390, -300], [-230, -560], [-70, -320], [40, -430], [220, -190], [360, -480], [520, -260], [620, -340], [620, 0]];
const VOLCANO: Pt[] = [[-560, 0], [-150, -660], [-80, -630], [0, -660], [80, -630], [150, -660], [560, 0]];
const CALDERA: Pt[] = [[-560, 0], [-320, -390], [-230, -300], [0, -290], [230, -300], [320, -390], [560, 0]];
const ISLAND: Part[] = [
  {poly: [[-280, 0], [-190, -120], [-60, -180], [70, -160], [190, -96], [280, 0]], perforate: 16},
  ...[-120, 0, 110].map((x, i) => ({poly: [[x - 40, -150 + i * 8], [x, -300 + i * 20], [x + 40, -150 + i * 8]] as Pt[]})),
];
const circlePts = (r: number, n = 40): Pt[] => Array.from({length: n}, (_, i) => [Math.cos((i / n) * Math.PI * 2) * r, Math.sin((i / n) * Math.PI * 2) * r] as Pt);
const SUN: Part[] = [{poly: circlePts(110), perforate: 15}];
const lerpPts = (A: Pt[], B: Pt[], k: number): Pt[] => A.map(([x, y], i) => [x + (B[i][0] - x) * k, y + (B[i][1] - y) * k]);
const wave = (w: number, amp: number, period: number, depth: number): Pt[] => {
  const pts: Pt[] = [];
  for (let x = -w; x <= w; x += 20) pts.push([x, -amp * Math.sin((x / period) * Math.PI * 2)]);
  pts.push([w, depth], [-w, depth]);
  return pts;
};
const WATER = wave(1300, 22, 170, 900);

const LT: React.FC<{y: number; w?: number; q: number; children: React.ReactNode; h?: number; x?: number}> = ({y, w = 900, q, children, h = 110, x}) => (
  <Lontar x={x ?? (1080 - w) / 2} y={y} w={w} h={h} q={q}>
    <div style={{width: '100%', textAlign: 'center'}}>{children}</div>
  </Lontar>
);
const title = (size = 60, color: string = H.ink): React.CSSProperties => ({...incised, fontFamily: HEAD, fontWeight: 800, fontSize: size, letterSpacing: 4, color, whiteSpace: 'nowrap', lineHeight: 1.1});
const text = (size = 38): React.CSSProperties => ({...incised, fontFamily: TEXT, fontWeight: 700, fontSize: size, whiteSpace: 'nowrap'});

export const DanauTobaEpisode: React.FC = () => {
  const {t} = useTime();
  const bob = (k: number, amp = 6) => Math.sin(t * 2.2 + k) * amp;
  const vis = (from: number, to: number, fi = 0.4, fo = 0.4) => p(t, from - fi, from) * (1 - p(t, to, to + fo));

  // gunungan appearances: opening, after Samosir is named, at "Itu legendanya", and as the divider at the end
  const g1 = pio(t, 1.0, 1.9);
  const g2in = pio(t, 25.95, 26.55), g2out = pio(t, 26.75, 27.35);
  const g3in = pio(t, 47.55, 48.05), g3out = pio(t, 48.3, 48.9);
  const g4 = pio(t, a('jadi mana') - 0.4, a('jadi mana') + 0.3);
  const gunX = t < 2 ? 540 - 1300 * g1 : t < 30 ? 1600 - 1060 * g2in - 1300 * g2out : t < 50 ? 1600 - 1060 * g3in - 1300 * g3out : 1600 - 1060 * g4;
  const gunRot = t < 2 ? -32 * g1 : t < 30 ? -20 + 20 * g2in - 25 * g2out : t < 50 ? -20 + 20 * g3in - 25 * g3out : -20 + 20 * g4;
  const gunShow = t < 1.95 || (t > 25.9 && t < 27.4) || (t > 47.5 && t < 48.95) || t > a('jadi mana') - 0.45;
  const gunScale = t > 60 ? 0.62 : 1.08;

  // storm
  const dark = p(t, a('seketika') - 0.1, a('menghitam') + 0.4) * (1 - p(t, a('air itulah') - 0.3, a('air itulah') + 0.8) * 0.7);
  const rain = vis(a('hujan') - 0.1, a('air itulah'), 0.3, 0.6);
  const flood = pio(t, a('sampai seluruh') - 0.4, a('tenggelam') + 0.6);
  const waterY = t < a('suatu hari') - 0.5 ? 2400 : t < a('tapi') ? 1430 - 160 * p(t, a('suatu hari') - 0.5, a('suatu hari') + 0.3) + 600 * p(t, a('tapi') - 0.6, a('tapi')) : t < a('seketika') ? 2400 : t < 47.3 ? 1500 - 720 * flood + 700 * p(t, 47.0, 47.6) : 2400;
  const shout = a('dasar');
  const sh = shake(t, [shout + 0.05, a('meletus') + 0.1, a('meletus') + 0.6], 16);

  // volcano / caldera
  const volc = vis(a('tapi ilmuwan'), a('jadi mana') - 0.4, 0.5, 0.4);
  const erupt = p(t, a('meletus') - 0.05, a('meletus') + 0.5) * (1 - p(t, a('kawahnya') - 0.3, a('kawahnya') + 0.5));
  const collapse = pio(t, a('runtuh') - 0.2, a('runtuh') + 0.7);
  const fill = p(t, a('terisi') - 0.5, a('terisi') + 0.9);
  const volcPts = lerpPts(VOLCANO, CALDERA, collapse);

  // people
  const tobaIn = pio(t, a('pemuda') - 0.4, a('toba') + 0.3);
  const tobaX = t < a('ia mau') - 1 ? 1260 - 900 * tobaIn : t < a('suatu siang') ? 360 : 1300 - 520 * pio(t, a('toba marah') - 0.5, a('toba marah') + 0.2);
  const showToba = (t > a('pemuda') - 0.5 && t < a('suatu siang') - 0.4) || (t > a('toba marah') - 0.6 && t < a('sampai seluruh') + 1);
  const fishUp = pb(t, a('ikan') - 0.1, a('ikan') + 0.5);
  const gold = p(t, a('ikan', 1) - 0.1, a('emas') + 0.3);
  const morph = p(t, a('berubah') - 0.1, a('berubah') + 0.6);
  const womanQ = p(t, a('berubah') + 0.1, a('perempuan') + 0.2);
  const showWoman = t > a('berubah') && t < a('suatu siang') - 0.4;
  const child = pb(t, a('anak') - 0.2, a('anak') + 0.5);
  const showChild = t > a('anak') - 0.3 && t < a('sampai seluruh') + 1;
  const angry = t > a('toba marah') && t < a('seketika') ? Math.sin(t * 50) * 2.5 : 0;
  const eat = p(t, a('menghabiskan') - 0.2, a('ayahnya') + 0.4);

  const flash = Math.max(p(t, a('berubah'), a('berubah') + 0.15) * (1 - p(t, a('berubah') + 0.15, a('berubah') + 0.9)), p(t, a('meletus'), a('meletus') + 0.12) * (1 - p(t, a('meletus') + 0.12, a('meletus') + 1.2)), p(t, shout, shout + 0.08) * (1 - p(t, shout + 0.08, shout + 0.5)) * 0.6);
  const label = t < a('tapi ilmuwan') ? 'LEGENDA' : 'SAINS';

  return (
    <AbsoluteFill style={{background: H.dark, overflow: 'hidden'}}>
      <StyleFonts />
      <EpisodeAudio vo="audio/vo/hikayat.mp3" words={C.words} music="audio/music/hikayat.wav" bed={0.5} duck={0.17} sfx={HIKAYAT_SFX} />
      <div style={{position: 'absolute', inset: 0, transform: `translate(${sh.x}px, ${sh.y}px)`}}>
        <Kelir t={t} dim={0.55 * dark} tint={dark > 0.05 ? `rgba(80,100,150,${0.5 * dark})` : undefined} />
        <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
          <HikDefs />
          {/* far landscape */}
          {t < a('seketika') + 2 && <Silhouette id="mtn" parts={[{poly: MOUNTAINS, perforate: 24}]} x={540} y={1180} s={1} opacity={0.55 * p(t, 1.0, 1.8) * (1 - p(t, a('tapi ilmuwan') - 1, a('tapi ilmuwan')))} rod={false} />}
          {/* the house */}
          <Silhouette id="house" parts={HOUSE} x={t > a('tapi') - 0.5 ? 880 : 790} y={1430} s={t > a('tapi') - 0.5 ? 0.78 : 0.9} opacity={Math.max(vis(1.3, a('suatu hari') - 0.3), vis(a('tapi') - 0.2, a('ia mau') - 0.5))} />
          {/* sun for "suatu siang" */}
          {t > a('suatu siang') - 0.6 && t < a('seketika') + 0.6 && <Silhouette id="sun" parts={SUN} x={820} y={560 + (1 - pb(t, a('suatu siang') - 0.5, a('suatu siang') + 0.3)) * 400} s={1} rot={t * 8} opacity={1 - p(t, a('seketika'), a('seketika') + 0.5)} />}
          {/* fishing line + fish */}
          {t > a('suatu hari') - 0.3 && t < a('tapi') + 0.4 && (
            <g opacity={1 - p(t, a('tapi'), a('tapi') + 0.4)}>
              <line x1={tobaX + 442} y1={1430 - 676} x2={800} y2={1260 - 300 * fishUp} stroke={H.dark} strokeWidth={3} />
              {fishUp > 0 && (
                <g>
                  {gold > 0 && <circle cx={800} cy={1250 - 300 * fishUp} r={260} fill="#FFD27A" opacity={0.35 * gold} style={{mixBlendMode: 'screen'}} />}
                  <Silhouette id="fish" parts={[{poly: FISH, perforate: 15}]} x={800} y={1300 - 300 * fishUp} s={0.8} rot={-70 + bob(2, 8)} rod={false}
                    extra={gold > 0 ? <g opacity={gold}>{Array.from({length: 26}).map((_, i) => <circle key={i} cx={-200 + (i % 9) * 48} cy={-60 + Math.floor(i / 9) * 50} r={10} fill="#FFE08A" />)}</g> : undefined} />
                </g>
              )}
            </g>
          )}
          {/* fish → woman */}
          {t > a('tapi') && t < a('berubah') + 0.8 && <Silhouette id="fish2" parts={[{poly: FISH, perforate: 15}]} x={620} y={1250} s={0.55 * (1 - morph)} rot={-20} rod={false} opacity={1 - morph} />}
          {showWoman && <Silhouette id="woman" parts={WOMAN} x={(t < a('ia mau') - 0.3 ? 620 : 720) + bob(3, 3)} y={1430} s={1.3 * (0.6 + 0.4 * womanQ)} flip rot={bob(4, 1.2)} opacity={womanQ} />}
          {showChild && <Silhouette id="child" parts={CHILD} x={(t < a('suatu siang') ? 540 : 420) + bob(5, 2)} y={1430} s={0.8 * child} rot={bob(6, 1.5)}
            extra={t > a('suatu siang') - 0.5 ? <path d={`M 100 -330 L ${180 - 40 * eat} -330 L ${190 - 40 * eat} -300 L ${180 - 40 * eat} -270 L 100 -270 Z`} fill={H.dark} opacity={1 - eat * 0.9} /> : undefined} />}
          {showToba && (
            <Silhouette id="toba" parts={THIEF} x={tobaX + bob(1, 3)} y={1430} s={1.3} flip={t > a('toba marah') - 1} rot={bob(2, 1.2) + angry}
              extra={t < a('tapi') ? <line x1={150} y1={-345} x2={340} y2={-520} stroke={H.dark} strokeWidth={8} /> : undefined} />
          )}
          {/* rain + flood */}
          {rain > 0 && Array.from({length: 46}).map((_, i) => {
            const x = rnd(i) * 1120 - 20, sp = 900 + rnd(i + 3) * 500;
            const y = ((t * sp + rnd(i + 7) * 2000) % 2200) - 200;
            return <line key={i} x1={x} y1={y} x2={x - 18} y2={y + 70} stroke={H.dark} strokeWidth={4} opacity={0.75 * rain} />;
          })}
          {waterY < 2300 && <Silhouette id="water" parts={[{poly: WATER, perforate: 20}]} x={540 + ((t * 40) % 170)} y={waterY} s={1} rod={false} />}
          {/* the island */}
          {t > a('dan pulau') - 0.4 && t < 47.6 && <Silhouette id="island" parts={ISLAND} x={540} y={waterY - 10 + 250 * (1 - pb(t, a('dan pulau') - 0.2, a('pulau') + 0.6))} s={1.1} rod={false} opacity={1 - p(t, 47.0, 47.5)} />}
          {/* volcano */}
          {volc > 0 && (
            <g opacity={volc}>
              {erupt > 0 && (
                <g>
                  <ellipse cx={540} cy={800} rx={420 * erupt} ry={520 * erupt} fill="#FF7A2A" opacity={0.55 * erupt} style={{mixBlendMode: 'screen'}} />
                  {Array.from({length: 9}).map((_, i) => {
                    const k = p(t, a('meletus') + i * 0.08, a('meletus') + 1.6 + i * 0.1);
                    return <circle key={i} cx={540 + (rnd(i) - 0.5) * 520 * k} cy={760 - 700 * k} r={60 + 120 * k} fill={H.dark} opacity={0.85 * (1 - 0.4 * k) * erupt} />;
                  })}
                </g>
              )}
              <Silhouette id="volc" parts={[{poly: volcPts, perforate: 20}]} x={540} y={1430} s={1} rod={false} />
              {fill > 0 && <path d={`M ${540 - 230 - 90 * fill} ${1430 - 300 + 40 * (1 - fill)} Q 540 ${1430 - 300 - 30 * fill} ${540 + 230 + 90 * fill} ${1430 - 300 + 40 * (1 - fill)} L ${540 + 230} ${1430 - 290} L ${540 - 230} ${1430 - 290} Z`} fill="#9FC8E8" opacity={0.75 * fill} />}
            </g>
          )}
          {/* the gunungan */}
          {gunShow && <Silhouette id="gun" parts={[{poly: GUNUNGAN, perforate: 19}]} x={gunX} y={t > 60 ? 1300 : 1460} s={gunScale} rot={gunRot} extra={<GununganOrnament />} />}
          {/* the ending: legend vs science, small emblems either side */}
          {t > a('jadi mana') - 0.2 && (
            <g opacity={p(t, a('jadi mana'), a('jadi mana') + 0.5)}>
              <Silhouette id="endfish" parts={[{poly: FISH, perforate: 15}]} x={250} y={1000} s={0.55} rot={-10 + bob(7, 4)} rod={false} />
              <Silhouette id="endvolc" parts={[{poly: VOLCANO, perforate: 20}]} x={830} y={1130} s={0.42} rod={false} />
            </g>
          )}
        </svg>
        {flash > 0 && <AbsoluteFill style={{background: 'radial-gradient(circle at 60% 45%, rgba(255,240,200,.95), rgba(255,200,120,.3) 50%, transparent 75%)', opacity: flash}} />}

        {/* lontar text */}
        <LT y={230} w={940} q={vis(0, 3.4, 0.01, 0.4)}><div style={{...title(48), whiteSpace: 'nowrap'}}>LEGENDA DANAU TOBA</div></LT>
        <LT y={230} w={520} q={vis(a('toba') - 0.1, a('suatu hari') - 0.2)}><div style={title(64, H.ember)}>TOBA</div></LT>
        <LT y={230} w={760} q={vis(a('emas') - 0.2, a('tapi') - 0.1)}><div style={title(58)}>IKAN <span style={{color: '#B8860B'}}>EMAS</span></div></LT>
        <LT y={230} w={820} q={vis(a('satu syarat') - 0.2, a('mereka') - 0.3)}><div style={title(56)}>SATU SYARAT</div></LT>
        <LT y={370} w={900} h={96} q={vis(a('asal-usulnya') - 0.1, a('mereka') - 0.3)}><div style={text(38)}>asal-usulnya tak boleh diceritakan</div></LT>
        <LT y={230} w={600} q={vis(a('samosir') - 0.1, 26.0)}><div style={title(64, H.ember)}>SAMOSIR</div></LT>
        <LT y={300} w={990} h={150} q={vis(shout - 0.05, a('seketika') + 0.3, 0.05)}><div style={{...title(58, H.ember), transform: `rotate(-2deg)`}}>“DASAR ANAK IKAN!”</div></LT>
        <LT y={230} w={760} q={vis(a('danau toba') - 0.2, 47.2)}><div style={title(66)}>DANAU TOBA</div></LT>
        <LT y={360} w={640} h={96} q={vis(a('samosir', 2) - 0.2, 47.2)}><div style={text(40)}>dan Pulau Samosir</div></LT>
        <LT y={230} w={560} q={vis(a('itu legendanya') - 0.1, 47.8)}><div style={title(58)}>— LEGENDA —</div></LT>
        <LT y={220} w={960} h={160} q={vis(a('sekitar') - 0.1, a('salah satu') - 0.2)}>
          <div style={title(30)}>SEKITAR</div><div style={{...title(56, H.ember), whiteSpace: 'nowrap'}}>74.000 TAHUN LALU</div>
        </LT>
        <LT y={220} w={960} h={160} q={vis(a('salah satu') - 0.1, a('kawahnya') - 0.2)}>
          <div style={title(34)}>SALAH SATU LETUSAN</div><div style={title(58, H.ember)}>TERBESAR</div>
        </LT>
        <LT y={230} w={980} q={vis(a('kawahnya') - 0.1, a('jadi mana') - 0.4)}><div style={title(42)}>KAWAH RUNTUH <span style={{opacity: p(t, a('terisi') - 0.2, a('terisi') + 0.2)}}>→ DANAU</span></div></LT>
        <LT y={230} w={960} h={120} q={p(t, a('jadi mana') - 0.1, a('jadi mana') + 0.4)}><div style={{...title(42), whiteSpace: 'nowrap'}}>MANA YANG KAMU PERCAYA?</div></LT>
        <Lontar x={40} y={1150} w={480} h={140} q={p(t, a('janji') - 0.1, a('janji') + 0.4)}><div style={{...text(38), width: '100%', textAlign: 'center', lineHeight: 1.05}}>janji yang<br />dilanggar</div></Lontar>
        <Lontar x={560} y={1150} w={480} h={140} q={p(t, a('gunung yang') - 0.1, a('gunung yang') + 0.4)}><div style={{...text(38), width: '100%', textAlign: 'center', lineHeight: 1.05}}>gunung yang<br />meledak</div></Lontar>
        <Stage />
      </div>
      <div style={{position: 'absolute', right: 70, top: 96, width: 260, height: 54}}>
        <Lontar x={0} y={0} w={260} h={54} q={1}><div style={{...title(22), width: '100%', textAlign: 'center'}}>{label}</div></Lontar>
      </div>
      <Caption t={t} pages={PAGES} />
      <AbsoluteFill style={{background: 'radial-gradient(ellipse 120% 90% at 50% 40%, transparent 55%, rgba(10,4,0,.6))', pointerEvents: 'none'}} />
    </AbsoluteFill>
  );
};

const s = (t: number, src: string, vol = 0.55): Sfx => ({t, src: `audio/sfx/hikayat/${src}.wav`, vol});
export const HIKAYAT_SFX: Sfx[] = [
  s(0.0, 'gong', 0.6), s(1.0, 'swoosh', 0.5), s(a('pemuda') - 0.3, 'kecrek', 0.4),
  s(a('suatu hari') - 0.4, 'water', 0.45), s(a('ikan') - 0.1, 'splash', 0.55), s(a('ikan', 1), 'chime', 0.5),
  s(a('tapi') - 0.2, 'swoosh', 0.35), s(a('berubah') - 0.05, 'magic', 0.6), s(a('satu syarat'), 'kecrek', 0.35),
  s(a('anak') - 0.1, 'kecrek', 0.35), s(25.95, 'swoosh', 0.5), s(26.6, 'gong', 0.45),
  s(a('toba marah') - 0.5, 'kendang', 0.55), s(a('dasar') - 0.02, 'gong', 0.8), s(a('dasar'), 'kendang', 0.6),
  s(a('seketika'), 'thunder', 0.75), s(a('hujan') - 0.1, 'rain', 0.6), s(a('sampai seluruh') - 0.3, 'flood', 0.6),
  s(a('dan pulau') - 0.2, 'chime', 0.4), s(a('itu legendanya'), 'kecrek', 0.35), s(47.55, 'swoosh', 0.5), s(48.2, 'gong', 0.45),
  s(a('meletus') - 0.05, 'boom', 0.85), s(a('runtuh') - 0.1, 'rumble', 0.6), s(a('terisi') - 0.4, 'water', 0.45),
  s(a('jadi mana') - 0.4, 'swoosh', 0.45), s(a('janji'), 'kecrek', 0.35), s(a('gunung yang'), 'kecrek', 0.35), s(a('meledak') + 0.4, 'gong', 0.6),
];
