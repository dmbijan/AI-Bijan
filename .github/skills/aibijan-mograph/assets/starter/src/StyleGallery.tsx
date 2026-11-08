import React from 'react';
import {AbsoluteFill, Sequence, useCurrentFrame, useVideoConfig} from 'remotion';
import {CaptionPage} from './components/WordCaptions';
import {drawOn, p, pb, roughEllipse, StyleFonts} from './styles/shared';
import * as AR from './styles/arsip/kit';
import * as BP from './styles/blueprint/kit';
import * as SK from './styles/sketsa/kit';
import * as HK from './styles/hikayat/kit';
import * as GM from './styles/geometrik/kit';
import * as TM from './styles/terminal/kit';

// QA sheet for the six named style kits (ARSIP, BLUEPRINT, SKETSA, HIKAYAT, GEOMETRIK, TERMINAL): 4 s each.
// npm run style-preview → out/styles/sheet.jpg. Never the delivered video; the render always uses `Mograph`.

const fakePage = (text: string, start: number): CaptionPage[] => {
  const ws = text.split(' ').map((word, i) => ({i, word, start: start + i * 0.32, end: start + i * 0.32 + 0.28}));
  return [{start, end: start + 6, words: ws}];
};

const useT = () => {
  const f = useCurrentFrame();
  const {fps} = useVideoConfig();
  return f / fps;
};

const ArsipDemo: React.FC = () => {
  const t = useT();
  return (
    <AbsoluteFill style={{background: AR.A.desk}}>
      <AbsoluteFill style={{backgroundImage: AR.DESK, backgroundSize: '1024px 300px', opacity: 0.3, mixBlendMode: 'overlay'}} />
      <AR.Sheet x={80} y={200} w={560} h={300} rot={-2}>
        <div style={{position: 'absolute', left: 30, top: 24, fontFamily: AR.TYPE, fontWeight: 700, fontSize: 24, color: AR.A.red}}>
          <AR.TypeOn text="BERKAS No. 01 · ARSIP" t={t} start={0.1} cps={24} />
        </div>
        <div style={{position: 'absolute', left: 30, top: 70, fontFamily: AR.HEAD, fontWeight: 900, fontSize: 84, lineHeight: 1, color: AR.A.ink}}>Kasus<br />Contoh</div>
      </AR.Sheet>
      <AR.Mugshot x={600} y={300} rot={3} q={p(t, 0.4, 0.8)} n="#1" v="laurel" plate="ARSIP · 01" />
      <AR.CalendarPage t={t} x={90} y={620} w={480} rot={-1.5} shown={Math.floor(28 * p(t, 0.6, 1.6))} title="FEBRUARI" sub="CONTOH" markers={3} markerAt={1.7} circle28={p(t, 1.6, 2.0)} />
      <AR.Card x={600} y={980} w={400} h={200} rot={2} q={p(t, 1.0, 1.4)}>
        <div style={{position: 'absolute', left: 24, top: 24, fontFamily: AR.TYPE, fontWeight: 700, fontSize: 30, color: AR.A.ink}}>Card + Pin + Ring</div>
      </AR.Card>
      <AR.Pin x={800} y={990} q={p(t, 1.3, 1.4)} />
      <AR.Ring cx={800} cy={1080} rx={190} ry={80} q={p(t, 1.6, 2.1)} />
      <AR.Stamp id="gallery" t={t} at={2.2} x={540} y={760} w={600} h={190} rot={-9} lines={['STEMPEL', '— CONTOH —']} size={80} />
      <AR.Caption t={t} pages={fakePage('Caption kertas sobek dengan stabilo', 0.3)} />
    </AbsoluteFill>
  );
};

const BlueprintDemo: React.FC = () => {
  const t = useT();
  const iso = BP.isoAt(540, 1020, 280);
  return (
    <AbsoluteFill style={{background: BP.B.bg}}>
      <svg width={1080} height={1920}>
        <defs>
          <pattern id="sg-min" width="36" height="36" patternUnits="userSpaceOnUse"><path d="M 36 0 L 0 0 0 36" fill="none" stroke="#fff" strokeOpacity="0.07" /></pattern>
          <pattern id="sg-maj" width="180" height="180" patternUnits="userSpaceOnUse"><path d="M 180 0 L 0 0 0 180" fill="none" stroke="#fff" strokeOpacity="0.15" strokeWidth="1.5" /></pattern>
        </defs>
        <rect width={1080} height={1920} fill="url(#sg-min)" /><rect width={1080} height={1920} fill="url(#sg-maj)" />
        <BP.Typed x={96} y={280} text="DETAIL A — CONTOH" t={t} start={0.1} size={34} color={BP.B.cyan} />
        <text x={96} y={380} fill={BP.B.line} style={{fontFamily: BP.HEAD, fontWeight: 700, fontSize: 90}}>Gambar teknik</text>
        <BP.IsoBox iso={iso} x0={-0.5} x1={0.5} y0={-0.5} y1={0.5} z0={0} z1={0.3} q={p(t, 0.3, 1.3)} />
        <BP.IsoBox iso={iso} x0={-0.5} x1={0.5} y0={-0.5} y1={0.5} z0={0.4 + 0.3 * p(t, 1.6, 2.2)} z1={0.76 + 0.3 * p(t, 1.6, 2.2)} topInset={0.12} q={p(t, 0.7, 1.6)} />
        <BP.Callout a={iso(-0.3, 0.2, 0.9)} l={[300, 640]} n="1" label="KEYCAP" side="left" t={t} start={2.0} />
        <BP.Callout a={iso(0.35, 0.1, 0.12)} l={[820, 1150]} n="2" label="MODUL" t={t} start={2.3} color={BP.B.yellow} />
        <BP.Dim a={[180, 1400]} b={[900, 1400]} text="DIMENSI 720" q={p(t, 2.4, 3.0)} />
        <BP.Hatch x={140} y={1240} w={120} h={120} q={p(t, 1.0, 1.6)} id="sg-h" />
        <BP.BreakMark x={760} y={560} w={120} q={p(t, 2.6, 3.0)} />
      </svg>
      <BP.Caption t={t} pages={fakePage('Caption mono dengan tanda potong', 0.3)} />
    </AbsoluteFill>
  );
};

const SketsaDemo: React.FC = () => {
  const t = useT();
  const {fps} = useVideoConfig();
  const frame = useCurrentFrame();
  return (
    <AbsoluteFill>
      <SK.Page>
        <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
          <defs><SK.Boil id="sg-boil" frame={frame} /></defs>
          <g filter="url(#sg-boil)">
            <SK.Swipe x={136} y={232} w={480} h={62} q={p(t, 0, 0.6)} />
            <text x={140} y={292} fill={SK.S.ink} style={{fontFamily: SK.HEAD, fontSize: 100}}>Buku catatan</text>
            <SK.Spotlight lamp={[540, 470]} target={[540, 1150]} spread={200} q={p(t, 0.4, 1.0)} seed={3} lampScale={1.4} />
            <SK.Sc x={540} y={1150} ox={540} oy={1150} k={1.5}><SK.Stick x={540} base={1150} q={p(t, 0.5, 1.6)} seed={5} style="spiky" color={SK.S.coral} /></SK.Sc>
            <SK.Bubble x={600} y={560} w={380} h={190} q={pb(t, 1.6, 2.0)} seed={9} tail={[600, 820]} thought>
              <text x={790} y={672} textAnchor="middle" fill={SK.S.ink} style={{fontFamily: SK.HEAD, fontSize: 60}}>hmm…</text>
            </SK.Bubble>
            <SK.Pie cx={240} cy={1300} r={110} part={0.25} q={p(t, 2.0, 2.4)} fill={p(t, 2.2, 2.8)} color={SK.S.yellow} seed={4} id="sg-pie" />
            <path d={roughEllipse(850, 1300, 120, 80, 7)} {...drawOn(p(t, 2.6, 3.1))} fill="none" stroke={SK.S.coral} strokeWidth={6} strokeLinecap="round" />
          </g>
        </svg>
      </SK.Page>
      <SK.Caption t={t} pages={fakePage('Caption tulisan tangan dengan stabilo', 0.3 + 0 * fps)} />
    </AbsoluteFill>
  );
};


const HikayatDemo: React.FC = () => {
  const t = useT();
  return (
    <AbsoluteFill style={{background: HK.H.dark}}>
      <HK.Kelir t={t} />
      <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
        <HK.HikDefs />
        <HK.Silhouette id="sg-mtn" parts={[{poly: HK.MOUNTAINS, perforate: 24}]} x={540} y={1180} opacity={0.55} rod={false} />
        <HK.Silhouette id="sg-gun" parts={[{poly: HK.GUNUNGAN, perforate: 19}]} x={540 - 1300 * p(t, 1.2, 2.2)} y={1460} s={1.08} rot={-30 * p(t, 1.2, 2.2)} extra={<HK.GununganOrnament />} />
        <HK.Silhouette id="sg-king" parts={HK.KING} x={720} y={1430} s={1.25} flip opacity={p(t, 1.6, 2.2)} />
        <HK.Silhouette id="sg-woman" parts={HK.WOMAN} x={380} y={1430} s={1.25} opacity={p(t, 1.9, 2.5)} />
      </svg>
      <HK.Lontar x={90} y={230} w={900} q={p(t, 0.2, 0.6)}><div style={{...HK.lontarTitle(52), width: '100%', textAlign: 'center'}}>HIKAYAT · CONTOH</div></HK.Lontar>
      <HK.Stage />
      <HK.Caption t={t} pages={fakePage('Caption di daun lontar', 0.3)} />
    </AbsoluteFill>
  );
};

const GeometrikDemo: React.FC = () => {
  const t = useT();
  return (
    <AbsoluteFill>
      <GM.GridPage />
      <div style={{position: 'absolute', left: 72, top: 200}}><GM.Big size={120} stretch={70}>DATA GEOMETRIK</GM.Big></div>
      <GM.Block x={72} y={520} w={300} h={300} color={GM.G.red} q={pb(t, 0.3, 0.7)}><GM.Big size={200} color="#fff">72</GM.Big></GM.Block>
      <GM.Block x={400} y={520} w={300} h={300} color={GM.G.yellow} q={pb(t, 0.6, 1.0)} round><GM.Big size={140}>%</GM.Big></GM.Block>
      <GM.Block x={72} y={880} w={936} h={170} color={GM.G.blue} q={GM.ps(t, 1.0, 1.4)} from="left"><div style={GM.label(44, '#fff')}>BLOK = DATA</div></GM.Block>
      <svg width={1080} height={1920} style={{position: 'absolute', left: 0, top: 0}}>
        {[28, 30, 31].map((n, i) => <rect key={n} x={120 + i * 300} y={1400 - n * 9 * GM.ps(t, 1.4 + i * 0.2, 1.9 + i * 0.2)} width={220} height={n * 9 * GM.ps(t, 1.4 + i * 0.2, 1.9 + i * 0.2)} fill={[GM.G.red, GM.G.blue, GM.G.ink][i]} />)}
      </svg>
      <GM.Caption t={t} pages={fakePage('Caption blok hitam stabilo kuning', 0.3)} />
    </AbsoluteFill>
  );
};

const TerminalDemo: React.FC = () => {
  const t = useT();
  const frame = useCurrentFrame();
  return (
    <AbsoluteFill style={{background: TM.T.bg}}>
      <TM.HexRain t={t} />
      <TM.Win x={72} y={190} w={936} h={520} title="contoh.log" q={p(t, 0, 0.3)}>
        <div><TM.Typed text="> scan --data" t={t} start={0.2} cps={20} /></div>
        <div style={{fontSize: 34, marginTop: 10}}><TM.Bar n={24} total={31} shown={Math.floor(31 * p(t, 0.6, 1.6))} missing /></div>
      </TM.Win>
      <TM.Win x={120} y={780} w={840} h={260} title="ALERT" q={pb(t, 1.8, 2.1)} color={TM.T.alert}>
        <div style={{fontFamily: TM.HEAD, fontSize: 92, lineHeight: 1, color: TM.T.alert, textShadow: TM.glow(TM.T.alert)}}>!! ANOMALI</div>
      </TM.Win>
      <svg width={1080} height={400} style={{position: 'absolute', left: 0, top: 1080}}><TM.Lock x={540} y={200} s={2} open={p(t, 2.6, 3.0)} /></svg>
      <TM.Glitch frame={frame} active={t > 1.8 && t < 2.0} />
      <TM.Caption t={t} pages={fakePage('Caption prompt terminal', 0.3)} />
      <TM.CRT frame={frame} />
    </AbsoluteFill>
  );
};

export const StyleGallery: React.FC = () => {
  const {fps} = useVideoConfig();
  return (
    <AbsoluteFill style={{background: '#000'}}>
      <StyleFonts />
      <Sequence from={0} durationInFrames={4 * fps}><ArsipDemo /></Sequence>
      <Sequence from={4 * fps} durationInFrames={4 * fps}><BlueprintDemo /></Sequence>
      <Sequence from={8 * fps} durationInFrames={4 * fps}><SketsaDemo /></Sequence>
      <Sequence from={12 * fps} durationInFrames={4 * fps}><HikayatDemo /></Sequence>
      <Sequence from={16 * fps} durationInFrames={4 * fps}><GeometrikDemo /></Sequence>
      <Sequence from={20 * fps} durationInFrames={4 * fps}><TerminalDemo /></Sequence>
    </AbsoluteFill>
  );
};
