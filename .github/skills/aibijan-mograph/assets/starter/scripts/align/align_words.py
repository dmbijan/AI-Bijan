#!/usr/bin/env python3
"""VOYNANN MOGRAPH · align_words.py - word-level timestamps for a known narration script.

Aligns the cleaned script against the real voice-over with CTC forced alignment (Omnilingual-ASR 300M CTC,
Apache-2.0, 20 ms frames, 1600+ languages including Indonesian). Fully offline after a one-time ~350 MB
model download; the model cache (~/.cache/voynann-models) is shared with VOYNANN Video Editor.

Usage (normally through `npm run align`, which also prepares the Python environment):
  python align_words.py --setup                                   # download the model once
  python align_words.py VO.mp3 input/script.clean.txt --out src/words.json

Output JSON
  {"method", "duration", "words": [{"i", "word", "start", "end", "conf"}],
   "sentences": [{"start", "end", "text", "w0", "w1"}], "low_confidence": [i, ...]}
Times are seconds on the voice-over timeline (= composition seconds when the VO starts at frame 0).
If the model is unavailable, words are spread over the detected speech by syllable count and "method" is
"proportional", so later steps know the timing is approximate. Words are never invented or changed.
"""
import argparse, json, math, os, re, shutil, subprocess, sys, tarfile, time, unicodedata
from pathlib import Path

import numpy as np

RELEASE = "https://github.com/k2-fsa/sherpa-onnx/releases/download/asr-models"
CTC_MODEL = {
    "archive": "sherpa-onnx-omnilingual-asr-1600-languages-300M-ctc-int8-2025-11-12.tar.bz2",
    "dir": "sherpa-onnx-omnilingual-asr-1600-languages-300M-ctc-int8-2025-11-12",
    "files": ["model.int8.onnx", "tokens.txt"],
}
SR = 16000
FRAME = 0.02  # CTC frame hop (wav2vec2: 320 samples @ 16 kHz)


def log(*a):
    print("[align]", *a, file=sys.stderr, flush=True)


def models_dir(arg=None):
    d = Path(arg or os.environ.get("VY_MODELS_DIR") or Path.home() / ".cache" / "voynann-models")
    d.mkdir(parents=True, exist_ok=True)
    return d


def ensure_model(mdir):
    spec = CTC_MODEL
    target = mdir / spec["dir"]
    if all((target / f).exists() for f in spec["files"]) and (target / ".ok").exists():
        return target
    arc = mdir / spec["archive"]
    url = f"{RELEASE}/{spec['archive']}"
    log(f"downloading the word-alignment model (~350 MB, one time) ...")
    if shutil.which("curl"):
        r = subprocess.run(["curl", "-fsSL", "--retry", "3", "-C", "-", "-o", str(arc), url])
        if r.returncode not in (0, 33):  # 33 = range not supported -> already complete
            raise RuntimeError(f"download failed ({url}); the network may block github.com release assets")
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


# ----------------------------------------------------------------------------- audio
def load_audio(path):
    """Any audio/video -> mono float32 @16 kHz via ffmpeg."""
    cmd = ["ffmpeg", "-v", "error", "-i", str(path), "-vn", "-ac", "1", "-ar", str(SR), "-f", "f32le", "-"]
    raw = subprocess.run(cmd, capture_output=True, check=True).stdout
    return np.frombuffer(raw, dtype=np.float32).copy()


def rms_env(x, hop=160, win=400):
    n = max(1, (len(x) - win) // hop + 1)
    idx = np.arange(win)[None, :] + hop * np.arange(n)[:, None]
    fr = x[np.minimum(idx, len(x) - 1)]
    return 20 * np.log10(np.sqrt((fr ** 2).mean(1)) + 1e-9)  # dBFS per 10 ms


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
    if n < 1_000_000_000_000: return num_id(n // 1_000_000_000) + " miliar" + ("" if n % 1_000_000_000 == 0 else " " + num_id(n % 1_000_000_000))
    return " ".join(ONES[int(c)] for c in str(n))


# English loanwords as Indonesian narrators usually pronounce them. Used ONLY to help the aligner;
# the caption text is always the script word itself.
RESPELL = {
    "ai": "ei", "chat": "cet", "zoom": "zum", "sound": "saun", "effect": "efek", "effects": "efek",
    "subtitle": "saptaitel", "subtitles": "saptaitel", "workflow": "wekflo", "skill": "skil", "skills": "skil",
    "creator": "kreator", "content": "konten", "upload": "aplod", "download": "daonlod", "online": "onlen",
    "follow": "folo", "like": "laik", "share": "syer", "comment": "komen", "youtube": "yutub", "what": "wat",
    "if": "if", "update": "apdet", "trend": "tren", "viral": "viral", "cashless": "kesles", "qris": "kris",
}


def align_chars(word, vocab):
    w = word.lower().strip()
    core = re.sub(r"^[^\w]+|[^\w]+$", "", w)
    if core in RESPELL:
        w = RESPELL[core]
    # Rp5.000.000 -> "rupiah lima juta"; 2.500 -> 2500 (Indonesian thousands separator)
    w = re.sub(r"^rp\.?\s*(?=\d)", "rupiah ", w)
    w = re.sub(r"(?<=\d)\.(?=\d{3}\b)", "", w)
    w = w.replace("%", " persen")
    w = re.sub(r"\d+", lambda m: " " + num_id(m.group()) + " ", w)
    w = unicodedata.normalize("NFKD", w)
    w = "".join(c for c in w if not unicodedata.combining(c))
    return [c for c in w if c in vocab and c != " "]


def tokenize_words(text):
    return [w for w in re.split(r"\s+", text.strip()) if w]


# ----------------------------------------------------------------------------- CTC forced alignment
class CTC:
    def __init__(self, mdir):
        import onnxruntime as ort
        d = ensure_model(mdir)
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
        """Frame log-probs; inputs longer than 30 s run in 20 s chunks (+1 s context) so memory stays flat."""
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
        """Viterbi CTC alignment. Space is merged into blank so word gaps never constrain the path."""
        T, L = lp.shape[0], len(ids)
        if L == 0: return []
        blank = np.logaddexp(lp[:, self.blank], lp[:, self.space])
        S = 2 * L + 1
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
        return [(f0, f1, float(np.mean(ps)) if ps else 0.0) for f0, f1, ps in res]


def align_words(words, x, ctc):
    lp = ctc.logprobs(x)
    ids, owner = [], []
    for wi, w in enumerate(words):
        for c in align_chars(w, ctc.vocab):
            ids.append(ctc.vocab[c]); owner.append(wi)
    if len(ids) > lp.shape[0]:
        raise RuntimeError("the script is longer than the audio allows; script and voice-over do not match")
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
            out.append({"word": w, "start": round(r[0] * FRAME, 3), "end": round((r[1] + 1) * FRAME, 3),
                        "conf": round(float(np.mean(r[2])), 3)})
    return out


def syllables(w):
    return max(1, len(re.findall(r"[aiueoy]+", w.lower())))


def proportional(words, x):
    """Fallback without the alignment model: spread words over voiced regions by syllable count."""
    env = rms_env(x)
    voiced = env > (np.percentile(env, 90) - 25)
    if not voiced.any():
        return [{"word": w, "start": None, "end": None, "conf": 0.0} for w in words]
    cum = np.cumsum(voiced); total = cum[-1]
    weights = np.array([syllables(w) for w in words], float)
    edges = np.concatenate(([0], np.cumsum(weights))) / weights.sum()
    def at(frac):
        k = int(np.searchsorted(cum, max(1, frac * total)))
        return min(k, len(env) - 1) * 0.01
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
        if re.search(r"[.!?…]$", w["word"]) or i == len(words) - 1:
            out.append({"start": words[w0]["start"], "end": words[i]["end"],
                        "text": " ".join(x["word"] for x in words[w0:i + 1]), "w0": w0, "w1": i})
            w0 = i + 1
    return out


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("audio", nargs="?")
    ap.add_argument("script", nargs="?")
    ap.add_argument("--out", default="src/words.json")
    ap.add_argument("--models-dir")
    ap.add_argument("--setup", action="store_true", help="only download the alignment model")
    a = ap.parse_args()
    mdir = models_dir(a.models_dir)
    if a.setup:
        ensure_model(mdir); print("SETUP OK", mdir); return
    if not a.audio or not a.script:
        ap.error("voice-over and script are required")
    t0 = time.time()
    x = load_audio(a.audio); dur = len(x) / SR
    ws = tokenize_words(Path(a.script).read_text(encoding="utf-8"))
    if not ws:
        print("EMPTY SCRIPT - nothing to align", file=sys.stderr); sys.exit(4)
    method, words = "ctc-forced-alignment", []
    try:
        words = align_words(ws, x, CTC(mdir))
    except Exception as ex:  # noqa: BLE001
        log(f"alignment unavailable ({ex}); using proportional (estimated) timing")
    if not words:
        method, words = "proportional", proportional(ws, x)
    words = fill_gaps(words, dur)
    for i, w in enumerate(words): w["i"] = i
    out = {"method": method, "duration": round(dur, 3), "words": words, "sentences": sentences(words),
           "low_confidence": [w["i"] for w in words if w["conf"] < 0.15]}
    Path(a.out).parent.mkdir(parents=True, exist_ok=True)
    Path(a.out).write_text(json.dumps(out, ensure_ascii=False, indent=1), encoding="utf-8")
    log(f"{len(words)} words, method={method}, {time.time() - t0:.1f}s -> {a.out}")


if __name__ == "__main__":
    main()
