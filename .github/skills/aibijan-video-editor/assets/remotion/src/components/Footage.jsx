// Footage layer: one or more "views" of the (already cut) footage, each a rectangle on the canvas with its
// own zoom, face-anchored framing and colour grade. Layout keyframes morph views between states
// (full-frame -> cards -> split -> end card) or reveal a new state with a wipe.
//
// v2 additions (After-Effects-grade camera):
//   * cut transitions at jump cuts (E.cutFx): zoom-through, whip, push, flash, leak - each with real
//     directional motion blur derived from the move's velocity (180 deg shutter)
//   * zoom-velocity blur on fast punch-ins, subtle fbm "operator" drift (style.camera.drift px)
//   * per-view finishing: colour tint wash + vignette (style.finish)
import React from 'react';
import {AbsoluteFill, Freeze, OffthreadVideo, staticFile} from 'remotion';
import {clamp, dirBlur, ease, evalTrack, fbm, lerp, prog, rand, sample, shutterBlur, springAt} from '../lib/anim.js';
import {Icon} from './Icons.jsx';
import {LeakSweep, rgbFilter} from './FX.jsx';

const NUM = ['x', 'y', 'w', 'h', 'radius', 'z', 'fx', 'fy', 'grade', 'shadow', 'opacity', 'rot', 'border', 'scale'];

const viewDefaults = (v, W, H) => ({
  x: 0, y: 0, w: W, h: H, radius: 0, z: 1, fx: null, fy: null, grade: 1, shadow: 0, opacity: 1, rot: 0, border: 0, scale: 1,
  camera: false, ...v, ...(v.rect ? {x: v.rect[0], y: v.rect[1], w: v.rect[2], h: v.rect[3]} : {}),
});

function jumpK(E, t) {
  let k = 1;
  for (const [tt, kk] of E.jumpZoom || []) { if (t >= tt) k = kk; else break; }
  return k;
}

function baseCam(E, t) {
  const c = evalTrack(E.camera, t, ['s', 'fx', 'fy']) || {s: 1, fx: 0.5, fy: 0.4};
  const breathe = 1 + (E.style?.camera?.breathe ?? 0.012) * (1 - Math.cos((t * 2 * Math.PI) / 9)) / 2;
  const push = E.camPush && E.camPush.length ? (evalTrack(E.camPush, t, ['k'])?.k ?? 1) : 1; // rhythm pushes (slow dolly-ins)
  return {z: c.s * jumpK(E, t) * breathe * push, fx: c.fx, fy: c.fy};
}

// Cut transition active at t -> {zMul, dx, dy, bx, by, flash, leak}. Moves are expressed as functions of time so
// the blur can be computed from the true velocity of the move (not guessed).
// Camera hits (v3.7, creative.json camera presets "shake" / "glitch"): a decaying handheld shake on an impact word, or a
// digital glitch (RGB split + horizontal jitter at 30 Hz). They ride on top of whatever cut transition is active, so the
// face track, the person matte and every face-anchored element shake with the footage.
function hitAt(E, t) {
  let dx = 0, dy = 0, rgb = 0, zMul = 1;
  for (const h of E.hits || []) {
    const dur = h.dur ?? (h.kind === 'glitch' ? 0.3 : 0.45);
    if (t < h.t || t > h.t + dur) continue;
    const p = (t - h.t) / dur, amt = h.amount ?? 1;
    if (h.kind === 'shake') {
      const decay = Math.pow(1 - p, 2.2);
      dx += (fbm(t * 38, 3) * 26 + Math.sin(t * 95) * 6) * decay * amt;
      dy += (fbm(t * 34, 9) * 20) * decay * amt;
      zMul *= 1 + 0.025 * decay * amt;
    } else if (h.kind === 'glitch') {
      const step = Math.floor(t * 30);
      const on = rand(step * 7.13 + 2) > 0.25 ? 1 : 0.2;
      rgb += (8 + 16 * rand(step * 3.1)) * on * (1 - p * 0.6) * amt * (rand(step * 1.9) > 0.5 ? 1 : -1);
      dx += (rand(step * 5.7) - 0.5) * 34 * on * (1 - p) * amt;
    }
  }
  return dx || dy || rgb || zMul !== 1 ? {dx, dy, rgb, zMul} : null;
}

function cutFxAt(E, t) {
  const base = cutFxBase(E, t), h = hitAt(E, t);
  if (!h) return base;
  const b = base || {zMul: 1, dx: 0, dy: 0, bx: 0, by: 0};
  return {...b, zMul: (b.zMul ?? 1) * h.zMul, dx: (b.dx || 0) + h.dx, dy: (b.dy || 0) + h.dy, rgb: h.rgb};
}

function cutFxBase(E, t) {
  const W = E.width;
  for (const c of E.cutFx || []) {
    const pre = c.pre ?? 0.1, dur = c.dur ?? 0.24;
    if (c.fx === 'leak') {
      // the leak drifts across for ~0.75 s centred slightly before the cut, so it hides the jump
      if (t >= c.t - 0.3 && t <= c.t + 0.5) return {zMul: 1, dx: 0, dy: 0, bx: 0, by: 0, leak: c};
      continue;
    }
    if (t < c.t - pre || t > c.t + dur) continue;
    const before = t < c.t;
    const dir = c.dir || 1;
    if (c.fx === 'zoom') {
      // outgoing pushes in fast, incoming lands from a bigger scale: reads as one continuous zoom through the cut
      const m = (k) => (k < c.t ? 1 + 0.12 * ease.in(prog(k, c.t - pre, c.t)) : 1 + 0.2 * (1 - ease.snappy(prog(k, c.t, c.t + dur))));
      const zm = m(t), v = Math.abs(m(t + 1 / 120) - m(t - 1 / 120)) * 60;
      const b = shutterBlur(v * W * 0.5);
      return {zMul: zm, dx: 0, dy: 0, bx: b, by: b, iso: true};
    }
    if (c.fx === 'whip' || c.fx === 'push') {
      const amp = c.fx === 'whip' ? 1 : 0.45;
      const f = (k) => (k < c.t ? -dir * amp * W * 0.18 * ease.in(prog(k, c.t - pre, c.t))
        : dir * amp * W * 0.22 * (1 - ease.snappy(prog(k, c.t, c.t + dur))));
      const x = f(t), v = Math.abs(f(t + 1 / 120) - f(t - 1 / 120)) * 60;
      const bx = shutterBlur(v) * (c.fx === 'whip' ? 1.6 : 1);
      return {zMul: 1.06, dx: x, dy: 0, bx, by: 0};
    }
    if (c.fx === 'flash' && !before) {
      const p = prog(t, c.t, c.t + 0.14);
      return {zMul: 1 + 0.04 * (1 - ease.snappy(prog(t, c.t, c.t + dur))), dx: 0, dy: 0, bx: 0, by: 0, flash: (1 - p) * (c.amount ?? 0.55)};
    }
    if (c.fx === 'leak') return {zMul: 1, dx: 0, dy: 0, bx: 0, by: 0, leak: c};
  }
  return null;
}

function resolveView(E, v, t, W, H) {
  const r = viewDefaults(v, W, H);
  if (r.camera) {
    const c = baseCam(E, t);
    r.z = c.z; r.fx = c.fx; r.fy = c.fy;
    r.zv = Math.abs(baseCam(E, t + 1 / 120).z - baseCam(E, t - 1 / 120).z) * 60; // zoom speed (scale units/s)
    if (!v.rect) { r.x = 0; r.y = 0; r.w = W; r.h = H; }
  }
  return r;
}

function enterState(r, en = {}) {
  const s = en.s ?? 0.88;
  return {
    ...r,
    x: r.x + (en.dx ?? 0) + (r.w * (1 - s)) / 2,
    y: r.y + (en.dy ?? 80) + (r.h * (1 - s)) / 2,
    w: r.w * s, h: r.h * s, opacity: en.o ?? 0, rot: (r.rot || 0) + (en.rot ?? 0),
  };
}

function mixState(a, b, p) {
  const o = {...b};
  for (const f of NUM) {
    if (a[f] == null || b[f] == null) { o[f] = b[f] ?? a[f]; continue; }
    // opacity resolves faster than geometry so entering cards never look like ghosts
    o[f] = f === 'opacity' ? lerp(a[f], b[f], clamp(p * 2.5)) : lerp(a[f], b[f], p);
  }
  if (a.fx == null || b.fx == null) { o.fx = p < 0.5 ? a.fx : b.fx; o.fy = p < 0.5 ? a.fy : b.fy; }
  return o;
}

// Views to draw at time t: [{state, key}] plus optional wipe info.
export function layoutAt(E, t, W, H) {
  const L = E.layouts && E.layouts.length ? E.layouts : [{t: 0, dur: 0, views: [{id: 'main', camera: true}]}];
  let i = 0;
  for (let k = 0; k < L.length; k++) if (t >= L[k].t) i = k;
  const cur = L[i], prev = i > 0 ? L[i - 1] : null;
  const curViews = cur.views.map((v) => resolveView(E, v, t, W, H));
  const dur = cur.dur ?? 0.5;
  const raw = prog(t, cur.t, cur.t + dur);
  if (!prev || dur <= 0 || raw >= 1 || cur.transition === 'cut') {
    return {views: curViews.map((s) => ({state: s})), wipe: null, divider: cur.divider ? {...cur.divider, o: 1} : null};
  }
  const prevViews = prev.views.map((v) => resolveView(E, v, t, W, H));
  if (cur.transition === 'wipe') {
    return {views: prevViews.map((s) => ({state: s})), next: curViews.map((s) => ({state: s})), wipe: {p: ease.inOut(raw), dir: cur.dir || 1},
      divider: prev.divider ? {...prev.divider, o: 1} : null, nextDivider: cur.divider ? {...cur.divider, o: 1} : null};
  }
  const curve = (k) => (cur.spring
    ? clamp(springAt(k, cur.t, E.fps, {damping: 16, stiffness: 120, ...(cur.springConfig || {})}), 0, 1.15)
    : (ease[cur.ease || 'smooth'] || ease.smooth)(prog(k, cur.t, cur.t + dur)));
  const p = curve(t);
  const pv = Math.abs(curve(t + 1 / 120) - curve(t - 1 / 120)) * 60; // layout progress per second
  const out = [];
  for (const s of curViews) {
    const from = prevViews.find((pv_) => pv_.id === s.id) || enterState(s, s.enter);
    const m = mixState(from, s, p);
    // motion blur of the morph itself: how fast the card's centre travels
    const dx = Math.abs((s.x + s.w / 2) - (from.x + from.w / 2)) * pv, dy = Math.abs((s.y + s.h / 2) - (from.y + from.h / 2)) * pv;
    m.mbx = shutterBlur(dx); m.mby = shutterBlur(dy);
    out.push({state: m});
  }
  for (const pvw of prevViews) {
    if (!curViews.find((s) => s.id === pvw.id)) {
      const ex = pvw.exit || {};
      const sc = lerp(1, ex.s ?? 0.9, p);
      out.unshift({state: {...pvw, opacity: pvw.opacity * (1 - p), x: pvw.x + (ex.dx ?? 0) * p + (pvw.w * (1 - sc)) / 2,
        y: pvw.y + (ex.dy ?? 0) * p + (pvw.h * (1 - sc)) / 2, w: pvw.w * sc, h: pvw.h * sc}});
    }
  }
  const divider = cur.divider ? {...cur.divider, o: clamp(raw * 2)} : prev.divider ? {...prev.divider, o: 1 - clamp(raw * 2)} : null;
  return {views: out, wipe: null, divider};
}

// Diagonal divider for 'versus' layouts: a white (or style) stripe along the clip edge, drawn on as it enters.
function Divider({E, d}) {
  if (!d || d.o <= 0) return null;
  const W = E.width, H = E.height;
  const x1 = d.top * W, x2 = d.bottom * W;
  const len = d.o;
  return (
    <svg width={W} height={H} style={{position: 'absolute', inset: 0, pointerEvents: 'none', filter: 'drop-shadow(0 0 18px rgba(0,0,0,0.35))'}}>
      <line x1={x1} y1={0} x2={lerp(x1, x2, len)} y2={H * len} stroke={d.color || '#FFFFFF'} strokeWidth={d.width || 14} strokeLinecap="butt" />
    </svg>
  );
}

// Torn-paper reveal edge for the 'tear' wipe: deterministic jagged x offsets along the edge.
function tearEdge(X, H, sk, amp = 22, step = 46, seed = 3) {
  const pts = [];
  for (let y = 0, i = 0; y <= H + step; y += step, i++) {
    const yy = Math.min(H, y);
    const base = X + sk - (2 * sk * yy) / H;
    pts.push([base + (rand(i * 7.13 + seed) - 0.5) * 2 * amp, yy]);
  }
  return pts;
}

export function gradeFilter(g, G = {}) {
  const sat = lerp(G.rawSat ?? 0.42, G.sat ?? 1.1, g);
  const con = lerp(G.rawCon ?? 0.86, G.con ?? 1.07, g);
  const bri = lerp(G.rawBri ?? 0.98, G.bri ?? 1.04, g);
  const warm = (G.warm ?? 0) * g;
  return `saturate(${sat.toFixed(3)}) contrast(${con.toFixed(3)}) brightness(${bri.toFixed(3)})` + (warm ? ` sepia(${warm.toFixed(3)})` : '');
}

function ViewBox({E, s, t, clip, fxs, mask, outline}) {
  const vw = E.footage.width, vh = E.footage.height;
  const contain = s.fit === 'contain';
  const c = contain ? Math.min(s.w / vw, s.h / vh) : Math.max(s.w / vw, s.h / vh);
  const VW = vw * c, VH = vh * c;
  const cut = s.camera ? fxs : null;
  const z = Math.max(1, s.z) * (cut?.zMul ?? 1);
  const face = sample(E.faces, t) || [0.5, 0.4, 0.3];
  const drift = s.camera ? (E.style?.camera?.drift ?? 0) : 0;
  let tx, ty;
  if (contain) {
    // source aspect far from canvas aspect (e.g. 9:16 footage in 16:9): show it whole, zoom about its centre
    tx = (s.w - z * VW) / 2; ty = (s.h - z * VH) / 2;
  } else if (s.fx == null) { tx = (s.w - z * VW) / 2; ty = (s.h - z * VH) / 2; }
  else {
    tx = s.fx * s.w - z * face[0] * VW;
    ty = s.fy * s.h - z * face[1] * VH;
  }
  if (drift) { tx += fbm(t * 0.32, 1) * drift; ty += fbm(t * 0.27, 5) * drift * 0.7; }
  if (cut) { tx += cut.dx; ty += cut.dy; }
  if (!contain) { tx = clamp(tx, s.w - z * VW, 0); ty = clamp(ty, s.h - z * VH, 0); }
  const C = E.style.colors;
  const F = E.style.finish || {};
  const shadow = s.shadow > 0 ? `0 ${30 * s.shadow}px ${80 * s.shadow}px rgba(10,12,8,${0.28 * s.shadow})` : 'none';
  const ring = s.border > 0 ? `0 0 0 ${s.border}px ${s.borderColor || C.accent}` : null;
  const tagP = s.tag ? springAt(t, (s.tagAt ?? 0), E.fps, {damping: 12, stiffness: 190}) : 0;
  // blur budget: zoom speed (punch-ins), cut transition moves, card morphs. Capped to stay tasteful.
  const zb = s.zv ? Math.min(3, shutterBlur(s.zv * Math.hypot(s.w, s.h) * 0.5)) : 0;
  let vidFilter = gradeFilter(s.grade, E.style.grade);
  let boxFilter = 'none';
  if (cut && (cut.bx > 0.6 || cut.by > 0.6)) {
    if (cut.iso) vidFilter += ` blur(${Math.min(9, cut.bx).toFixed(1)}px)`;
    else boxFilter = dirBlur(Math.min(24, cut.bx), Math.min(24, cut.by));
  } else if (zb > 0.7) vidFilter += ` blur(${zb.toFixed(1)}px)`;
  if ((s.mbx || 0) > 0.8 || (s.mby || 0) > 0.8) boxFilter = dirBlur(Math.min(18, s.mbx || 0), Math.min(18, s.mby || 0));
  if (cut?.rgb) vidFilter += ' ' + rgbFilter(cut.rgb);
  // "Printed photo" treatment for cards (style.photo defaults, per-view overrides)
  const P = !s.camera || s.rect ? (E.style.photo || {}) : {};
  const halftone = s.halftone ?? P.halftone ?? 0;
  const tape = s.tape ?? P.tape ?? false;
  const ringCol = s.borderColor || P.borderColor;
  const ring2 = s.border > 0 ? `0 0 0 ${s.border}px ${ringCol || C.accent}` : null;
  if (mask) {
    const o = outline?.width || 0, oc = outline?.color || '#FFFFFF';
    const stroke = o > 0 ? `drop-shadow(${o}px 0 0 ${oc}) drop-shadow(-${o}px 0 0 ${oc}) drop-shadow(0 ${o}px 0 ${oc}) drop-shadow(0 -${o}px 0 ${oc})` : '';
    const depth = outline?.shadow === false ? '' : ' drop-shadow(0 24px 40px rgba(0,0,0,0.35))';
    return (
      <div style={{position: 'absolute', left: s.x, top: s.y, width: s.w, height: s.h, overflow: 'hidden', opacity: s.opacity,
        transform: `rotate(${s.rot || 0}deg) scale(${s.scale || 1})`, filter: boxFilter}}>
        <div style={{position: 'absolute', inset: 0, filter: (stroke + depth).trim() || 'none'}}>
          <OffthreadVideo src={staticFile(E.footage.src)} muted
            style={{position: 'absolute', left: 0, top: 0, width: VW, height: VH, transformOrigin: '0 0', maxWidth: 'none',
              transform: `translate(${tx}px, ${ty}px) scale(${z})`, filter: vidFilter,
              WebkitMaskImage: `url(${mask})`, maskImage: `url(${mask})`, WebkitMaskSize: '100% 100%', maskSize: '100% 100%',
              WebkitMaskRepeat: 'no-repeat', maskRepeat: 'no-repeat'}} />
        </div>
      </div>
    );
  }
  const box = (
    <div style={{
      position: 'absolute', left: s.x, top: s.y, width: s.w, height: s.h, borderRadius: s.radius, overflow: 'hidden',
      opacity: s.opacity, transform: `rotate(${s.rot || 0}deg) scale(${s.scale || 1})`, boxShadow: [ring2, shadow].filter(Boolean).join(', ') || 'none',
      clipPath: clip || (s.clip ? `polygon(${s.clip})` : undefined), background: '#111', willChange: 'transform', filter: boxFilter,
    }}>
      {contain ? (() => {
        const cb = Math.max(s.w / vw, s.h / vh);
        return <OffthreadVideo src={staticFile(E.footage.src)} muted style={{position: 'absolute', left: (s.w - vw * cb * 1.1) / 2, top: (s.h - vh * cb * 1.1) / 2,
          width: vw * cb * 1.1, height: vh * cb * 1.1, filter: `blur(36px) brightness(0.92) ${gradeFilter(s.grade, E.style.grade)}`, maxWidth: 'none'}} />;
      })() : null}
      <OffthreadVideo
        src={staticFile(E.footage.src)}
        muted
        style={{position: 'absolute', left: 0, top: 0, width: VW, height: VH, transformOrigin: '0 0',
          transform: `translate(${tx}px, ${ty}px) scale(${z})`, filter: vidFilter, maxWidth: 'none'}}
      />
      {F.tint && s.grade > 0.5 ? <div style={{position: 'absolute', inset: 0, background: F.tint, mixBlendMode: 'soft-light', opacity: (F.tintOpacity ?? 0.35) * s.grade}} /> : null}
      {(s.vignette || (F.viewVignette && s.camera)) ? <div style={{position: 'absolute', inset: 0,
        background: `radial-gradient(ellipse at 50% 40%, rgba(0,0,0,0) 55%, rgba(0,0,0,${F.viewVignette ?? 0.22}) 100%)`}} /> : null}
      {halftone > 0 ? <div style={{position: 'absolute', inset: 0, mixBlendMode: 'multiply', opacity: halftone,
        backgroundImage: 'radial-gradient(rgba(20,18,14,0.9) 1.1px, transparent 1.6px)', backgroundSize: '6px 6px'}} /> : null}
      {s.tag && tagP > 0 ? (
        <div style={{position: 'absolute', left: 22, top: 22, transform: `scale(${tagP})`, transformOrigin: '0 0',
          display: 'flex', alignItems: 'center', gap: 10, padding: '12px 20px', borderRadius: 999,
          background: s.tag.tone === 'accent' ? C.accent : s.tag.tone === 'light' ? C.paper : C.ink,
          color: s.tag.tone === 'accent' || s.tag.tone === 'light' ? C.ink : C.paper,
          fontFamily: E.style.fonts.ui, fontWeight: 700, fontSize: 30, letterSpacing: '0.04em', boxShadow: '0 8px 24px rgba(0,0,0,0.2)'}}>
          {s.tag.icon ? <Icon name={s.tag.icon} size={28} color={s.tag.tone === 'accent' || s.tag.tone === 'light' ? C.ink : C.accent} /> : null}
          {s.tag.text}
        </div>
      ) : null}
    </div>
  );
  if (!tape || !s.rect && s.camera) return box;
  // two strips of translucent tape holding the "photo" (outside the clipped box, rotating with it)
  const tw = Math.min(170, s.w * 0.28);
  return (
    <>
      {box}
      <div style={{position: 'absolute', left: s.x, top: s.y, width: s.w, height: s.h, transform: `rotate(${s.rot || 0}deg) scale(${s.scale || 1})`, opacity: s.opacity, pointerEvents: 'none'}}>
        {[[-tw * 0.3, -18, -28], [s.w - tw * 0.7, -18, 24]].map(([l, tp, r], i) => (
          <div key={i} style={{position: 'absolute', left: l, top: tp, width: tw, height: 46, transform: `rotate(${r}deg)`,
            background: 'rgba(238,230,205,0.78)', boxShadow: '0 2px 6px rgba(0,0,0,0.12)', borderLeft: '2px dashed rgba(0,0,0,0.06)', borderRight: '2px dashed rgba(0,0,0,0.06)'}} />
        ))}
      </div>
    </>
  );
}

// ------------------------------------------------------------------------------------------------ PersonLayer
// The speaker cut out of the frame with the local person matte (render-video.py computes masks for the planned
// ranges). Drawn above 'behind' overlays (text behind the speaker) and above a Cutout's replaced background.
export const matteSrc = (E, t) => {
  const M = E.matte;
  if (!M || !M.dir || !M.ranges) return null;
  const r = M.ranges.find(([a, b]) => t >= a - 1e-6 && t <= b + 1e-6);
  if (!r) return null;
  const f = M.fps || 30;
  const k = Math.min(Math.floor(r[1] * f + 1e-6), Math.max(Math.ceil(r[0] * f - 1e-6), Math.round(t * f)));
  return staticFile(`${M.dir}/m_${String(k).padStart(5, '0')}.png`);
};

export const activeCutout = (E, t) => (E.overlays || []).find((o) => o.type === 'Cutout' && t >= o.start && t <= (o.end ?? 1e9));

export const PersonLayer = ({E, t}) => {
  if (t > E.footage.duration - 1 / E.fps) return null;
  const src = matteSrc(E, t);
  if (!src) return null;
  const cut = activeCutout(E, t);
  const behind = (E.overlays || []).some((o) => o.layer === 'behind' && t >= o.start - 0.05 && t <= (o.end ?? 1e9) + 0.05);
  if (!cut && !behind) return null;
  const W = E.width, H = E.height;
  const {views, wipe} = layoutAt(E, t, W, H);
  if (wipe) return null;
  const fxs = cutFxAt(E, t);
  let pop = 1, outline = null;
  if (cut) {
    const k = ease.snappy(prog(t, cut.start, cut.start + 0.35)) * (1 - ease.exitFast(prog(t, (cut.end ?? 1e9) - 0.22, cut.end ?? 1e9)));
    pop = 1 + ((cut.pop ?? 1.03) - 1) * k;
    outline = {width: Math.round((cut.outline ?? 10) * k), color: cut.outlineColor || '#FFFFFF', shadow: cut.shadow};
  }
  const face = sample(E.faces, t) || [0.5, 0.42, 0.2];
  return (
    <AbsoluteFill style={{transform: `scale(${pop})`, transformOrigin: `${(face[0] * 100).toFixed(1)}% ${(face[1] * 100).toFixed(1)}%`}}>
      {views.filter((v) => v.state.camera && !v.state.rect).map((v, i) => <ViewBox key={i} E={E} s={v.state} t={t} fxs={fxs} mask={src} outline={outline} />)}
    </AbsoluteFill>
  );
};

// Where the speaker's face sits on the canvas at t (full-frame camera view only): {x, y, h} in canvas px,
// following the same framing maths as ViewBox (camera keys, punch-ins, jump zoom, cut moves, drift, edge clamp).
// Face-tracked doodles (halo, sparkles) use it. Returns null when no full-frame camera view is on screen.
export function faceOnCanvas(E, t) {
  const W = E.width, H = E.height;
  // after the footage ends (end hold) the last frame is frozen: keep tracking the face where it stopped
  const tf = Math.min(t, E.footage.duration - 1 / E.fps);
  const {views, wipe} = layoutAt(E, t, W, H);
  if (wipe) return null;
  const v = views.find((x) => x.state.camera && !x.state.rect && (x.state.opacity ?? 1) > 0.5);
  if (!v) return null;
  const s = v.state;
  const vw = E.footage.width, vh = E.footage.height;
  const c = Math.max(s.w / vw, s.h / vh);
  const VW = vw * c, VH = vh * c;
  const fxs = cutFxAt(E, t);
  const z = Math.max(1, s.z) * (fxs?.zMul ?? 1);
  const face = sample(E.faces, tf) || [0.5, 0.4, 0.2];
  let tx, ty;
  if (s.fx == null) { tx = (s.w - z * VW) / 2; ty = (s.h - z * VH) / 2; }
  else { tx = s.fx * s.w - z * face[0] * VW; ty = s.fy * s.h - z * face[1] * VH; }
  const drift = E.style?.camera?.drift ?? 0;
  if (drift) { tx += fbm(t * 0.32, 1) * drift; ty += fbm(t * 0.27, 5) * drift * 0.7; }
  if (fxs) { tx += fxs.dx; ty += fxs.dy; }
  tx = clamp(tx, s.w - z * VW, 0); ty = clamp(ty, s.h - z * VH, 0);
  return {x: s.x + tx + z * face[0] * VW, y: s.y + ty + z * face[1] * VH, h: z * (face[2] || 0.2) * VH};
}

export const Footage = ({E, t}) => {
  const last = E.footage.duration - 1 / E.fps;
  if (t > last) {
    // end-card hold: the footage has ended -> hold its last frame (layouts keep animating on the real clock)
    return <Freeze frame={Math.max(0, Math.round(last * E.fps) - 1)}><FootageAt E={E} t={t} /></Freeze>;
  }
  return <FootageAt E={E} t={t} />;
};

const FootageAt = ({E, t}) => {
  const W = E.width, H = E.height;
  const {views, next, wipe, divider, nextDivider} = layoutAt(E, t, W, H);
  const fxs = cutFxAt(E, t);
  const extras = (
    <>
      {fxs?.flash ? <AbsoluteFill style={{background: '#fff', opacity: fxs.flash}} /> : null}
      {fxs?.leak ? <LeakSweep E={E} t={t} at={fxs.leak.t - 0.3} dur={0.8} dir={fxs.leak.dir || 1}
        color={E.style.finish?.leak?.[0]} color2={E.style.finish?.leak?.[1]} /> : null}
    </>
  );
  if (!wipe) {
    return <AbsoluteFill>{views.map((v, i) => <ViewBox key={i} E={E} s={v.state} t={t} fxs={fxs} />)}<Divider E={E} d={divider} />{extras}</AbsoluteFill>;
  }
  // Wipe: new state revealed behind a moving skewed edge, with an accent band riding the edge.
  const band = E.style.wipe?.band ?? 150, sk = E.style.wipe?.skew ?? 160;
  const X = lerp(-sk - band, W + sk + band, wipe.p);
  const C = E.style.colors;
  if (E.style.wipe?.kind === 'tear') {
    // tear wipe: the new shot is revealed behind a strip of torn newsprint
    const edge = tearEdge(X, H, sk, E.style.wipe?.amp ?? 22);
    // the old shot is the sheet being torn away: its torn edge shows a band of bare paper on the old side
    const front = tearEdge(X + band * 0.5, H, sk, (E.style.wipe?.amp ?? 22) * 0.7, 38, 11);
    const reveal = `polygon(0px 0px, ${edge.map(([x, y]) => `${x.toFixed(1)}px ${y}px`).join(', ')}, 0px ${H}px)`;
    const strip = [...edge.map(([x, y]) => `${(x - 2).toFixed(1)},${y}`), ...front.slice().reverse().map(([x, y]) => `${x.toFixed(1)},${y}`)].join(' ');
    return (
      <AbsoluteFill>
        {views.map((v, i) => <ViewBox key={'p' + i} E={E} s={v.state} t={t} />)}
        <Divider E={E} d={divider} />
        <AbsoluteFill style={{clipPath: reveal}}>
          {next.map((v, i) => <ViewBox key={'n' + i} E={E} s={v.state} t={t} />)}
          <Divider E={E} d={nextDivider} />
        </AbsoluteFill>
        <svg width={W} height={H} style={{position: 'absolute', inset: 0, filter: 'drop-shadow(-10px 0 14px rgba(20,18,14,0.4))'}}>
          <defs>
            <pattern id="ht" width="6" height="6" patternUnits="userSpaceOnUse"><circle cx="3" cy="3" r="0.9" fill="rgba(28,27,24,0.22)" /></pattern>
          </defs>
          <polygon points={strip} fill={C.paper} />
          <polygon points={strip} fill="url(#ht)" />
        </svg>
      </AbsoluteFill>
    );
  }
  const poly = `polygon(0px 0px, ${X + sk}px 0px, ${X - sk}px ${H}px, 0px ${H}px)`; // left -> right reveal
  return (
    <AbsoluteFill>
      {views.map((v, i) => <ViewBox key={'p' + i} E={E} s={v.state} t={t} />)}
      <Divider E={E} d={divider} />
      <AbsoluteFill style={{clipPath: poly}}>
        {next.map((v, i) => <ViewBox key={'n' + i} E={E} s={v.state} t={t} />)}
        <Divider E={E} d={nextDivider} />
      </AbsoluteFill>
      <svg width={W} height={H} style={{position: 'absolute', inset: 0}}>
        <defs>
          <linearGradient id="wipeg" x1="0" x2="1" y1="0" y2="0">
            <stop offset="0" stopColor={C.accent} stopOpacity="0" />
            <stop offset="0.55" stopColor={C.accent} stopOpacity="0.95" />
            <stop offset="1" stopColor={C.accent} stopOpacity="1" />
          </linearGradient>
        </defs>
        <polygon points={`${X + sk - band},0 ${X + sk},0 ${X - sk},${H} ${X - sk - band},${H}`} fill="url(#wipeg)" />
        <polygon points={`${X + sk},0 ${X + sk + 10},0 ${X - sk + 10},${H} ${X - sk},${H}`} fill={C.ink} />
      </svg>
    </AbsoluteFill>
  );
};
