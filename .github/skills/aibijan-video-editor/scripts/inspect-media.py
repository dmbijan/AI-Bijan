#!/usr/bin/env python3
"""voynann-video-editor · inspect-media.py

Read the real metadata of the user's footage, normalise it into a safe working copy, and (optionally)
track the speaker's face so zooms stay centred on it. The original file is opened read-only and never modified.

  python3 inspect-media.py INPUT --work work/                      # metadata only -> work/media.json
  python3 inspect-media.py INPUT --work work/ --normalize --faces  # + work/source.mp4, audio WAVs, work/faces.json
  python3 inspect-media.py --check                                 # environment check (ffmpeg, node, chromium, python libs)

What --normalize produces (all in --work):
  source.mp4    rotation applied (display orientation), constant 60 fps (this product always outputs 60 fps), H.264 CRF 16, BT.709, no audio
  voice48k.wav  original audio, mono 48 kHz PCM (used for the final mix)
  audio16k.wav  mono 16 kHz PCM (used for transcription)
HDR (HLG/PQ) sources are tone-mapped to SDR BT.709 with zscale when available.
"""
import argparse, json, os, shutil, subprocess, sys
from fractions import Fraction
from pathlib import Path


def run(cmd, **kw):
    return subprocess.run(cmd, capture_output=True, text=True, **kw)


def ffprobe(path):
    r = run(["ffprobe", "-v", "error", "-show_format", "-show_streams", "-of", "json", str(path)])
    if r.returncode != 0:
        sys.exit(f"ffprobe could not read {path}: {r.stderr.strip()}")
    return json.loads(r.stdout)


def rotation_of(vs):
    for sd in vs.get("side_data_list", []) or []:
        if "rotation" in sd:
            return int(round(float(sd["rotation"])))
    tag = (vs.get("tags") or {}).get("rotate")
    return int(tag) if tag else 0


def fr(s):
    try:
        f = Fraction(s)
        return float(f) if f.denominator else 0.0
    except Exception:  # noqa: BLE001
        return 0.0


def loudness(path):
    r = run(["ffmpeg", "-hide_banner", "-nostats", "-i", str(path), "-vn", "-af", "ebur128=peak=true", "-f", "null", "-"])
    out = {}
    for line in r.stderr.splitlines()[-20:]:
        line = line.strip()
        if line.startswith("I:"): out["integrated_lufs"] = float(line.split()[1])
        if line.startswith("LRA:"): out["lra"] = float(line.split()[1])
        if line.startswith("Peak:"): out["true_peak_dbfs"] = float(line.split()[1])
    return out


def silences(path, noise=-35, d=0.25):
    r = run(["ffmpeg", "-hide_banner", "-nostats", "-i", str(path), "-vn", "-af",
             f"silencedetect=noise={noise}dB:d={d}", "-f", "null", "-"])
    out, s = [], None
    for line in r.stderr.splitlines():
        if "silence_start" in line: s = float(line.split("silence_start:")[1])
        if "silence_end" in line and s is not None:
            e = float(line.split("silence_end:")[1].split("|")[0]); out.append([round(s, 3), round(e, 3)]); s = None
    return out


def inspect(path):
    info = ffprobe(path)
    v = next((s for s in info["streams"] if s["codec_type"] == "video"), None)
    a = next((s for s in info["streams"] if s["codec_type"] == "audio"), None)
    if v is None:
        sys.exit("no video stream found")
    rot = rotation_of(v)
    w, h = int(v["width"]), int(v["height"])
    dw, dh = (h, w) if abs(rot) % 180 == 90 else (w, h)
    dur = float(info["format"].get("duration") or v.get("duration") or 0)
    nb = int(v.get("nb_frames") or 0)
    r_fps, avg_fps = fr(v.get("r_frame_rate", "0/1")), fr(v.get("avg_frame_rate", "0/1"))
    # Container duration may be audio-limited and differ by a video frame; prefer the video stream clock.
    video_dur = float(v.get("duration") or 0) or dur
    counted = nb / video_dur if nb and video_dur else avg_fps
    transfer = v.get("color_transfer", "")
    m = {
        "file": str(Path(path).resolve()),
        "size_bytes": int(info["format"].get("size", 0)),
        "container": info["format"].get("format_name"),
        "duration": round(dur, 3),
        "video": {
            "codec": v.get("codec_name"), "profile": v.get("profile"),
            "coded_width": w, "coded_height": h, "rotation": rot,
            "display_width": dw, "display_height": dh,
            "orientation": "portrait" if dh > dw else "landscape" if dw > dh else "square",
            "aspect": f"{dw}:{dh}",
            "r_frame_rate": round(r_fps, 3), "avg_frame_rate": round(avg_fps, 3),
            "frames": nb, "fps_from_count": round(counted, 3),
            "variable_frame_rate": abs(r_fps - avg_fps) > 0.05 or abs(counted - r_fps) > 0.2,
            "pix_fmt": v.get("pix_fmt"), "color_transfer": transfer, "color_primaries": v.get("color_primaries"),
            "hdr": transfer in ("arib-std-b67", "smpte2084"), "bit_rate": int(v.get("bit_rate", 0) or 0),
        },
        "audio": None,
    }
    if a:
        m["audio"] = {"codec": a.get("codec_name"), "sample_rate": int(a.get("sample_rate", 0)),
                      "channels": a.get("channels"), "layout": a.get("channel_layout"),
                      "duration": round(float(a.get("duration") or dur), 3)}
        m["audio"].update(loudness(path))
        m["audio"]["silences_-35dB_0.25s"] = silences(path)
    notes = []
    if rot: notes.append(f"display matrix rotation {rot} deg: coded {w}x{h} is shown as {dw}x{dh}; normalisation applies it")
    if m["video"]["variable_frame_rate"]: notes.append("variable frame rate: normalised to the selected constant output fps")
    if m["video"]["hdr"]: notes.append("HDR transfer detected: tone-mapped to SDR BT.709")
    if not a: notes.append("NO AUDIO STREAM: captions/transcription impossible; ask the user")
    m["notes"] = notes
    return m


def normalize(path, m, work, fps=60):
    work.mkdir(parents=True, exist_ok=True)
    dw, dh = m["video"]["display_width"], m["video"]["display_height"]
    short = min(dw, dh)
    scale = 1080 / short if short < 1080 else 1.0  # upscale small sources so the 1080 canvas is never soft-scaled twice
    tw, th = int(round(dw * scale / 2) * 2), int(round(dh * scale / 2) * 2)
    vf = []
    if m["video"]["hdr"]:
        vf.append("zscale=t=linear:npl=100,format=gbrpf32le,zscale=p=bt709,tonemap=mobius:desat=0,"
                  "zscale=t=bt709:m=bt709:r=tv,format=yuv420p")
    vf += [f"fps={fps}", f"scale={tw}:{th}:flags=lanczos", "setsar=1", "format=yuv420p"]
    out = work / "source.mp4"
    cmd = ["ffmpeg", "-v", "error", "-y", "-i", str(path), "-map", "0:v:0", "-vf", ",".join(vf),
           "-c:v", "libx264", "-preset", "veryfast", "-crf", "16", "-g", str(fps // 2), "-bf", "0",
           "-color_primaries", "bt709", "-color_trc", "bt709", "-colorspace", "bt709",
           "-movflags", "+faststart", "-an", str(out)]
    r = run(cmd)
    if r.returncode != 0:
        sys.exit("normalisation failed: " + r.stderr[-800:])
    if m["audio"]:
        run(["ffmpeg", "-v", "error", "-y", "-i", str(path), "-map", "0:a:0", "-ac", "1", "-ar", "48000",
             "-c:a", "pcm_s16le", str(work / "voice48k.wav")], check=True)
        run(["ffmpeg", "-v", "error", "-y", "-i", str(path), "-map", "0:a:0", "-ac", "1", "-ar", "16000",
             "-c:a", "pcm_s16le", str(work / "audio16k.wav")], check=True)
    p = ffprobe(out)["streams"][0]
    m["normalized"] = {"file": str(out.resolve()), "width": int(p["width"]), "height": int(p["height"]), "fps": fps,
                       "frames": int(p.get("nb_frames", 0)), "duration": round(float(p.get("duration", 0)), 3),
                       "voice48k": str((work / "voice48k.wav").resolve()) if m["audio"] else None,
                       "audio16k": str((work / "audio16k.wav").resolve()) if m["audio"] else None}
    return m


def track_faces(video, work, step=0.25):
    """Haar-cascade face track (OpenCV, offline). Output centre/size normalised to the frame, median-smoothed."""
    try:
        import cv2
        casc = Path(cv2.data.haarcascades) / "haarcascade_frontalface_default.xml"
        if not casc.exists(): raise ImportError("cascade missing")
    except (ImportError, AttributeError):
        print("[inspect] opencv-python-headless<5 not available -> pip install 'opencv-python-headless<5'", file=sys.stderr)
        return None
    import numpy as np
    det = cv2.CascadeClassifier(str(casc))
    cap = cv2.VideoCapture(str(video))
    fps = cap.get(cv2.CAP_PROP_FPS) or 30
    W, H = cap.get(cv2.CAP_PROP_FRAME_WIDTH), cap.get(cv2.CAP_PROP_FRAME_HEIGHT)
    every = max(1, int(round(step * fps)))
    raw, i = [], 0
    while True:
        ok = cap.grab()
        if not ok: break
        if i % every == 0:
            ok, fr_ = cap.retrieve()
            if ok:
                small = cv2.resize(fr_, (int(W / 3), int(H / 3)))
                g = cv2.equalizeHist(cv2.cvtColor(small, cv2.COLOR_BGR2GRAY))
                fs = det.detectMultiScale(g, scaleFactor=1.1, minNeighbors=6, minSize=(int(W / 3 * 0.12),) * 2)
                if len(fs):
                    x, y, w, h = max(fs, key=lambda f: f[2] * f[3])
                    raw.append([i / fps, (x + w / 2) * 3 / W, (y + h / 2) * 3 / H, w * 3 / W, h * 3 / H])
                else:
                    raw.append([i / fps, None, None, None, None])
        i += 1
    cap.release()
    found = [r for r in raw if r[1] is not None]
    if not found:
        return {"found": False, "samples": [], "median": {"cx": 0.5, "cy": 0.33, "w": 0.3, "h": 0.17}}
    arr = np.array([r[1:] for r in found])
    med = np.median(arr, 0)
    # fill gaps with nearest detection, then a 5-tap median filter + light EMA to remove jitter
    filled, last = [], found[0][1:]
    for r in raw:
        last = r[1:] if r[1] is not None else last
        filled.append([r[0]] + list(last))
    f = np.array(filled)
    sm = f.copy()
    for k in range(len(f)):
        lo, hi = max(0, k - 2), min(len(f), k + 3)
        sm[k, 1:] = np.median(f[lo:hi, 1:], 0)
    for k in range(1, len(sm)):
        sm[k, 1:] = 0.6 * sm[k - 1, 1:] + 0.4 * sm[k, 1:]
    out = {"found": True, "coverage": round(len(found) / len(raw), 3), "step": step,
           "median": {"cx": round(float(med[0]), 4), "cy": round(float(med[1]), 4), "w": round(float(med[2]), 4), "h": round(float(med[3]), 4)},
           "samples": [{"t": round(r[0], 3), "cx": round(r[1], 4), "cy": round(r[2], 4), "w": round(r[3], 4), "h": round(r[4], 4)}
                       for r in sm]}
    (work / "faces.json").write_text(json.dumps(out, indent=1))
    return out


def check_env():
    ok = True
    def line(name, good, extra=""):
        nonlocal ok
        ok &= bool(good)
        print(f"{name:<22}{'OK' if good else 'MISSING'} {extra}")
    line("ffmpeg", shutil.which("ffmpeg"), shutil.which("ffmpeg") or "apt/conda ffmpeg required")
    line("ffprobe", shutil.which("ffprobe"))
    node = shutil.which("node")
    ver = run(["node", "--version"]).stdout.strip() if node else ""
    line("node>=18", node and int(ver.lstrip("v").split(".")[0]) >= 18, ver)
    line("npm", shutil.which("npm"))
    chrome = find_chrome()
    line("chromium (headless)", chrome, chrome or "pip install playwright && playwright install chromium-headless-shell")
    for mod, pipname in [("numpy", "numpy"), ("cv2", "'opencv-python-headless<5'"), ("sherpa_onnx", "sherpa-onnx"),
                         ("onnxruntime", "onnxruntime"), ("PIL", "pillow")]:
        try:
            m = __import__(mod)
            if mod == "cv2":  # face tracking needs the bundled Haar cascades (missing in OpenCV 5 wheels)
                casc = Path(m.data.haarcascades) / "haarcascade_frontalface_default.xml"
                if not casc.exists(): raise ImportError("haar cascades missing")
            line(f"python:{mod}", True)
        except (ImportError, AttributeError):
            line(f"python:{mod}", False, f"pip install --break-system-packages {pipname}")
    print("READY" if ok else "NOT READY (install the missing items above, then re-run --check)")
    return ok


def find_chrome():
    env = os.environ.get("VY_BROWSER")
    if env and Path(env).exists(): return env
    roots = [os.environ.get("PLAYWRIGHT_BROWSERS_PATH", ""), "/opt/pw-browsers", str(Path.home() / ".cache/ms-playwright")]
    for root in roots:
        if root and Path(root).exists():
            for pat in ["chromium_headless_shell-*/chrome-headless-shell-linux64/chrome-headless-shell",
                        "chromium_headless_shell-*/chrome-linux/headless_shell",
                        "chromium-*/chrome-linux64/chrome", "chromium-*/chrome-linux/chrome"]:
                hits = sorted(Path(root).glob(pat))
                if hits: return str(hits[-1])
    for name in ["chromium", "chromium-browser", "google-chrome"]:
        if shutil.which(name): return shutil.which(name)
    return None


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("input", nargs="?")
    ap.add_argument("--work", default="work")
    ap.add_argument("--normalize", action="store_true")
    ap.add_argument("--faces", action="store_true")
    ap.add_argument("--fps", type=int, choices=[60], default=60,
                    help="working/output frames per second (default 60; footage below 60 fps is duplicated, not AI-interpolated)")
    ap.add_argument("--check", action="store_true")
    a = ap.parse_args()
    if a.check:
        sys.exit(0 if check_env() else 3)
    if not a.input: ap.error("input required")
    src = Path(a.input)
    if not src.exists(): sys.exit(f"not found: {src}")
    work = Path(a.work); work.mkdir(parents=True, exist_ok=True)
    if work.resolve() == src.resolve().parent:
        print("[inspect] note: work dir is the upload dir; originals are still only read", file=sys.stderr)
    m = inspect(src)
    if a.normalize:
        m = normalize(src, m, work, a.fps)
    if a.faces:
        vid = m.get("normalized", {}).get("file") or str(src)
        fc = track_faces(vid, work)
        m["faces"] = None if fc is None else {"found": fc["found"], "coverage": fc.get("coverage"), "median": fc["median"],
                                               "file": str((work / "faces.json").resolve())}
    (work / "media.json").write_text(json.dumps(m, indent=1))
    v = m["video"]
    print(f"{src.name}: {v['coded_width']}x{v['coded_height']} {v['codec']} rot={v['rotation']} -> display "
          f"{v['display_width']}x{v['display_height']} ({v['orientation']}), {m['duration']}s, "
          f"{v['r_frame_rate']} fps (avg {v['avg_frame_rate']}, VFR={v['variable_frame_rate']}), "
          f"audio={'yes' if m['audio'] else 'NO'}" + (f" {m['audio'].get('integrated_lufs')} LUFS" if m['audio'] else ""))
    for n in m["notes"]: print(" -", n)
    if "faces" in m and m["faces"]: print(" - face:", m["faces"])
    print("->", work / "media.json")


if __name__ == "__main__":
    main()
