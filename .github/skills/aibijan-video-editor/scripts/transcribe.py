#!/usr/bin/env python3
"""voynann-video-editor · transcribe.py

Speech -> text -> word-level timestamps, fully offline after a one-time model download.

  1. Text:       Whisper large-v3-turbo (int8 ONNX via sherpa-onnx). Audio is split at quiet points into
                 windows of <= 28 s because Whisper only sees 30 s at a time.
  2. Timing:     CTC forced alignment of that exact text against Meta's Omnilingual-ASR 300M CTC model
                 (character-level, 1600+ languages, 20 ms frames). Viterbi alignment is done in numpy.
  3. Fallback:   --script FILE skips Whisper and aligns the given script (e.g. the teleprompter text).
                 If the alignment model is unavailable, word times are spread over detected speech by
                 syllable count (marked "method": "proportional" so later steps know it is approximate).

Usage
  python3 transcribe.py --setup                          # download + unpack models once (~860 MB)
  python3 transcribe.py work/audio16k.wav --lang id --out work/transcript.json
  python3 transcribe.py work/audio16k.wav --script script.txt --out work/transcript.json
  python3 transcribe.py work/audio16k.wav --chunk 12 ...   # force smaller windows (testing)
  Long audio: stops at --budget seconds (default 170) with NOT FINISHED; re-run the same command to resume.

Output JSON
  {"text", "language", "method", "duration",
   "words": [{"i", "word", "start", "end", "conf"}], "sentences": [{"start", "end", "text", "w0", "w1"}]}
Times are seconds on the SOURCE timeline. Never invent words: if Whisper returns nothing, the file says so.
"""
import argparse, json, math, os, re, shutil, subprocess, sys, tarfile, time, unicodedata
from pathlib import Path

import numpy as np

RELEASE = "https://github.com/k2-fsa/sherpa-onnx/releases/download/asr-models"
MODELS = {
    "whisper": {
        "archive": "sherpa-onnx-whisper-turbo.tar.bz2",
        "dir": "sherpa-onnx-whisper-turbo",
        "files": ["turbo-encoder.int8.onnx", "turbo-decoder.int8.onnx", "turbo-tokens.txt"],
    },
    "ctc": {
        "archive": "sherpa-onnx-omnilingual-asr-1600-languages-300M-ctc-int8-2025-11-12.tar.bz2",
        "dir": "sherpa-onnx-omnilingual-asr-1600-languages-300M-ctc-int8-2025-11-12",
        "files": ["model.int8.onnx", "tokens.txt"],
    },
}
SR = 16000
FRAME = 0.02  # CTC frame hop (wav2vec2: 320 samples @ 16 kHz)


def models_dir(arg=None):
    d = Path(arg or os.environ.get("VY_MODELS_DIR") or Path.home() / ".cache" / "voynann-models")
    d.mkdir(parents=True, exist_ok=True)
    return d


def log(*a):
    print("[transcribe]", *a, file=sys.stderr, flush=True)


# ----------------------------------------------------------------------------- model download
def ensure_model(key, mdir):
    spec = MODELS[key]
    target = mdir / spec["dir"]
    if all((target / f).exists() for f in spec["files"]) and (target / ".ok").exists():
        return target
    arc = mdir / spec["archive"]
    url = f"{RELEASE}/{spec['archive']}"
    log(f"downloading {spec['archive']} (one time) ...")
    if shutil.which("curl"):
        r = subprocess.run(["curl", "-fsSL", "--retry", "3", "-C", "-", "-o", str(arc), url])
        if r.returncode not in (0, 33):  # 33 = range not supported -> already complete
            raise RuntimeError(f"download failed ({url}); network may block github.com release assets")
    else:
        import urllib.request
        urllib.request.urlretrieve(url, arc)
    log(f"unpacking {arc.name} ...")
    wanted = {f"{spec['dir']}/{f}" for f in spec["files"]}
    with tarfile.open(arc, "r:bz2") as tf:
        for m in tf:
            if m.name in wanted:
                tf.extract(m, mdir, filter="data") if sys.version_info >= (3, 12) else tf.extract(m, mdir)
    (target / ".ok").write_text("ok")
    arc.unlink(missing_ok=True)
    return target


def ensure_python_deps():
    try:
        import sherpa_onnx  # noqa: F401
        import onnxruntime  # noqa: F401
    except ImportError:
        log("installing sherpa-onnx + onnxruntime ...")
        subprocess.run([sys.executable, "-m", "pip", "install", "-q", "--break-system-packages",
                        "sherpa-onnx", "onnxruntime"], check=True)


# ----------------------------------------------------------------------------- audio
def load_audio(path):
    """Any audio/video -> mono float32 @16 kHz via ffmpeg (handles rotation-free audio streams)."""
    cmd = ["ffmpeg", "-v", "error", "-i", str(path), "-vn", "-ac", "1", "-ar", str(SR), "-f", "f32le", "-"]
    raw = subprocess.run(cmd, capture_output=True, check=True).stdout
    return np.frombuffer(raw, dtype=np.float32).copy()


def rms_env(x, hop=160, win=400):
    n = max(1, (len(x) - win) // hop + 1)
    idx = np.arange(win)[None, :] + hop * np.arange(n)[:, None]
    fr = x[np.minimum(idx, len(x) - 1)]
    return 20 * np.log10(np.sqrt((fr ** 2).mean(1)) + 1e-9)  # dBFS per 10 ms


def split_points(x, max_len):
    """Window boundaries (samples) at the quietest 10 ms frame inside the last 40% of each window."""
    if len(x) <= max_len * SR:
        return [(0, len(x))]
    env = rms_env(x)
    out, s = [], 0
    while len(x) - s > max_len * SR:
        lo, hi = int((s + 0.6 * max_len * SR) / 160), int((s + max_len * SR) / 160)
        cut = (lo + int(np.argmin(env[lo:hi]))) * 160
        out.append((s, cut)); s = cut
    out.append((s, len(x)))
    return out


# ----------------------------------------------------------------------------- text normalisation for alignment
ONES = ["nol", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"]


def num_id(n):
    """Integer -> Indonesian words (for alignment only; captions keep the digits)."""
    n = int(n)
    if n < 12: return ONES[n]
    if n < 20: return ONES[n - 10] + " belas"
    if n < 100: return ONES[n // 10] + " puluh" + ("" if n % 10 == 0 else " " + ONES[n % 10])
    if n < 200: return "seratus" + ("" if n == 100 else " " + num_id(n - 100))
    if n < 1000: return ONES[n // 100] + " ratus" + ("" if n % 100 == 0 else " " + num_id(n % 100))
    if n < 2000: return "seribu" + ("" if n == 1000 else " " + num_id(n - 1000))
    if n < 1_000_000: return num_id(n // 1000) + " ribu" + ("" if n % 1000 == 0 else " " + num_id(n % 1000))
    if n < 1_000_000_000: return num_id(n // 1_000_000) + " juta" + ("" if n % 1_000_000 == 0 else " " + num_id(n % 1_000_000))
    return " ".join(ONES[int(c)] for c in str(n))


# English loanwords as Indonesian speakers usually pronounce them. Used ONLY to help the aligner;
# the caption text is always the transcript word itself.
RESPELL = {
    "ai": "ei", "chat": "cet", "zoom": "zum", "sound": "saun", "effect": "efek", "effects": "efek",
    "subtitle": "saptaitel", "subtitles": "saptaitel", "b-roll": "biroll", "broll": "biroll", "let's": "les",
    "workflow": "wekflo", "skill": "skil", "skills": "skil", "creator": "kreator", "content": "konten",
    "video": "video", "upload": "aplod", "download": "daonlod", "prompt": "prom", "tools": "tuls", "tool": "tul",
    "editing": "editing", "editor": "editor", "online": "onlen", "follow": "folo", "like": "laik",
    "share": "syer", "comment": "komen", "link": "link", "bio": "baio", "tiktok": "tiktok", "youtube": "yutub",
}


def align_chars(word, vocab):
    w = word.lower().strip()
    core = re.sub(r"^[^\w]+|[^\w]+$", "", w)
    if core in RESPELL:
        w = RESPELL[core]
    w = re.sub(r"\d+", lambda m: " " + num_id(m.group()) + " ", w)
    w = unicodedata.normalize("NFKD", w)
    w = "".join(c for c in w if not unicodedata.combining(c))
    return [c for c in w if c in vocab and c != " "]


def tokenize_words(text):
    return [w for w in re.split(r"\s+", text.strip()) if w]


# ----------------------------------------------------------------------------- whisper
def whisper_recognizer(lang, mdir):
    import sherpa_onnx as so
    d = ensure_model("whisper", mdir)
    return so.OfflineRecognizer.from_whisper(
        encoder=str(d / "turbo-encoder.int8.onnx"), decoder=str(d / "turbo-decoder.int8.onnx"),
        tokens=str(d / "turbo-tokens.txt"), language=lang, task="transcribe",
        num_threads=max(1, min(4, os.cpu_count() or 2)), tail_paddings=-1)


def whisper_window(rec, x, a, b):
    st = rec.create_stream(); st.accept_waveform(SR, x[a:b]); rec.decode_stream(st)
    txt = st.result.text.strip()
    log(f"whisper {a / SR:6.2f}-{b / SR:6.2f}s: {txt[:70]}{'...' if len(txt) > 70 else ''}")
    return txt


# ----------------------------------------------------------------------------- CTC forced alignment
class CTC:
    def __init__(self, mdir):
        import onnxruntime as ort
        d = ensure_model("ctc", mdir)
        so_ = ort.SessionOptions(); so_.intra_op_num_threads = max(1, min(4, os.cpu_count() or 2))
        self.sess = ort.InferenceSession(str(d / "model.int8.onnx"), so_)
        self.vocab = {}
        for line in open(d / "tokens.txt", encoding="utf-8"):
            line = line.rstrip("\n")
            if not line: continue
            t, i = line.rsplit(" ", 1)
            self.vocab[t if t else " "] = int(i)
        self.blank, self.space = 0, self.vocab.get(" ", 4)

    def _lp(self, x):
        x = (x - x.mean()) / (x.std() + 1e-7)
        lg = self.sess.run(None, {"x": x[None].astype(np.float32)})[0][0].astype(np.float64)
        lg -= lg.max(1, keepdims=True)
        return lg - np.log(np.exp(lg).sum(1, keepdims=True))

    def logprobs(self, x, win=20.0, pad=1.0):
        """Frame log-probs. Inputs longer than 30 s run in 20 s chunks (+1 s context each side) so memory stays
        flat: the model's attention grows with the square of the input length."""
        if len(x) <= 30 * SR:
            return self._lp(x)
        hop, W, P = int(FRAME * SR), int(win * SR), int(pad * SR)
        out = []
        for s in range(0, len(x), W):
            a, b = max(0, s - P), min(len(x), s + W + P)
            lp = self._lp(x[a:b])
            f0 = (s - a) // hop
            out.append(lp[f0:f0 + (min(len(x), s + W) - s) // hop])
        return np.concatenate(out)

    def align(self, lp, ids):
        """Viterbi CTC alignment. Space is merged into blank so word gaps never constrain the path.
        Returns per-target-token (first_frame, last_frame, mean_prob)."""
        T, L = lp.shape[0], len(ids)
        if L == 0: return []
        blank = np.logaddexp(lp[:, self.blank], lp[:, self.space])
        S = 2 * L + 1
        ext = np.full(S, -1); ext[1::2] = ids
        em = np.empty((T, S)); em[:, 0::2] = blank[:, None]; em[:, 1::2] = lp[:, ids]
        skip = np.zeros(S, bool)
        skip[3::2] = np.array(ids[1:]) != np.array(ids[:-1])
        NEG = -1e18
        dp = np.full(S, NEG); dp[0] = em[0, 0]; dp[1] = em[0, 1]
        bp = np.zeros((T, S), np.int8)
        for t in range(1, T):
            a = dp
            b = np.concatenate(([NEG], dp[:-1]))
            c = np.concatenate(([NEG, NEG], dp[:-2])); c = np.where(skip, c, NEG)
            stack = np.stack([a, b, c]); arg = stack.argmax(0)
            dp = stack[arg, np.arange(S)] + em[t]
            bp[t] = arg
        s = S - 1 if dp[S - 1] >= dp[S - 2] else S - 2
        path = np.empty(T, int)
        for t in range(T - 1, -1, -1):
            path[t] = s; s -= int(bp[t, s])
        res = [[None, None, []] for _ in range(L)]
        for t, s in enumerate(path):
            if s % 2 == 1:
                j = s // 2
                if res[j][0] is None: res[j][0] = t
                res[j][1] = t; res[j][2].append(math.exp(em[t, s]))
        out = []
        for f0, f1, ps in res:
            out.append((f0, f1, float(np.mean(ps)) if ps else 0.0))
        return out


def align_words(words, x, ctc, offset):
    lp = ctc.logprobs(x)
    ids, owner = [], []
    for wi, w in enumerate(words):
        for c in align_chars(w, ctc.vocab):
            ids.append(ctc.vocab[c]); owner.append(wi)
    if len(ids) > lp.shape[0]:
        raise RuntimeError("text longer than audio allows; transcript and audio do not match")
    spans = ctc.align(lp, ids)
    res = [None] * len(words)
    for (f0, f1, p), wi in zip(spans, owner):
        if f0 is None: continue
        r = res[wi]
        res[wi] = [f0, f1, [p]] if r is None else [min(r[0], f0), max(r[1], f1), r[2] + [p]]
    out = []
    for w, r in zip(words, res):
        if r is None:
            out.append({"word": w, "start": None, "end": None, "conf": 0.0})
        else:
            out.append({"word": w, "start": round(offset + r[0] * FRAME, 3),
                        "end": round(offset + (r[1] + 1) * FRAME, 3), "conf": round(float(np.mean(r[2])), 3)})
    return out


def syllables(w):
    return max(1, len(re.findall(r"[aiueoy]+", w.lower())))


def proportional(words, x, offset):
    """Fallback without an alignment model: spread words over voiced regions by syllable count."""
    env = rms_env(x)
    voiced = env > (np.percentile(env, 90) - 25)
    tv = np.flatnonzero(voiced)
    if len(tv) == 0:
        return [{"word": w, "start": None, "end": None, "conf": 0.0} for w in words]
    cum = np.cumsum(voiced)  # voiced time index -> frame
    total = cum[-1]
    weights = np.array([syllables(w) for w in words], float); edges = np.concatenate(([0], np.cumsum(weights))) / weights.sum()
    def at(frac):
        k = int(np.searchsorted(cum, max(1, frac * total)))
        return offset + min(k, len(env) - 1) * 0.01
    return [{"word": w, "start": round(at(edges[i]), 3), "end": round(at(edges[i + 1]), 3), "conf": 0.0}
            for i, w in enumerate(words)]


def fill_gaps(words, dur):
    """Interpolate words the aligner could not place (symbols only, etc.)."""
    for i, w in enumerate(words):
        if w["start"] is None:
            prev = next((words[j]["end"] for j in range(i - 1, -1, -1) if words[j]["end"] is not None), 0.0)
            nxt = next((words[j]["start"] for j in range(i + 1, len(words)) if words[j]["start"] is not None), dur)
            w["start"], w["end"] = round(prev, 3), round(max(prev + 0.05, min(nxt, prev + 0.3)), 3)
    return words


def sentences(words):
    out, w0 = [], 0
    for i, w in enumerate(words):
        if re.search(r"[.!?]$", w["word"]) or i == len(words) - 1:
            out.append({"start": words[w0]["start"], "end": words[i]["end"],
                        "text": " ".join(x["word"] for x in words[w0:i + 1]), "w0": w0, "w1": i})
            w0 = i + 1
    return out


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("audio", nargs="?")
    ap.add_argument("--out", default="transcript.json")
    ap.add_argument("--lang", default="id", help="Whisper language code (id, en, ms, jv, ...)")
    ap.add_argument("--script", help="text file to align instead of running Whisper")
    ap.add_argument("--models-dir")
    ap.add_argument("--chunk", type=float, default=28.0, help="max Whisper window in seconds (<=29)")
    ap.add_argument("--setup", action="store_true", help="only install deps + download models")
    ap.add_argument("--no-align", action="store_true", help="skip CTC alignment (proportional timing)")
    ap.add_argument("--budget", type=float, default=float(os.environ.get("VY_BUDGET", 170)),
                    help="seconds; stops cleanly (exit 2, NOT FINISHED) and resumes on the next identical run")
    a = ap.parse_args()
    mdir = models_dir(a.models_dir)
    ensure_python_deps()
    if a.setup:
        t0 = time.time()
        for k in MODELS:
            done = (mdir / MODELS[k]["dir"] / ".ok").exists()
            if not done and time.time() - t0 > 45:
                print("SETUP PARTIAL - run the same command again to fetch the next model"); sys.exit(2)
            ensure_model(k, mdir)
            log(f"{k} model ready")
        print("SETUP OK", mdir); return
    if not a.audio: ap.error("audio file required")
    t0 = time.time()
    x = load_audio(a.audio); dur = len(x) / SR
    chunk = min(max(a.chunk, 5.0), 29.0)
    script_text = " ".join(tokenize_words(Path(a.script).read_text(encoding="utf-8"))) if a.script else None
    windows = [(0, len(x))] if a.script else split_points(x, chunk)
    source = "script" if a.script else "whisper-large-v3-turbo"
    # resume state: finished windows are kept in <out>.partial.json until the transcript is complete
    part = Path(a.out).with_suffix(".partial.json")
    key = {"audio": str(Path(a.audio).resolve()), "bytes": Path(a.audio).stat().st_size, "lang": a.lang,
           "script": script_text, "chunk": chunk, "no_align": a.no_align}
    state = {"key": key, "done": {}}
    if part.exists():
        try:
            prev = json.loads(part.read_text())
            if prev.get("key") == key:
                state = prev; log(f"resuming: {len(state['done'])}/{len(windows)} window(s) already done")
        except Exception:  # noqa: BLE001
            pass
    Path(a.out).parent.mkdir(parents=True, exist_ok=True)
    rec, ctc, ctc_tried = None, None, False
    all_words, methods = [], set()
    for s, e in windows:
        wk = f"{s}-{e}"
        if wk in state["done"]:
            all_words += state["done"][wk]["words"]; methods.add(state["done"][wk]["method"]); continue
        if time.time() - t0 > a.budget:
            print(f"NOT FINISHED (time budget): {len(state['done'])}/{len(windows)} windows done. "
                  "Run the same command again to continue."); sys.exit(2)
        if script_text is not None:
            txt = script_text
        else:
            rec = rec or whisper_recognizer(a.lang, mdir)
            txt = whisper_window(rec, x, s, e)
        ws = tokenize_words(txt)
        words, m = [], "ctc-forced-alignment"
        if ws:
            if not a.no_align and not ctc_tried:
                ctc_tried = True
                try:
                    ctc = CTC(mdir)
                except Exception as ex:  # noqa: BLE001
                    log(f"alignment model unavailable ({ex}); falling back to proportional timing")
            if ctc is not None:
                try:
                    words = align_words(ws, x[s:e], ctc, s / SR)
                except Exception as ex:  # noqa: BLE001
                    log(f"alignment failed for window {s / SR:.1f}s ({ex}); proportional timing used")
            if not words:
                m = "proportional"; words = proportional(ws, x[s:e], s / SR)
        state["done"][wk] = {"words": words, "method": m}
        part.write_text(json.dumps(state, ensure_ascii=False))
        all_words += words; methods.add(m)
    method = "proportional" if "proportional" in methods else "ctc-forced-alignment"
    all_words = fill_gaps(all_words, dur)
    for i, w in enumerate(all_words): w["i"] = i
    out = {
        "text": " ".join(w["word"] for w in all_words), "language": a.lang, "source": source,
        "method": method, "duration": round(dur, 3), "words": all_words, "sentences": sentences(all_words),
        "low_confidence": [w["i"] for w in all_words if w["conf"] < 0.15],
    }
    Path(a.out).parent.mkdir(parents=True, exist_ok=True)
    Path(a.out).write_text(json.dumps(out, ensure_ascii=False, indent=1))
    part.unlink(missing_ok=True)
    log(f"{len(all_words)} words, method={method}, {time.time() - t0:.1f}s -> {a.out}")
    if not all_words:
        print("NO SPEECH FOUND - do not invent captions", file=sys.stderr); sys.exit(4)


if __name__ == "__main__":
    main()
