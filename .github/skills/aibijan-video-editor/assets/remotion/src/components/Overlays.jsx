// Motion-graphics overlays. Each is a pure function of time, configured from edit.json:
//   {type, start, end, ...props}. Times are seconds on the edited timeline.
// Illustrations (chat panels, prompt bars) are stylised and generic on purpose: they illustrate the
// workflow and must never be presented as screenshots of a real product.
import React from 'react';
import {Img, OffthreadVideo, Sequence, staticFile} from 'remotion';
import {clamp, dirBlur, ease, lerp, life, prog, rand, shutterBlur, springAt} from '../lib/anim.js';
import {Icon} from './Icons.jsx';
import {FxText, LeakSweep} from './FX.jsx';
import {Accents, EndCardHero, HeroTitle, SceneChat, SceneFeatures, ScenePhones, ScenePrompt, SceneStack, ShapeWipe} from './Scenes.jsx';
import {Floaters, MoodGrade, SceneLens, SearchStack, Shout, StoryTitle} from './Story.jsx';
import {CUSTOM} from './custom/index.js';
import {SceneBuild} from './Kit.jsx';
import {DepthText, Doodles, FeedWall, HeadRing} from './Depth.jsx';
import {BadgeOrbit, Checklist, Glossy, GlowPill, SideCards} from './Premium.jsx';
import {BigNote, HeadNote, NoteBoard, NoteClips, NoteTags} from './Notes.jsx';
import {faceOnCanvas} from './Footage.jsx';

const tone = (C, name) => ({
  ink: {bg: C.ink, fg: C.paper, ic: C.accent},
  accent: {bg: C.accent, fg: C.ink, ic: C.ink},
  accent2: {bg: C.accent2, fg: C.paper, ic: C.paper},
  paper: {bg: C.paper, fg: C.ink, ic: C.accent2},
}[name || 'ink']);

// Relative luminance of a #RRGGBB colour (0 = black, 1 = white); used to keep text readable on dark styles.
const lum = (hex) => {
  const m = /^#?([0-9a-f]{6})$/i.exec(String(hex || '')); if (!m) return 1;
  const n = parseInt(m[1], 16), ch = [(n >> 16) & 255, (n >> 8) & 255, n & 255].map((v) => { v /= 255; return v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4; });
  return 0.2126 * ch[0] + 0.7152 * ch[1] + 0.0722 * ch[2];
};
const onBg = (C) => (lum(C.bg) < 0.25 ? C.paper : C.ink);

const shadow = (k = 1) => `0 ${18 * k}px ${50 * k}px rgba(12,14,10,${0.2 * Math.min(1.4, k)}), 0 2px 6px rgba(12,14,10,0.08)`;

// ------------------------------------------------------------------------------------------------ Viewfinder
const Viewfinder = ({E, t, o}) => {
  const C = E.style.colors, W = E.width, H = E.height;
  const {out} = life(t, o.start, o.end, E.fps, {exitDur: 0.12});
  const op = 1 - out;
  const r = o.rect || [64, 280, W - 128, H - 280 - 640];
  const L = 70, th = 6;
  const corner = (x, y, sx, sy) => (
    <path d={`M${x} ${y + sy * L} L${x} ${y} L${x + sx * L} ${y}`} stroke="#fff" strokeWidth={th} fill="none" strokeLinecap="round" />
  );
  const src = (o.tcOffset || 0) + (t - o.start);
  const fr = Math.floor((src % 1) * E.fps), ss = Math.floor(src) % 60, mm = Math.floor(src / 60);
  const tc = `00:${String(mm).padStart(2, '0')}:${String(ss).padStart(2, '0')}:${String(fr).padStart(2, '0')}`;
  const blink = Math.floor(t * 2) % 2 === 0;
  const lab = {fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: 34, color: '#fff', letterSpacing: '0.08em', textShadow: '0 2px 8px rgba(0,0,0,0.5)'};
  return (
    <div style={{position: 'absolute', inset: 0, opacity: op}}>
      <svg width={W} height={H} style={{position: 'absolute', inset: 0}}>
        {corner(r[0], r[1], 1, 1)}{corner(r[0] + r[2], r[1], -1, 1)}
        {corner(r[0], r[1] + r[3], 1, -1)}{corner(r[0] + r[2], r[1] + r[3], -1, -1)}
        <line x1={W / 2 - 26} y1={r[1] + r[3] / 2} x2={W / 2 + 26} y2={r[1] + r[3] / 2} stroke="rgba(255,255,255,0.8)" strokeWidth={3} />
        <line x1={W / 2} y1={r[1] + r[3] / 2 - 26} x2={W / 2} y2={r[1] + r[3] / 2 + 26} stroke="rgba(255,255,255,0.8)" strokeWidth={3} />
      </svg>
      <div style={{position: 'absolute', left: r[0] + 34, top: r[1] + 30, display: 'flex', alignItems: 'center', gap: 14, ...lab}}>
        <span style={{width: 24, height: 24, borderRadius: 12, background: '#FF3B30', opacity: blink ? 1 : 0.25}} />REC
      </div>
      <div style={{position: 'absolute', right: W - r[0] - r[2] + 34, top: r[1] + 26, padding: '6px 16px', borderRadius: 10,
        background: 'rgba(0,0,0,0.55)', ...lab}}>{o.label || 'RAW'}</div>
      <div style={{position: 'absolute', left: r[0] + 34, top: r[1] + r[3] - 74, ...lab, fontWeight: 500}}>{tc}</div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Badge
const Badge = ({E, t, o}) => {
  const C = E.style.colors;
  const {p, out} = life(t, o.start, o.end, E.fps, {enter: {damping: 12, stiffness: 200}});
  const tn = tone(C, o.tone);
  const size = o.size || 40;
  const ax = o.align === 'left' ? '0%' : o.align === 'right' ? '-100%' : '-50%';
  return (
    <div style={{position: 'absolute', left: o.x, top: o.y, transform: `translate(${ax}, -50%) scale(${p * (1 - 0.15 * out)}) rotate(${(o.rot || 0) * p}deg)`,
      opacity: 1 - out, display: 'flex', alignItems: 'center', gap: size * 0.32, padding: `${size * 0.36}px ${size * 0.62}px`,
      borderRadius: 999, background: tn.bg, color: tn.fg, boxShadow: shadow(0.8), fontFamily: E.style.fonts.head, fontWeight: 800,
      fontSize: size, letterSpacing: '-0.01em', whiteSpace: 'nowrap'}}>
      {o.icon ? <Icon name={o.icon} size={size * 1.05} color={tn.ic} stroke={2.4} /> : null}
      {o.text}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ KineticTitle
// Stacked headline lines, each animated with an FxText preset (rise/pop/slam/track/focus/slide/cascade/type).
// x is the horizontal CENTRE of the block (default canvas centre); align:'left' makes x the left edge instead.
// dim: 0..0.7 darkens the whole frame behind the title (pattern-interrupt "word slam" moments).
const KineticTitle = ({E, t, o}) => {
  const C = E.style.colors;
  const W = E.width;
  const left = o.align === 'left';
  const width = o.width ?? (W - 120);
  const x = o.x ?? (left ? 60 : W / 2);
  const defFx = o.fx || E.style.textFx || 'rise';
  const dimIn = o.dim ? ease.smooth(prog(t, o.start - 0.05, o.start + 0.25)) * (1 - ease.exitFast(prog(t, o.end - 0.2, o.end))) : 0;
  return (
    <>
      {o.dim ? <div style={{position: 'absolute', inset: 0, background: `rgba(0,0,0,${(o.dim * dimIn).toFixed(3)})`}} /> : null}
      <div style={{position: 'absolute', left: left ? x : x - width / 2, top: o.y, width, display: 'flex', flexDirection: 'column',
        alignItems: left ? 'flex-start' : 'center', gap: o.gap ?? 10}}>
        {o.lines.map((ln, i) => {
          const at = ln.at ?? o.start + i * 0.12;
          const chip = ln.tone === 'chip' || ln.tone === 'accentChip';
          const fx = ln.fx || defFx;
          const size = ln.size || 90;
          const serif = ln.font === 'serif';
          const color = ln.tone === 'accent' ? C.accent : ln.tone === 'paper' ? C.paper : ln.tone === 'accentChip' ? C.ink : chip ? C.accent : C.ink;
          const disp = !serif && E.style.fonts.display;
          const style = {fontFamily: serif ? E.style.fonts.serif : (disp || E.style.fonts.head), fontStyle: serif ? 'italic' : 'normal',
            fontWeight: serif ? (E.style.fonts.serifWeight ?? 400) : (disp ? (E.style.fonts.displayWeight ?? 400) : 800), fontSize: size, lineHeight: 1.04, letterSpacing: serif ? '-0.01em' : '-0.03em', wordSpacing: serif ? '0.04em' : '0.12em', color,
            textShadow: ln.shadow ? '0 4px 24px rgba(0,0,0,0.35)' : 'none', whiteSpace: 'nowrap'};
          if (t < at) return <div key={i} style={{height: size * 1.08 + (chip ? size * 0.12 : 0), opacity: 0}} />;
          // chips: the plate wipes open left->right (AE rectangle mask) and the text animates inside it
          const plate = chip ? ease.snappy(prog(t, at - 0.04, at + 0.32)) : 1;
          const out = ease.exitFast(prog(t, o.end - 0.2, o.end));
          const inner = <FxText t={t} at={at + (chip ? 0.06 : 0)} out={o.end} text={ln.text} fx={fx} fps={E.fps} style={style} caretColor={C.accent} />;
          return (
            <div key={i} style={{transform: `rotate(${(ln.rot || 0) * plate}deg)`, opacity: chip ? 1 - out : 1}}>
              {chip ? (
                <div style={{position: 'relative', padding: '0.06em 0.24em', borderRadius: 18, fontSize: size}}>
                  <div style={{position: 'absolute', inset: 0, borderRadius: 18, background: ln.tone === 'accentChip' ? C.accent : C.ink,
                    transform: `scaleX(${plate})`, transformOrigin: left ? '0 50%' : '50% 50%', boxShadow: shadow(0.7)}} />
                  <div style={{position: 'relative'}}>{inner}</div>
                </div>
              ) : inner}
            </div>
          );
        })}
      </div>
    </>
  );
};

// ------------------------------------------------------------------------------------------------ Newsprint
// Editorial paper clipping. Headline text must be grounded in the user's transcript/user-provided copy.
// This is a fictional design element, never a depiction of an actual newspaper or news source.
const Newsprint = ({E, t, o}) => {
  const C = E.style.colors;
  const {p, out} = life(t, o.start, o.end, E.fps, {enter: {damping: 17, stiffness: 170}, exitDur: 0.23});
  const w = o.w || Math.min(840, E.width - 120);
  const x = o.x ?? E.width / 2;
  const y = o.y ?? E.height * 0.18;
  const title = String(o.headline || '').slice(0, 86);
  const subhead = String(o.subhead || '').slice(0, 130);
  const kicker = String(o.kicker || 'EDITORIAL NOTE').slice(0, 40);
  const rotation = o.tilt ?? -2.5;
  const enterY = (1 - Math.min(1, p)) * 125;
  const markProgress = ease.out(prog(t, o.start + 0.4, o.start + 0.78));
  const red = C.accent || '#D7362E';
  if (!title) return null;
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, padding: '29px 37px 30px', boxSizing: 'border-box',
      transform: `translateX(-50%) translateY(${enterY - out * 70}px) scale(${0.89 + 0.11 * Math.min(1, p) - 0.055 * out}) rotate(${rotation + (1 - Math.min(1, p)) * -6}deg)`,
      transformOrigin: '50% 50%', opacity: Math.min(1, p * 2) * (1 - out),
      backgroundColor: o.paper || C.paper, color: C.ink, boxShadow: '0 23px 45px rgba(24,22,20,.22),0 5px 12px rgba(24,22,20,.13)',
      clipPath: 'polygon(0% 0%, 13% 0.35%, 26% 0%, 44% .4%, 59% 0%, 78% .3%, 100% 0%, 99.6% 18%, 100% 34%, 99.55% 54%, 100% 75%, 99.7% 100%, 86% 99.6%, 70% 100%, 54% 99.5%, 33% 100%, 12% 99.7%, 0% 100%, .45% 78%, 0% 55%, .35% 31%)'}}>
      <div style={{position: 'absolute', inset: 0, opacity: 0.12, pointerEvents: 'none',
        backgroundImage: 'radial-gradient(rgba(32,31,27,.45) .85px, transparent 1px)', backgroundSize: '7px 7px'}} />
      <div style={{position: 'relative', borderTop: `5px solid ${C.ink}`, borderBottom: `2px solid ${C.ink}`,
        padding: '8px 0 10px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 15,
        fontFamily: E.style.fonts.ui, fontSize: 17, letterSpacing: '.09em', fontWeight: 700}}>
        <span>{kicker}</span><span style={{fontSize: 15, whiteSpace: 'nowrap'}}>✦</span>
      </div>
      <div style={{position: 'relative', padding: '20px 0 15px', fontFamily: E.style.fonts.serif,
        fontSize: o.fontSize || 92, lineHeight: 0.93, letterSpacing: '-.045em',
        overflowWrap: 'break-word', wordBreak: 'normal'}}>
        <span style={{position: 'relative', display: 'inline'}}>{title}</span>
        {o.mark === 'underline' ? (
          // Marks are positioned relative to the headline box, so they follow wrapping instead of striking through text.
          <div style={{position: 'absolute', left: '1%', width: '92%', bottom: 4, height: 11, borderRadius: 6, background: red,
            transform: `rotate(-0.8deg) scaleX(${markProgress})`, transformOrigin: '0 50%'}} />
        ) : o.mark === 'circle' ? (
          <div style={{position: 'absolute', left: '-4%', right: '-4%', top: '2%', bottom: '-2%', borderRadius: '50%',
            border: `8px solid ${red}`, opacity: Math.min(1, markProgress * 1.6),
            transform: `rotate(-3deg) scale(${0.86 + 0.14 * markProgress})`}} />
        ) : null}
      </div>
      <div style={{position: 'relative', height: 8, borderTop: `2px solid ${C.ink}`, borderBottom: `1px solid ${C.ink}`, marginBottom: subhead ? 17 : 0}} />
      {subhead ? <div style={{position: 'relative', fontFamily: E.style.fonts.body, fontSize: o.subSize || 25,
        fontWeight: 700, lineHeight: 1.25, maxWidth: '94%'}}>{subhead}</div> : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ InsightCard (business explainer)
// Only display statistics explicitly present in transcript or supplied by the user.
const InsightCard = ({E, t, o}) => {
  const C = E.style.colors;
  const {p, out} = life(t, o.start, o.end, E.fps, {enter: {damping: 19, stiffness: 190}, exitDur: 0.22});
  const v = Math.min(1, p);
  const x = o.x ?? E.width / 2, y = o.y ?? E.height * 0.17, w = o.w ?? Math.min(870, E.width - 100);
  const headline = String(o.headline || '').slice(0, 76);
  if (!headline) return null;
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, boxSizing: 'border-box', padding: '30px 34px 34px',
      transform: `translateX(-50%) translateY(${(1 - v) * 85 - out * 50}px) scale(${0.91 + v * .09 - out * .05})`,
      opacity: v * (1 - out), borderRadius: 28, color: C.ink, background: C.paper,
      border: `2px solid ${C.line}`, boxShadow: shadow(1.2), overflow: 'hidden'}}>
      <div style={{height: 9, position: 'absolute', left: 0, top: 0, right: 0, background: C.accent2}} />
      <div style={{display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 19,
        fontFamily: E.style.fonts.ui, fontSize: 24, fontWeight: 800, letterSpacing: '.06em'}}>
        <span>{String(o.eyebrow || 'POINT UTAMA').slice(0, 38)}</span>
        <span style={{background: C.accent, padding: '4px 10px', borderRadius: 8}}>↗</span>
      </div>
      <div style={{fontFamily: E.style.fonts.head, fontSize: o.fontSize || 67, fontWeight: 800,
        lineHeight: 1.04, letterSpacing: '-.04em', overflowWrap: 'break-word'}}>{headline}</div>
      {o.value ? <div style={{fontFamily: E.style.fonts.head, fontSize: o.valueSize || 82, fontWeight: 800,
        color: C.accent2, marginTop: 8}}>{String(o.value).slice(0, 24)}</div> : null}
      {o.detail ? <div style={{fontFamily: E.style.fonts.body, color: C.muted, fontSize: 26, lineHeight: 1.3,
        marginTop: 12, fontWeight: 700}}>{String(o.detail).slice(0, 115)}</div> : null}
      <div style={{height: 7, background: C.soft, borderRadius: 4, marginTop: 23, overflow: 'hidden'}}>
        <div style={{height: '100%', width: `${Math.min(100, Math.max(0, (t - o.start) / .7 * 100))}%`, background: C.accent2}} />
      </div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ EvidenceCard (evidence / proof card)
// Labels are editorial signposts, never presented as independent proof. sourceLabel only from real user assets.
const EvidenceCard = ({E, t, o}) => {
  const C = E.style.colors;
  const {p, out} = life(t, o.start, o.end, E.fps, {enter: {damping: 21, stiffness: 140}, exitDur: .3});
  const v = Math.min(1, p), w = o.w || Math.min(E.width - 120, 820);
  const x = o.x ?? E.width / 2, y = o.y ?? E.height * .16;
  const headline = String(o.headline || '').slice(0, 88);
  if (!headline) return null;
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, background: C.paper, borderRadius: 7,
      padding: '33px 43px 35px', boxSizing: 'border-box', color: C.ink, boxShadow: '0 22px 56px rgba(0,0,0,.32)',
      opacity: v * (1 - out), transform: `translateX(-50%) translateY(${(1 - v) * 35 - out * 30}px) scale(${.97 + v * .03})`}}>
      <div style={{display: 'flex', alignItems: 'center', justifyContent: 'space-between', borderBottom: `2px solid ${C.line}`,
        paddingBottom: 17, marginBottom: 20, fontSize: 20, fontFamily: E.style.fonts.ui, fontWeight: 700, letterSpacing: '.11em'}}>
        <span>{String(o.label || 'PENJELASAN').slice(0, 34)}</span>
        <span style={{color: C.accent2}}>●</span>
      </div>
      <div style={{fontFamily: E.style.fonts.serif, fontSize: o.fontSize || 82, lineHeight: .99,
        letterSpacing: '-.025em', overflowWrap: 'break-word'}}>{headline}</div>
      {o.detail ? <div style={{fontFamily: E.style.fonts.body, marginTop: 20, fontSize: 27,
        lineHeight: 1.35, color: '#404A50', fontWeight: 600}}>{String(o.detail).slice(0, 135)}</div> : null}
      <div style={{height: 5, marginTop: 26, background: C.line}}>
        <div style={{height: '100%', width: `${Math.min(100, Math.max(0, (t - o.start - .18) / .8 * 100))}%`, background: C.accent2}} />
      </div>
      {o.sourceLabel ? <div style={{fontFamily: E.style.fonts.ui, fontSize: 18, color: '#646E73', marginTop: 13}}>
        SUMBER: {String(o.sourceLabel).slice(0, 70)}</div> : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ ScrapNote (hand-placed paper note)
const ScrapNote = ({E, t, o}) => {
  const C = E.style.colors;
  const {p, out} = life(t, o.start, o.end, E.fps, {enter: {damping: 16, stiffness: 175}, exitDur: .22});
  const v = Math.min(1, p), w = o.w || Math.min(E.width - 120, 760);
  const x = o.x ?? E.width / 2, y = o.y ?? E.height * .17;
  const headline = String(o.headline || '').slice(0, 74);
  if (!headline) return null;
  const tilt = Math.min(4, Math.max(-4, o.tilt ?? -2));
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, padding: '35px 37px 37px', boxSizing: 'border-box',
      color: C.ink, background: o.paper || C.paper, boxShadow: '9px 18px 0 rgba(44,32,25,.13),0 20px 45px rgba(44,32,25,.19)',
      border: `1px solid ${C.line}`, transformOrigin: 'center top',
      transform: `translateX(-50%) translateY(${(1 - v) * 110 - out * 50}px) scale(${.83 + v * .17}) rotate(${tilt + (1 - v) * 8}deg)`,
      opacity: v * (1 - out)}}>
      <div style={{position: 'absolute', left: '37%', top: -22, width: '26%', height: 48, background: 'rgba(255,177,160,.74)',
        border: '1px solid rgba(110,80,72,.12)', transform: 'rotate(-2deg)'}} />
      <div style={{fontFamily: E.style.fonts.ui, fontSize: 21, letterSpacing: '.11em', fontWeight: 700,
        textTransform: 'uppercase', color: C.accent2, marginBottom: 16}}>{String(o.label || 'CATATAN').slice(0, 32)}</div>
      <div style={{fontFamily: E.style.fonts.head, fontSize: o.fontSize || 71, lineHeight: 1.03,
        fontWeight: 800, letterSpacing: '-.045em', overflowWrap: 'break-word'}}>{headline}</div>
      {o.detail ? <div style={{fontFamily: E.style.fonts.body, fontWeight: 700, fontSize: 27,
        color: C.muted, lineHeight: 1.25, marginTop: 14}}>{String(o.detail).slice(0, 110)}</div> : null}
      <div style={{height: 10, width: `${Math.min(90, Math.max(0, (t - o.start - .25) / .55 * 90))}%`,
        background: C.accent, marginTop: 23, transform: 'rotate(-1deg)', borderRadius: 5}} />
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Waveform (real audio peaks)
const Waveform = ({E, t, o}) => {
  const C = E.style.colors;
  const {p, out} = life(t, o.start, o.end, E.fps, {enter: {damping: 15, stiffness: 160}});
  const peaks = o.peaks || [];
  const N = peaks.length || 1;
  const w = o.w || 820, h = o.h || 150;
  const cutAt = o.cutAt ?? o.start + 0.5;
  const mark = prog(t, cutAt - 0.35, cutAt);
  const col = ease.inOutExpo(prog(t, cutAt, cutAt + 0.4));
  const inCut = (i) => (o.cuts || []).some(([a, b]) => i / N >= a && i / N < b);
  const removed = (o.cuts || []).reduce((s, [a, b]) => s + (b - a), 0);
  const scaleX = 1 / (1 - removed * col);
  let acc = 0;
  const bw = w / N;
  const bars = [];
  for (let i = 0; i < N; i++) {
    const cut = inCut(i);
    const width = cut ? bw * (1 - col) : bw;
    const X = acc * scaleX, BW = width * scaleX;
    const bh = Math.max(6, peaks[i] * h * 0.92);
    bars.push(<rect key={i} x={X + BW * 0.18} y={(h - bh) / 2} width={Math.max(0, BW * 0.64)}
      height={bh} rx={Math.min(6, bw * 0.3)} fill={cut && mark > 0 ? C.danger : C.ink} opacity={cut ? 1 - col : 1} />);
    acc += width;
  }
  const firstCut = (o.cuts || [])[0];
  const sx = firstCut ? (firstCut[0] + firstCut[1]) / 2 * w : w / 2;
  const snip = prog(t, cutAt - 0.4, cutAt);
  return (
    <div style={{position: 'absolute', left: o.x, top: o.y, transform: `translate(-50%, -50%) scale(${(0.8 + 0.2 * p) * (1 - 0.1 * out)})`,
      opacity: Math.min(1, p * 1.5) * (1 - out), padding: '22px 30px 26px', borderRadius: 30, background: C.paper, boxShadow: shadow(1)}}>
      <div style={{display: 'flex', alignItems: 'center', gap: 12, marginBottom: 12, fontFamily: E.style.fonts.ui, fontWeight: 700,
        fontSize: 28, color: C.muted, letterSpacing: '0.06em'}}>
        <Icon name="scissors" size={30} color={C.ink} />{o.label || 'AUTO CUT'}
        <span style={{marginLeft: 'auto', color: col > 0.5 ? C.ink : C.muted, background: col > 0.5 ? C.accent : 'transparent', padding: '2px 10px', borderRadius: 8}}>
          {o.saved ? `-${o.saved}` : ''}
        </span>
      </div>
      <div style={{position: 'relative', width: w, height: h}}>
        <svg width={w} height={h}>{bars}</svg>
        {snip > 0 && col < 1 ? (
          <div style={{position: 'absolute', left: lerp(-40, sx - 22, ease.out(snip)), top: -8, transform: `rotate(${-90 + 25 * Math.sin(t * 28)}deg)`}}>
            <Icon name="scissors" size={46} color={C.danger} stroke={2.6} />
          </div>
        ) : null}
      </div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ FilmStrip (thumbnails from the source)
const FilmStrip = ({E, t, o}) => {
  const C = E.style.colors, W = E.width;
  const thumbs = o.thumbs || [];
  const tw = o.thumbW || 230, th = tw * 16 / 9;
  return (
    <div style={{position: 'absolute', left: 0, top: o.y, width: W, height: th + 80}}>
      {thumbs.map((src, i) => {
        const at = o.start + i * 0.09;
        const p = springAt(t, at, E.fps, {damping: 15, stiffness: 150});
        const out = ease.in(prog(t, o.end - 0.3 + i * 0.03, o.end + i * 0.03));
        const tx = lerp(W + 200, o.x0 + i * (tw + 26), p) - out * (W + 400);
        const rot = (i - 1) * 5 + (1 - p) * 12;
        return (
          <div key={i} style={{position: 'absolute', left: tx, top: 30 + (i % 2) * 24, width: tw, height: th, borderRadius: 24, overflow: 'hidden',
            transform: `rotate(${rot}deg)`, boxShadow: shadow(1), border: `6px solid ${C.paper}`, background: '#222'}}>
            <Img src={staticFile(src)} style={{width: '100%', height: '100%', objectFit: 'cover'}} />
            <div style={{position: 'absolute', left: 10, bottom: 10, padding: '4px 10px', borderRadius: 8, background: 'rgba(0,0,0,0.6)',
              color: '#fff', fontFamily: E.style.fonts.ui, fontSize: 22, fontWeight: 700}}>{`CLIP ${i + 1}`}</div>
          </div>
        );
      })}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Chat panel (stylised)
const estLines = (text, maxW, fs) => Math.max(1, Math.ceil((text.length * fs * 0.56) / maxW));

const ChatPanel = ({E, t, o}) => {
  const C = E.style.colors;
  const {p, out} = life(t, o.start, o.end, E.fps, {enter: {damping: 17, stiffness: 190}, exitDur: 0.16});
  const w = o.w || 900, fs = o.fontSize || 34, pad = 26;
  const maxB = w * 0.8 - pad * 2;
  const msgs = [];
  let inputText = '', inputActive = false, sendPulse = 0;
  for (const m of o.messages || []) {
    if (m.role === 'user') {
      const tEnd = m.at + (m.typing || 0.8);
      if (t >= m.at && t < tEnd) {
        inputActive = true;
        inputText = m.text.slice(0, Math.round(m.text.length * ease.out(prog(t, m.at, tEnd))));
      }
      sendPulse = Math.max(sendPulse, 1 - Math.abs(t - tEnd) / 0.18);
      if (t >= tEnd) msgs.push({...m, shownAt: tEnd});
    } else if (t >= m.at) {
      msgs.push({...m, shownAt: m.at});
    }
  }
  const caret = Math.floor(t * 3) % 2 === 0;
  const avatar = (
    <div style={{width: 56, height: 56, borderRadius: 28, background: C.accent2, display: 'flex', alignItems: 'center', justifyContent: 'center', flex: 'none'}}>
      <Icon name="sparkle" size={32} color={C.paper} />
    </div>
  );
  return (
    <div style={{position: 'absolute', left: o.x, top: o.y, width: w, transform: `translate(-50%, 0) translateY(${(1 - p) * 120}px) scale(${0.92 + 0.08 * p})`,
      opacity: Math.min(1, p * 3) * (1 - out), borderRadius: 38, background: C.paper, boxShadow: shadow(1.3), padding: pad, boxSizing: 'border-box'}}>
      <div style={{display: 'flex', alignItems: 'center', gap: 16, paddingBottom: 18, borderBottom: `2px solid ${C.line}`}}>
        {avatar}
        <div style={{fontFamily: E.style.fonts.head, fontWeight: 800, fontSize: 34, color: C.ink}}>{o.title || 'AI Editor'}</div>
        <div style={{marginLeft: 'auto', fontFamily: E.style.fonts.ui, fontSize: 24, fontWeight: 700, color: C.muted, letterSpacing: '0.06em'}}>{o.badge || 'CHAT'}</div>
      </div>
      <div style={{display: 'flex', flexDirection: 'column', justifyContent: o.anchor === 'top' ? 'flex-start' : 'flex-end', gap: 16, height: o.threadH || 330, overflow: 'hidden', paddingTop: 16}}>
        {msgs.map((m, i) => {
          const a = springAt(t, m.shownAt, E.fps, {damping: 15, stiffness: 200});
          const user = m.role === 'user';
          const lines = estLines(m.text, maxB, fs);
          let body = m.text;
          let extraH = 0;
          let extra = null;
          if (!user && m.thinking && t < m.at + m.thinking) {
            body = null;
            extra = (
              <div style={{display: 'flex', gap: 10, padding: '6px 4px'}}>
                {[0, 1, 2].map((d) => <span key={d} style={{width: 14, height: 14, borderRadius: 7, background: C.muted,
                  transform: `translateY(${-8 * Math.max(0, Math.sin((t - m.at) * 14 - d * 0.9))}px)`}} />)}
              </div>
            );
          }
          if (!user && m.progress && t >= m.progress.at) {
            const pr = ease.inOut(prog(t, m.progress.at, m.progress.at + m.progress.dur));
            const done = pr >= 1;
            extraH = 70;
            extra = (
              <div style={{marginTop: body ? 14 : 0}}>
                <div style={{display: 'flex', alignItems: 'center', gap: 10, fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: 26, color: done ? C.ink : C.muted}}>
                  {done ? <Icon name="check" size={28} color={C.ink} stroke={3} /> : <Icon name="refresh" size={26} color={C.muted} style={{transform: `rotate(${t * 360}deg)`}} />}
                  {done ? m.progress.doneText : m.progress.label}
                  <span style={{marginLeft: 'auto'}}>{Math.round(pr * 100)}%</span>
                </div>
                <div style={{height: 14, borderRadius: 7, background: C.line, marginTop: 10, overflow: 'hidden'}}>
                  <div style={{width: `${pr * 100}%`, height: '100%', borderRadius: 7, background: done ? C.accent : C.accent2}} />
                </div>
              </div>
            );
          }
          const estH = (body ? lines * fs * 1.25 : 26) + 2 * 18 + extraH;
          return (
            <div key={i} style={{height: estH * Math.min(1, a), flex: 'none', display: 'flex', justifyContent: user ? 'flex-end' : 'flex-start', alignItems: 'flex-end', gap: 12}}>
              <div style={{maxWidth: user ? '80%' : '86%', minWidth: !user && m.progress ? '78%' : undefined, padding: '18px 24px', borderRadius: 28,
                borderBottomRightRadius: user ? 8 : 28, borderBottomLeftRadius: user ? 28 : 8, background: user ? C.ink : C.soft,
                color: user ? C.paper : C.ink, fontFamily: E.style.fonts.body, fontWeight: 700, fontSize: fs, lineHeight: 1.25,
                transform: `scale(${0.6 + 0.4 * a})`, transformOrigin: user ? '100% 100%' : '0% 100%', opacity: Math.min(1, a * 1.5), boxSizing: 'border-box'}}>
                {body}
                {extra}
              </div>
            </div>
          );
        })}
      </div>
      <div style={{marginTop: 18, display: 'flex', alignItems: 'center', gap: 14, padding: '14px 14px 14px 26px', borderRadius: 999,
        background: C.soft, border: `2px solid ${inputActive ? C.ink : 'transparent'}`}}>
        <div style={{flex: 1, minWidth: 0, display: 'flex', justifyContent: inputText ? 'flex-end' : 'flex-start', alignItems: 'center', fontFamily: E.style.fonts.body,
          fontWeight: 600, fontSize: o.inputSize || 30, color: inputText ? C.ink : C.muted, whiteSpace: 'nowrap', overflow: 'hidden'}}>
          <span style={{flex: 'none'}}>{inputText || o.placeholder || 'Ketik revisi...'}</span>
          {inputActive ? <span style={{flex: 'none', display: 'inline-block', width: 3, height: 34, background: C.ink, marginLeft: 3, opacity: caret ? 1 : 0}} /> : null}
        </div>
        <div style={{width: 62, height: 62, borderRadius: 31, background: C.accent, display: 'flex', alignItems: 'center', justifyContent: 'center',
          transform: `scale(${1 + 0.25 * clamp(sendPulse)})`}}>
          <Icon name="send" size={32} color={C.ink} stroke={2.6} />
        </div>
      </div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ PromptBar
const PromptBar = ({E, t, o}) => {
  const C = E.style.colors;
  const {p, out} = life(t, o.start, o.end, E.fps, {enter: {damping: 14, stiffness: 170}});
  const typing = o.typing ?? 0.9;
  const at = o.at ?? o.start + 0.15;
  const n = Math.round(o.text.length * ease.out(prog(t, at, at + typing)));
  const sent = t >= at + typing;
  const pulse = clamp(1 - Math.abs(t - (at + typing)) / 0.2);
  const caret = Math.floor(t * 3) % 2 === 0;
  return (
    <div style={{position: 'absolute', left: o.x, top: o.y, width: o.w || 860, transform: `translate(-50%, -50%) translateY(${(1 - p) * 60}px) scale(${(0.85 + 0.15 * p) * (1 - 0.08 * out)})`,
      opacity: Math.min(1, p * 1.5) * (1 - out), display: 'flex', alignItems: 'center', gap: 18, padding: '18px 18px 18px 26px', borderRadius: 999,
      background: C.paper, boxShadow: shadow(1.1), border: `3px solid ${sent ? C.accent : C.ink}`, boxSizing: 'border-box'}}>
      <Icon name="sparkle" size={40} color={C.accent2} stroke={2.4} />
      <div style={{flex: 1, fontFamily: E.style.fonts.body, fontWeight: 700, fontSize: o.fontSize || 34, color: C.ink, whiteSpace: 'nowrap', overflow: 'hidden'}}>
        {o.text.slice(0, n)}
        {!sent && caret ? <span style={{display: 'inline-block', width: 3, height: 36, background: C.ink, marginLeft: 3, verticalAlign: 'middle'}} /> : null}
      </div>
      <div style={{width: 66, height: 66, borderRadius: 33, background: sent ? C.accent : C.ink, display: 'flex', alignItems: 'center', justifyContent: 'center',
        transform: `scale(${1 + 0.3 * pulse})`, flex: 'none'}}>
        <Icon name={sent ? 'check' : 'send'} size={34} color={sent ? C.ink : C.paper} stroke={2.8} />
      </div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ FloatCard
const FloatCard = ({E, t, o}) => {
  const C = E.style.colors;
  const at = o.at ?? o.start;
  const p = springAt(t, at, E.fps, {damping: 13, stiffness: 150});
  const out = o.end != null ? ease.in(prog(t, o.end - 0.25, o.end)) : 0;
  if (t < at) return null;
  const dir = o.from === 'left' ? -1 : o.from === 'right' ? 1 : 0;
  const tn = tone(C, o.tone || 'paper');
  const bob = Math.sin((t - at) * 2.2 + (o.seed || 0)) * 8;
  const sub = o.subAt != null ? springAt(t, o.subAt, E.fps, {damping: 12, stiffness: 200}) : 1;
  return (
    <div style={{position: 'absolute', left: o.x, top: o.y, width: o.w || 380, perspective: 900,
      transform: `translate(-50%, -50%) translate(${(1 - p) * dir * 260}px, ${(1 - p) * (dir ? 0 : 160) + bob - out * 40}px)`,
      opacity: Math.min(1, p * 1.6) * (1 - out)}}>
      <div style={{transform: `rotateY(${(o.tilt || 0) * (dir || 1)}deg) rotate(${(o.rot || 0)}deg) scale(${0.75 + 0.25 * p})`, borderRadius: 32,
        background: tn.bg, color: tn.fg, boxShadow: shadow(1.2), padding: '26px 28px', display: 'flex', flexDirection: 'column', gap: 14}}>
        <div style={{display: 'flex', alignItems: 'center', gap: 16}}>
          <div style={{width: 72, height: 72, borderRadius: 22, background: o.iconBg ? C[o.iconBg] : C.accent, display: 'flex', alignItems: 'center', justifyContent: 'center', flex: 'none'}}>
            <Icon name={o.icon || 'sparkle'} size={42} color={C.ink} stroke={2.4} />
          </div>
          <div style={{fontFamily: E.style.fonts.head, fontWeight: 800, fontSize: o.titleSize || 52, lineHeight: 1.0, letterSpacing: '-0.02em'}}>{o.title}</div>
        </div>
        {o.subtitle ? (
          <div style={{display: 'flex', alignItems: 'center', gap: 10, fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: o.subSize || 30, letterSpacing: '0.03em',
            opacity: sub, transform: `translateY(${(1 - sub) * 12}px)`, color: o.tone === 'ink' ? C.accent : C.muted}}>
            {o.subIcon ? <Icon name={o.subIcon} size={28} color={o.tone === 'ink' ? C.accent : C.ink} stroke={3} /> : null}{o.subtitle}
          </div>
        ) : null}
      </div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Tile (icon + label, can fly into a target)
const Tile = ({E, t, o}) => {
  const C = E.style.colors;
  const at = o.at ?? o.start;
  if (t < at) return null;
  const p = springAt(t, at, E.fps, {damping: 12, stiffness: 190});
  const dir = o.from === 'left' ? -1 : o.from === 'right' ? 1 : 0;
  const exitDur = o.exitDur ?? 0.38;
  const ex = o.end != null ? ease.in(prog(t, o.end - exitDur, o.end)) : 0;
  const w = o.w || 236;
  let x = o.x + (1 - p) * dir * 220, y = o.y + (1 - p) * (dir ? 0 : 120) + Math.sin((t - at) * 2.4 + (o.seed || 0)) * 6;
  let sc = 0.6 + 0.4 * p;
  if (o.exitTo && ex > 0) { x = lerp(x, o.exitTo[0], ex); y = lerp(y, o.exitTo[1], ex); sc *= 1 - 0.75 * ex; }
  const ring = clamp(1 - (t - at) / 0.5);
  const tn = tone(C, o.tone || 'paper');
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, transform: `translate(-50%, -50%) scale(${sc}) rotate(${(1 - p) * dir * 10 + (o.rot || 0)}deg)`,
      opacity: Math.min(1, p * 1.8) * (o.exitTo ? 1 - ex * ex : 1 - ex), padding: '20px 14px 18px', borderRadius: 30, background: tn.bg, color: tn.fg,
      boxShadow: `${shadow(1)}${ring > 0 ? `, 0 0 0 ${8 * ring}px ${C.accent}` : ''}`, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 12, boxSizing: 'border-box'}}>
      <div style={{width: 96, height: 96, borderRadius: 28, background: o.iconBg ? C[o.iconBg] : C.accent, display: 'flex', alignItems: 'center', justifyContent: 'center'}}>
        <Icon name={o.icon || 'sparkle'} size={56} color={o.iconBg === 'ink' || o.iconBg === 'accent2' ? C.paper : C.ink} stroke={2.4}
          style={o.spin ? {transform: `rotate(${(t - at) * 180}deg)`} : undefined} />
      </div>
      <div style={{fontFamily: E.style.fonts.head, fontWeight: 800, fontSize: o.size || 34, lineHeight: 1.0, letterSpacing: '-0.01em', textAlign: 'center',
        textTransform: 'uppercase'}}>{o.label}</div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ EndCard
// kicker pill -> two-part wordmark (word1 letters stagger in, word2 slams in as one accent block) -> CTA button
// with a bouncing arrow, plus optional chevrons pointing down to the platform's own CTA button.
const EndCard = ({E, t, o}) => {
  const variant = o.variant || E.style.endcard?.variant || 'block';
  if (variant === 'title') return <EndCardTitle E={E} t={t} o={o} />;
  if (variant === 'newsprint') return <EndCardNews E={E} t={t} o={o} />;
  if (variant === 'sticker') return <EndCardSticker E={E} t={t} o={o} />;
  if (variant === 'hero') return <EndCardHero E={E} t={t} o={o} />;
  const C = E.style.colors, W = E.width;
  const at = o.at || {};
  const k = springAt(t, at.kicker ?? o.start, E.fps, {damping: 13, stiffness: 190});
  const btnAt = at.button ?? o.start + 0.6;
  const btn = springAt(t, btnAt, E.fps, {damping: 12, stiffness: 170});
  const wm = at.wordmark ?? o.start + 0.3;
  const l1 = Array.from(o.word1).map((ch) => (ch === ' ' ? '\u00A0' : ch));
  const blockAt = wm + l1.length * 0.03 + 0.04;
  const bp = springAt(t, blockAt, E.fps, {damping: 10, stiffness: 260});
  const y = o.shiftAt != null ? lerp(o.y ?? 1000, o.y2 ?? o.y, ease.inOutExpo(prog(t, o.shiftAt, o.shiftAt + (o.shiftDur ?? 0.5)))) : (o.y ?? 1000);
  const fs = o.size || 132;
  const bounce = t >= btnAt ? Math.abs(Math.sin((t - btnAt) * 5.5)) * 12 : 0;
  return (
    <div style={{position: 'absolute', left: 0, top: y, width: W, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: o.gap ?? 26}}>
      {o.kicker ? (
        <div style={{opacity: Math.min(1, k * 1.4), transform: `translateY(${(1 - k) * 30}px) scale(${0.8 + 0.2 * Math.min(1.05, k)})`, padding: '12px 28px', borderRadius: 999,
          background: C.ink, color: C.accent, fontFamily: E.style.fonts.head, fontWeight: 800, fontSize: o.kickerSize || 42, letterSpacing: '0.01em', boxShadow: shadow(0.6)}}>{o.kicker}</div>
      ) : null}
      <div style={{display: 'flex', alignItems: 'center', fontFamily: E.style.fonts.display || E.style.fonts.head, fontWeight: E.style.fonts.display ? (E.style.fonts.displayWeight ?? 400) : 800,
        fontSize: fs, letterSpacing: E.style.fonts.display ? '0.005em' : '-0.045em', lineHeight: 1, height: fs * 1.1}}>
        {l1.map((ch, i) => {
          const p = springAt(t, wm + i * 0.03, E.fps, {damping: 11, stiffness: 240});
          return (
            <span key={i} style={{display: 'inline-block', color: onBg(C), transform: `translateY(${(1 - p) * 70}px) scale(${0.5 + 0.5 * Math.min(1.08, p)})`,
              opacity: t < wm + i * 0.03 ? 0 : 1, filter: p < 0.85 ? `blur(${((1 - p) * 8).toFixed(1)}px)` : 'none'}}>{ch}</span>
          );
        })}
        <span style={{display: 'inline-block', marginLeft: fs * 0.08, padding: `${fs * 0.02}px ${fs * 0.1}px ${fs * 0.04}px`, borderRadius: fs * 0.16, background: C.accent,
          color: C.ink, transform: `scale(${t < blockAt ? 0 : 0.3 + 0.7 * bp}) rotate(${(1 - Math.min(1, bp)) * -12 - 2}deg)`, letterSpacing: '-0.04em'}}>{o.word2}</span>
      </div>
      {o.button ? (
        <div style={{opacity: Math.min(1, btn * 1.5), transform: `translateY(${(1 - btn) * 40}px) scale(${0.8 + 0.2 * Math.min(1.05, btn)})`, display: 'flex', alignItems: 'center',
          gap: 18, padding: '22px 22px 22px 38px', borderRadius: 999, background: C.accent2, color: C.paper, fontFamily: E.style.fonts.head, fontWeight: 800,
          fontSize: o.buttonSize || 44, boxShadow: shadow(1)}}>
          {o.button}
          <span style={{width: 60, height: 60, borderRadius: 30, background: C.paper, display: 'flex', alignItems: 'center', justifyContent: 'center',
            transform: `translateY(${bounce}px)`}}>
            <Icon name="arrowDown" size={38} color={C.accent2} stroke={3} />
          </span>
        </div>
      ) : null}
      {o.chevrons && t >= btnAt + 0.2 ? (
        <svg width={120} height={200} style={{marginTop: 30}}>
          {[0, 1, 2].map((i) => {
            const ph = ((t - btnAt) * 1.6 - i * 0.22) % 1;
            const op = ph < 0 ? 0 : Math.sin(Math.max(0, ph) * Math.PI);
            return <path key={i} d={`M20 ${30 + i * 55} L60 ${70 + i * 55} L100 ${30 + i * 55}`} stroke={onBg(C)} strokeWidth={12} fill="none"
              strokeLinecap="round" strokeLinejoin="round" opacity={0.15 + 0.75 * op} />;
          })}
        </svg>
      ) : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Burst
const Burst = ({E, t, o}) => {
  const C = E.style.colors;
  const at = o.at ?? o.start;
  const p = prog(t, at, at + (o.dur || 0.6));
  if (p <= 0 || p >= 1) return null;
  const n = o.count || 12, R = o.radius || 220;
  const items = [];
  for (let i = 0; i < n; i++) {
    const ang = (i / n) * Math.PI * 2 + rand(i + 3) * 0.4;
    const r0 = R * 0.35 + R * 0.65 * ease.out(p);
    const len = (1 - p) * (30 + 30 * rand(i));
    const x1 = o.x + Math.cos(ang) * r0, y1 = o.y + Math.sin(ang) * r0;
    const x2 = o.x + Math.cos(ang) * (r0 + len), y2 = o.y + Math.sin(ang) * (r0 + len);
    items.push(<line key={i} x1={x1} y1={y1} x2={x2} y2={y2} stroke={i % 3 === 0 ? C.accent2 : o.color ? C[o.color] : C.accent} strokeWidth={10 * (1 - p) + 2} strokeLinecap="round" />);
  }
  return <svg width={E.width} height={E.height} style={{position: 'absolute', inset: 0}}>{items}</svg>;
};

// ------------------------------------------------------------------------------------------------ Flash / Background tint
const Flash = ({E, t, o}) => {
  const at = o.at ?? o.start;
  const p = prog(t, at, at + (o.dur || 0.18));
  if (p <= 0 || p >= 1) return null;
  return <div style={{position: 'absolute', inset: 0, background: o.color || '#fff', opacity: (1 - p) * (o.amount ?? 0.6)}} />;
};


// ------------------------------------------------------------------------------------------------ LowerThird
// Name + role strip (the speaker's real name/role only, as the user wrote it). Accent bar wipes in, text rises
// out of a mask, then everything leaves the way it came. Bottom-left by default, above the caption band.
const LowerThird = ({E, t, o}) => {
  const C = E.style.colors;
  const x = o.x ?? 70, y = o.y ?? Math.round(E.height * 0.56);
  const bar = ease.snappy(prog(t, o.start, o.start + 0.45));
  const out = ease.exitFast(prog(t, o.end - 0.28, o.end));
  const line = ease.smooth(prog(t, o.start + 0.15, o.start + 0.75));
  return (
    <div style={{position: 'absolute', left: x, top: y, opacity: 1 - out, transform: `translateX(${-40 * out}px)`}}>
      <div style={{position: 'relative', display: 'inline-block', padding: '14px 26px 12px 26px'}}>
        <div style={{position: 'absolute', inset: 0, background: o.tone === 'paper' ? C.paper : C.ink, borderRadius: 14,
          transform: `scaleX(${bar})`, transformOrigin: '0 50%', boxShadow: shadow(0.6)}} />
        <div style={{position: 'absolute', left: 0, top: 0, bottom: 0, width: 10, background: C.accent, borderRadius: '14px 0 0 14px',
          transform: `scaleY(${ease.snappy(prog(t, o.start, o.start + 0.3))})`}} />
        <div style={{position: 'relative'}}>
          <FxText t={t} at={o.start + 0.16} text={o.name} fx="rise" fps={E.fps}
            style={{fontFamily: E.style.fonts.head, fontWeight: 800, fontSize: o.size || 52, letterSpacing: '-0.02em',
              color: o.tone === 'paper' ? C.ink : C.paper, lineHeight: 1.05}} />
        </div>
      </div>
      {o.role ? (
        <div style={{marginTop: 10, marginLeft: 26}}>
          <FxText t={t} at={o.start + 0.32} text={o.role} fx="rise" fps={E.fps}
            style={{fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: o.roleSize || 30, letterSpacing: '0.06em', textTransform: 'uppercase',
              color: o.over === 'paper' ? C.ink : C.paper, textShadow: o.over === 'paper' ? 'none' : '0 2px 12px rgba(0,0,0,0.55)'}} />
          <div style={{height: 4, width: 220 * line, background: C.accent, marginTop: 8, borderRadius: 2}} />
        </div>
      ) : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Steps
// Numbered list that builds item by item on the spoken words ("tiga cara: satu..., dua..., tiga...").
// The current item is highlighted, earlier items dim. items: [{text, at}]
const Steps = ({E, t, o}) => {
  const C = E.style.colors;
  const W = E.width;
  const w = o.w ?? Math.min(860, W - 140);
  const x = o.x ?? W / 2, y = o.y ?? Math.round(E.height * 0.17);
  const out = ease.exitFast(prog(t, o.end - 0.25, o.end));
  const items = o.items || [];
  const fs = o.fontSize || 50;
  let cur = -1;
  items.forEach((it, i) => { if (t >= it.at) cur = i; });
  return (
    <div style={{position: 'absolute', left: x - w / 2, top: y, width: w, display: 'flex', flexDirection: 'column', gap: 18,
      opacity: 1 - out, transform: `translateY(${-30 * out}px)`}}>
      {o.title ? <FxText t={t} at={o.start} text={o.title} fx="rise" fps={E.fps}
        style={{fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: 34, letterSpacing: '0.1em', textTransform: 'uppercase',
          color: C.ink, background: C.accent, padding: '6px 16px', borderRadius: 10, alignSelf: 'flex-start'}} /> : null}
      {items.map((it, i) => {
        if (t < it.at - 0.02) return null;
        const p = springAt(t, it.at, E.fps, {damping: 15, stiffness: 210});
        const pv = (springAt(t + 1 / 120, it.at, E.fps, {damping: 15, stiffness: 210}) - springAt(t - 1 / 120, it.at, E.fps, {damping: 15, stiffness: 210})) * 60;
        const on = i === cur;
        const dim = on ? 1 : 0.8;
        return (
          <div key={i} style={{display: 'flex', alignItems: 'center', gap: 22, padding: '18px 26px', borderRadius: 24,
            background: on ? C.paper : C.soft, boxShadow: on ? shadow(1) : shadow(0.4), opacity: Math.min(1, p * 2) * dim,
            transform: `translateX(${(1 - Math.min(1, p)) * -120}px) scale(${on ? 1 : 0.96})`, transformOrigin: '0 50%',
            filter: dirBlur(shutterBlur(Math.abs(pv) * 120), 0)}}>
            <div style={{width: fs * 1.25, height: fs * 1.25, flex: 'none', borderRadius: fs * 0.35, background: on ? C.accent : C.soft,
              display: 'flex', alignItems: 'center', justifyContent: 'center', fontFamily: E.style.fonts.head, fontWeight: 800,
              fontSize: fs * 0.8, color: C.ink}}>{i + 1}</div>
            <div style={{fontFamily: E.style.fonts.head, fontWeight: 800, fontSize: fs, lineHeight: 1.05, letterSpacing: '-0.02em', color: C.ink}}>{it.text}</div>
          </div>
        );
      })}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Counter
// Animated number. ONLY for numbers the speaker actually said (or the user wrote). Counts with a decelerating
// curve and lands with a small overshoot; prefix/suffix (Rp, %, x, jt) stay still.
const Counter = ({E, t, o}) => {
  const C = E.style.colors;
  const {p, out} = life(t, o.start, o.end, E.fps, {enter: {damping: 14, stiffness: 190}, exitDur: 0.22});
  const at = o.countAt ?? o.start + 0.1;
  const k = ease.snappy(prog(t, at, at + (o.countDur ?? 0.9)));
  const from = Number(o.from ?? 0), to = Number(o.value);
  const dec = o.decimals ?? (String(o.value).includes('.') ? String(o.value).split('.')[1].length : 0);
  const val = from + (to - from) * k;
  const txt = (o.thousands === false ? val.toFixed(dec) : val.toLocaleString('id-ID', {minimumFractionDigits: dec, maximumFractionDigits: dec}));
  const x = o.x ?? E.width / 2, y = o.y ?? E.height * 0.24;
  const fs = o.fontSize || 170;
  const land = k >= 1 ? Math.sin(clamp((t - at - (o.countDur ?? 0.9)) / 0.25) * Math.PI) * 0.05 : 0;
  return (
    <div style={{position: 'absolute', left: x, top: y, transform: `translate(-50%, -50%) scale(${(0.7 + 0.3 * Math.min(1.04, p) + land) * (1 - 0.1 * out)})`,
      opacity: Math.min(1, p * 2) * (1 - out), display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 6}}>
      <div style={{fontFamily: E.style.fonts.head, fontWeight: 800, fontSize: fs, letterSpacing: '-0.05em', lineHeight: 1, whiteSpace: 'nowrap',
        color: o.tone === 'ink' ? C.ink : C.paper, fontVariantNumeric: 'tabular-nums',
        ...(o.plate === false ? {textShadow: '0 6px 30px rgba(0,0,0,0.45)'} : {background: o.tone === 'ink' ? C.paper : 'rgba(12,12,14,0.86)',
          padding: '0.08em 0.22em 0.1em', borderRadius: fs * 0.16, boxShadow: shadow(1)})}}>
        {o.prefix ? <span style={{fontSize: '0.5em', marginRight: '0.08em'}}>{o.prefix}</span> : null}{txt}
        {o.suffix ? <span style={{fontSize: '0.55em', marginLeft: '0.06em', color: C.accent}}>{o.suffix}</span> : null}
      </div>
      {o.label ? <div style={{padding: '8px 20px', borderRadius: 999, background: C.accent, color: C.ink, fontFamily: E.style.fonts.ui,
        fontWeight: 700, fontSize: 30, letterSpacing: '0.05em', textTransform: 'uppercase'}}>{o.label}</div> : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Callout
// Hand-drawn "write-on" marker: circle / underline / arrow drawn onto the frame (stroke-dashoffset, like AE's
// Trim Paths), optional label. Point at something visible in the footage, never at the mouth.
const Callout = ({E, t, o}) => {
  const C = E.style.colors;
  const draw = ease.smooth(prog(t, o.start, o.start + (o.drawDur ?? 0.5)));
  const out = ease.exitFast(prog(t, o.end - 0.22, o.end));
  const col = o.color ? (C[o.color] || o.color) : C.accent;
  const sw = o.stroke || 10;
  const [cx, cy] = [o.x ?? E.width / 2, o.y ?? E.height * 0.4];
  const rw = o.w ?? 300, rh = o.h ?? 150;
  let d, len;
  if (o.shape === 'underline') { d = `M${cx - rw / 2} ${cy} q ${rw * 0.5} ${-sw * 1.2} ${rw} ${sw * 0.4}`; len = rw * 1.05; }
  else if (o.shape === 'arrow') {
    const [tx, ty] = o.to || [cx + 180, cy + 180];
    const mx = (cx + tx) / 2 + (ty - cy) * 0.25, my = (cy + ty) / 2 - (tx - cx) * 0.25;
    d = `M${cx} ${cy} Q ${mx} ${my} ${tx} ${ty}`; len = Math.hypot(tx - cx, ty - cy) * 1.15;
  } else {
    // slightly open, overshooting ellipse: reads as drawn by hand
    const pts = [];
    for (let i = 0; i <= 40; i++) {
      const a = -Math.PI * 0.62 + (i / 40) * Math.PI * 2.12;
      const r = 1 + 0.04 * Math.sin(i * 0.9);
      pts.push(`${(cx + Math.cos(a) * rw / 2 * r).toFixed(1)} ${(cy + Math.sin(a) * rh / 2 * r).toFixed(1)}`);
    }
    d = 'M' + pts.join(' L'); len = Math.PI * (rw + rh) / 2 * 1.08;
  }
  let head = null;
  if (o.shape === 'arrow' && draw > 0.92) {
    const [tx, ty] = o.to || [cx + 180, cy + 180];
    const ang = Math.atan2(ty - cy, tx - cx) + 0.35;
    const L = sw * 4;
    head = <path d={`M${tx - Math.cos(ang - 0.5) * L} ${ty - Math.sin(ang - 0.5) * L} L${tx} ${ty} L${tx - Math.cos(ang + 0.5) * L} ${ty - Math.sin(ang + 0.5) * L}`}
      stroke={col} strokeWidth={sw} fill="none" strokeLinecap="round" strokeLinejoin="round" />;
  }
  return (
    <div style={{position: 'absolute', inset: 0, opacity: 1 - out}}>
      <svg width={E.width} height={E.height} style={{position: 'absolute', inset: 0, filter: 'drop-shadow(0 4px 10px rgba(0,0,0,0.35))'}}>
        <path d={d} stroke={col} strokeWidth={sw} fill="none" strokeLinecap="round" strokeLinejoin="round"
          strokeDasharray={len} strokeDashoffset={len * (1 - draw)} />
        {head}
      </svg>
      {o.label ? (
        <div style={{position: 'absolute', left: o.labelX ?? cx, top: o.labelY ?? (cy - rh / 2 - 70), transform: 'translateX(-50%)'}}>
          <FxText t={t} at={o.start + 0.3} text={o.label} fx="pop" fps={E.fps}
            style={{fontFamily: E.style.fonts.head, fontWeight: 800, fontSize: o.labelSize || 44, color: C.ink, background: col,
              padding: '6px 18px', borderRadius: 12, whiteSpace: 'nowrap'}} />
        </div>
      ) : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ BRoll
// The user's OWN extra footage or photos (uploaded with the video) as a cut-away. mode 'full' covers the frame,
// 'card' floats it in a rounded card. Photos get a Ken Burns move; clips play from `from` seconds (muted).
const BRoll = ({E, t, o}) => {
  const C = E.style.colors, W = E.width, H = E.height;
  const full = (o.mode || 'full') === 'full';
  const enter = o.enter || (full ? 'zoom' : 'rise');
  const pin = ease.snappy(prog(t, o.start, o.start + 0.32));
  const pinV = Math.abs(ease.snappy(prog(t + 1 / 120, o.start, o.start + 0.32)) - ease.snappy(prog(t - 1 / 120, o.start, o.start + 0.32))) * 60;
  const out = o.exit === 'cut' ? 0 : ease.exitFast(prog(t, o.end - 0.24, o.end));
  const r = full ? [0, 0, W, H] : (o.rect || [110, Math.round(H * 0.16), W - 220, Math.round(H * 0.42)]);
  const kb = o.kenburns || 'in';
  const kp = prog(t, o.start, o.end);
  const ks = kb === 'out' ? lerp(1.14, 1.02, ease.glide(kp)) : lerp(1.02, 1.14, ease.glide(kp));
  const kx = kb === 'left' ? lerp(30, -30, kp) : kb === 'right' ? lerp(-30, 30, kp) : 0;
  let tf = '', op = 1, flt = 'none';
  if (enter === 'cut') { tf = ''; op = 1; flt = 'none'; }
  else if (enter === 'zoom') { tf = `scale(${lerp(1.25, 1, pin)})`; op = Math.min(1, pin * 3); flt = pinV > 0.5 ? `blur(${Math.min(8, pinV * 2.2).toFixed(1)}px)` : 'none'; }
  else if (enter === 'whip') { tf = `translateX(${(1 - pin) * W * 0.6}px)`; flt = dirBlur(shutterBlur(pinV * W * 0.6) * 1.5, 0); }
  else { tf = `translateY(${(1 - pin) * 160}px) scale(${0.92 + 0.08 * pin})`; op = Math.min(1, pin * 2.5); flt = dirBlur(0, shutterBlur(pinV * 160)); }
  const src = staticFile(o.file);
  // the clip starts playing at the overlay's start (a Sequence re-bases the video clock; without it the clip would be
  // read at the composition time, i.e. seconds into the clip that the user never chose)
  const media = o.kind === 'video'
    ? <Sequence from={Math.round(o.start * E.fps)} layout="none"><OffthreadVideo src={src} muted startFrom={Math.round((o.from || 0) * E.fps)} style={{width: '100%', height: '100%', objectFit: 'cover'}} /></Sequence>
    : <Img src={src} style={{width: '100%', height: '100%', objectFit: 'cover', transform: `scale(${ks}) translateX(${kx}px)`}} />;
  return (
    <div style={{position: 'absolute', left: r[0], top: r[1], width: r[2], height: r[3], borderRadius: full ? 0 : 36, overflow: 'hidden',
      boxShadow: full ? 'none' : shadow(1.3), transform: tf + ` translateY(${out * (full ? 0 : -60)}px)`, opacity: op * (1 - (full ? 0 : out)),
      filter: flt, background: '#111', border: full ? 'none' : `6px solid ${C.paper}`, boxSizing: 'border-box'}}>
      {media}
      {o.label ? <div style={{position: 'absolute', left: 24, bottom: 24, padding: '8px 18px', borderRadius: 10, background: 'rgba(0,0,0,0.6)',
        color: '#fff', fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: 28}}>{o.label}</div> : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ ProgressBar
// Thin watch-progress bar at the very top (opt-in; many creators use it for retention). Not text, so it may sit
// in the top safe area.
const ProgressBar = ({E, t, o}) => {
  const C = E.style.colors;
  const p = clamp(t / E.duration);
  return (
    <div style={{position: 'absolute', left: 0, top: o.y ?? 0, width: E.width, height: o.h ?? 10, background: 'rgba(0,0,0,0.18)'}}>
      <div style={{width: `${p * 100}%`, height: '100%', background: o.color ? (C[o.color] || o.color) : C.accent}} />
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ LightLeak
const LightLeak = ({E, t, o}) => <LeakSweep E={E} t={t} at={o.start} dur={(o.end ?? o.start + 0.8) - o.start} dir={o.dir || 1}
  color={E.style.finish?.leak?.[0]} color2={E.style.finish?.leak?.[1]} strength={o.strength ?? 0.7} />;

// ================================================================================================ v2.1 style kits
// Signature elements for EDITORIAL and CREATOR POP (and any style that asks for them) so they do not
// reuse IMPACT components with a colour swap.

const RAGGED = 'polygon(0% 1.2%, 9% 0%, 21% 1%, 34% 0.2%, 48% 1.1%, 61% 0%, 74% 0.9%, 88% 0.1%, 100% 1%, 99.4% 22%, 100% 47%, 99.3% 71%, 100% 98.6%, 90% 100%, 77% 99%, 63% 100%, 50% 98.9%, 36% 100%, 22% 99.1%, 10% 100%, 0% 98.8%, 0.6% 74%, 0% 49%, 0.7% 24%)';
const halftoneBg = (a = 0.14, s = 7) => ({backgroundImage: `radial-gradient(rgba(28,27,24,${a * 3}) .9px, transparent 1.1px)`, backgroundSize: `${s}px ${s}px`});

// ------------------------------------------------------------------------------------------------ Scrim
// Darkens (optionally blurs) the footage behind a title so white/serif type reads on any background.
// gradient: full | center | bottom | top. Layer 'below' (under captions and titles).
const Scrim = ({E, t, o}) => {
  const inD = o.inDur ?? 0.5, outD = o.outDur ?? 0.35;
  const a = ease.smooth(prog(t, o.start, o.start + inD)) * (1 - (o.end != null ? ease.smooth(prog(t, o.end - outD, o.end)) : 0));
  if (a <= 0.001) return null;
  const dim = (o.dim ?? 0.5) * a, blur = (o.blur ?? 0) * a;
  const r = o.rect || [0, 0, E.width, E.height];
  const g = o.gradient || 'full';
  const bg = g === 'bottom' ? `linear-gradient(180deg, rgba(0,0,0,${dim * 0.25}) 0%, rgba(0,0,0,${dim}) 75%)`
    : g === 'top' ? `linear-gradient(0deg, rgba(0,0,0,${dim * 0.25}) 0%, rgba(0,0,0,${dim}) 75%)`
    : g === 'center' ? `radial-gradient(ellipse 95% 62% at 50% 46%, rgba(0,0,0,${dim}) 0%, rgba(0,0,0,${(dim * 0.6).toFixed(3)}) 100%)`
    : `rgba(0,0,0,${dim.toFixed(3)})`;
  const bf = blur > 0.3 ? `blur(${blur.toFixed(1)}px)` : 'none';
  return <div style={{position: 'absolute', left: r[0], top: r[1], width: r[2], height: r[3], background: bg, backdropFilter: bf, WebkitBackdropFilter: bf}} />;
};

// ------------------------------------------------------------------------------------------------ TitleCard (EDITORIAL)
// Magazine / film title: letter-spaced kicker, hairline rules that draw from the centre, serif lines that rack-focus in,
// optional sub line. tone 'light' (white type, use over a Scrim) or 'dark' (ink type on paper/backdrop).
const TitleCard = ({E, t, o}) => {
  const C = E.style.colors, W = E.width;
  const light = (o.tone || 'light') === 'light';
  const fg = light ? '#FFFFFF' : C.ink;
  const subCol = light ? 'rgba(255,255,255,0.82)' : C.muted;
  const ruleCol = light ? 'rgba(255,255,255,0.6)' : 'rgba(20,19,17,0.55)';
  const w = o.w ?? W - 140, x = o.x ?? W / 2, y = o.y ?? E.height * 0.3;
  const kAt = o.kickerAt ?? o.start, tAt = o.titleAt ?? o.start + 0.18, sAt = o.subAt ?? tAt + 0.55;
  const out = o.end != null ? ease.exitFast(prog(t, o.end - 0.32, o.end)) : 0;
  const rule = ease.smooth(prog(t, o.start + 0.05, o.start + 0.85));
  const lines = o.lines || String(o.title || '').split('\n').filter(Boolean).map((s) => ({text: s}));
  const fx = o.fx || E.style.textFx || 'focus';
  const size = o.size || 112;
  const acc = o.accentColor || (light ? C.accent : (C.accentInk || C.ink));
  const Rule = () => (o.rules === false ? null : <div style={{width: (o.ruleW ?? w * 0.36) * rule, height: 2, background: ruleCol}} />);
  return (
    <div style={{position: 'absolute', left: x - w / 2, top: y, width: w, display: 'flex', flexDirection: 'column', alignItems: 'center',
      gap: o.gap ?? 20, opacity: 1 - out, filter: out > 0.02 ? `blur(${(out * 7).toFixed(1)}px)` : 'none', textAlign: 'center'}}>
      {o.quote ? <div style={{fontFamily: E.style.fonts.serif, fontStyle: 'italic', fontSize: size * 1.3, lineHeight: 0.5, height: size * 0.5, color: acc,
        opacity: ease.smooth(prog(t, o.start, o.start + 0.5))}}>&ldquo;</div> : null}
      {o.kicker ? <FxText t={t} at={kAt} text={String(o.kicker).toUpperCase()} fx="track" fps={E.fps}
        style={{fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: o.kickerSize || 28, letterSpacing: '0.3em', color: light ? acc : C.muted}} /> : null}
      <Rule />
      {lines.map((ln, i) => (
        <FxText key={i} t={t} at={ln.at ?? tAt + i * 0.24} text={ln.text} fx={ln.fx || fx} fps={E.fps}
          style={{fontFamily: ln.font === 'sans' ? E.style.fonts.head : E.style.fonts.serif, fontStyle: ln.italic ? 'italic' : 'normal',
            fontWeight: ln.font === 'sans' ? 700 : (E.style.fonts.serifWeight ?? 400), fontSize: ln.size || size, lineHeight: 1.02, letterSpacing: ln.font === 'sans' ? '-0.02em' : '-0.012em',
            color: ln.accent ? acc : fg, textShadow: light ? '0 6px 42px rgba(0,0,0,0.5)' : 'none', whiteSpace: 'nowrap'}} />
      ))}
      <Rule />
      {o.sub ? <FxText t={t} at={sAt} text={o.sub} fx="rise" fps={E.fps}
        style={{fontFamily: E.style.fonts.body, fontWeight: 700, fontSize: o.subSize || 34, color: subCol, letterSpacing: '0.01em'}} /> : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Headline (front-page sheet)
// Front-page headline band: ragged newsprint paper, thick + thin rules, kicker, a huge serif headline that is
// "printed" (stamp landing + ink settling), a red marker on one word (highlight) or the whole line, decorative
// column rules. Fictional design treatment: never a real masthead, logo or date.
const Headline = ({E, t, o}) => {
  const C = E.style.colors, W = E.width;
  const w = o.w ?? W - 90, x = o.x ?? W / 2, y = o.y ?? E.height * 0.15;
  const st = o.start;
  const cfg = {damping: 15, stiffness: 220};
  const p = springAt(t, st, E.fps, cfg);
  const pv = Math.abs(springAt(t + 1 / 120, st, E.fps, cfg) - springAt(t - 1 / 120, st, E.fps, cfg)) * 60;
  const out = o.end != null ? ease.exitFast(prog(t, o.end - 0.25, o.end)) : 0;
  const sc = lerp(1.18, 1, Math.min(1.03, p));
  const blur = Math.min(8, shutterBlur(pv * 240));
  const ink = ease.out(prog(t, st + 0.05, st + 0.38));
  const mark = ease.out(prog(t, st + 0.42, st + 0.82));
  const tilt = o.tilt ?? -1.5;
  const red = C.accent;
  const size = o.size || 122;
  const words = String(o.headline || '').split(/\s+/).filter(Boolean);
  const hl = String(o.highlight || '').toLowerCase().replace(/[^\p{L}\p{N}-]/gu, '');
  const markEl = (whole) => o.mark === 'circle'
    ? <span style={{position: 'absolute', left: whole ? '-3%' : '-12%', right: whole ? '-3%' : '-12%', top: '-6%', bottom: '-10%', borderRadius: '50%',
        border: `${Math.round(size * 0.075)}px solid ${red}`, opacity: Math.min(1, mark * 1.6), transform: `rotate(-3deg) scale(${0.85 + 0.15 * mark})`, zIndex: 0}} />
    : <span style={{position: 'absolute', left: '-2%', width: '104%', bottom: size * 0.02, height: size * 0.16, borderRadius: 6, background: red, opacity: 0.92,
        transform: `rotate(-1deg) scaleX(${mark})`, transformOrigin: '0 50%', zIndex: 0}} />;
  return (
    <div style={{position: 'absolute', left: x, top: y, width: w, opacity: Math.min(1, p * 3) * (1 - out),
      transform: `translateX(-50%) translateY(${-out * 50}px) scale(${sc - 0.04 * out}) rotate(${tilt + (1 - Math.min(1, p)) * -5}deg)`,
      filter: blur > 0.8 ? `blur(${blur.toFixed(1)}px)` : 'none'}}>
      <div style={{position: 'relative', background: o.paper || C.paper, padding: '24px 40px 30px', boxSizing: 'border-box', clipPath: RAGGED,
        boxShadow: '0 30px 60px rgba(20,18,14,.35)'}}>
        <div style={{position: 'absolute', inset: 0, opacity: 0.5, ...halftoneBg(0.05, 6)}} />
        <div style={{position: 'relative', display: 'flex', justifyContent: 'space-between', alignItems: 'center', borderTop: `7px solid ${C.ink}`,
          borderBottom: `2px solid ${C.ink}`, padding: '10px 0 12px', fontFamily: E.style.fonts.ui, fontSize: 24, letterSpacing: '.16em', fontWeight: 700, color: C.ink}}>
          <span>{String(o.kicker || 'SOROTAN').toUpperCase().slice(0, 30)}</span>
          <span style={{display: 'flex', gap: 9}}>{[0, 1, 2].map((i) => <span key={i} style={{width: 11, height: 11, borderRadius: 6, background: i === 0 ? red : C.ink}} />)}</span>
        </div>
        <div style={{position: 'relative', padding: '20px 0 12px', fontFamily: E.style.fonts.serif, fontSize: size, lineHeight: 0.92, letterSpacing: '-0.045em',
          color: C.ink, textAlign: o.align || 'left', opacity: 0.2 + 0.8 * ink, filter: ink < 0.98 ? `blur(${((1 - ink) * 3.5).toFixed(1)}px)` : 'none'}}>
          {hl ? words.map((wd, i) => {
            const on = wd.toLowerCase().replace(/[^\p{L}\p{N}-]/gu, '') === hl;
            // highlighted word: red printer's block behind it (ivory type once the block has passed), or a red circle
            const block = on && o.mark !== 'circle';
            return <span key={i} style={{position: 'relative', display: 'inline-block', marginRight: '0.22em'}}>
              {block ? <span style={{position: 'absolute', left: '-0.08em', right: '-0.08em', top: '0.04em', bottom: '-0.06em', background: red,
                transform: `rotate(-1.2deg) scaleX(${mark})`, transformOrigin: '0 50%', zIndex: 0}} /> : on ? markEl(false) : null}
              <span style={{position: 'relative', zIndex: 1, color: block && mark > 0.55 ? (o.paper || C.paper) : C.ink}}>{wd}</span></span>;
          }) : <span style={{position: 'relative', display: 'inline-block'}}>{o.mark && o.mark !== 'none' ? markEl(true) : null}<span style={{position: 'relative', zIndex: 1}}>{o.headline}</span></span>}
        </div>
        <div style={{position: 'relative', height: 9, borderTop: `2px solid ${C.ink}`, borderBottom: `1px solid ${C.ink}`}} />
        {o.dek ? <div style={{position: 'relative', marginTop: 16, fontFamily: E.style.fonts.body, fontWeight: 700, fontSize: o.dekSize || 30, lineHeight: 1.22, color: C.ink}}>{o.dek}</div> : null}
        {o.columns !== false ? (
          <div style={{position: 'relative', display: 'flex', gap: 22, marginTop: 18}}>
            {[0, 1, 2].map((c) => (
              <div key={c} style={{flex: 1, display: 'flex', flexDirection: 'column', gap: 9, opacity: ease.smooth(prog(t, st + 0.25 + c * 0.06, st + 0.6 + c * 0.06))}}>
                {[0, 1, 2].map((r) => <div key={r} style={{height: 7, borderRadius: 2, background: C.line, width: `${[100, 92, 64][(r + c) % 3]}%`}} />)}
              </div>
            ))}
          </div>
        ) : null}
      </div>
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Sticker (CREATOR POP)
// Die-cut sticker: thick white outline, hard drop shadow, peels onto the screen with a spin and a little wobble.
const stickerTone = (C, tone) => {
  const bg = tone === 'mint' ? (C.mint || '#7CF29A') : tone === 'coral' ? (C.coral || '#FF7A59') : tone === 'accent2' ? C.accent2
    : tone === 'ink' ? C.ink : tone === 'paper' ? C.paper : C.accent;
  const fg = tone === 'accent2' || tone === 'ink' ? C.paper : C.ink;
  return {bg, fg};
};
const StickerBody = ({E, text, icon, tone, size = 48, shape, maxW}) => {
  const C = E.style.colors;
  const tn = stickerTone(C, tone);
  const ol = Math.max(6, Math.round(size * 0.2));
  return (
    <div style={{display: 'flex', alignItems: 'center', justifyContent: 'center', gap: size * 0.24, padding: `${size * 0.26}px ${size * 0.5}px ${size * 0.3}px`,
      borderRadius: shape === 'rect' ? size * 0.28 : 999, background: tn.bg, color: tn.fg, fontFamily: E.style.fonts.head, fontWeight: 800, fontSize: size,
      lineHeight: 0.98, textTransform: 'uppercase', letterSpacing: '-0.01em', textAlign: 'center', maxWidth: maxW, boxSizing: 'border-box',
      whiteSpace: maxW ? 'normal' : 'nowrap', boxShadow: `0 0 0 ${ol}px #FFFFFF, 0 ${Math.round(ol * 1.1)}px 0 ${ol}px rgba(0,0,0,0.28), 0 22px 44px rgba(0,0,0,0.28)`}}>
      {icon ? <Icon name={icon} size={size * 0.95} color={tn.fg} stroke={2.6} /> : null}
      <span>{text}</span>
    </div>
  );
};
const Sticker = ({E, t, o}) => {
  const at = o.at ?? o.start;
  if (t < at) return null;
  const cfg = {damping: 10, stiffness: 230};
  const p = springAt(t, at, E.fps, cfg);
  const out = o.end != null ? ease.in(prog(t, o.end - 0.2, o.end)) : 0;
  const rot = o.rot ?? -4;
  const wob = o.wobble === false ? 0 : Math.sin((t - at) * 3.1 + (o.seed || 0)) * 1.3;
  const pv = Math.abs(springAt(t + 1 / 120, at, E.fps, cfg) - springAt(t - 1 / 120, at, E.fps, cfg)) * 60;
  const b = Math.min(6, pv * 0.05);
  return (
    <div style={{position: 'absolute', left: o.x ?? E.width / 2, top: o.y ?? E.height * 0.2, opacity: Math.min(1, p * 3) * (1 - out),
      transform: `translate(-50%, -50%) scale(${(0.15 + 0.85 * p) * (1 - 0.6 * out)}) rotate(${rot + (1 - Math.min(1, p)) * -30 + wob + out * 20}deg)`,
      filter: b > 0.7 ? `blur(${b.toFixed(1)}px)` : 'none'}}>
      <StickerBody E={E} text={o.text} icon={o.icon} tone={o.tone} size={o.size || 52} shape={o.shape} maxW={o.maxW} />
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ StickerStack (CREATOR POP lists)
// A spoken list as stickers slapped around the speaker, one per item on its word; earlier ones stay (slightly
// smaller) so the list visibly piles up. items: [{text, at, x?, y?, rot?, tone?, icon?}]. Default slots ring the
// face in 'talk' framing (beside and above the head), never on it.
const SLOTS = [[175, 600, -6], [905, 640, 5], [190, 860, 4], [895, 900, -5], [300, 380, -3], [785, 390, 4]];
const StickerStack = ({E, t, o}) => {
  const items = o.items || [];
  const tones = o.tones || ['accent', 'mint', 'accent2', 'coral', 'paper', 'accent'];
  const n = items.length;
  let cur = -1;
  items.forEach((it, i) => { if (t >= it.at) cur = i; });
  return (
    <>
      {items.map((it, i) => {
        if (t < it.at) return null;
        const [sx, sy, sr] = (o.slots || SLOTS)[i % (o.slots || SLOTS).length];
        const older = i < cur ? ease.smooth(prog(t, items[i + 1].at, items[i + 1].at + 0.3)) : 0;
        return (
          <div key={i} style={{position: 'absolute', inset: 0, transform: `scale(1)`, opacity: 1 - 0.12 * older}}>
            <Sticker E={E} t={t} o={{at: it.at, end: o.end != null ? o.end - (n - 1 - i) * 0.03 : null, x: it.x ?? sx, y: it.y ?? sy,
              rot: it.rot ?? sr, tone: it.tone || tones[i % tones.length], text: it.text, icon: it.icon, size: (it.size || o.size || 44) * (1 - 0.08 * older),
              maxW: o.maxW ?? 270, seed: i}} />
          </div>
        );
      })}
    </>
  );
};

// ------------------------------------------------------------------------------------------------ EndCard variants
const endTiming = (E, t, o) => {
  const at = o.at || {};
  const kAt = at.kicker ?? o.start, wm = at.wordmark ?? o.start + 0.3, btnAt = at.button ?? o.start + 0.6;
  const y = o.shiftAt != null ? lerp(o.y ?? 1000, o.y2 ?? o.y, ease.inOutExpo(prog(t, o.shiftAt, o.shiftAt + (o.shiftDur ?? 0.5)))) : (o.y ?? 1000);
  return {kAt, wm, btnAt, y};
};

// EDITORIAL: film title card. Serif wordmark (word2 in italic accent), hairline rules, quiet text CTA with an arrow.
const EndCardTitle = ({E, t, o}) => {
  const C = E.style.colors, W = E.width;
  const {kAt, wm, btnAt, y} = endTiming(E, t, o);
  const light = o.tone === 'light';
  const fg = light ? '#FFFFFF' : C.ink;
  const acc = light ? C.accent : (o.accentInk || C.accentInk || C.ink);
  const fs = o.size || 132;
  const rule = ease.smooth(prog(t, kAt + 0.1, kAt + 0.9));
  const ul = ease.smooth(prog(t, btnAt + 0.15, btnAt + 0.6));
  const bounce = t >= btnAt ? Math.sin((t - btnAt) * 4.2) * 6 : 0;
  const ruleCol = light ? 'rgba(255,255,255,0.55)' : 'rgba(20,19,17,0.5)';
  return (
    <div style={{position: 'absolute', left: 0, top: y, width: W, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: o.gap ?? 24}}>
      {o.kicker ? <FxText t={t} at={kAt} text={String(o.kicker).toUpperCase()} fx="track" fps={E.fps}
        style={{fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: o.kickerSize || 26, letterSpacing: '0.32em', color: light ? 'rgba(255,255,255,0.8)' : C.muted}} /> : null}
      <div style={{width: 300 * rule, height: 2, background: ruleCol}} />
      <div style={{display: 'flex', alignItems: 'baseline', gap: fs * 0.22, whiteSpace: 'nowrap'}}>
        <FxText t={t} at={wm} text={o.word1} fx="focus" fps={E.fps} style={{fontFamily: E.style.fonts.serif, fontWeight: E.style.fonts.serifWeight ?? 400, fontSize: fs, lineHeight: 1, color: fg, letterSpacing: '-0.02em'}} />
        <FxText t={t} at={wm + 0.18} text={o.word2} fx="focus" fps={E.fps} style={{fontFamily: E.style.fonts.serif, fontWeight: E.style.fonts.serifWeight ?? 400, fontStyle: 'italic', fontSize: fs, lineHeight: 1, color: acc, letterSpacing: '-0.02em'}} />
      </div>
      <div style={{width: 300 * rule, height: 2, background: ruleCol}} />
      {o.button && t >= btnAt ? (
        <div style={{display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8, opacity: ease.smooth(prog(t, btnAt, btnAt + 0.4)),
          transform: `translateY(${(1 - ease.snappy(prog(t, btnAt, btnAt + 0.5))) * 24}px)`}}>
          <div style={{display: 'flex', alignItems: 'center', gap: 14, fontFamily: E.style.fonts.body, fontWeight: 700, fontSize: o.buttonSize || 38, color: fg}}>
            {o.button}<span style={{transform: `translateY(${bounce}px)`}}><Icon name="arrowDown" size={40} color={fg} stroke={2.4} /></span>
          </div>
          <div style={{width: '100%', height: 3, background: fg, transform: `scaleX(${ul})`, transformOrigin: '0 50%', opacity: 0.8}} />
        </div>
      ) : null}
    </div>
  );
};

// Front-page sheet: Inverse kicker strip, serif wordmark with a red circle drawn round word2,
// boxed CTA with a red underline stamp. Lands like paper slapped on the desk.
const EndCardNews = ({E, t, o}) => {
  const C = E.style.colors, W = E.width;
  const {kAt, wm, btnAt, y} = endTiming(E, t, o);
  const p = springAt(t, kAt, E.fps, {damping: 16, stiffness: 200});
  const fs = o.size || 132;
  const wp = ease.out(prog(t, wm, wm + 0.3));
  const circ = ease.out(prog(t, wm + 0.35, wm + 0.8));
  const btn = springAt(t, btnAt, E.fps, {damping: 13, stiffness: 190});
  const stamp = ease.out(prog(t, btnAt + 0.25, btnAt + 0.55));
  const sheetW = o.w ?? 860;
  return (
    <div style={{position: 'absolute', left: (W - sheetW) / 2, top: y, width: sheetW, opacity: Math.min(1, p * 3),
      transform: `translateY(${(1 - Math.min(1, p)) * 90}px) rotate(${(o.tilt ?? -1.2) + (1 - Math.min(1, p)) * -5}deg)`}}>
      <div style={{position: 'relative', background: C.paper, padding: '28px 40px 36px', clipPath: RAGGED, boxShadow: '0 30px 60px rgba(20,18,14,.3)',
        display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 20}}>
        <div style={{position: 'absolute', inset: 0, opacity: 0.5, ...halftoneBg(0.05, 6)}} />
        {o.kicker ? <div style={{position: 'relative', alignSelf: 'stretch', background: C.ink, color: C.paper, textAlign: 'center', padding: '10px 0',
          fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: o.kickerSize || 26, letterSpacing: '0.2em'}}>{String(o.kicker).toUpperCase()}</div> : null}
        <div style={{position: 'relative', display: 'flex', alignItems: 'baseline', gap: fs * 0.18, fontFamily: E.style.fonts.serif, fontSize: fs, lineHeight: 1,
          letterSpacing: '-0.045em', color: C.ink, opacity: wp, filter: wp < 0.98 ? `blur(${((1 - wp) * 4).toFixed(1)}px)` : 'none'}}>
          <span>{o.word1}</span>
          <span style={{position: 'relative', fontStyle: 'italic'}}>
            <span style={{position: 'absolute', left: '-16%', right: '-16%', top: '-4%', bottom: '-12%', borderRadius: '50%', border: `${Math.round(fs * 0.07)}px solid ${C.accent}`,
              opacity: Math.min(1, circ * 1.5), transform: `rotate(-4deg) scale(${0.8 + 0.2 * circ})`}} />
            <span style={{position: 'relative'}}>{o.word2}</span>
          </span>
        </div>
        <div style={{position: 'relative', alignSelf: 'stretch', height: 10, borderTop: `3px solid ${C.ink}`, borderBottom: `1px solid ${C.ink}`}} />
        {o.button && t >= btnAt ? (
          <div style={{position: 'relative', opacity: Math.min(1, btn * 2), transform: `scale(${0.85 + 0.15 * Math.min(1.05, btn)})`, display: 'flex', alignItems: 'center', gap: 14,
            padding: '14px 26px', border: `4px solid ${C.ink}`, fontFamily: E.style.fonts.body, fontWeight: 700, fontSize: o.buttonSize || 36, color: C.ink}}>
            {o.button}<Icon name="arrowDown" size={36} color={C.ink} stroke={2.8} />
            <div style={{position: 'absolute', left: -6, right: -6, bottom: -16, height: 9, background: C.accent, transform: `rotate(-1.5deg) scaleX(${stamp})`, transformOrigin: '0 50%'}} />
          </div>
        ) : null}
      </div>
    </div>
  );
};

// CREATOR POP: everything is a sticker. Kicker sticker, two-sticker wordmark that slaps on in sequence,
// mint CTA sticker with a bouncing arrow, chevrons.
const EndCardSticker = ({E, t, o}) => {
  const C = E.style.colors, W = E.width;
  const {kAt, wm, btnAt, y} = endTiming(E, t, o);
  const fs = o.size || 110;
  const bounce = t >= btnAt ? Math.abs(Math.sin((t - btnAt) * 5.5)) * 12 : 0;
  const S = (k, at, rot, body) => {
    if (t < at) return <div key={k} style={{opacity: 0}}>{body}</div>;
    const p = springAt(t, at, E.fps, {damping: 10, stiffness: 240});
    return <div key={k} style={{transform: `scale(${0.15 + 0.85 * p}) rotate(${rot + (1 - Math.min(1, p)) * -28 + Math.sin((t - at) * 3 + k) * 1.2}deg)`, opacity: Math.min(1, p * 3)}}>{body}</div>;
  };
  return (
    <div style={{position: 'absolute', left: 0, top: y, width: W, display: 'flex', flexDirection: 'column', alignItems: 'center', gap: o.gap ?? 34}}>
      {o.kicker ? S(0, kAt, -3, <StickerBody E={E} text={o.kicker} tone="ink" size={o.kickerSize || 30} />) : null}
      <div style={{display: 'flex', alignItems: 'center', gap: fs * 0.12}}>
        {S(1, wm, -4, <StickerBody E={E} text={o.word1} tone="accent" size={fs} shape="rect" />)}
        {S(2, wm + 0.22, 6, <StickerBody E={E} text={o.word2} tone="accent2" size={fs} shape="rect" />)}
      </div>
      {o.button ? S(3, btnAt, -2, (
        <div style={{transform: `translateY(${bounce * 0.3}px)`}}><StickerBody E={E} text={o.button} icon="arrowDown" tone="mint" size={o.buttonSize || 42} /></div>
      )) : null}
      {o.chevrons !== false && t >= btnAt + 0.2 ? (
        <svg width={120} height={170} style={{marginTop: 6}}>
          {[0, 1, 2].map((i) => {
            const ph = ((t - btnAt) * 1.6 - i * 0.22) % 1;
            const op = ph < 0 ? 0 : Math.sin(Math.max(0, ph) * Math.PI);
            return <path key={i} d={`M20 ${25 + i * 48} L60 ${62 + i * 48} L100 ${25 + i * 48}`} stroke={C.ink} strokeWidth={13} fill="none"
              strokeLinecap="round" strokeLinejoin="round" opacity={0.15 + 0.8 * op} />;
          })}
        </svg>
      ) : null}
    </div>
  );
};

// ------------------------------------------------------------------------------------------------ Cutout (background remove)
// Replaces the background behind the speaker for a beat: the person (local matte, PersonLayer) pops forward with a
// white sticker outline over a solid colour, a blurred/dimmed frame or the style backdrop. Always layer 'cutout'.
const Cutout = ({E, t, o}) => {
  const C = E.style.colors;
  const k = ease.smooth(prog(t, o.start, o.start + 0.28)) * (1 - ease.smooth(prog(t, (o.end ?? 1e9) - 0.22, o.end ?? 1e9)));
  if (k <= 0.001) return null;
  const bg = o.bg || 'color';
  if (bg === 'blur' || bg === 'dim') {
    const bl = (o.blur ?? (bg === 'blur' ? 18 : 0)) * k;
    const f = bl > 0.3 ? `blur(${bl.toFixed(1)}px)` : 'none';
    return <div style={{position: 'absolute', inset: 0, background: `rgba(0,0,0,${((o.dim ?? 0.45) * k).toFixed(3)})`, backdropFilter: f, WebkitBackdropFilter: f}} />;
  }
  const col = o.color ? (C[o.color] || o.color) : C.accent;
  const col2 = o.color2 ? (C[o.color2] || o.color2) : null;
  if (bg === 'poster') {
    // POSTER colour field: a lit radial gradient (light spot drifting top-right), two faint concentric rings and a
    // soft floor shade; revealed by a circle that opens from the speaker's head instead of a fade
    const deep = col2 || 'rgba(0,0,0,0.55)';
    const f = faceOnCanvas(E, t);
    const fx = f ? f.x : E.width / 2, fy = f ? f.y : E.height * 0.4;
    const rIn = ease.snappy(prog(t, o.start, o.start + 0.42)), rOut = ease.exitFast(prog(t, (o.end ?? 1e9) - 0.3, o.end ?? 1e9));
    const R = Math.hypot(E.width, E.height) * 1.05 * rIn * (1 - rOut);
    const lx = 72 + Math.sin((t - o.start) * 0.35) * 5, ly = 22 + Math.cos((t - o.start) * 0.3) * 4;
    return (
      <div style={{position: 'absolute', inset: 0, overflow: 'hidden', clipPath: `circle(${R.toFixed(1)}px at ${fx.toFixed(0)}px ${fy.toFixed(0)}px)`,
        background: `radial-gradient(circle at ${lx}% ${ly}%, ${col} 0%, ${col} 18%, ${deep} 100%)`}}>
        <div style={{position: 'absolute', left: `${lx}%`, top: `${ly}%`, width: 520, height: 520, marginLeft: -260, marginTop: -260, borderRadius: '50%',
          border: '2px solid rgba(255,255,255,0.10)', boxShadow: '0 0 0 70px rgba(255,255,255,0.025)'}} />
        <div style={{position: 'absolute', left: `${lx}%`, top: `${ly}%`, width: 980, height: 980, marginLeft: -490, marginTop: -490, borderRadius: '50%',
          border: '2px solid rgba(255,255,255,0.06)'}} />
        <div style={{position: 'absolute', left: 0, right: 0, bottom: 0, height: '38%', background: 'linear-gradient(180deg, rgba(0,0,0,0), rgba(0,0,0,0.28))'}} />
        {o.pattern ? <div style={{position: 'absolute', inset: 0, opacity: 0.08, backgroundImage: 'radial-gradient(rgba(255,255,255,0.9) 1.2px, transparent 1.6px)', backgroundSize: '26px 26px'}} /> : null}
      </div>
    );
  }
  return (
    <div style={{position: 'absolute', inset: 0, opacity: k, overflow: 'hidden',
      background: col2 ? `radial-gradient(circle at 50% 40%, ${col} 0%, ${col2} 100%)` : col}}>
      {o.pattern !== false ? <div style={{position: 'absolute', inset: -40, opacity: 0.14, transform: `rotate(-8deg) translateX(${(t * 40) % 80}px)`,
        backgroundImage: `repeating-linear-gradient(90deg, ${C.ink} 0 3px, transparent 3px 80px)`}} /> : null}
    </div>
  );
};

const REGISTRY = {Cutout, LowerThird, Steps, Counter, Callout, BRoll, ProgressBar, LightLeak, Viewfinder, Badge, KineticTitle, Newsprint, InsightCard, EvidenceCard, ScrapNote, Waveform, FilmStrip, ChatPanel, PromptBar, FloatCard, Tile, EndCard, Burst, Flash,
  Scrim, TitleCard, Headline, Sticker, StickerStack,
  HeroTitle, Accents, ShapeWipe, SceneStack, SceneChat, ScenePrompt, SceneFeatures, ScenePhones,
  MoodGrade, SearchStack, Floaters, Shout, StoryTitle, SceneLens, SceneBuild,
  DepthText, HeadRing, Doodles, FeedWall,
  Glossy, GlowPill, BadgeOrbit, SideCards, Checklist,
  HeadNote, NoteTags, NoteBoard, NoteClips, BigNote,
  ...CUSTOM};  // per-video components from creative.json "custom_components" (creative mode)
export const OVERLAY_TYPES = Object.keys(REGISTRY);

export const Overlays = ({E, t, layer}) => (
  <>
    {(E.overlays || []).filter((o) => (o.layer || 'above') === layer).map((o, i) => {
      const C = REGISTRY[o.type];
      if (!C) return null;
      if (t < o.start - 0.001 || (o.end != null && t > o.end + 0.001)) return null;
      return <C key={i} E={E} t={t} o={o} />;
    })}
  </>
);
