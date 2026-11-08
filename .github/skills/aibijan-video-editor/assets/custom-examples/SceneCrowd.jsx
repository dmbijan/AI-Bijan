// SceneCrowd - people as flat silhouettes with search bars floating above their heads.
//   mode 'crowd': a crowd fills in, a blank search bar pops above every head (barsAt), the viewer's own figure ("you")
//                 slides into the foreground, then one person in front steps forward ("focus") and their bar types `prompt`.
//   mode 'pair' : two people face each other (you + the other person), a heart pops between them (heartAt), a bar rises
//                 above the other person (barAt) and types `prompt` (typeAt).
// Every label/prompt is a spoken phrase (grounding-checked by the planner: items[].text, prompt).
import React from 'react';
import {AbsoluteFill, SceneFrame, theme, tw, qOut, qIn, qInOut, eOut, backOut, prog, lerp, clamp, rand, Icon, Chip, rgba,
  shutterBlur, vel, dirBlur} from '../../lib/kit.js';

// one silhouette, feet at (0, 0), facing the camera: rounded shoulders + head
const Person = ({x, y, s, color, op = 1, blur = 0}) => (
  <g transform={`translate(${x.toFixed(1)} ${y.toFixed(1)}) scale(${s.toFixed(4)})`} opacity={op}
    style={{filter: blur > 0.4 ? `blur(${blur.toFixed(1)}px)` : 'none'}}>
    <path d="M-78 0 L-78 -118 Q-78 -196 0 -196 Q78 -196 78 -118 L78 0 Z" fill={color} />
    <circle cx={0} cy={-262} r={56} fill={color} />
  </g>
);
const HEAD_TOP = 318; // from the feet, in person units

// search bar above a head; text typed from typeAt (blank shimmer when there is no text)
const Bar = ({E, t, T, cx, cy, size, at, text, typeAt, typing = 1, k = 1, wide = false}) => {
  if (t < at) return null;
  const p = tw(t, at, 0.55);
  const txt = String(text || '');
  const n = typeAt != null ? Math.round(txt.length * clamp((t - typeAt) / typing)) : 0;
  const typingNow = typeAt != null && t >= typeAt - 0.2 && t < typeAt + typing + 0.5;
  const ink = T.flat ? T.ink : 'rgba(10,12,18,0.72)';
  const W = E.width;
  const minW = wide ? size * 12 : size * 6.2;
  return (
    <div style={{position: 'absolute', left: cx, top: cy, transform: `translate(-50%, -50%) translateY(${(1 - p) * 30}px) scale(${(0.8 + 0.2 * p) * k})`,
      opacity: clamp(p * 1.6), filter: p < 0.98 ? `blur(${((1 - p) * 8).toFixed(1)}px)` : 'none'}}>
      <div style={{display: 'flex', alignItems: 'center', gap: size * 0.45, padding: `${size * 0.36}px ${size * 0.62}px`, minWidth: minW, maxWidth: W - 120,
        width: 'max-content', background: ink, borderRadius: T.flat ? size * 0.26 : size * 0.9, color: T.flat ? T.paper : '#FFFFFF',
        fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: size, lineHeight: 1.15,
        boxShadow: T.flat ? 'none' : '0 18px 40px rgba(0,0,0,0.35)', border: T.flat ? 'none' : '2px solid rgba(255,255,255,0.2)'}}>
        <Icon name="search" size={size * 0.95} color={T.accent} stroke={2.8} />
        {n > 0 || typingNow ? (
          <span style={{textWrap: 'balance'}}>{txt.slice(0, n)}
            {typingNow ? <span style={{display: 'inline-block', width: Math.max(3, size * 0.07), height: size * 1.02, marginLeft: 3, verticalAlign: 'middle',
              background: T.accent, opacity: 0.35 + 0.65 * (0.5 + 0.5 * Math.cos(t * 9))}} /> : null}</span>
        ) : (
          <span style={{display: 'flex', gap: size * 0.28}}>
            {[0.9, 1.5, 1.0].map((w, j) => <span key={j} style={{width: size * w * 1.5, height: size * 0.4, borderRadius: T.flat ? size * 0.08 : size * 0.2,
              background: T.flat ? rgba(T.paper, 0.32) : 'rgba(255,255,255,0.24)', opacity: 0.45 + 0.55 * (0.5 + 0.5 * Math.sin(t * 5 - j * 0.8 + at))}} />)}
          </span>
        )}
      </div>
    </div>
  );
};

const Label = ({E, t, at, x, y, text, tone}) => {
  if (!text || t < at) return null;
  const p = tw(t, at + 0.15, 0.55);
  return (
    <div style={{position: 'absolute', left: x, top: y, transform: `translate(-50%, 0) translateY(${(1 - p) * 22}px)`, opacity: clamp(p * 1.6),
      filter: p < 0.98 ? `blur(${((1 - p) * 8).toFixed(1)}px)` : 'none'}}>
      <Chip E={E} text={text} size={40} tone={tone} dot={false} />
    </div>
  );
};

export const SceneCrowd = ({E, t, o}) => {
  const W = E.width, H = E.height, T = theme(E);
  const items = o.items || [];
  const role = (r) => items.find((it) => it.role === r) || {};
  const crowdCol = (a) => (T.flat ? rgba(T.ink, a) : `rgba(255,255,255,${(a * 0.7).toFixed(3)})`);
  const mode = o.mode || 'crowd';

  if (mode === 'pair') {
    const you = role('you'), other = role('other');
    const enter = (side) => (k) => (1 - qOut(prog(k, o.start + 0.05, o.start + 0.75))) * side * W * 0.6;
    const fl = enter(-1), fr = enter(1);
    const bl = Math.min(26, shutterBlur(Math.abs(vel(fl, t))));
    const S = 1.75, feet = 1190, yx = 290 + fl(t), ox = 790 + fr(t);
    const headY = feet - 262 * S, topY = feet - HEAD_TOP * S;
    const hp = o.heartAt != null ? tw(t, o.heartAt, 0.55, backOut) : 0;
    const beat = 1 + 0.06 * Math.max(0, Math.sin((t - (o.heartAt ?? 0)) * 7.5)) * (t > (o.heartAt ?? 1e9) + 0.5 ? 1 : 0);
    const barX = clamp(ox, 420, W - 420);
    return (
      <SceneFrame E={E} t={t} o={{...o, enter: 'cut'}}>
        <svg width={W} height={H} style={{position: 'absolute', inset: 0, overflow: 'visible', filter: dirBlur(bl, 0)}}>
          <Person x={yx} y={feet} s={S} color={T.accent} />
          <Person x={ox} y={feet} s={S} color={T.flat ? T.ink : '#FFFFFF'} />
        </svg>
        <Label E={E} t={t} at={you.at ?? o.start} x={290} y={headY + 170} text={you.text} tone="ink" />
        <Label E={E} t={t} at={other.at ?? o.start} x={790} y={headY + 170} text={other.text} tone="paper" />
        {hp > 0.01 ? (
          <div style={{position: 'absolute', left: W / 2, top: headY - 40, transform: `translate(-50%, -50%) scale(${hp * beat})`, opacity: clamp(hp * 2)}}>
            <Icon name="heart" size={150} color={T.accent} stroke={2.6} fill={T.accent} />
          </div>
        ) : null}
        <Bar E={E} t={t} T={T} cx={barX} cy={topY - 120} size={46} at={o.barAt ?? o.start + 1} text={o.prompt} typeAt={o.typeAt} typing={o.typing || 1.1} wide />
      </SceneFrame>
    );
  }

  // crowd: two rows walking slowly across, blank bars above every head
  const n = o.people || 7, barsAt = o.barsAt ?? o.start + 0.3;
  const you = role('you'), focus = role('focus');
  const fAt = focus.at ?? 1e9, fk = tw(t, fAt, 0.9, qInOut);
  const crowd = Array.from({length: n}, (_, i) => {
    const back = i % 2 === 0;
    const s = back ? 0.8 : 1.08;
    const dir = rand(i * 3.7) > 0.5 ? 1 : -1;
    const x0 = ((i + 0.5) / n) * W + (rand(i * 1.9) - 0.5) * 70;
    const x = ((x0 + dir * (t - o.start) * (18 + 14 * rand(i)) + W * 1.2) % (W * 1.2)) - W * 0.1;
    const feet = back ? 950 : 1210, bob = Math.abs(Math.sin((t - o.start) * 3.2 + i)) * 6 * s;
    return {i, s, x, y: feet - bob, back, inAt: o.start + 0.1 + (i % 5) * 0.06};
  });
  // the person in front: steps forward and grows on focus.at
  const fS = lerp(1.08, 1.6, fk), fx_ = W * 0.64, fFeet = lerp(1210, 1330, fk);
  const yp = you.at != null ? tw(t, you.at, 0.8) : 0;
  const yS = 1.75, yx = lerp(-260, 170, yp), yFeet = 1420;
  return (
    <SceneFrame E={E} t={t} o={{...o, enter: 'cut'}}>
      <svg width={W} height={H} style={{position: 'absolute', inset: 0, overflow: 'visible'}}>
        {crowd.map((c) => {
          const p = tw(t, c.inAt, 0.6);
          const dim = 1 - 0.55 * fk;
          return <Person key={c.i} x={c.x} y={c.y + (1 - p) * 60} s={c.s} color={crowdCol(c.back ? 0.2 : 0.38)} op={clamp(p * 1.5) * dim} blur={fk * (c.back ? 3 : 1.5)} />;
        })}
        {fk > 0.001 ? <Person x={fx_} y={fFeet} s={fS} color={T.flat ? T.ink : '#FFFFFF'} op={clamp(fk * 3)} /> : null}
        {yp > 0.001 ? <Person x={yx} y={yFeet} s={yS} color={T.accent} /> : null}
      </svg>
      {crowd.map((c) => (
        <Bar key={c.i} E={E} t={t} T={T} cx={c.x} cy={c.y - HEAD_TOP * c.s - 38} size={c.back ? 26 : 32} at={barsAt + (c.i % n) * 0.07}
          k={1 - 0.6 * fk} />
      ))}
      <Label E={E} t={t} at={you.at ?? 1e9} x={yx + 30} y={yFeet - 318 * yS - 90} text={you.text} tone="ink" />
      {fk > 0.001 ? (
        <>
          <Bar E={E} t={t} T={T} cx={clamp(fx_, 430, W - 430)} cy={fFeet - HEAD_TOP * fS - 70} size={44} at={fAt + 0.2} text={o.prompt} typeAt={o.typeAt}
            typing={o.typing || 1} wide />
          <Label E={E} t={t} at={fAt + 0.3} x={fx_} y={fFeet - 262 * fS + 110} text={focus.text} tone="paper" />
        </>
      ) : null}
    </SceneFrame>
  );
};
