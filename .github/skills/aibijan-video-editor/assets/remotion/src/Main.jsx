import React from 'react';
import {AbsoluteFill, useCurrentFrame, useVideoConfig} from 'remotion';
import {Footage, PersonLayer} from './components/Footage.jsx';
import {Captions} from './components/Captions.jsx';
import {Overlays} from './components/Overlays.jsx';
import {AuroraBackdrop, PaperBackdrop} from './components/Scenes.jsx';
import {Finish, Letterbox, MotionBlurDefs} from './components/FX.jsx';

// Soft neutral backdrop with a dot grid and two slowly drifting accent glows. Only visible where the
// footage does not cover the canvas (cards, split screens, end card).
const Backdrop = ({E, t}) => {
  const C = E.style.colors, W = E.width, H = E.height;
  const gx = Math.sin(t * 0.35) * 60, gy = Math.cos(t * 0.27) * 50;
  if (E.style.backdrop?.variant === 'aurora') return <AuroraBackdrop E={E} t={t} />;
  if (E.style.backdrop?.variant === 'paper') return <PaperBackdrop E={E} t={t} />;
  if (E.style.backdrop?.variant === 'newsprint') return (
    <AbsoluteFill style={{background: C.bg, overflow: 'hidden'}}>
      <div style={{position: 'absolute', inset: -60, opacity: 0.12,
        backgroundImage: 'radial-gradient(rgba(35,34,28,.55) 1px, transparent 1.3px)',
        backgroundSize: '8px 8px', transform: `translate(${Math.sin(t * .2) * 5}px, ${Math.cos(t * .2) * 5}px)`}} />
      <div style={{position: 'absolute', inset: 80, border: `2px solid ${C.line}`, opacity: 0.6}} />
      <div style={{position: 'absolute', left: 95, right: 95, top: 95, height: 5, background: C.ink, opacity: 0.8}} />
      <div style={{position: 'absolute', left: 95, right: 95, top: 108, height: 2, background: C.ink, opacity: 0.55}} />
      {/* decorative text columns (grey rules, no words): reads as a printed page behind the collage */}
      <div style={{position: 'absolute', left: 110, right: 110, top: 150, bottom: 140, display: 'flex', gap: 34, opacity: 0.5}}>
        {[0, 1, 2].map((c) => (
          <div key={c} style={{flex: 1, display: 'flex', flexDirection: 'column', gap: 14, borderLeft: c ? `1px solid ${C.line}` : 'none', paddingLeft: c ? 17 : 0}}>
            {Array.from({length: 46}).map((_, r) => <div key={r} style={{height: 7, borderRadius: 2, background: C.line,
              width: `${[100, 96, 88, 100, 93, 71][(r * 7 + c * 3) % 6]}%`, opacity: r % 11 === 10 ? 0 : 1}} />)}
          </div>
        ))}
      </div>
    </AbsoluteFill>
  );
  if (E.style.backdrop?.variant === 'casefile') return (
    <AbsoluteFill style={{background: C.bg, overflow: 'hidden'}}>
      <div style={{position: 'absolute', inset: -35, opacity: 0.3,
        backgroundImage: `linear-gradient(${C.grid} 1px, transparent 1px),linear-gradient(90deg,${C.grid} 1px,transparent 1px)`,
        backgroundSize: '55px 55px'}} />
      <div style={{position: 'absolute', left: 70, right: 70, top: 115, height: 2, background: C.line, opacity: .5}} />
      <div style={{position: 'absolute', left: W * .2, top: H * .09, width: W * .8, height: W * .8,
        borderRadius: '50%', background: `radial-gradient(circle,${C.glow1},transparent 70%)`}} />
    </AbsoluteFill>
  );
  if (E.style.backdrop?.variant === 'business') return (
    <AbsoluteFill style={{background: C.bg, overflow: 'hidden'}}>
      <div style={{position: 'absolute', inset: -40, opacity: 1,
        backgroundImage: `linear-gradient(${C.grid} 1px, transparent 1px),linear-gradient(90deg,${C.grid} 1px,transparent 1px)`,
        backgroundSize: '90px 90px', transform: `translateY(${(t * 8) % 90}px)`}} />
      <div style={{position: 'absolute', left: 0, right: 0, top: H * 0.62, height: H * 0.38,
        background: `linear-gradient(180deg, transparent, ${C.glow1})`}} />
      <div style={{position: 'absolute', width: W * 0.9, height: W * 0.9, right: -W * 0.35 + gx * 0.5, top: H * 0.04 + gy * 0.5, borderRadius: '50%',
        background: `radial-gradient(circle, ${C.glow2} 0%, transparent 65%)`}} />
    </AbsoluteFill>
  );
  if (E.style.backdrop?.variant === 'scrapbook') return (
    <AbsoluteFill style={{background: C.bg, overflow: 'hidden'}}>
      <div style={{position: 'absolute', inset: 0, opacity: .28, backgroundImage:
        `radial-gradient(${C.grid} 1.5px, transparent 1.9px)`, backgroundSize: '16px 16px'}} />
      <div style={{position: 'absolute', left: -80, top: 250, width: 200, height: 85,
        transform: 'rotate(-13deg)', background: C.accent, opacity: .35}} />
      <div style={{position: 'absolute', right: -65, bottom: 320, width: 160, height: 250,
        transform: 'rotate(11deg)', background: C.accent2, opacity: .13}} />
    </AbsoluteFill>
  );
  return (
    <AbsoluteFill style={{background: C.bg, overflow: 'hidden'}}>
      <div style={{position: 'absolute', inset: -40, backgroundImage: `radial-gradient(${C.grid} 2.2px, transparent 2.6px)`,
        backgroundSize: '44px 44px', transform: `translate(${(t * 6) % 44}px, ${(t * 4) % 44}px)`}} />
      <div style={{position: 'absolute', width: W * 1.1, height: W * 1.1, left: -W * 0.45 + gx, top: H * 0.05 + gy, borderRadius: '50%',
        background: `radial-gradient(circle, ${C.glow1} 0%, transparent 62%)`}} />
      <div style={{position: 'absolute', width: W * 1.2, height: W * 1.2, right: -W * 0.55 - gx, bottom: H * 0.02 - gy, borderRadius: '50%',
        background: `radial-gradient(circle, ${C.glow2} 0%, transparent 62%)`}} />
    </AbsoluteFill>
  );
};

export const Main = (E) => {
  const frame = useCurrentFrame();
  const {fps} = useVideoConfig();
  if (!E || !E.style) {
    return <AbsoluteFill style={{background: '#fff', justifyContent: 'center', alignItems: 'center', fontSize: 40}}>edit.json missing</AbsoluteFill>;
  }
  const t = frame / fps;
  return (
    <AbsoluteFill style={{background: E.style.colors.bg}}>
      <MotionBlurDefs />
      <Backdrop E={E} t={t} />
      <Overlays E={E} t={t} layer="back" />
      <Footage E={E} t={t} />
      <Overlays E={E} t={t} layer="cutout" />
      <Overlays E={E} t={t} layer="behind" />
      <PersonLayer E={E} t={t} />
      <Overlays E={E} t={t} layer="below" />
      <Captions E={E} t={t} />
      <Overlays E={E} t={t} layer="above" />
      <Overlays E={E} t={t} layer="top" />
      <Letterbox E={E} t={t} />
      <Finish E={E} t={t} />
    </AbsoluteFill>
  );
};
