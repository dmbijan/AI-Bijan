#!/usr/bin/env node
// Original, dependency-free sound for the named styles (no third-party samples, nothing downloaded).
//
//   npm run style-sfx -- arsip|blueprint|sketsa|hikayat|geometrik|terminal
//       -> public/audio/sfx/<style>/*.wav  (one-shots that match the look; reference them in audio-plan sfxCues)
//   npm run style-score -- <style> src/audio-plan.json public/audio/music/<name>.wav
//       -> a music bed in that style, following the plan's sections (mood + energy) and optional styleHits.
//
// audio-plan.json additions understood here (all optional):
//   "styleHits": [{"time": 12.4, "type": "hit"}, {"time": 30.1, "type": "riser", "duration": 1.6}]
//   A section with mood "hopeful" switches the harmony to major (use it for the resolution / payoff).
// Moods: playful curious energetic hopeful (bright) · reflective warm neutral (calm) · suspense tense dramatic (dark) · silence.
// Like `npm run score`, this is an honest procedural fallback: audition it and say so in the handoff.
import {existsSync, mkdirSync, readFileSync, writeFileSync} from 'node:fs';
import path from 'node:path';

const SR = 48000;
const STYLES = ['arsip', 'blueprint', 'sketsa', 'hikayat', 'geometrik', 'terminal'];
let seed = 7;
const rnd = () => { seed = (seed * 1664525 + 1013904223) >>> 0; return seed / 4294967296; };
const noise = () => rnd() * 2 - 1;
const mtof = (m) => 440 * 2 ** ((m - 69) / 12);
const buf = (sec) => ({L: new Float32Array(Math.ceil(sec * SR)), R: new Float32Array(Math.ceil(sec * SR))});
const put = (b, i, v, pan = 0) => { if (i < 0 || i >= b.L.length) return; b.L[i] += v * Math.min(1, 1 - pan); b.R[i] += v * Math.min(1, 1 + pan); };

function writeWav(file, b, peak = 0.85) {
  const n = b.L.length; let m = 1e-9;
  for (let i = 0; i < n; i++) m = Math.max(m, Math.abs(b.L[i]), Math.abs(b.R[i]));
  const g = peak / m; const out = Buffer.alloc(44 + n * 4);
  out.write('RIFF', 0); out.writeUInt32LE(36 + n * 4, 4); out.write('WAVE', 8); out.write('fmt ', 12);
  out.writeUInt32LE(16, 16); out.writeUInt16LE(1, 20); out.writeUInt16LE(2, 22); out.writeUInt32LE(SR, 24);
  out.writeUInt32LE(SR * 4, 28); out.writeUInt16LE(4, 32); out.writeUInt16LE(16, 34); out.write('data', 36); out.writeUInt32LE(n * 4, 40);
  for (let i = 0; i < n; i++) {
    out.writeInt16LE(Math.max(-32767, Math.min(32767, Math.round(b.L[i] * g * 32767))), 44 + i * 4);
    out.writeInt16LE(Math.max(-32767, Math.min(32767, Math.round(b.R[i] * g * 32767))), 46 + i * 4);
  }
  mkdirSync(path.dirname(file), {recursive: true});
  writeFileSync(file, out);
}

// ---------- effects ----------
function reverb(b, mixAmt = 0.25, size = 1) {
  for (const ch of ['L', 'R']) {
    const x = b[ch]; const y = new Float32Array(x.length);
    [1557, 1617, 1491, 1422].map((d) => Math.round(d * size * SR / 44100)).forEach((d, k) => {
      const dd = d + (ch === 'R' ? 23 * (k + 1) : 0); const line = new Float32Array(dd); let idx = 0, lp = 0;
      for (let i = 0; i < x.length; i++) { const o = line[idx]; lp = o * 0.75 + lp * 0.25; line[idx] = x[i] + lp * 0.8; idx = (idx + 1) % dd; y[i] += o * 0.25; }
    });
    [225, 556].forEach((d) => { const line = new Float32Array(d); let idx = 0; for (let i = 0; i < y.length; i++) { const bv = line[idx]; const o = -y[i] + bv; line[idx] = y[i] + bv * 0.5; idx = (idx + 1) % d; y[i] = o; } });
    for (let i = 0; i < x.length; i++) x[i] = x[i] * (1 - mixAmt * 0.4) + y[i] * mixAmt;
  }
}
function echo(b, sec, fb = 0.35) {
  const d = Math.round(sec * SR);
  for (const ch of ['L', 'R']) { const x = b[ch]; const off = ch === 'R' ? Math.round(d * 1.5) : d; for (let i = off; i < x.length; i++) x[i] += x[i - off] * fb * 0.7; }
}
const lowpass = (x, fc) => { const a = 1 - Math.exp((-2 * Math.PI * fc) / SR); let y = 0; for (let i = 0; i < x.length; i++) { y += a * (x[i] - y); x[i] = y; } };
const mix = (dst, src, g = 1) => { for (let i = 0; i < dst.L.length; i++) { dst.L[i] += src.L[i] * g; dst.R[i] += src.R[i] * g; } };

// ---------- instruments ----------
function pad(b, t0, dur, notes, g, bright = 1200) {
  const n = Math.round(dur * SR), s0 = Math.round(t0 * SR);
  for (const m of notes) {
    const f = mtof(m); const tmp = new Float32Array(n + Math.round(0.9 * SR));
    for (const det of [-0.006, 0, 0.007]) { let ph = rnd(); for (let i = 0; i < tmp.length; i++) { ph += (f * (1 + det)) / SR; tmp[i] += (2 * (ph % 1) - 1) / 3; } }
    lowpass(tmp, bright); lowpass(tmp, bright * 1.5);
    for (let i = 0; i < tmp.length; i++) { const tt = i / SR; put(b, s0 + i, tmp[i] * Math.min(1, tt / 0.7) * (i < n ? 1 : Math.exp(-(i - n) / SR / 0.3)) * g / notes.length, m % 2 ? 0.2 : -0.2); }
  }
}
function ep(b, t0, dur, notes, g) {
  const s0 = Math.round(t0 * SR); const n = Math.round((dur + 1.2) * SR);
  for (const m of notes) {
    const f = mtof(m);
    for (let i = 0; i < n; i++) {
      const tt = i / SR; const env = Math.min(1, tt / 0.012) * Math.exp(-tt / 1.3) * (tt > dur ? Math.exp(-(tt - dur) / 0.25) : 1);
      const v = Math.sin(2 * Math.PI * f * tt) + 0.28 * Math.sin(4 * Math.PI * f * tt) * Math.exp(-tt / 0.4) + 0.08 * Math.sin(6 * Math.PI * f * tt);
      put(b, s0 + i, v * env * (1 + 0.12 * Math.sin(2 * Math.PI * 4.6 * tt)) * g / notes.length);
    }
  }
}
function pluck(b, t0, m, g, decay = 0.996, pan = 0, dur = 1.6, bright = 0.5) {
  const N = Math.max(2, Math.round(SR / mtof(m))); const line = new Float32Array(N);
  for (let i = 0; i < N; i++) line[i] = noise();
  let lp = 0; for (let i = 0; i < N; i++) { lp += bright * (line[i] - lp); line[i] = lp; }
  const s0 = Math.round(t0 * SR); const n = Math.round(dur * SR); let idx = 0;
  for (let i = 0; i < n; i++) { const nx = (idx + 1) % N; const v = line[idx]; line[idx] = 0.5 * (line[idx] + line[nx]) * decay; idx = nx; put(b, s0 + i, v * g * Math.min(1, (n - i) / 2000), pan); }
}
function bass(b, t0, m, dur, g) {
  const f = mtof(m); const s0 = Math.round(t0 * SR);
  for (let i = 0; i < (dur + 0.15) * SR; i++) { const tt = i / SR; put(b, s0 + i, (Math.sin(2 * Math.PI * f * tt) + 0.25 * Math.sin(4 * Math.PI * f * tt)) * Math.min(1, tt / 0.01) * Math.exp(-tt / (dur * 0.9)) * (tt > dur ? Math.exp(-(tt - dur) / 0.05) : 1) * g); }
}
function kick(b, t0, g) { const s0 = Math.round(t0 * SR); let ph = 0; for (let i = 0; i < 0.35 * SR; i++) { const tt = i / SR; ph += (45 + 85 * Math.exp(-tt / 0.03)) / SR; put(b, s0 + i, Math.sin(2 * Math.PI * ph) * Math.exp(-tt / 0.12) * g); } }
function hat(b, t0, g, len = 0.03) { const s0 = Math.round(t0 * SR); let prev = 0; for (let i = 0; i < len * 4 * SR; i++) { const x = noise(); put(b, s0 + i, (x - prev) * Math.exp(-i / SR / len) * g, 0.25); prev = x; } }
function brush(b, t0, g) { const s0 = Math.round(t0 * SR); let lp = 0, prev = 0; for (let i = 0; i < 0.25 * SR; i++) { lp += 0.25 * (noise() - lp); put(b, s0 + i, (lp - prev) * Math.exp(-i / SR / 0.08) * g * 2, -0.15); prev = lp; } }
function tick(b, t0, g, f = 2400, pan = 0) { const s0 = Math.round(t0 * SR); for (let i = 0; i < 0.03 * SR; i++) { const tt = i / SR; put(b, s0 + i, (Math.sin(2 * Math.PI * f * tt) * 0.6 + noise() * 0.4) * Math.exp(-tt / 0.006) * g, pan); } }
function crackle(b, from, to, g) {
  for (let t = from; t < to; t += 0.02) if (rnd() < 0.12) { const s0 = Math.round(t * SR) + Math.floor(rnd() * 800); const a = (0.3 + rnd()) * g; for (let i = 0; i < 40; i++) put(b, s0 + i, noise() * a * Math.exp(-i / 8), rnd() - 0.5); }
  let lp = 0; for (let i = Math.round(from * SR); i < Math.min(b.L.length, to * SR); i++) { lp += 0.02 * (noise() - lp); put(b, i, lp * g * 0.6); }
}
function riser(b, t0, dur, g) { const s0 = Math.round(t0 * SR); let lp = 0; for (let i = 0; i < dur * SR; i++) { const k = i / (dur * SR); lp += (0.01 + 0.3 * k) * (noise() - lp); put(b, s0 + i, lp * k * k * g); } }
function hit(b, t0, g, root = 38) { pad(b, t0, 1.6, [root, root + 12, root + 19], g * 1.5, 700); kick(b, t0, g * 1.4); const s0 = Math.round(t0 * SR); for (let i = 0; i < 0.6 * SR; i++) put(b, s0 + i, noise() * Math.exp(-i / SR / 0.12) * g * 0.35); }

// ---------- styles: one instrument set each, driven by the plan's moods ----------
const GROUP = {playful: 'bright', curious: 'bright', energetic: 'bright', hopeful: 'major', reflective: 'calm', warm: 'calm', neutral: 'calm', suspense: 'dark', tense: 'dark', dramatic: 'dark', silence: 'silence'};

function scoreArsip(plan, b, wet, at) {
  const beat = 60 / 84, bar = beat * 4;
  const minor = [[50, 53, 57], [46, 50, 53], [43, 46, 50], [45, 49, 52]];
  const major = [[50, 54, 57], [55, 59, 62], [47, 50, 54], [45, 49, 52]];
  crackle(b, 0, plan.durationSeconds, 0.05);
  for (let t = 0, k = 0; t < plan.durationSeconds; t += bar, k++) {
    const s = at(t + 0.01); const gr = GROUP[s.mood]; if (gr === 'silence') continue;
    const e = 0.4 + 0.6 * s.energy; const chord = (gr === 'major' ? major : minor)[k % 4];
    pad(b, t, bar, chord.map((m) => m + 12), 0.16 * e, gr === 'dark' ? 750 : 1100); pad(wet, t, bar, chord.map((m) => m + 12), 0.08 * e, 900);
    const eighths = gr === 'dark' && s.energy > 0.55;
    for (let q = 0; q < (eighths ? 8 : 4); q++) {
      const tt = t + q * (eighths ? beat / 2 : beat); const sq = at(tt); const g2 = GROUP[sq.mood]; if (g2 === 'silence') continue;
      if (g2 !== 'calm') pluck(b, tt, chord[0] - 12 + [0, 7, 12, 7][q % 4], 0.42 * (0.4 + 0.6 * sq.energy), 0.993, -0.1, 0.9, 0.35);
      tick(b, tt, 0.06 * (g2 === 'calm' ? 0.5 : 1), q % 2 ? 2600 : 2100, 0.3);
    }
    if (k % 2 === 0 && gr !== 'major') [[0, 74], [1.5, 77], [2.5, 76], [3, 69]].forEach(([o, m]) => { pluck(b, t + o * beat, m, 0.2 * e, 0.997, 0.25, 2, 0.7); pluck(wet, t + o * beat, m, 0.16 * e, 0.997, 0.25, 2, 0.7); });
    if (gr === 'major' && k % 2 === 0) [0, 0.5, 1, 1.5].forEach((o, i) => pluck(b, t + o, [74, 78, 81, 86][i], 0.22 * e, 0.998, 0.2, 2.5, 0.7));
  }
}
function scoreBlueprint(plan, b, wet, at) {
  const beat = 60 / 110, bar = beat * 4, s16 = beat / 4;
  const minor = [[57, 60, 64], [53, 57, 60], [48, 52, 55], [55, 59, 62]];
  const major = [[48, 52, 55], [53, 57, 60], [55, 59, 62], [48, 52, 55]];
  const tense = [[57, 60, 64], [58, 62, 65]];
  const pattern = [0, 2, 1, 2, 0, 2, 1, 3];
  for (let t = 0, k = 0; t < plan.durationSeconds; t += bar, k++) {
    for (let n = 0; n < 16; n++) {
      const tt = t + n * s16; const s = at(tt); const gr = GROUP[s.mood]; if (gr === 'silence') continue;
      const e = 0.4 + 0.6 * s.energy;
      const chord = gr === 'major' ? major[k % 4] : gr === 'dark' ? tense[k % 2] : minor[k % 4];
      const notes = [chord[0] + 12, chord[1] + 12, chord[2] + 12, chord[0] + 24];
      const arp = gr === 'dark' ? 0.5 + 0.5 * ((tt % (bar * 2)) / (bar * 2)) : gr === 'calm' ? (n % 2 ? 0 : 0.6) : 1;
      if (arp > 0) pluck(wet, tt, notes[pattern[n % 8]], 0.16 * e * arp, 0.99, n % 2 ? 0.3 : -0.3, 0.4, 0.8);
      if (n % 4 === 0) {
        if ((gr === 'bright' || gr === 'major') && n % 8 === 0) kick(b, tt, 0.55 * e);
        bass(b, tt, chord[0] - 12, gr === 'dark' ? beat * 0.4 : beat * 0.8, 0.3 * e);
      }
      if (n % 4 === 2 && gr !== 'dark' && gr !== 'calm') hat(b, tt, 0.07 * e);
      if (gr === 'dark' && n % 2 === 0) tick(b, tt, 0.045, 3000, -0.2);
      if (n === 0) pad(b, tt, bar, chord.map((m) => m + 12), 0.1 * e, gr === 'dark' ? 1600 : 900);
    }
  }
  echo(wet, beat * 0.75, 0.35);
}
function scoreSketsa(plan, b, wet, at) {
  const beat = 60 / 80, bar = beat * 4;
  const prog = [[53, 57, 60, 64], [52, 55, 59, 62], [50, 53, 57, 60], [48, 52, 55, 59]];
  const major = [[53, 57, 60, 64], [48, 52, 55, 59], [50, 53, 57, 60], [46, 50, 53, 57]];
  const melody = [72, 74, 76, 79, 76, 74, 72, 69];
  crackle(b, 0, plan.durationSeconds, 0.035);
  for (let t = 0, k = 0; t < plan.durationSeconds; t += bar, k++) {
    for (let q = 0; q < 8; q++) {
      const tt = t + q * beat / 2; const s = at(tt); const gr = GROUP[s.mood]; if (gr === 'silence') continue;
      const e = 0.4 + 0.6 * s.energy; const chord = (gr === 'major' ? major : prog)[k % 4];
      if (q === 0) { ep(b, tt, beat * 1.5, chord.map((m) => m + 12), 0.28 * e); ep(wet, tt, beat * 1.5, chord.map((m) => m + 12), 0.15 * e); }
      if (q === 5 && gr !== 'dark') ep(b, tt, beat, chord.slice(1).map((m) => m + 12), 0.16 * e);
      if ((q === 0 || q === 5) && gr !== 'dark') bass(b, tt, chord[0] - 12, beat * 1.2, 0.28 * e);
      if (gr === 'bright' || gr === 'major') {
        if (q === 0 || q === 5) kick(b, tt + (q === 5 ? 0.04 : 0), 0.35 * e);
        if (q === 2 || q === 6) brush(b, tt + 0.02, 0.35 * e);
        hat(b, tt + (q % 2 ? 0.05 : 0), 0.035 * e, 0.02);
      }
      if ((gr === 'bright' || gr === 'major' || (gr === 'calm' && q % 4 === 0)) && (q % 2 === 0 || k % 2) && rnd() < 0.7) {
        const m = melody[(k * 3 + q) % melody.length];
        pluck(b, tt, m, 0.2 * e, 0.995, 0.3, 1.4, 0.6); pluck(wet, tt, m, 0.14 * e, 0.995, 0.3, 1.4, 0.6);
      }
      if ((gr === 'calm' || gr === 'dark') && q === 0) pad(b, tt, bar, chord.map((m) => m + 12), 0.08 * e, 800);
    }
  }
}


// ---------- v1.7 instruments: gamelan, wood, synth ----------
/** Metallophone (saron/gender/bonang): inharmonic partials, bright attack, ringing decay. */
function metal(b, t0, f, g, decay = 1.6, pan = 0, partials = [[1, 1], [2.76, 0.42], [5.4, 0.18], [8.93, 0.06]]) {
  const s0 = Math.round(t0 * SR); const n = Math.round(decay * 3 * SR);
  for (let i = 0; i < n; i++) {
    const tt = i / SR; let v = 0;
    partials.forEach(([m, amp], j) => { v += Math.sin(2 * Math.PI * f * m * tt + j) * amp * Math.exp(-tt / (decay / (1 + j * 1.4))); });
    put(b, s0 + i, v * g * Math.min(1, tt / 0.003) * (1 + 0.04 * Math.sin(2 * Math.PI * 5.5 * tt)), pan);
  }
}
/** Gong: low fundamental with slow beating partials and a long tail. */
function gong(b, t0, f, g, decay = 4) {
  const s0 = Math.round(t0 * SR); const n = Math.round(decay * 1.6 * SR);
  for (let i = 0; i < n; i++) {
    const tt = i / SR; const sw = 1 + 0.015 * Math.exp(-tt * 2);
    const v = Math.sin(2 * Math.PI * f * sw * tt) + 0.6 * Math.sin(2 * Math.PI * f * 1.003 * tt) * Math.sin(2 * Math.PI * 1.2 * tt) + 0.3 * Math.sin(2 * Math.PI * f * 2.02 * tt) * Math.exp(-tt / 1.2) + 0.12 * Math.sin(2 * Math.PI * f * 3.4 * tt) * Math.exp(-tt / 0.6);
    put(b, s0 + i, v * g * Math.min(1, tt / 0.02) * Math.exp(-tt / decay));
  }
}
/** Kendang: membrane thump with a pitch drop plus a slap. */
function kendang(b, t0, g, high = false) {
  const s0 = Math.round(t0 * SR); let ph = 0;
  for (let i = 0; i < 0.35 * SR; i++) { const tt = i / SR; ph += ((high ? 210 : 95) + (high ? 160 : 70) * Math.exp(-tt / 0.02)) / SR; put(b, s0 + i, (Math.sin(2 * Math.PI * ph) * Math.exp(-tt / (high ? 0.07 : 0.16)) + noise() * 0.25 * Math.exp(-tt / 0.01)) * g, high ? 0.2 : -0.1); }
}
/** Suling: breathy bamboo flute line (sine + breath noise + vibrato). */
function suling(b, t0, f, dur, g) {
  const s0 = Math.round(t0 * SR); let ph = 0, lp = 0;
  for (let i = 0; i < (dur + 0.3) * SR; i++) {
    const tt = i / SR; const env = Math.min(1, tt / 0.12) * (tt > dur ? Math.exp(-(tt - dur) / 0.12) : 1);
    ph += f * (1 + 0.006 * Math.sin(2 * Math.PI * 5.2 * tt) * Math.min(1, tt / 0.4)) / SR; lp += 0.08 * (noise() - lp);
    put(b, s0 + i, (Math.sin(2 * Math.PI * ph) + 0.15 * Math.sin(4 * Math.PI * ph) + lp * 0.5) * env * g, 0.15);
  }
}
/** Wooden mallet (marimba-like). */
function wood(b, t0, m, g, pan = 0) { const f = mtof(m); const s0 = Math.round(t0 * SR); for (let i = 0; i < 0.6 * SR; i++) { const tt = i / SR; put(b, s0 + i, (Math.sin(2 * Math.PI * f * tt) + 0.35 * Math.sin(2 * Math.PI * f * 4 * tt) * Math.exp(-tt / 0.02)) * Math.exp(-tt / 0.18) * g, pan); } }
function clap(b, t0, g) { const s0 = Math.round(t0 * SR); for (let k = 0; k < 3; k++) { let lp = 0, prev = 0; const o = Math.round((k * 0.011) * SR); for (let i = 0; i < 0.12 * SR; i++) { lp += 0.5 * (noise() - lp); put(b, s0 + o + i, (lp - prev) * Math.exp(-i / SR / (k === 2 ? 0.05 : 0.008)) * g, 0.1); prev = lp; } } }
/** Filtered saw voice for synth bass/lead. */
function synth(b, t0, m, dur, g, cutoff = 900, pan = 0, square = false) {
  const f = mtof(m); const s0 = Math.round(t0 * SR); const n = Math.round((dur + 0.08) * SR); let ph = 0, lp = 0, lp2 = 0;
  const a = 1 - Math.exp((-2 * Math.PI * cutoff) / SR);
  for (let i = 0; i < n; i++) {
    const tt = i / SR; ph += f / SR; const raw = square ? (ph % 1 < 0.5 ? 1 : -1) : 2 * (ph % 1) - 1;
    lp += a * (raw - lp); lp2 += a * (lp - lp2);
    put(b, s0 + i, lp2 * Math.min(1, tt / 0.005) * (tt > dur ? Math.exp(-(tt - dur) / 0.03) : Math.exp(-tt / (dur * 1.5))) * g, pan);
  }
}
const SLENDRO = (root, k) => root * 2 ** (Math.floor(k / 5) + (k % 5) * 0.24);

function scoreHikayat(plan, b, wet, at) {
  const beat = 60 / 70, gatra = beat * 4;
  const balungan = [2, 3, 2, 1, 2, 1, 5, 6, 5, 3, 2, 1, 6, 5, 3, 2];
  for (let t = 0, k = 0; t < plan.durationSeconds; t += beat, k++) {
    const s = at(t + 0.01); const gr = GROUP[s.mood]; if (gr === 'silence') continue;
    const e = 0.4 + 0.6 * s.energy;
    const note = balungan[k % balungan.length];
    if (k % 8 === 7 || k === 0) gong(b, t, gr === 'dark' ? 55 : 65, 0.55 * e, 4.5);
    if (k % 4 === 1 && gr !== 'dark') metal(b, t, SLENDRO(196, 5 + (note % 5)), 0.16 * e, 1.2, -0.2, [[1, 1], [1.5, 0.3], [3.9, 0.15]]);
    if (gr !== 'dark') { metal(b, t, SLENDRO(392, note), 0.22 * e, 1.0, 0.1); metal(wet, t, SLENDRO(392, note), 0.12 * e, 1.0, 0.1); }
    if (gr === 'dark') { if (k % 2 === 0) metal(b, t, SLENDRO(130, 1), 0.2 * e, 2.4, 0, [[1, 1], [2.1, 0.3]]); kendang(b, t + beat / 2, 0.4 * e, true); if (k % 4 === 3) kendang(b, t, 0.55 * e); }
    if (gr === 'bright' || gr === 'major') {
      [0, 0.5].forEach((o, j) => metal(b, t + o * beat, SLENDRO(784, (note + j * 2) % 10), 0.1 * e, 0.4, 0.3));
      if (k % 2 === 0) kendang(b, t, 0.35 * e); else kendang(b, t + beat / 2, 0.25 * e, true);
    }
    if ((gr === 'calm' || gr === 'major') && k % 8 === 0) suling(b, t + beat, SLENDRO(587, note % 5), beat * 3, 0.14 * e);
  }
  for (let t = 0; t < plan.durationSeconds; t += 0.05) { const s = at(t); if (GROUP[s.mood] === 'dark') { const i0 = Math.round(t * SR); for (let i = 0; i < 0.05 * SR; i++) put(b, i0 + i, Math.sin(2 * Math.PI * 49 * (t + i / SR)) * 0.06 * s.energy); } }
}
function scoreGeometrik(plan, b, wet, at) {
  const beat = 60 / 118, s16 = beat / 4;
  const prog = [[60, 64, 67], [57, 60, 64], [65, 69, 72], [67, 71, 74]];
  for (let t = 0, k = 0; t < plan.durationSeconds; t += s16, k++) {
    const s = at(t + 0.001); const gr = GROUP[s.mood]; if (gr === 'silence') continue;
    const e = 0.4 + 0.6 * s.energy; const bar = Math.floor(k / 16); const chord = prog[bar % 4]; const n = k % 16;
    const drums = gr === 'bright' || gr === 'major';
    if (drums && n % 4 === 0) kick(b, t, 0.55 * e);
    if (drums && (n === 4 || n === 12)) clap(b, t, 0.35 * e);
    if (gr !== 'calm' && n % 2 === 1) hat(b, t, 0.05 * e, 0.015);
    if (gr === 'dark' && n % 4 === 0) { synth(b, t, chord[0] - 24, beat * 0.9, 0.35 * e, 300); tick(b, t, 0.05, 1800); }
    if (gr !== 'dark' && [0, 3, 6, 8, 11, 14].includes(n)) wood(wet, t, chord[[0, 1, 2, 1, 2, 0][[0, 3, 6, 8, 11, 14].indexOf(n)]] + 12, 0.2 * e, n % 2 ? 0.3 : -0.3);
    if (drums && n % 8 === 0) synth(b, t, chord[0] - 12, beat * 0.45, 0.32 * e, 700, 0, true);
  }
}
function scoreTerminal(plan, b, wet, at) {
  const beat = 60 / 96, s16 = beat / 4;
  const prog = [[57, 60, 64], [53, 57, 60], [55, 59, 62], [52, 56, 59]];
  for (let t = 0, k = 0; t < plan.durationSeconds; t += s16, k++) {
    const s = at(t + 0.001); const gr = GROUP[s.mood]; if (gr === 'silence') continue;
    const e = 0.4 + 0.6 * s.energy; const chord = prog[Math.floor(k / 16) % 4]; const n = k % 16;
    if (n % 2 === 0) synth(b, t, chord[0] - 24, s16 * 1.6, 0.3 * e, gr === 'dark' ? 380 : 650);
    if (gr === 'bright' || gr === 'major') {
      synth(wet, t, [chord[0], chord[1], chord[2], chord[0] + 12][n % 4] + 12, s16 * 0.8, 0.09 * e, 2400, n % 2 ? 0.35 : -0.35, true);
      if (n % 4 === 0) kick(b, t, 0.5 * e); if (n === 4 || n === 12) clap(b, t, 0.28 * e); if (n % 2 === 1) hat(b, t, 0.05 * e, 0.012);
    }
    if (gr === 'dark' && n % 4 === 0) tick(b, t, 0.06, 3200, (rnd() - 0.5) * 0.6);
    if (gr === 'dark' && n === 0) pad(b, t, beat * 4, chord.map((m) => m - 12), 0.12 * e, 500);
    if (gr === 'calm' && n === 0) pad(b, t, beat * 4, chord, 0.1 * e, 900);
    if (rnd() < 0.04 * e) bells(wet, t, [1800 + rnd() * 2400], 0.05, 0.05);
  }
}

function score(style, planFile, outFile) {
  const plan = JSON.parse(readFileSync(path.resolve(planFile), 'utf8'));
  const dest = path.resolve(outFile);
  if (existsSync(dest)) { console.error(`Refusing to overwrite existing score: ${dest}`); process.exit(2); }
  const secs = Number(plan.durationSeconds); const sections = plan.sections;
  if (!(secs > 0 && secs <= 900) || !Array.isArray(sections) || !sections.length) throw new Error('audio-plan.json needs durationSeconds (0..900) and a nonempty sections array.');
  for (const s of sections) if (!Object.hasOwn(GROUP, s.mood)) throw new Error(`Unknown mood: ${s.mood} (use ${Object.keys(GROUP).join(', ')})`);
  seed = Number.isInteger(plan.seed) ? plan.seed : 7;
  const at = (t) => sections.find((s) => t >= s.start && t < s.end) ?? sections[sections.length - 1];
  const b = buf(secs + 0.5), wet = buf(secs + 0.5);
  ({arsip: scoreArsip, blueprint: scoreBlueprint, sketsa: scoreSketsa, hikayat: scoreHikayat, geometrik: scoreGeometrik, terminal: scoreTerminal})[style](plan, b, wet, at);
  for (const h of plan.styleHits ?? []) {
    if (h.type === 'hit') { if (style === 'hikayat') gong(b, h.time, 52, 0.7, 5); else hit(b, h.time, 0.28, style === 'blueprint' ? 33 : 38); }
    else if (h.type === 'riser') riser(b, Math.max(0, h.time - (h.duration ?? 1.5)), h.duration ?? 1.5, 0.25);
  }
  if (style === 'geometrik' || style === 'terminal') echo(wet, (60 / (style === 'geometrik' ? 118 : 96)) * 0.75, 0.3);
  reverb(wet, style === 'blueprint' || style === 'geometrik' ? 0.3 : 0.85, 1.1); mix(b, wet, style === 'blueprint' || style === 'geometrik' ? 1 : 0.75); reverb(b, style === 'hikayat' ? 0.3 : 0.15);
  // intentional silences + head/tail fades
  for (let i = 0; i < b.L.length; i++) {
    const t = i / SR; const s = at(t);
    let g = Math.min(1, t / 0.25) * Math.max(0, Math.min(1, (secs - t) / 1.2));
    if (s.mood === 'silence') g *= Math.max(0, Math.min(1, (s.start + 0.25 - t) / 0.25, (t - s.end + 0.25) / 0.25));
    b.L[i] *= g; b.R[i] *= g;
  }
  writeWav(dest, b, 0.85);
  console.log(`Style score (${style}): ${dest} (${secs.toFixed(2)}s, 48kHz, stereo, ${sections.length} sections, ${(plan.styleHits ?? []).length} hits)`);
  console.log('Procedural fallback in the style\'s sound: audition and mix it; do not claim professional music quality without listening.');
}

// ---------- one-shot SFX ----------
const one = (sec, fn) => { const b = buf(sec); fn(b); return b; };
const sweep = (b, f0, f1, dur, g) => { let ph = 0; for (let i = 0; i < dur * SR; i++) { const k = i / (dur * SR); ph += f0 * (f1 / f0) ** k / SR; put(b, i, Math.sin(2 * Math.PI * ph) * g * Math.sin(Math.PI * Math.min(1, k * 1.05))); } };
const nburst = (b, t0, dur, g, lpA = 0.3, hp = true) => { const s0 = Math.round(t0 * SR); let lp = 0, prev = 0; for (let i = 0; i < dur * SR; i++) { lp += lpA * (noise() - lp); put(b, s0 + i, (hp ? lp - prev : lp) * g * Math.exp(-i / SR / (dur / 4))); prev = lp; } };
const swish = (b, dur, g, flutter = 0) => { let lp = 0, prev = 0; for (let i = 0; i < dur * SR; i++) { const k = i / (dur * SR); lp += (0.05 + 0.4 * Math.sin(Math.PI * k)) * (noise() - lp); const fl = flutter ? 0.6 + 0.4 * Math.sin(2 * Math.PI * flutter * i / SR) : 1; put(b, i, (lp - prev * 0.6) * g * Math.sin(Math.PI * k) * fl, (k - 0.5) * 0.6); prev = lp; } };
const bells = (b, t0, freqs, g, decay = 0.5) => { const s0 = Math.round(t0 * SR); for (let i = 0; i < decay * 4 * SR; i++) { const tt = i / SR; let v = 0; freqs.forEach((f, j) => { v += Math.sin(2 * Math.PI * f * tt) * Math.exp(-tt / (decay / (1 + j * 0.6))); }); put(b, s0 + i, v * g / freqs.length * Math.min(1, tt / 0.002)); } };
const tone = (b, dur, fn, g) => { let ph = 0; for (let i = 0; i < dur * SR; i++) { const tt = i / SR; const [f, a] = fn(tt); ph += f / SR; put(b, i, Math.sin(2 * Math.PI * ph) * a * g); } };
const whoosh = () => one(0.8, (b) => swish(b, 0.7, 0.8));

const SFX = {
  arsip: {
    type: ['typewriter burst (~7 keys)', () => one(0.75, (b) => { for (let k = 0; k < 7; k++) { const t0 = (k / 7) * 0.6 + rnd() * 0.03; nburst(b, t0, 0.02, 0.9, 0.9); bass(b, t0, 40 + Math.floor(rnd() * 4), 0.04, 0.22); } })],
    stamp: ['rubber stamp slam', () => one(0.6, (b) => { kick(b, 0, 1); nburst(b, 0.005, 0.08, 0.7, 0.15, false); nburst(b, 0, 0.03, 0.4, 0.8); })],
    paper: ['sheet slides / lands', () => one(0.5, (b) => swish(b, 0.42, 0.9))],
    scroll: ['parchment unrolls', () => one(0.8, (b) => swish(b, 0.7, 0.8, 18))],
    pin: ['push-pin', () => one(0.15, (b) => { tick(b, 0, 0.6, 3200); tick(b, 0.035, 0.3, 1800); })],
    marker: ['red marker squeak (circle / cross-out)', () => one(0.45, (b) => { let ph = 0; for (let i = 0; i < 0.4 * SR; i++) { const tt = i / SR; ph += (1700 + 300 * Math.sin(tt * 40) + 500 * tt) / SR; put(b, i, (Math.sin(2 * Math.PI * ph) * 0.3 + noise() * 0.25) * Math.sin(Math.PI * tt / 0.4) * 0.6); } })],
    string: ['red string pulled tight', () => one(1.0, (b) => pluck(b, 0, 55, 0.8, 0.994, 0, 0.9, 0.6))],
    bell: ['typewriter bell', () => one(1.6, (b) => bells(b, 0, [2093, 5274, 3136], 0.8, 0.4))],
    thud: ['dull thud', () => one(0.5, (b) => { kick(b, 0, 0.9); nburst(b, 0, 0.1, 0.3, 0.05, false); })],
    shutter: ['polaroid shutter', () => one(0.35, (b) => { tick(b, 0, 0.9, 1500); nburst(b, 0.02, 0.12, 0.5, 0.5); tick(b, 0.12, 0.6, 1100); })],
    tick: ['clock tick / evidence marker', () => one(0.1, (b) => tick(b, 0, 0.9, 2600))],
    whoosh: ['camera move between stations', whoosh],
  },
  blueprint: {
    blip: ['callout / label appears', () => one(0.15, (b) => { bells(b, 0, [1320], 0.6, 0.04); bells(b, 0.05, [1980], 0.4, 0.04); })],
    scribe: ['line draws itself', () => one(0.45, (b) => { let lp = 0, prev = 0; for (let i = 0; i < 0.4 * SR; i++) { const tt = i / SR; lp += 0.6 * (noise() - lp); put(b, i, (lp - prev) * 0.5 * (0.6 + 0.4 * Math.sin(tt * 90)) * Math.sin(Math.PI * tt / 0.4)); prev = lp; } })],
    confirm: ['check / spec confirmed', () => one(0.6, (b) => { bells(b, 0, [880], 0.6, 0.08); bells(b, 0.09, [1318], 0.6, 0.12); })],
    drop: ['falling tone', () => one(0.5, (b) => sweep(b, 620, 140, 0.45, 0.7))],
    whir: ['machine spins up', () => one(1.9, (b) => { let ph = 0; for (let i = 0; i < 1.8 * SR; i++) { const tt = i / SR; ph += (180 + 700 * (tt / 1.8) ** 1.5) / SR; put(b, i, ((2 * (ph % 1) - 1) * 0.3 + Math.sin(4 * Math.PI * ph) * 0.3) * (0.5 + 0.5 * Math.sin(tt * 60)) * Math.min(1, tt / 0.3) * 0.6); } })],
    clank: ['metal clamp / mechanism locks', () => one(1.4, (b) => { bells(b, 0, [320, 873, 1530, 2470, 3911], 1.0, 0.35); nburst(b, 0, 0.06, 0.9, 0.6); kick(b, 0, 0.8); })],
    snap: ['cable / line snaps', () => one(0.5, (b) => { nburst(b, 0, 0.05, 1, 0.8); bells(b, 0, [180, 410], 0.5, 0.15); })],
    chop: ['axe / cut', () => one(0.6, (b) => { nburst(b, 0, 0.05, 1, 0.7); kick(b, 0.01, 0.8); nburst(b, 0.02, 0.2, 0.3, 0.1, false); })],
    spring: ['spring compresses', () => one(1.0, (b) => tone(b, 0.9, (tt) => [260 + 60 * Math.sin(tt * 55) * Math.exp(-tt * 3), Math.exp(-tt / 0.35)], 0.8))],
    alarm: ['warning beep', () => one(0.7, (b) => { for (let k = 0; k < 2; k++) bells(b, k * 0.22, [1400], 0.6, 0.05); })],
    whoosh: ['camera move to a detail panel', whoosh],
  },
  sketsa: {
    scribble: ['pencil scribble', () => one(0.6, (b) => { let lp = 0, prev = 0; for (let i = 0; i < 0.55 * SR; i++) { const tt = i / SR; lp += 0.5 * (noise() - lp); put(b, i, (lp - prev) * 0.7 * Math.max(0, Math.sin(tt * 34)) ** 2 * Math.sin(Math.PI * tt / 0.55)); prev = lp; } })],
    flip: ['page flip', () => one(0.6, (b) => swish(b, 0.5, 1, 22))],
    pop: ['doodle pops in', () => one(0.2, (b) => tone(b, 0.12, (tt) => [400 + 1800 * tt, Math.exp(-tt / 0.03)], 0.8))],
    squeak: ['marker / highlighter squeak', () => one(0.45, (b) => { let ph = 0; for (let i = 0; i < 0.4 * SR; i++) { const tt = i / SR; ph += (2000 + 400 * Math.sin(tt * 50)) / SR; put(b, i, (Math.sin(2 * Math.PI * ph) * 0.25 + noise() * 0.2) * Math.sin(Math.PI * tt / 0.4) * 0.6); } })],
    boing: ['comic boing', () => one(0.8, (b) => tone(b, 0.7, (tt) => [520 * Math.exp(-tt * 1.6) + 90 * Math.sin(tt * 70) * Math.exp(-tt * 4), Math.exp(-tt / 0.3)], 0.7))],
    ding: ['idea / reveal ding', () => one(1.6, (b) => bells(b, 0, [1568, 2349, 3136], 0.9, 0.45))],
    chime: ['warm 3-note chime', () => one(2.2, (b) => { bells(b, 0, [1046], 0.7, 0.5); bells(b, 0.18, [1318], 0.7, 0.5); bells(b, 0.36, [1568], 0.7, 0.6); })],
    switch: ['light switch click', () => one(0.2, (b) => { tick(b, 0, 0.9, 1800); tick(b, 0.05, 0.6, 1200); })],
    bubble: ['bubbles / experiment', () => one(0.5, (b) => [0, 0.1, 0.2].forEach((o, i) => { const s0 = Math.round(o * SR); for (let k = 0; k < 0.08 * SR; k++) { const tt = k / SR; put(b, s0 + k, Math.sin(2 * Math.PI * (500 + i * 220 + 2400 * tt) * tt) * Math.exp(-tt / 0.03) * 0.7); } }))],
    tick: ['small tick', () => one(0.1, (b) => tick(b, 0, 0.9, 2600))],
    whoosh: ['page or object slides', whoosh],
  },
  hikayat: {
    gong: ['gong (scene opens / big moment)', () => one(6, (b) => gong(b, 0, 62, 0.9, 4.5))],
    kecrek: ['kecrek metal plates (dalang cue)', () => one(0.5, (b) => { for (let k = 0; k < 3; k++) { nburst(b, k * 0.045, 0.06, 0.9, 0.95); bells(b, k * 0.045, [3400 + k * 300, 5100], 0.25, 0.05); } })],
    swoosh: ['gunungan / puppet sweeps across', () => one(0.9, (b) => swish(b, 0.8, 0.9))],
    kendang: ['kendang drum hit', () => one(0.5, (b) => { kendang(b, 0, 0.9); kendang(b, 0.12, 0.6, true); })],
    chime: ['saron bell (gold, reveal)', () => one(2.4, (b) => { metal(b, 0, 784, 0.6, 1.2); metal(b, 0.14, 1176, 0.4, 1.2); })],
    magic: ['transformation shimmer', () => one(2.4, (b) => [0, 1, 2, 3, 4, 5].forEach((i) => metal(b, i * 0.07, SLENDRO(784, i * 2), 0.35, 0.9, (i % 2 ? 0.3 : -0.3))))],
    water: ['water ripple', () => one(1.6, (b) => { for (let k = 0; k < 9; k++) tone(b, 0.1, (tt) => [380 + k * 60 + 900 * tt, Math.exp(-tt / 0.03)], 0.3); swish(b, 1.4, 0.35); })],
    splash: ['fish splash', () => one(0.9, (b) => { nburst(b, 0, 0.35, 0.9, 0.5); swish(b, 0.6, 0.5); })],
    thunder: ['thunder', () => one(3.5, (b) => { nburst(b, 0, 0.15, 1, 0.6); let lp = 0; for (let i = 0; i < 3.2 * SR; i++) { const tt = i / SR; lp += 0.012 * (noise() - lp); put(b, i, lp * 6 * Math.exp(-tt / 1.1) * (0.6 + 0.4 * Math.sin(tt * 9))); } })],
    rain: ['rain (3 s)', () => one(3.2, (b) => { for (let i = 0; i < 3 * SR; i++) put(b, i, noise() * 0.18 * Math.min(1, i / SR / 0.4) * Math.min(1, (3 - i / SR) / 0.4), rnd() - 0.5); for (let k = 0; k < 90; k++) tick(b, rnd() * 3, 0.15, 2000 + rnd() * 3000, rnd() - 0.5); })],
    flood: ['rising water', () => one(2.6, (b) => { let lp = 0; for (let i = 0; i < 2.5 * SR; i++) { const k = i / (2.5 * SR); lp += (0.01 + 0.05 * k) * (noise() - lp); put(b, i, lp * 3 * Math.sin(Math.PI * k)); } })],
    boom: ['volcano eruption', () => one(4, (b) => { kick(b, 0, 1); gong(b, 0, 40, 0.8, 2); let lp = 0; for (let i = 0; i < 3.5 * SR; i++) { const tt = i / SR; lp += 0.02 * (noise() - lp); put(b, i, lp * 5 * Math.exp(-tt / 1.2)); } })],
    rumble: ['ground collapses', () => one(2.4, (b) => { let lp = 0; for (let i = 0; i < 2.2 * SR; i++) { const tt = i / SR; lp += 0.01 * (noise() - lp); put(b, i, lp * 7 * Math.sin(Math.PI * tt / 2.2) * (0.7 + 0.3 * Math.sin(tt * 23))); } })],
  },
  geometrik: {
    tick: ['digit tick', () => one(0.1, (b) => tick(b, 0, 0.9, 2900))],
    slam: ['number slams in', () => one(0.6, (b) => { kick(b, 0, 1); clap(b, 0, 0.6); })],
    pop: ['block pops', () => one(0.2, (b) => tone(b, 0.12, (tt) => [520 + 1400 * tt, Math.exp(-tt / 0.03)], 0.8))],
    click: ['operator click', () => one(0.12, (b) => { tick(b, 0, 0.9, 1600); tick(b, 0.02, 0.5, 2600); })],
    swipe: ['bar / label slides in', () => one(0.35, (b) => swish(b, 0.28, 0.9))],
    wipe: ['full-frame wipe', () => one(0.6, (b) => swish(b, 0.5, 1))],
    flip: ['frame flips red', () => one(0.7, (b) => { swish(b, 0.4, 0.9); kick(b, 0.35, 0.6); })],
    ding: ['result ding', () => one(1.2, (b) => bells(b, 0, [1318, 1976], 0.8, 0.3))],
    rise: ['value rises', () => one(0.8, (b) => sweep(b, 300, 1200, 0.7, 0.6))],
    down: ['value falls', () => one(0.8, (b) => sweep(b, 900, 220, 0.7, 0.6))],
    cut: ['shape sliced', () => one(0.3, (b) => { nburst(b, 0, 0.08, 1, 0.9); tone(b, 0.15, (tt) => [2400 - 6000 * tt, Math.exp(-tt / 0.05)], 0.4); })],
    thud: ['object lands', () => one(0.4, (b) => kick(b, 0, 0.8))],
  },
  terminal: {
    ting: ['phone notification', () => one(1.2, (b) => { bells(b, 0, [1760], 0.8, 0.25); bells(b, 0.12, [2637], 0.7, 0.35); })],
    msg: ['message bubble', () => one(0.3, (b) => tone(b, 0.15, (tt) => [880 + 600 * tt, Math.exp(-tt / 0.05)], 0.6))],
    keys: ['typing burst', () => one(0.8, (b) => { for (let k = 0; k < 8; k++) tick(b, k * 0.08 + rnd() * 0.02, 0.7, 1200 + rnd() * 1500, rnd() - 0.5); })],
    key: ['single key', () => one(0.08, (b) => tick(b, 0, 0.9, 1500))],
    blip: ['UI blip', () => one(0.15, (b) => { bells(b, 0, [1480], 0.6, 0.04); })],
    glitch: ['digital glitch', () => one(0.35, (b) => { for (let i = 0; i < 0.3 * SR; i++) { const step = Math.floor(i / 220); put(b, i, (rnd() < 0.5 ? 1 : -1) * 0.5 * ((step * 7919) % 3 === 0 ? 1 : 0.2) * Math.sign(Math.sin(i * 0.05 * (1 + (step % 5))))); } })],
    drone: ['dark synth drone', () => one(2.4, (b) => { synth(b, 0, 33, 2, 0.8, 300); synth(b, 0, 40, 2, 0.4, 500); })],
    ok: ['confirm', () => one(0.4, (b) => { bells(b, 0, [988], 0.6, 0.05); bells(b, 0.08, [1480], 0.6, 0.08); })],
    warn: ['warning beeps', () => one(0.7, (b) => { for (let k = 0; k < 2; k++) synth(b, k * 0.2, 69, 0.12, 0.6, 2000, 0, true); })],
    lock: ['lock click', () => one(0.5, (b) => { tick(b, 0, 0.9, 900); kick(b, 0.01, 0.4); bells(b, 0.08, [1320], 0.3, 0.06); })],
    granted: ['access granted', () => one(1.2, (b) => [0, 1, 2, 3].forEach((i) => synth(b, i * 0.09, 72 + [0, 4, 7, 12][i], 0.12, 0.5, 3000, 0, true)))],
    drain: ['balance drains', () => one(1.4, (b) => { sweep(b, 1400, 120, 1.2, 0.6); for (let k = 0; k < 14; k++) tick(b, k * 0.08, 0.3, 2500 - k * 120); })],
    alarm: ['alarm', () => one(0.9, (b) => { for (let k = 0; k < 3; k++) synth(b, k * 0.25, 76, 0.18, 0.6, 2500, 0, true); })],
    error: ['error buzz', () => one(0.5, (b) => synth(b, 0, 40, 0.4, 0.8, 1200, 0, true))],
    x: ['short buzz (✗)', () => one(0.25, (b) => synth(b, 0, 45, 0.16, 0.7, 1500, 0, true))],
  },
};

function sfx(style) {
  const dir = path.resolve('public/audio/sfx', style);
  for (const [name, [desc, gen]] of Object.entries(SFX[style])) {
    seed = name.length * 31 + 5;
    writeWav(path.join(dir, `${name}.wav`), gen(), 0.8);
    console.log(`  audio/sfx/${style}/${name}.wav  — ${desc}`);
  }
  console.log(`Style SFX ready (${Object.keys(SFX[style]).length}). Reference them in src/audio-plan.json sfxCues as "audio/sfx/${style}/<name>.wav".`);
}

const [cmd, style, a1, a2] = process.argv.slice(2);
if (!STYLES.includes(style) || !['sfx', 'score'].includes(cmd) || (cmd === 'score' && (!a1 || !a2))) {
  console.error(`Usage:\n  npm run style-sfx -- ${STYLES.join('|')}\n  npm run style-score -- ${STYLES.join('|')} src/audio-plan.json public/audio/music/<name>.wav`);
  process.exit(2);
}
if (cmd === 'sfx') sfx(style); else score(style, a1, a2);
