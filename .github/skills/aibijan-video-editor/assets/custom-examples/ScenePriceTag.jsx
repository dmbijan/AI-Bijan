// ScenePriceTag - a big hanging price tag drops in on a string and swings to rest; the spoken word sits on it and a
// stamp slams across ("paling mahal"). For "X jadi barang paling mahal", "harganya", "nilai", "mahal" moments.
//   {kicker, kickerAt, text, at, tag, tagAt, sub, subAt}
import React from 'react';
import {SceneFrame, theme, dispFont, tw, qOut, eOut, prog, lerp, clamp, Chip, Icon, rgba, shutterBlur, vel} from '../../lib/kit.js';

export const ScenePriceTag = ({E, t, o}) => {
  const W = E.width, T = theme(E), D = dispFont(E);
  const at = o.at ?? o.start + 0.2;
  const ax = W / 2, ay = 300;                       // string anchor
  const drop = (k) => lerp(-1300, 0, eOut(prog(k, at - 0.15, at + 0.5)));
  const dy = drop(t), vy = Math.abs(vel(drop, t));
  const u = Math.max(0, t - (at + 0.3));
  const ang = (t < at + 0.3 ? 0 : 1) * 16 * Math.exp(-u * 1.6) * Math.sin(u * 5.2) + Math.sin(t * 1.3) * 0.8;
  const tw_ = 660, th_ = 760, top = ay + 170;       // tag box (hangs 170 px under the anchor)
  const ink = T.flat ? T.ink : '#FFFFFF', paper = T.flat ? T.paper : 'rgba(255,255,255,0.08)';
  const sp = o.tagAt != null ? prog(t, o.tagAt, o.tagAt + 0.38) : 0;
  const ss = lerp(1.7, 1, eOut(sp)), sv = o.tagAt != null ? Math.abs(vel((k) => lerp(1.7, 1, eOut(prog(k, o.tagAt, o.tagAt + 0.38))), t)) : 0;
  const kp = o.kicker && o.kickerAt != null ? tw(t, o.kickerAt, 0.6) : 0;
  const subp = o.sub && o.subAt != null ? tw(t, o.subAt, 0.6) : 0;
  const path = `M${-tw_ / 2 + 110} 0 L${tw_ / 2 - 110} 0 L${tw_ / 2} 110 L${tw_ / 2} ${th_} L${-tw_ / 2} ${th_} L${-tw_ / 2} 110 Z`;
  return (
    <SceneFrame E={E} t={t} o={{...o, tag: undefined, kicker: undefined, enter: 'cut'}}>
      {kp > 0.01 ? (
        <div style={{position: 'absolute', left: 100, top: 285, opacity: clamp(kp * 1.6), transform: `translateY(${(1 - kp) * 24}px)`}}>
          <Chip E={E} text={o.kicker} size={42} tone={T.flat ? 'paper' : 'glass'} />
        </div>
      ) : null}
      <div style={{position: 'absolute', left: 0, top: 0, width: W, height: E.height, transform: `translateY(${dy.toFixed(1)}px)`,
        filter: vy > 60 ? `blur(${Math.min(16, shutterBlur(vy) * 0.6).toFixed(1)}px)` : 'none'}}>
        <div style={{position: 'absolute', left: ax, top: ay, width: 0, height: 0, transform: `rotate(${ang.toFixed(2)}deg)`}}>
          <svg width={tw_ + 40} height={th_ + 260} style={{position: 'absolute', left: -(tw_ + 40) / 2, top: -20, overflow: 'visible'}}>
            <g transform={`translate(${(tw_ + 40) / 2} 20)`}>
              <line x1={0} y1={-400} x2={0} y2={170 + 62} stroke={ink} strokeWidth={6} />
              <g transform="translate(0 170)">
                <path d={path} fill={paper} stroke={ink} strokeWidth={10} strokeLinejoin="round" />
                <circle cx={0} cy={62} r={30} fill={T.flat ? (E.style.colors.bg || '#F1EEE7') : 'rgba(0,0,0,0.4)'} stroke={ink} strokeWidth={8} />
                <line x1={-tw_ / 2 + 60} y1={th_ - 120} x2={tw_ / 2 - 60} y2={th_ - 120} stroke={ink} strokeWidth={4} strokeDasharray="14 12" />
              </g>
            </g>
          </svg>
          <div style={{position: 'absolute', left: -tw_ / 2, top: top - ay + 150, width: tw_, display: 'flex', justifyContent: 'center',
            ...D, fontSize: o.size || 150, letterSpacing: '-0.045em', color: ink, lineHeight: 1}}>{o.text}</div>
          {o.sub && subp > 0.01 ? (
            <div style={{position: 'absolute', left: -tw_ / 2, top: top - ay + th_ - 100, width: tw_, textAlign: 'center', fontFamily: E.style.fonts.ui, fontWeight: 800,
              fontSize: 40, color: T.flat ? (E.style.colors.muted || '#6E6A62') : 'rgba(255,255,255,0.7)', opacity: clamp(subp * 1.6), letterSpacing: '0.02em'}}>{o.sub}</div>
          ) : null}
          {o.tag && sp > 0 ? (
            <div style={{position: 'absolute', left: 0, top: top - ay + 470, transform: `translate(-50%, -50%) rotate(-7deg) scale(${ss.toFixed(3)})`,
              opacity: clamp(sp * 4), filter: sv > 1 ? `blur(${Math.min(12, sv * 2).toFixed(1)}px)` : 'none'}}>
              <div style={{display: 'flex', alignItems: 'center', gap: 18, padding: '18px 34px 20px', background: T.accent, color: T.onFill('accent'),
                fontFamily: E.style.fonts.ui, fontWeight: 800, fontSize: 64, lineHeight: 1, whiteSpace: 'nowrap', borderRadius: T.flat ? 10 : 999,
                border: T.flat ? `6px solid ${T.ink}` : 'none'}}>
                <Icon name="lock" size={56} color={T.onFill('accent')} stroke={3} />{o.tag}
              </div>
            </div>
          ) : null}
        </div>
      </div>
    </SceneFrame>
  );
};
