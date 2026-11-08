#!/usr/bin/env python3
"""voynann-video-editor · sound.py

Licence-free sound design, generated from code (no downloaded samples):
  * SFX palette: whoosh, swoosh, pop, click, tick, impact, riser, ding, shimmer, snap, scribble, glitch, paper, page
  * Procedural music bed per style (impact / editorial / system) that follows bpm, has an intro filter
    sweep, an optional break + final hit, and ends cleanly on the last bar
  * Mixer: voice clean-up (HPF, presence EQ, compression), music ducking under the voice,
    SFX placement, then two-pass EBU R128 loudness normalisation (-14 LUFS, -1 dBTP) with ffmpeg.

Used by render-video.py. Can be run on its own to audition sounds:
  python3 sound.py --demo palette.wav
  python3 sound.py --bed impact --dur 20 bed.wav
"""
import argparse, json, math, subprocess, sys, tempfile, wave
from pathlib import Path

import numpy as np

SR = 48000
TAU = 2 * np.pi


# ------------------------------------------------------------------------------------------- DSP basics
def t_(d):
    return np.arange(int(SR * d)) / SR


def rng(seed):
    return np.random.default_rng(int(seed) & 0x7FFFFFFF)


def biquad(x, kind, f, q=0.707, gain_db=0.0):
    from scipy.signal import lfilter
    w = TAU * min(max(f, 10.0), SR * 0.45) / SR
    c, s = math.cos(w), math.sin(w); al = s / (2 * q); A = 10 ** (gain_db / 40)
    if kind == "lp": b = [(1 - c) / 2, 1 - c, (1 - c) / 2]; a = [1 + al, -2 * c, 1 - al]
    elif kind == "hp": b = [(1 + c) / 2, -(1 + c), (1 + c) / 2]; a = [1 + al, -2 * c, 1 - al]
    elif kind == "bp": b = [al, 0, -al]; a = [1 + al, -2 * c, 1 - al]
    else:  # peak
        b = [1 + al * A, -2 * c, 1 - al * A]; a = [1 + al / A, -2 * c, 1 - al / A]
    return lfilter(np.array(b) / a[0], np.array(a) / a[0], x)


def sweep_bp(x, f0, f1, q=1.2, blocks=48):
    """Band-pass with exponentially sweeping centre frequency, processed in short crossfaded blocks."""
    n = len(x); out = np.zeros(n); edges = np.linspace(0, n, blocks + 1).astype(int)
    for k in range(blocks):
        a, b = edges[k], edges[k + 1]
        f = f0 * (f1 / f0) ** ((k + 0.5) / blocks)
        lo, hi = max(0, a - 256), min(n, b + 256)
        seg = biquad(x[lo:hi], "bp", f, q)
        out[a:b] += seg[a - lo:a - lo + (b - a)]
    return out


def env_ad(n, attack, decay_tau):
    t = np.arange(n) / SR
    e = np.minimum(1, t / max(attack, 1e-4)) * np.exp(-np.maximum(0, t - attack) / decay_tau)
    return e


def stereo(x, pan=0.0):
    """pan -1..1 (constant power). pan may be an array."""
    p = (np.asarray(pan) + 1) * np.pi / 4
    return np.stack([x * np.cos(p), x * np.sin(p)], 1)


def norm(x, peak=0.9):
    m = np.max(np.abs(x)) + 1e-9
    return x * (peak / m)


# ------------------------------------------------------------------------------------------- SFX palette
def sfx_whoosh(dur=0.5, direction=1, seed=1, bright=1.0):
    n = int(SR * dur); r = rng(seed)
    noise = r.standard_normal(n)
    body = sweep_bp(noise, 350 * bright, 4200 * bright, q=0.9)
    tail = sweep_bp(noise, 3000 * bright, 900 * bright, q=0.7)
    x_ = np.linspace(0, 1, n)
    peak = 0.62
    e = np.where(x_ < peak, (x_ / peak) ** 2.2, np.exp(-(x_ - peak) / 0.14))
    sig = (0.8 * body * np.where(x_ < peak, 1, 0.6) + 0.5 * tail * np.where(x_ < peak, 0.2, 1)) * e
    tone = np.sin(TAU * np.cumsum(90 + 140 * x_) / SR) * e * 0.15
    pan = direction * (x_ * 1.4 - 0.7)
    return norm(stereo(sig + tone, pan), 0.8)


def sfx_swoosh(seed=2):
    return sfx_whoosh(0.28, direction=1, seed=seed, bright=1.4) * 0.8


def sfx_pop(pitch=1.0, seed=3):
    d = 0.14; t = t_(d)
    f = 320 * pitch + 700 * pitch * np.exp(-t / 0.018)
    body = np.sin(TAU * np.cumsum(f) / SR) * env_ad(len(t), 0.002, 0.045)
    click = rng(seed).standard_normal(len(t)) * env_ad(len(t), 0.0005, 0.004) * 0.5
    return norm(stereo(body + biquad(click, "hp", 2500)), 0.7)


def sfx_click(seed=4, pitch=1.0):
    d = 0.06; t = t_(d)
    n_ = rng(seed).standard_normal(len(t)) * env_ad(len(t), 0.0003, 0.006)
    tone = np.sin(TAU * 2400 * pitch * t) * env_ad(len(t), 0.0005, 0.012) * 0.6
    return norm(stereo(biquad(n_, "bp", 3500 * pitch, 1.5) + tone), 0.55)


def sfx_tick(seed=5):
    r = rng(seed)
    d = 0.045; t = t_(d)
    n_ = r.standard_normal(len(t)) * env_ad(len(t), 0.0002, 0.004)
    tone = np.sin(TAU * (1700 + 500 * r.random()) * t) * env_ad(len(t), 0.0003, 0.006) * 0.4
    return norm(stereo(biquad(n_, "bp", 2800 + 1200 * r.random(), 2.0) + tone, r.uniform(-0.2, 0.2)), 0.35)


def sfx_impact(seed=6):
    d = 0.9; t = t_(d)
    f = 42 + 70 * np.exp(-t / 0.05)
    sub = np.sin(TAU * np.cumsum(f) / SR) * env_ad(len(t), 0.002, 0.28)
    thump = biquad(rng(seed).standard_normal(len(t)), "lp", 900) * env_ad(len(t), 0.001, 0.05) * 0.9
    snap = biquad(rng(seed + 1).standard_normal(len(t)), "hp", 3000) * env_ad(len(t), 0.0005, 0.02) * 0.35
    x = np.tanh((sub * 1.4 + thump + snap) * 1.6)
    return norm(stereo(x), 0.85)


def sfx_riser(dur=1.2, seed=7):
    n = int(SR * dur); x_ = np.linspace(0, 1, n)
    noise = sweep_bp(rng(seed).standard_normal(n), 400, 7000, q=1.4)
    tone = np.sin(TAU * np.cumsum(220 * 2 ** (2 * x_)) / SR) * 0.25
    e = x_ ** 2.5
    return norm(stereo((noise + tone) * e, (x_ - 0.5) * 0.6), 0.6)


def sfx_ding(seed=8, pitch=1.0):
    d = 1.1; t = t_(d)
    out = np.zeros(len(t))
    for f, a, tau in [(1318.5, 1.0, 0.35), (1975.5, 0.55, 0.25), (2637, 0.25, 0.15), (3951, 0.12, 0.08)]:
        out += a * np.sin(TAU * f * pitch * t) * env_ad(len(t), 0.002, tau)
    second = np.zeros(len(t)); k = int(0.09 * SR)
    for f, a, tau in [(1760, 0.8, 0.4), (2637, 0.4, 0.25)]:
        second[k:] += a * np.sin(TAU * f * pitch * t[:-k]) * env_ad(len(t) - k, 0.002, tau)
    return norm(stereo(out * 0.6 + second * 0.6), 0.45)


def sfx_shimmer(seed=9, dur=1.2):
    r = rng(seed); n = int(SR * dur); t = np.arange(n) / SR; out = np.zeros(n)
    for k in range(26):
        st = int(r.uniform(0, 0.55) * SR); f = r.uniform(3500, 9000)
        seg = np.sin(TAU * f * t[: n - st]) * env_ad(n - st, 0.003, r.uniform(0.05, 0.22)) * r.uniform(0.3, 1)
        out[st:] += seg
    return norm(stereo(out, np.sin(t * 9) * 0.5), 0.3)


def sfx_snap(seed=10):
    d = 0.12; t = t_(d)
    n_ = biquad(rng(seed).standard_normal(len(t)), "bp", 1800, 0.9) * env_ad(len(t), 0.0005, 0.018)
    return norm(stereo(n_), 0.6)


def sfx_scribble(dur=0.4, seed=11):
    """Marker on paper: band-passed noise with stroke-rate amplitude wobble (pen scratch), soft attack and tail."""
    r = rng(seed); n = int(SR * max(0.15, dur)); x_ = np.linspace(0, 1, n)
    noise = biquad(r.standard_normal(n), "bp", 2600 + 900 * r.random(), 0.8)
    strokes = 0.55 + 0.45 * np.abs(np.sin(TAU * (7 + 4 * r.random()) * x_ * dur + r.random() * 3))
    grit = biquad(r.standard_normal(n), "hp", 5200) * 0.25
    e = np.minimum(1, x_ / 0.08) * np.minimum(1, (1 - x_) / 0.2)
    return norm(stereo((noise + grit) * strokes * e, r.uniform(-0.25, 0.25)), 0.32)


def sfx_glitch(dur=0.3, seed=12):
    """Digital glitch: gated square bursts at random pitches + bit-crushed noise, chopped at ~30 Hz."""
    r = rng(seed); n = int(SR * max(0.12, dur)); t = np.arange(n) / SR
    out = np.zeros(n); blk = int(SR / 30)
    for k in range(0, n, blk):
        if r.random() < 0.3: continue
        f = r.choice([180, 360, 720, 1440, 2880]) * r.uniform(0.95, 1.05)
        seg = np.sign(np.sin(TAU * f * t[: min(blk, n - k)])) * r.uniform(0.3, 1.0)
        out[k:k + len(seg)] += seg
    crush = np.round(r.standard_normal(n) * 3) / 3 * 0.35
    e = np.exp(-t / (dur * 0.7))
    return norm(stereo(biquad(out * 0.6 + crush, "hp", 220) * e, np.sin(t * 60) * 0.5), 0.5)


def sfx_paper(seed=13, pitch=1.0):
    """A paper tag placed on a page: a short dry slide (band-passed noise, fast decay) and a soft low tap as it lands."""
    r = rng(seed); d = 0.16; n = int(SR * d); t = np.arange(n) / SR
    slide = biquad(r.standard_normal(n), "bp", (2300 + 700 * r.random()) * pitch, 0.7) * env_ad(n, 0.004, 0.035)
    grit = biquad(r.standard_normal(n), "hp", 5500) * env_ad(n, 0.002, 0.012) * 0.35
    tap = np.sin(TAU * np.cumsum(170 * pitch + 140 * np.exp(-t / 0.01)) / SR) * env_ad(n, 0.001, 0.022) * 0.55
    return norm(stereo(slide + grit + tap, r.uniform(-0.2, 0.2)), 0.5)


def sfx_page(seed=14, dur=0.42):
    """A sheet of paper sliding over the frame: soft low-passed swish with a papery top, gentle swell and a quick tail."""
    r = rng(seed); n = int(SR * dur); x_ = np.linspace(0, 1, n)
    body = sweep_bp(r.standard_normal(n), 900, 2600, q=0.6)
    top = biquad(r.standard_normal(n), "hp", 4200) * 0.25
    e = np.where(x_ < 0.55, (x_ / 0.55) ** 1.6, np.exp(-(x_ - 0.55) / 0.12))
    flutter = 0.75 + 0.25 * np.sin(TAU * 23 * x_ * dur + r.random() * 3)
    return norm(stereo((body + top) * e * flutter, (x_ - 0.5) * 0.5), 0.55)


SFX = {
    "paper": lambda s, **k: sfx_paper(s, k.get("pitch", 1.0)),
    "page": lambda s, **k: sfx_page(s, k.get("dur", 0.42)),
    "whoosh": lambda s, **k: sfx_whoosh(k.get("dur", 0.5), k.get("dir", 1), s),
    "swoosh": lambda s, **k: sfx_swoosh(s),
    "pop": lambda s, **k: sfx_pop(k.get("pitch", 1.0), s),
    "click": lambda s, **k: sfx_click(s, k.get("pitch", 1.0)),
    "tick": lambda s, **k: sfx_tick(s),
    "impact": lambda s, **k: sfx_impact(s),
    "riser": lambda s, **k: sfx_riser(k.get("dur", 1.2), s),
    "ding": lambda s, **k: sfx_ding(s, k.get("pitch", 1.0)),
    "shimmer": lambda s, **k: sfx_shimmer(s, k.get("dur", 1.2)),
    "snap": lambda s, **k: sfx_snap(s),
    "scribble": lambda s, **k: sfx_scribble(k.get("dur", 0.4), s),
    "glitch": lambda s, **k: sfx_glitch(k.get("dur", 0.3), s),
}
# Some SFX are "anticipatory": their energy peak sits late, so they start before the visual hit.
PRE_ROLL = {"whoosh": 0.62, "swoosh": 0.62, "riser": 1.0, "page": 0.5}


def render_sfx(cues, total, intensity=1.0):
    """cues: [{t, kind, gain_db?, dur?, dir?, pitch?, typing_until?}] -> stereo buffer."""
    out = np.zeros((int(SR * total) + SR, 2))
    for i, c in enumerate(cues):
        kind = c["kind"]
        if kind not in SFX: continue
        if kind == "typing":
            continue
        extra = {k: c[k] for k in ("dur", "dir", "pitch") if k in c}
        x = SFX[kind](c.get("seed", 100 + i), **extra)
        g = 10 ** (c.get("gain_db", 0) / 20) * intensity
        pre = PRE_ROLL.get(kind, 0) * (c.get("dur", 0.5 if kind != "riser" else 1.2))
        st = int((c["t"] - pre) * SR)
        if st < 0: x = x[-st:]; st = 0
        e = min(len(out), st + len(x)); out[st:e] += x[: e - st] * g
    # typing: a burst of ticks between t and until
    for i, c in enumerate(cues):
        if c["kind"] != "typing": continue
        r = rng(500 + i); t = c["t"]; g = 10 ** (c.get("gain_db", -4) / 20) * intensity
        while t < c["until"]:
            x = sfx_tick(int(r.integers(1, 1e6))); st = int(t * SR); e = min(len(out), st + len(x))
            out[st:e] += x[: e - st] * g * r.uniform(0.6, 1.0)
            t += r.uniform(0.055, 0.11)
    return out[: int(SR * total)]


# ------------------------------------------------------------------------------------------- music bed
BED_PRESETS = {
    # chords as semitone offsets from A2 (110 Hz) root; progression vi-IV-I-V in C
    "impact": {"bpm": 118, "prog": [[0, 3, 7, 10], [-4, 0, 3, 7], [3, 7, 10, 14], [-2, 2, 5, 9]], "kick": "four", "hat": 16,
               "pluck": True, "pad": 0.35, "bass": 0.55},
    "system": {"bpm": 110, "prog": [[0, 3, 7, 12], [-4, 3, 7, 11], [-7, 0, 4, 9], [-2, 5, 9, 12]], "kick": "four", "hat": 8,
               "pluck": True, "pad": 0.25, "bass": 0.5},
    # STORYTELLER: slow minor i-VI-III-VII, heartbeat kick, no claps or hats, wide pad (suspense under narration)
    "cinematic": {"bpm": 76, "prog": [[0, 3, 7, 12], [-4, 0, 3, 8], [3, 7, 10, 15], [-2, 2, 5, 10]], "kick": "half", "hat": 0,
                  "pluck": False, "pad": 0.8, "bass": 0.3, "clap": False},
    # MOGRAPH MIX: bright major I-V-vi-IV, bouncy plucks
    "playful": {"bpm": 124, "prog": [[3, 7, 10, 15], [-2, 2, 5, 10], [0, 3, 7, 12], [-4, 0, 3, 8]], "kick": "four", "hat": 16,
                "pluck": True, "pad": 0.3, "bass": 0.5},
    # LUMEN: warm premium groove, major 7ths / 9ths, half-time kick, soft plucks and a wide pad
    "lumen": {"bpm": 96, "prog": [[3, 7, 10, 14], [-2, 2, 5, 9], [-4, 0, 3, 10], [-7, -3, 0, 7]], "kick": "half", "hat": 8,
              "pluck": True, "pad": 0.6, "bass": 0.45},
    # DEPTH: modern minimal groove, minor 9ths, half-time kick with claps, soft plucks (premium Reels bed)
    "depth": {"bpm": 100, "prog": [[0, 3, 7, 14], [-4, 0, 7, 10], [-7, 0, 3, 10], [-2, 2, 9, 12]], "kick": "half", "hat": 8,
              "pluck": True, "pad": 0.45, "bass": 0.5},
    # NOTED: unhurried notebook groove, maj7/9 colours, half-time kick, soft hats and plucks, a light pad (lo-fi study feel)
    "note": {"bpm": 86, "prog": [[3, 7, 10, 14], [-4, 0, 3, 10], [-2, 2, 5, 9], [0, 3, 7, 12]], "kick": "half", "hat": 8,
             "pluck": True, "pad": 0.5, "bass": 0.4},
    "editorial": {"bpm": 92, "prog": [[3, 7, 10, 14], [-4, 0, 3, 7], [0, 3, 7, 10], [-2, 2, 5, 9]], "kick": "half", "hat": 8,
                  "pluck": False, "pad": 0.55, "bass": 0.35},
}


def hz(semi):
    return 110.0 * 2 ** (semi / 12)


def kick(n):
    t = np.arange(n) / SR
    f = 48 + 110 * np.exp(-t / 0.035)
    return np.sin(TAU * np.cumsum(f) / SR) * env_ad(n, 0.001, 0.18)


def clap(n, seed):
    x = biquad(rng(seed).standard_normal(n), "bp", 1500, 0.8)
    e = env_ad(n, 0.001, 0.06)
    for d in (0.008, 0.017):  # flam
        k = int(d * SR); e[k:] += env_ad(n - k, 0.001, 0.05) * 0.6
    return x * e * 0.6


def hat(n, seed, open_=False):
    x = biquad(biquad(rng(seed).standard_normal(n), "hp", 6500), "lp", 11000)
    return x * env_ad(n, 0.0005, 0.09 if open_ else 0.022) * 0.16


def saw(f, t, detune=0.0):
    out = np.zeros_like(t)
    for d in (-detune, 0, detune):
        ph = (f * (1 + d) * t) % 1.0
        out += 2 * ph - 1
    return out / 3


def make_bed(style, total, hit_at=None, break_at=None, seed=11):
    p = BED_PRESETS.get(style, BED_PRESETS["impact"])
    bpm = p["bpm"]; beat = 60 / bpm; bar = 4 * beat
    n = int(SR * (total + 2)); L = np.zeros(n); R = np.zeros(n)
    drums = np.zeros(n); music = np.zeros(n); bass_bus = np.zeros(n)
    r = rng(seed)
    nbeats = int(total / beat) + 2
    end_drums = break_at if break_at is not None else total + 9
    for b in range(nbeats):
        t0 = b * beat
        if t0 >= end_drums and (hit_at is None or t0 < hit_at): continue
        if hit_at is not None and t0 > hit_at + 0.01: break
        st = int(t0 * SR)
        if p["kick"] == "four" or (p["kick"] == "half" and b % 2 == 0):
            k = kick(int(0.4 * SR)); e = min(n, st + len(k)); drums[st:e] += k[: e - st] * 0.75
        if p.get("clap", True) and b % 4 in (1, 3):
            c = clap(int(0.3 * SR), seed + b); e = min(n, st + len(c)); drums[st:e] += c[: e - st] * 0.4
        steps = p["hat"] // 4
        for s in range(steps):
            ts = t0 + s * beat / steps; ss = int(ts * SR)
            h = hat(int(0.12 * SR), seed * 7 + b * 16 + s, open_=(steps >= 2 and s == steps // 2 and b % 2 == 1))
            vel = (0.55 if s % 2 else 0.85) * r.uniform(0.85, 1)
            e = min(n, ss + len(h)); drums[ss:e] += h[: e - ss] * vel
    nbars = int(total / bar) + 2
    for k in range(nbars):
        ch = p["prog"][k % len(p["prog"])]
        t0 = k * bar; st = int(t0 * SR); ln = int(bar * SR)
        if st >= n: break
        ln = min(ln, n - st); t = np.arange(ln) / SR
        pad = sum(saw(hz(s + 12), t, 0.004) for s in ch) / len(ch)
        pad = biquad(pad, "lp", 1800, 0.6) * np.minimum(1, t / 0.25) * p["pad"] * 2.2
        music[st:st + ln] += pad
        # bass: 8th-note pulses on the root
        for q in range(8):
            qs = int(q * beat / 2 * SR); ql = int(beat / 2 * SR)
            if qs + ql > ln: break
            tq = np.arange(ql) / SR
            f = hz(ch[0] - 12)
            note = (np.sin(TAU * f * tq) + 0.3 * np.sin(TAU * 2 * f * tq)) * env_ad(ql, 0.004, 0.16)
            bass_bus[st + qs: st + qs + ql] += note * p["bass"]
        if p["pluck"]:
            pattern = [0, 2, 1, 3, 2, 1, 3, 2]
            for q in range(8):
                qs = int((q * beat / 2 + beat / 4) * SR) if q % 2 else int(q * beat / 2 * SR)
                ql = int(0.35 * SR)
                if qs + ql > ln: break
                tq = np.arange(ql) / SR
                f = hz(ch[pattern[q] % len(ch)] + 24)
                note = (saw(f, tq) * 0.6 + np.sin(TAU * f * tq) * 0.4) * env_ad(ql, 0.002, 0.07)
                music[st + qs: st + qs + ql] += biquad(note, "lp", 3600, 0.8) * 0.5
    # sidechain "pump" on music+bass from the kick grid
    tt = np.arange(n) / SR
    ph = (tt % beat) / beat
    pump = 0.45 + 0.55 * np.minimum(1, ph / 0.42) ** 1.4
    if p["kick"] == "half": pump = 0.7 + 0.3 * pump
    music *= pump; bass_bus *= pump
    mix = drums * 0.8 + music + bass_bus
    # intro: low-pass sweep over the first bar so the hook breathes, then full
    intro = min(bar, total * 0.25)
    k_end = int(intro * SR)
    if k_end > 0:
        lp = biquad(mix[:k_end], "lp", 900, 0.7)
        w = np.clip(np.arange(k_end) / k_end, 0, 1) ** 1.5
        mix[:k_end] = lp * (1 - w) + mix[:k_end] * w
    # final hit + ring out
    if hit_at is not None:
        hs = int(hit_at * SR)
        mix[hs:] *= np.exp(-np.arange(n - hs) / SR / 0.25)
        ch = p["prog"][0]
        ring_len = int(min(2.5, total - hit_at + 0.6) * SR)
        if ring_len > 0:
            t = np.arange(ring_len) / SR
            ring = sum(saw(hz(s + 12), t, 0.005) for s in ch) / len(ch)
            ring = biquad(ring, "lp", 2200, 0.6) * env_ad(ring_len, 0.005, 0.9) * 0.6
            ring += kick(ring_len) * 1.0
            e = min(n, hs + ring_len); mix[hs:e] += ring[: e - hs]
    mix = mix[: int(total * SR)]
    fade = int(0.4 * SR)
    mix[-fade:] *= np.linspace(1, 0, fade) ** 2
    st = stereo(mix)
    # light stereo width from a short delay on the right channel
    d = int(0.011 * SR); st[d:, 1] = 0.85 * st[d:, 1] + 0.15 * st[:-d, 0]
    return norm(st, 0.8), bpm


# ------------------------------------------------------------------------------------------- I/O + mix
def read_wav(path):
    """Any audio -> float32 stereo @48k via ffmpeg."""
    raw = subprocess.run(["ffmpeg", "-v", "error", "-i", str(path), "-ac", "2", "-ar", str(SR), "-f", "f32le", "-"],
                         capture_output=True, check=True).stdout
    return np.frombuffer(raw, np.float32).reshape(-1, 2).astype(np.float64)


def write_wav(path, x):
    x = np.clip(x, -1, 1)
    pcm = (x * 32767).astype("<i2")
    with wave.open(str(path), "wb") as w:
        w.setnchannels(pcm.shape[1] if pcm.ndim == 2 else 1); w.setsampwidth(2); w.setframerate(SR)
        w.writeframes(pcm.tobytes())


def ffmpeg_af(src, dst, af):
    subprocess.run(["ffmpeg", "-v", "error", "-y", "-i", str(src), "-af", af, "-ar", str(SR), str(dst)], check=True)


def loudnorm(src, dst, target=-14.0, tp=-1.0, lra=11.0):
    """Two-pass EBU R128 normalisation (linear when possible)."""
    r = subprocess.run(["ffmpeg", "-hide_banner", "-nostats", "-i", str(src), "-af",
                        f"loudnorm=I={target}:TP={tp}:LRA={lra}:print_format=json", "-f", "null", "-"],
                       capture_output=True, text=True)
    js = r.stderr[r.stderr.rfind("{"): r.stderr.rfind("}") + 1]
    m = json.loads(js)
    af = (f"loudnorm=I={target}:TP={tp}:LRA={lra}:measured_I={m['input_i']}:measured_TP={m['input_tp']}:"
          f"measured_LRA={m['input_lra']}:measured_thresh={m['input_thresh']}:offset={m['target_offset']}:linear=true")
    ffmpeg_af(src, dst, af + f",aresample={SR}")
    return m


def lufs_of(x, tmp):
    """Integrated loudness (EBU R128) of a buffer, measured by ffmpeg."""
    f = Path(tmp) / "_meter.wav"
    write_wav(f, x / max(1.0, float(np.max(np.abs(x)))))
    scale = max(1.0, float(np.max(np.abs(x))))
    r = subprocess.run(["ffmpeg", "-hide_banner", "-nostats", "-i", str(f), "-af", "ebur128", "-f", "null", "-"], capture_output=True, text=True)
    vals = [l for l in r.stderr.splitlines() if l.strip().startswith("I:")]
    val = float(vals[-1].split()[1]) if vals else -70.0
    return val + 20 * math.log10(scale)


def envelope(x, attack=0.02, release=0.25):
    mono = np.abs(x).mean(1) if x.ndim == 2 else np.abs(x)
    hop = 240; n = len(mono) // hop
    fr = mono[: n * hop].reshape(n, hop).max(1)
    e = np.zeros(n); a = math.exp(-hop / (attack * SR)); r_ = math.exp(-hop / (release * SR)); s = 0
    for i, v in enumerate(fr):
        s = a * s + (1 - a) * v if v > s else r_ * s + (1 - r_) * v
        e[i] = s
    out = np.repeat(e, hop) if n else np.zeros(0)
    if len(out) < len(mono): out = np.pad(out, (0, len(mono) - len(out)), mode="edge" if len(out) else "constant")
    return out[: len(mono)]


def load_music_file(path, total, tmp):
    """User-supplied track (their licence): decoded to 48 kHz stereo, looped if short, 0.4 s fade-in,
    1.5 s fade-out at the end of the edit."""
    dst = Path(tmp) / "user_music.wav"
    r = subprocess.run(["ffmpeg", "-v", "error", "-y", "-i", str(path), "-ac", "2", "-ar", str(SR), "-c:a", "pcm_s16le", str(dst)],
                       capture_output=True, text=True)
    if r.returncode != 0:
        raise RuntimeError(f"could not decode music file {path}: {r.stderr[-400:]}")
    x = read_wav(dst)
    n = int(total * SR)
    if len(x) < n:
        reps = int(np.ceil(n / max(1, len(x))))
        x = np.concatenate([x] * reps)
    x = x[:n].copy()
    fi, fo = int(0.4 * SR), int(min(1.5, total / 3) * SR)
    x[:fi] *= np.linspace(0, 1, fi)[:, None]
    x[n - fo:] *= np.linspace(1, 0, fo)[:, None]
    return x


def mix(voice_wav, total, cues, out_wav, style="impact", music=True, music_db=-17.0, sfx_db=-6.0,
        sfx_intensity=1.0, music_hit_at=None, music_break_at=None, voice_offset=0.0, lufs=-14.0, tmp=None, music_file=None):
    tmp = Path(tmp or tempfile.mkdtemp()); tmp.mkdir(parents=True, exist_ok=True)
    # 1) voice clean-up chain (keeps the original voice, only cleans and levels it)
    vclean = tmp / "voice_clean.wav"
    ffmpeg_af(voice_wav, vclean, "highpass=f=75,equalizer=f=250:t=q:w=1.2:g=-2,equalizer=f=3200:t=q:w=1.0:g=2.5,"
                                 "acompressor=threshold=-21dB:ratio=3:attack=6:release=90:makeup=3dB,"
                                 "alimiter=limit=0.95:level=false")
    v = read_wav(vclean)
    n = int(total * SR)
    voice = np.zeros((n, 2)); off = int(voice_offset * SR)
    e = min(n, off + len(v)); voice[off:e] = v[: e - off]
    vrms = np.sqrt(np.mean(voice[np.abs(voice).mean(1) > 1e-3] ** 2)) if np.any(np.abs(voice) > 1e-3) else 0.1
    bus = voice.copy()
    info = {"voice_rms_dbfs": round(20 * math.log10(vrms + 1e-9), 2)}
    if music:
        if music_file:
            bed, bpm = load_music_file(music_file, total, tmp), None
        else:
            bed, bpm = make_bed(style, total, hit_at=music_hit_at, break_at=music_break_at)
        bed = bed[:n] if len(bed) >= n else np.pad(bed, ((0, n - len(bed)), (0, 0)))
        # level the bed by perceived loudness (LUFS), not RMS: the kick/bass dominate RMS but barely count to the ear
        v_l, b_l = lufs_of(voice, tmp), lufs_of(bed, tmp)
        bed *= 10 ** ((v_l + music_db - b_l) / 20)
        env = envelope(voice); env = env / (np.percentile(env[env > 1e-4], 90) + 1e-9) if np.any(env > 1e-4) else env
        duck = 1 - 0.45 * np.clip(env, 0, 1)  # about -5 dB more while the person speaks
        bus += bed * duck[:, None]
        info.update({"music": ("user file " + Path(music_file).name) if music_file else style, "bpm": bpm, "music_lu_rel_voice_unducked": music_db, "voice_lufs_pre": round(v_l, 1)})
    if cues:
        fx = render_sfx(cues, total, sfx_intensity)
        frms = np.sqrt(np.mean(fx[np.abs(fx).mean(1) > 1e-3] ** 2)) + 1e-9 if np.any(np.abs(fx) > 1e-3) else 1
        bus += fx * (vrms / frms) * 10 ** (sfx_db / 20)
        info["sfx_cues"] = len(cues)
    pre = tmp / "premix.wav"; write_wav(pre, bus / max(1.0, np.max(np.abs(bus)) / 0.98))
    # -1.6 dBTP on the WAV leaves headroom for the AAC encoder's inter-sample overshoot (delivered file stays <= -1 dBTP)
    m = loudnorm(pre, out_wav, target=lufs, tp=-1.6)
    info["premix_lufs"] = float(m["input_i"])
    return info


def demo(out):
    parts = []
    for k in ["whoosh", "swoosh", "pop", "click", "tick", "impact", "riser", "ding", "shimmer", "snap", "scribble", "glitch", "paper", "page"]:
        x = SFX[k](42); parts += [x, np.zeros((int(0.35 * SR), 2))]
    write_wav(out, np.concatenate(parts) * 0.9)


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--demo"); ap.add_argument("--bed"); ap.add_argument("--dur", type=float, default=16)
    ap.add_argument("out", nargs="?")
    a = ap.parse_args()
    if a.demo: demo(a.demo); print("wrote", a.demo); return
    if a.bed and a.out:
        x, bpm = make_bed(a.bed, a.dur, hit_at=a.dur - 1.6); write_wav(a.out, x); print("wrote", a.out, bpm, "bpm"); return
    ap.print_help()


if __name__ == "__main__":
    main()
