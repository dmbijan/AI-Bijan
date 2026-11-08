#!/usr/bin/env python3
"""voynann-video-editor · verify-output.py

Quality control for a rendered MP4. A finished render is not automatically a good edit, so this checks the
file technically AND produces a contact sheet that Claude must look at before delivering.

  python3 verify-output.py out/final.mp4 --edit work/edit.json
  python3 verify-output.py out/preview.mp4 --edit work/edit.json --range 0-7

Checks
  1 file exists / size            6 black or frozen stretches (blackdetect / freezedetect)
  2 duration vs edit.json         7 audio/video sync: cross-correlation of output audio with the cut voice
  3 resolution, fps, codecs,      8 caption timing: every spoken word is covered by a caption page that is
    orientation (no rotate flag)    on screen at the word's start (or deliberately hidden), pages ordered
  4 full decode without errors    9 assets referenced by edit.json exist (footage, fonts, thumbnails)
  5 loudness (-14 LUFS +/-2,     10 mobile readability: caption/overlay font sizes, safe-zone lint
    true peak <= -1 dBTP)        11 contact sheet of representative frames (+ blank-frame detection)
Exit code: 0 = all PASS (warnings allowed), 1 = at least one FAIL.
"""
import argparse, json, math, re, subprocess, sys, wave
from fractions import Fraction
from pathlib import Path

import numpy as np

HERE = Path(__file__).resolve().parent
SKILL = HERE.parent


def run(cmd):
    return subprocess.run(cmd, capture_output=True, text=True)


class Report:
    def __init__(self): self.items = []
    def add(self, status, name, detail):
        self.items.append({"status": status, "check": name, "detail": detail})
        print(f"[{status:4}] {name}: {detail}")
    @property
    def failed(self): return any(i["status"] == "FAIL" for i in self.items)


def probe(path):
    r = run(["ffprobe", "-v", "error", "-show_streams", "-show_format", "-of", "json", str(path)])
    return json.loads(r.stdout)


def pcm(path, sr=16000, ss=None, t=None):
    cmd = ["ffmpeg", "-v", "error"]
    if ss is not None: cmd += ["-ss", str(ss)]
    cmd += ["-i", str(path)]
    if t is not None: cmd += ["-t", str(t)]
    cmd += ["-vn", "-ac", "1", "-ar", str(sr), "-f", "f32le", "-"]
    return np.frombuffer(subprocess.run(cmd, capture_output=True).stdout, np.float32).astype(np.float64)


def xcorr_lag(a, b, sr, max_lag=0.3):
    """Lag (s) of b relative to a using band-limited onset envelopes (robust to EQ, music and SFX)."""
    def env(x):
        hop = int(sr * 0.005)
        n = len(x) // hop
        e = np.sqrt((x[: n * hop].reshape(n, hop) ** 2).mean(1) + 1e-12)
        e = np.log(e); d = np.diff(e, prepend=e[0]); return np.maximum(d, 0)
    ea, eb = env(a), env(b)
    n = min(len(ea), len(eb)); ea, eb = ea[:n] - ea[:n].mean(), eb[:n] - eb[:n].mean()
    L = int(max_lag / 0.005)
    best, arg = -1e18, 0
    for k in range(-L, L + 1):
        if k >= 0: v = float(np.dot(ea[: n - k], eb[k:]))
        else: v = float(np.dot(ea[-k:], eb[: n + k]))
        if v > best: best, arg = v, k
    norm = math.sqrt(float(np.dot(ea, ea)) * float(np.dot(eb, eb))) + 1e-12
    return arg * 0.005, best / norm


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("video"); ap.add_argument("--edit"); ap.add_argument("--range", help="A-B seconds if the file is a partial render")
    ap.add_argument("--sheet"); ap.add_argument("--report")
    ap.add_argument("--max-mb", type=float, default=100.0)
    a = ap.parse_args()
    R = Report()
    out = Path(a.video)
    E = json.loads(Path(a.edit).read_text()) if a.edit else None
    work = Path(a.edit).parent if a.edit else out.parent
    r0, r1 = (0.0, None)
    if a.range: r0, r1 = (float(x) for x in a.range.split("-"))

    # 1 exists / size
    if not out.exists() or out.stat().st_size < 1000:
        R.add("FAIL", "file", f"{out} missing or empty"); finish(R, a, out); return
    mb = out.stat().st_size / 1e6
    R.add("PASS" if mb <= a.max_mb else "WARN", "file", f"{mb:.1f} MB" + ("" if mb <= a.max_mb else f" (> {a.max_mb} MB: may exceed download/upload limits; re-encode with higher CRF)"))

    info = probe(out)
    v = next((s for s in info["streams"] if s["codec_type"] == "video"), None)
    au = next((s for s in info["streams"] if s["codec_type"] == "audio"), None)
    dur = float(info["format"]["duration"])
    # 2 duration
    if E:
        exp = (r1 if r1 is not None else E["duration"]) - r0
        ok = abs(dur - exp) <= 1.5 / E["fps"] + 0.03
        R.add("PASS" if ok else "FAIL", "duration", f"{dur:.3f}s (expected {exp:.3f}s)")
    else:
        R.add("PASS", "duration", f"{dur:.3f}s")
    # 3 stream properties
    if v is None:
        R.add("FAIL", "video stream", "missing")
    else:
        W, H = int(v["width"]), int(v["height"])
        rot = 0
        for sd in v.get("side_data_list", []) or []:
            if "rotation" in sd: rot = int(float(sd["rotation"]))
        expW, expH = (E["width"], E["height"]) if E else (W, H)
        fps = float(Fraction(v["r_frame_rate"]))  # ffprobe rational like "60/1"; avoid eval()
        ok = (W, H) == (expW, expH) and rot == 0 and v["codec_name"] == "h264" and v.get("pix_fmt") == "yuv420p"
        R.add("PASS" if ok else "FAIL", "video format",
              f"{v['codec_name']} {v.get('pix_fmt')} {W}x{H} ({'portrait' if H > W else 'landscape' if W > H else 'square'}), rotation={rot}, {fps:.2f} fps, frames={v.get('nb_frames')}")
        if E and abs(fps - E["fps"]) > 0.01: R.add("FAIL", "frame rate", f"{fps} != {E['fps']}")
        if abs(fps - 60) > 0.01: R.add("FAIL", "frame rate", f"{fps:.2f} fps; this product must deliver 60 fps")
    if au is None:
        R.add("FAIL", "audio stream", "missing")
    else:
        R.add("PASS" if au["codec_name"] == "aac" else "WARN", "audio format", f"{au['codec_name']} {au.get('sample_rate')} Hz, {au.get('channels')} ch")
    # 4 decode
    r = run(["ffmpeg", "-v", "error", "-i", str(out), "-f", "null", "-"])
    errs = [l for l in r.stderr.splitlines() if l.strip()]
    R.add("PASS" if not errs else "FAIL", "full decode", "no decoder errors" if not errs else f"{len(errs)} errors: {errs[:3]}")
    # 5 loudness
    if au is not None:
        r = run(["ffmpeg", "-hide_banner", "-nostats", "-i", str(out), "-af", "ebur128=peak=true", "-f", "null", "-"])
        I = TP = None
        for line in r.stderr.splitlines()[-15:]:
            s = line.strip()
            if s.startswith("I:"): I = float(s.split()[1])
            if s.startswith("Peak:"): TP = float(s.split()[1])
        ok = I is not None and -16.5 <= I <= -11.5 and (TP is None or TP <= -0.5)
        R.add("PASS" if ok else "WARN", "loudness", f"integrated {I} LUFS, true peak {TP} dBTP (target -14 LUFS, <= -1 dBTP)")
        x = pcm(out, 16000)
        silent = float(np.mean(np.abs(x) < 1e-4)) if x.size else 1.0
        R.add("PASS" if x.size and silent < 0.5 else "FAIL", "audio present", f"{100 * (1 - silent):.0f}% of samples non-silent")
    # 6 black / frozen
    r = run(["ffmpeg", "-hide_banner", "-nostats", "-i", str(out), "-vf", "blackdetect=d=0.25:pix_th=0.08,freezedetect=n=0.0015:d=1.2", "-an", "-f", "null", "-"])
    blacks = re.findall(r"black_start:([\d.]+) black_end:([\d.]+)", r.stderr)
    freezes = re.findall(r"freeze_start: ([\d.]+)", r.stderr)
    # deliberate black: an iris cover with "hold" (the video ends in black)
    held = [(o["at"] - r0 - 0.6, o.get("end", dur + r0) - r0 + 0.1) for o in (E or {}).get("overlays", [])
            if o.get("type") == "ShapeWipe" and o.get("shape") == "iris" and o.get("hold")]
    intended = lambda t0, t1=None: any(h0 <= t0 and (t1 if t1 is not None else t0) <= h1 for h0, h1 in held)
    planned = [b_ for b_ in blacks if intended(float(b_[0]), float(b_[1]))]
    blacks = [b_ for b_ in blacks if b_ not in planned]
    R.add("PASS" if not blacks else "FAIL", "black frames", ("none" if not blacks else f"{blacks}") +
          (f" (planned iris-to-black ending at {[tuple(map(float, p_)) for p_ in planned]})" if planned else ""))
    R.add("PASS" if not freezes else "WARN", "frozen picture", "none" if not freezes else f"static stretches start at {freezes} s (fine for an end card hold; check otherwise)")
    # 7 A/V sync against the cut voice
    voice = work / "voice_cut.wav"
    if E and voice.exists() and au is not None:
        seg_len = (r1 if r1 is not None else E["footage"]["duration"]) - r0
        seg_len = min(seg_len, E["footage"]["duration"] - r0)
        if seg_len > 1:
            ref = pcm(voice, 16000, ss=r0, t=seg_len); got = pcm(out, 16000, ss=0, t=seg_len)
            lag, corr = xcorr_lag(ref, got, 16000)
            ok = abs(lag) <= 0.02 and corr > 0.3
            R.add("PASS" if ok else "FAIL", "A/V sync", f"output audio vs cut voice: offset {lag * 1000:+.0f} ms, correlation {corr:.2f} (video and captions share the cut timeline)")
    # 8 caption timing
    if E and E.get("captions"):
        pages = E["captions"]["pages"]; zones = E["captions"].get("zones", [])
        hidden = lambda t: any(z.get("hidden") and z["from"] <= t < z["to"] for z in zones)
        words = [w for w in E["words"] if (r1 is None or w["start"] < r1) and w["start"] >= r0 - 0.01]
        miss, late = [], []
        for w in words:
            pg = next((p for p in pages if p["start"] <= w["start"] < p["end"]), None)
            if pg is None and not hidden(w["start"]): miss.append(w["text"])
            elif pg is not None and not any(abs(x["start"] - w["start"]) < 1e-3 for x in pg["words"]): late.append(w["text"])
        order = all(p["end"] <= q["start"] + 1e-3 for p, q in zip(pages, pages[1:]))
        nh = sum(1 for w in words if hidden(w["start"]))
        ok = not miss and not late and order
        R.add("PASS" if ok else "FAIL", "caption sync", f"{len(words) - len(miss) - nh}/{len(words)} words on screen at their spoken start (word-level, CTC-aligned)"
              + (f", {nh} deliberately hidden under a title" if nh else "") + (f"; missing {miss[:6]}" if miss else "") + ("" if order else "; pages overlap"))
        tr = work / "transcript.json"
        if tr.exists():
            T = json.loads(tr.read_text())
            if T.get("method") != "ctc-forced-alignment":
                R.add("WARN", "word timing", f"transcript method={T.get('method')}: timing is approximate")
            lc = T.get("low_confidence", [])
            R.add("PASS" if not lc else "WARN", "transcript confidence", "all words aligned with confidence >= 0.15" if not lc else f"low-confidence words: {lc[:10]} (check spelling)")
    # 9 assets
    if E:
        proj = work / "remotion" / "public"
        need = [proj / "cut.mp4"] + [proj / th["file"] for th in E.get("thumbs", [])]
        skill_fonts = Path(__file__).resolve().parent.parent / "assets" / "fonts"
        need += [proj / "fonts" / f.name for f in skill_fonts.iterdir() if f.suffix in (".ttf", ".woff2", ".otf")]
        if E.get("matte"):
            need += [proj / "matte"]  # background-removal masks (text behind the speaker / Cutout)
        need += [proj / "fx" / "grain.jpg"] + [proj / a_["file"] for a_ in E.get("assets", [])]
        missing = [str(p.name) for p in need if not p.exists()]
        R.add("PASS" if not missing else "FAIL", "assets", "footage, fonts and thumbnails present" if not missing else f"missing {missing}")
    # 10 readability + lint
    if E:
        S = E["style"]["captions"]
        short = min(E["width"], E["height"])
        cap = S["size"] / short * 1080
        small = [f"{o['type']}@{o.get('start', 0):.1f}s:{o.get('fontSize') or o.get('size')}" for o in E.get("overlays", [])
                 if (o.get("fontSize") or (o.get("size") if o["type"] in ("Badge", "Tile") else None) or 99) < 28]
        R.add("PASS" if cap >= 52 and not small else "WARN", "mobile readability",
              f"caption {S['size']}px on a {short}px short side" + (f"; small overlay text {small}" if small else "; overlay text >= 28px"))
        lint = E.get("lint", [])
        R.add("PASS" if not lint else "WARN", "plan lint (safe zones, targets)", "captions and text overlays inside the platform safe zone" if not lint else "; ".join(lint))
        slop = E.get("slop", [])
        R.add("PASS" if not slop else "WARN", "anti-slop", "no slop patterns (double text, ungrounded words, over-emphasis, face covered, overload, SFX spam)"
              if not slop else f"{len(slop)} issue(s): " + " | ".join(slop[:4]))
        ed = E.get("editor", {})
        mx = E["style"].get("rhythm", {}).get("maxStatic", 4.0) * {"low": 1.5, "medium": 1.0, "high": 0.8}.get(E["settings"].get("editing_intensity", "medium"), 1)
        ls = ed.get("longest_static")
        if ls is not None:
            R.add("PASS" if ls <= mx + 0.01 else "WARN", "edit rhythm", f"longest stretch without a visual change {ls:.1f}s (style limit {mx:.1f}s); "
                  f"{len(E.get('cutFx', []))} cut transitions, {len(ed.get('fillers_removed', []))} filler words cut, {len(E.get('removed', []))} pauses/retakes removed")
        variants = sorted({z.get("variant") for z in E["captions"].get("zones", []) if z.get("variant")} | {S["variant"]})
        R.add("PASS", "style consistency", f"style={E['settings']['style']}, caption looks used: {variants}, palette {E['style']['colors']['accent']}/{E['style']['colors']['accent2']}")
    # 11 contact sheet
    times = []
    if E:
        for L in E.get("layouts", []):
            t = L["t"] + max(0.6, L.get("dur", 0.5) + 0.25)
            times.append(t)
        times += [p["start"] + 0.3 for p in E["captions"]["pages"][:: max(1, len(E["captions"]["pages"]) // 8)]]
    times += [0.05, dur - 0.1]
    times = sorted({round(min(max(0.0, t - r0), dur - 0.05), 2) for t in times if 0 <= t - r0 <= dur})
    sheet = Path(a.sheet or out.with_name(out.stem + "_qc.jpg"))
    blanks = make_sheet(out, times, sheet)
    blanks = [t for t in blanks if not intended(t)]
    R.add("PASS" if not blanks else "FAIL", "representative frames", f"{len(times)} frames -> {sheet} (LOOK AT IT before delivering)" + (f"; near-blank frames at {blanks}" if blanks else ""))
    finish(R, a, out)


def make_sheet(path, times, out, cols=5):
    from PIL import Image, ImageDraw
    tiles, blanks = [], []
    for t in times:
        raw = subprocess.run(["ffmpeg", "-v", "error", "-ss", f"{t:.3f}", "-i", str(path), "-frames:v", "1", "-vf", "scale=270:-2", "-f", "image2pipe",
                              "-vcodec", "png", "-"], capture_output=True).stdout
        if not raw: continue
        import io
        im = Image.open(io.BytesIO(raw)).convert("RGB")
        if np.asarray(im).std() < 4: blanks.append(t)
        tiles.append((t, im))
    if not tiles: return [0]
    w, h = tiles[0][1].size
    rows = (len(tiles) + cols - 1) // cols
    sheet = Image.new("RGB", (cols * (w + 8) + 8, rows * (h + 30) + 8), (20, 20, 20))
    d = ImageDraw.Draw(sheet)
    for k, (t, im) in enumerate(tiles):
        x, y = 8 + (k % cols) * (w + 8), 8 + (k // cols) * (h + 30)
        sheet.paste(im, (x, y + 22)); d.text((x + 4, y + 4), f"{t:.2f}s", fill=(200, 255, 46))
    sheet.save(out, quality=85)
    return blanks


def finish(R, a, out):
    rep = Path(a.report or out.with_name(out.stem + "_qc.json"))
    rep.write_text(json.dumps({"file": str(out), "result": "FAIL" if R.failed else "PASS", "checks": R.items}, indent=1))
    print(("RESULT: FAIL" if R.failed else "RESULT: PASS") + f" -> {rep}")
    sys.exit(1 if R.failed else 0)


if __name__ == "__main__":
    main()
