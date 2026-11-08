#!/usr/bin/env python3
"""voynann-video-editor · plan-edit.py

Turns (media.json + transcript.json + optional creative.json) into a fully timed edit.json that the
Remotion compositor renders. Reusable logic lives here; footage-specific choices live in creative.json.

  python3 plan-edit.py --work work/ --style impact                      # automatic edit (cuts, captions, zooms, SFX, music)
  python3 plan-edit.py --work work/ --creative work/creative.json       # + Claude's scene plan (overlays, layouts, camera)
  python3 plan-edit.py --work work/ --intensity high --aspect 1:1 --target-duration 30 --music disabled

What it does
  * Pause removal: gaps between words longer than the intensity threshold are shortened to natural padding.
    Cuts are frame-snapped, never inside a word. Explicit retake removal via creative "remove_words".
  * Timeline map source -> edited time; every word re-timed.
  * Caption pages (style rules), emphasis words, caption zones (positions/variants/hidden ranges).
  * Camera: face-anchored framing presets (wide/talk/close), punch-ins, automatic jump-cut zoom alternation.
  * Layout keyframes (full / raw-full / split / card / pip / none) with morph, wipe or cut transitions.
  * Overlays with time references, real waveform peaks, film-strip thumbnail jobs.
  * Sound cues: explicit + automatic per overlay/transition, scaled by the sound_effects level. Music bed settings.
  * Lint: platform safe zones, density, overlaps -> printed and stored in edit.json["lint"].
  v2 (editor behaviour + anti-slop)
  * Filler words ("eh", "emm", "anu", "um", "uh") are cut automatically (settings.remove_fillers).
  * Retakes (a phrase restarted within a few seconds) are detected and printed as RETAKE? suggestions;
    creative "auto_retakes": true applies them.
  * Cut transitions at jump cuts (zoom-through / whip / push / flash / leak) per style, spaced and varied.
  * Rhythm: no stretch longer than the style's maxStatic without a visual change -> slow camera pushes are
    inserted automatically; anything left is reported.
  * SLOP lint: double text, ungrounded words, over-emphasis, graphics overload, unreadable flashes, SFX spam,
    repetition... printed as SLOP: lines and stored in edit.json["slop"]. Fix every SLOP line.

Time references accepted anywhere in creative.json:
  "w:12"  start of transcript word 12      "w:12e"  end of word 12       "w:12+0.2" offsets allowed
  "s:4.5" source-time seconds (mapped)    "t:3.2"  edited-time seconds  "end" end of the edit
"""
import argparse, hashlib, json, math, re, sys, wave
from collections import Counter
from pathlib import Path

HERE = Path(__file__).resolve().parent
SKILL = HERE.parent

ASPECTS = {"9:16": (1080, 1920), "16:9": (1920, 1080), "1:1": (1080, 1080), "4:5": (1080, 1350)}
# Platform safe zones (fraction of height/width kept free of important text). 9:16 = Meta Reels/Stories ads guidance.
SAFE = {"9:16": {"top": 0.14, "bottom": 0.35, "side": 0.06}, "4:5": {"top": 0.06, "bottom": 0.12, "side": 0.05},
        "1:1": {"top": 0.05, "bottom": 0.1, "side": 0.05}, "16:9": {"top": 0.06, "bottom": 0.12, "side": 0.05}}
INTENSITY = {  # pause threshold (s), padding kept before/after words, jump-cut zoom on/off
    "low": {"pause": 0.75, "pre": 0.15, "post": 0.16, "jump": False, "punch": False},
    "medium": {"pause": 0.38, "pre": 0.12, "post": 0.12, "jump": True, "punch": True},
    "high": {"pause": 0.26, "pre": 0.09, "post": 0.10, "jump": True, "punch": True},
}
SFX_LEVEL = {"off": 0, "subtle": 1, "medium": 2, "energetic": 3}
# Disfluencies a human editor always removes. Only whole, isolated tokens match ("anu" is a filler in spoken Indonesian).
FILLER_RE = re.compile(r"^(e+h*m*|e+m+|h+m+|u+m+|u+h+m*|e+r+m*|a+n+u+|mm+|eh+|ehm+)$")
TEXT_OVERLAYS = ("Badge", "KineticTitle", "Newsprint", "InsightCard", "EvidenceCard", "ScrapNote", "PromptBar", "FloatCard", "EndCard",
                 "Waveform", "LowerThird", "Steps", "Counter", "Tile", "Callout", "TitleCard", "Headline", "Sticker", "StickerStack",
                 "HeroTitle", "SceneStack", "SceneChat", "ScenePrompt", "SceneFeatures", "ScenePhones",
                 "SearchStack", "Shout", "StoryTitle", "SceneLens", "SceneBuild", "DepthText", "HeadRing", "GlowPill", "Checklist", "SideCards",
                 "HeadNote", "NoteTags", "NoteBoard", "NoteClips", "BigNote")
# PREMIUM PACK (v3.9): glossy 3D objects, glass pills, badge arcs, side cards, checklists
PREMIUM = ("Glossy", "GlowPill", "BadgeOrbit", "SideCards", "Checklist")
GLOSSY_KINDS = {"coin", "check", "cross", "star", "heart", "bolt", "up", "down", "plus"}
PREMIUM_SLOTS = {"above", "above-left", "above-right", "left", "right", "chest", "chest-left", "chest-right", "shoulder-left", "shoulder-right"}
GRAD_NAMES = {"fire", "gold", "ice", "mint", "rose", "violet", "silver"}
# NOTE PACK (v3.10): mono labels typed above the head, paper tags, the paper board, clip cards around the head (NOTED)
NOTES = ("HeadNote", "NoteTags", "NoteBoard", "NoteClips", "BigNote")
NOTE_MARKS = {"highlight", "circle", "underline", "none"}
NOTE_SURFACES = {"paper", "outline", "ink", "accent"}
NOTE_SLOTS = {"above", "above-left", "above-right", "left", "right", "chest"}
VIDEO_EXT = (".mp4", ".mov", ".m4v", ".webm", ".mkv")
# DEPTH PACK (v3.7): type behind the speaker, a ring of words around the head, hand-drawn doodles, the endless feed wall
DEPTH_MODES = {"stack", "echo", "wall", "split"}
TYPE_LOOKS = {"fill", "outline", "chrome", "gradient", "extrude", "glow", "lumen", "neon"}
FONT_ROLES = {"display", "accent", "serif", "condensed", "ui", "script"}
DOODLE_KINDS = {"sparks", "swirl", "question", "exclaim", "halo", "circle", "shock", "crown", "bulb", "stars", "arrow", "scribble", "drops", "heart"}
CAMERA_HITS = {"shake", "glitch"}
# full-screen motion-graphic scenes (MOGRAPH MIX): they sit on the backdrop while the face is off screen
SCENES = ("SceneStack", "SceneChat", "ScenePrompt", "SceneFeatures", "ScenePhones", "SceneLens", "SceneBuild")
SHAPE_WIPES = ("orb", "bars", "bloom", "iris")
EMOJI_RE = re.compile("[\U0001F300-\U0001FAFF\u2600-\u27BF]")
TIMEREF = re.compile(r"^(?:(w):(\d+)(e?)|(s):(-?[\d.]+)|(t):(-?[\d.]+)|(end))([+-][\d.]+)?$")
TIME_KEYS = {"start", "end", "at", "cutAt", "subAt", "kicker", "wordmark", "button", "from", "to", "t", "hit_at", "break_at", "until", "tagAt", "ats", "swipes"}
# SceneBuild (scene kit): item kinds, figure poses, face slots; thing names are read from Things.jsx
KIT_KINDS = {"figure", "crowd", "thing", "icon", "chip", "text", "bubble", "card", "tag", "bar", "counter", "bars", "meter", "arrow", "steps", "frame", "rect", "circle"}
KIT_POSES = {None, "stand", "wave", "point", "cheer", "shrug", "hold"}
KIT_SLOTS = {"above", "above-left", "above-right", "left", "right", "chest", "chest-left", "chest-right"}
KIT_MOVES = {None, "rise", "pop", "drop", "slide", "grow", "slam", "fade", "none"}


def bundled_fonts():
    """Font families the compositor ships (assets/remotion/src/index.jsx). Brand kits can only use these."""
    try:
        txt = (SKILL / "assets" / "remotion" / "src" / "index.jsx").read_text(encoding="utf-8")
        return sorted(set(re.findall(r"\[\s*'([A-Za-z0-9]+)'\s*,\s*'[^']+\.(?:woff2|ttf|otf)'", txt)))
    except Exception:  # noqa: BLE001
        return []


def kit_things():
    try:
        txt = (SKILL / "assets" / "remotion" / "src" / "components" / "Things.jsx").read_text(encoding="utf-8")
        return set(re.findall(r"^  ([a-z]+): \(", txt, re.M))
    except Exception:  # noqa: BLE001
        return set()


def builtin_overlays():
    try:
        txt = (SKILL / "assets" / "remotion" / "src" / "components" / "Overlays.jsx").read_text(encoding="utf-8")
        body = re.search(r"const REGISTRY = \{(.*?)\};", txt, re.S).group(1)
        return {x.strip() for x in re.split(r"[,\s]+", re.sub(r"//[^\n]*", "", body)) if x.strip() and not x.strip().startswith("...")}
    except Exception:  # noqa: BLE001
        return set()


def load_style(name):
    p = SKILL / "styles" / f"{name.lower()}.md"
    if not p.exists():
        sys.exit(f"unknown style '{name}' (have: {', '.join(x.stem for x in (SKILL / 'styles').glob('*.md'))})")
    m = re.search(r"```json style-config\s*(\{.*?\})\s*```", p.read_text(encoding="utf-8"), re.S)
    return json.loads(m.group(1))


def deep_merge(a, b):
    out = dict(a)
    for k, v in (b or {}).items():
        out[k] = deep_merge(out[k], v) if isinstance(v, dict) and isinstance(out.get(k), dict) else v
    return out


# ----------------------------------------------------------------------------------------------- editor helpers
def norm_tok(w):
    return re.sub(r"[^\w]", "", str(w).lower())


def find_fillers(words):
    out = []
    for w in words:
        t = norm_tok(w["word"])
        if t and FILLER_RE.match(t) and (w["end"] - w["start"]) < 1.2:
            out.append(w["i"])
    return out


def find_retakes(words, max_gap=8.0):
    """A speaker restarting a phrase: words[i:i+n] reappears at j (i+n <= j <= i+n+6) within max_gap seconds.
    The abandoned attempt is words[i:j-1]. Returns [{remove:[i, j-1], repeat_at:j, text}] (longest match first)."""
    toks = [norm_tok(w["word"]) for w in words]
    found, used = [], set()
    for n in (6, 5, 4, 3):
        for i in range(len(words) - n):
            if any(k in used for k in range(i, i + n)): continue
            seq = toks[i:i + n]
            if not all(seq) or sum(len(x) for x in seq) < 8: continue
            for j in range(i + n, min(len(words) - n + 1, i + n + 7)):
                if toks[j:j + n] == seq and words[j]["start"] - words[i]["start"] < max_gap:
                    found.append({"remove": [words[i]["i"], words[j - 1]["i"]], "repeat_at": words[j]["i"],
                                  "text": " ".join(w["word"] for w in words[i:j])})
                    used.update(range(i, j)); break
    return sorted(found, key=lambda r: r["remove"][0])


# ----------------------------------------------------------------------------------------------- cutting
def plan_cuts(words, dur, fps, cfg, creative):
    trim = creative.get("trim", {})
    removed_words = set()
    for a, b in creative.get("remove_words", []):
        removed_words.update(range(int(a), int(b) + 1))
    kept = [w for w in words if w["i"] not in removed_words]
    if not kept:
        sys.exit("no words left to keep")
    last_i = trim.get("end_after_word")
    if last_i is not None:
        kept = [w for w in kept if w["i"] <= int(last_i)]
    first_i = trim.get("start_at_word")
    if first_i is not None:
        kept = [w for w in kept if w["i"] >= int(first_i)]
    start = max(0.0, kept[0]["start"] - trim.get("head", cfg["pre"]))
    end = min(dur, kept[-1]["end"] + trim.get("tail", 0.3))
    segs, cur = [], [start, None]
    removed = []
    for a, b in zip(kept, kept[1:]):
        gap = b["start"] - a["end"]
        explicit = any(a["i"] < r < b["i"] for r in removed_words)
        if gap > cfg["pause"] or explicit:
            cut_a, cut_b = a["end"] + cfg["post"], b["start"] - cfg["pre"]
            if cut_b - cut_a >= 0.1:
                cur[1] = cut_a; segs.append(cur); cur = [cut_b, None]
                removed.append({"after_word": a["i"], "src": [round(cut_a, 3), round(cut_b, 3)], "dur": round(cut_b - cut_a, 3),
                                "reason": "retake removed" if explicit else f"pause {gap:.2f}s"})
    cur[1] = end; segs.append(cur)
    # frame-snap, drop/merge tiny segments
    snapped = []
    for a, b in segs:
        a, b = round(a * fps) / fps, round(b * fps) / fps
        if b - a < 0.2 and snapped:
            snapped[-1][1] = b; continue
        snapped.append([a, b])
    out, o = [], 0.0
    for a, b in snapped:
        out.append({"src": [round(a, 4), round(b, 4)], "edit": [round(o, 4), round(o + b - a, 4)]})
        o += b - a
    return out, removed


def src_to_edit(segs, t):
    for s in segs:
        a, b = s["src"]
        if a - 1e-6 <= t <= b + 1e-6:
            return s["edit"][0] + (t - a)
    # time inside a removed gap -> snap to the next segment start (or the end)
    for s in segs:
        if t < s["src"][0]:
            return s["edit"][0]
    return segs[-1]["edit"][1]


def edit_to_src(segs, t):
    for s in segs:
        if s["edit"][0] - 1e-6 <= t <= s["edit"][1] + 1e-6:
            return s["src"][0] + (t - s["edit"][0])
    return segs[-1]["src"][1]


# ----------------------------------------------------------------------------------------------- captions
STOP = set("yang dan di ke dari ini itu aja nya gue lo kalian kita kami aku saya kamu ada untuk sama juga lagi udah sudah kalau the a to of in".split())


def display_words(words, glossary):
    """Apply glossary (multi-word, case-insensitive) -> display tokens that keep source indices."""
    keys = sorted(((k.lower().split(), v) for k, v in glossary.items()), key=lambda kv: -len(kv[0]))
    out, i = [], 0
    clean = lambda s: re.sub(r"[^\w'-]", "", s.lower())
    while i < len(words):
        hit = None
        for ks, v in keys:
            seq = [clean(w["word"]) for w in words[i:i + len(ks)]]
            if seq == [clean(k) for k in ks]:
                hit = (len(ks), v); break
        if hit:
            n, v = hit
            tail = re.sub(r"^.*?([.,!?;:]*)$", r"\1", words[i + n - 1]["word"])
            out.append({"src": list(range(i, i + n)), "text": v + tail, "start": words[i]["start"], "end": words[i + n - 1]["end"], "glossary": True})
            i += n
        else:
            out.append({"src": [i], "text": words[i]["word"], "start": words[i]["start"], "end": words[i]["end"]}); i += 1
    return out


def auto_emphasis(tok):
    core = re.sub(r"[^\w]", "", tok["text"])
    return tok.get("glossary") or (len(core) >= 2 and core.isupper()) or core.isdigit()


def build_pages(toks, S, zones, breaks=()):
    """Group words into caption pages. Breaks: punctuation, pauses, max words/chars/duration, zone changes,
    scene (layout) changes, and never two emphasis chips on one page."""
    pages, cur = [], []
    breaks = sorted(breaks)
    def zone_variant(t):
        v = None
        for z in zones:
            if z["from"] <= t < z["to"] and z.get("variant"): v = z["variant"]
        return v
    def zone_id(t):
        zid = -1
        for n, z in enumerate(zones):
            if z["from"] <= t < z["to"]: zid = n
        return zid
    def flush():
        if cur: pages.append(list(cur)); cur.clear()
    for k, w in enumerate(toks):
        var = zone_variant(w["start"])
        hook = var == "hook"
        mw = S.get("hookMaxWords", 2) if hook else S["maxWords"]
        mc = S.get("hookMaxChars", 13) if hook else S["maxChars"]
        if cur:
            prev = cur[-1]
            chars = sum(len(x["text"]) + 1 for x in cur) + len(w["text"])
            scene_cut = any(cur[0]["start"] + 0.01 < b <= w["start"] + 0.08 for b in breaks)
            if (len(cur) >= mw or chars > mc or w["start"] - prev["end"] > 0.35 or w["end"] - cur[0]["start"] > S.get("maxPageDur", 1.8)
                    or zone_id(cur[0]["start"]) != zone_id(w["start"]) or scene_cut
                    or (w.get("emph") and any(x.get("emph") for x in cur))):
                flush()
        cur.append(w)
        nxt = toks[k + 1] if k + 1 < len(toks) else None
        if re.search(r"[.!?]$", w["text"]):
            flush()
        elif re.search(r"[,;:]$", w["text"]) and (len(cur) >= 2 or w.get("emph") or (nxt and nxt["start"] - w["end"] > 0.15)):
            flush()
    flush()
    # page starts lead the first word by 60 ms, but never hide the previous page's last word before it is spoken
    starts = []
    for i, pg in enumerate(pages):
        st = pg[0]["start"] - 0.06
        if i > 0:
            st = max(st, min(pg[0]["start"], pages[i - 1][-1]["start"] + 0.08))
        starts.append(st)
    out = []
    for i, pg in enumerate(pages):
        en = pg[-1]["end"] + 0.45
        if i + 1 < len(pages): en = min(en, starts[i + 1])
        out.append({"start": round(starts[i], 3), "end": round(en, 3), "words": [
            {k: w[k] for k in ("text", "start", "end", "emph", "icon", "i") if k in w} for w in pg]})
    return out


# ----------------------------------------------------------------------------------------------- faces
def face_track(work, segs, fps, total):
    f = work / "faces.json"
    if not f.exists():
        return [[0.0, 0.5, 0.42, 0.2]], {"cx": 0.5, "cy": 0.42, "h": 0.2}
    F = json.loads(f.read_text())
    med = F["median"]
    S = F.get("samples") or []
    def at_src(t):
        if not S: return med["cx"], med["cy"], med["h"]
        if t <= S[0]["t"]: s = S[0]; return s["cx"], s["cy"], s["h"]
        for a, b in zip(S, S[1:]):
            if a["t"] <= t <= b["t"]:
                p = (t - a["t"]) / max(1e-6, b["t"] - a["t"])
                return tuple(a[k] + (b[k] - a[k]) * p for k in ("cx", "cy", "h"))
        s = S[-1]; return s["cx"], s["cy"], s["h"]
    # reject outliers far from the median (hands/mic false positives), then smooth within each segment
    out = []
    for s in segs:
        a, b = s["edit"]
        n = max(2, int((b - a) / 0.1) + 1)
        pts = []
        for k in range(n):
            te = a + (b - a) * k / (n - 1)
            cx, cy, h = at_src(edit_to_src(segs, te))
            if abs(cx - med["cx"]) > 0.18 or abs(cy - med["cy"]) > 0.15 or abs(h - med["h"]) > 0.6 * med["h"]:
                cx, cy, h = med["cx"], med["cy"], med["h"]
            pts.append([te, cx, cy, h])
        sm = []
        for k in range(len(pts)):
            lo, hi = max(0, k - 6), min(len(pts), k + 7)
            win = pts[lo:hi]
            sm.append([round(pts[k][0], 3)] + [round(sum(p[j] for p in win) / len(win), 4) for j in (1, 2, 3)])
        out += sm
    return out, {"cx": med["cx"], "cy": med["cy"], "h": med["h"]}


# ----------------------------------------------------------------------------------------------- audio peaks
def peaks(wav_path, a, b, bars=64):
    try:
        import numpy as np
        with wave.open(str(wav_path)) as w:
            sr = w.getframerate(); w.setpos(int(max(0, a) * sr))
            x = np.frombuffer(w.readframes(int((b - a) * sr)), dtype="<i2").astype(float) / 32768
        if x.size == 0: return [0.1] * bars
        chunks = np.array_split(np.abs(x), bars)
        v = np.array([np.sqrt(np.mean(c ** 2)) if c.size else 0 for c in chunks])
        v = v / (v.max() + 1e-9)
        return [round(float(max(0.04, min(1, x_ ** 0.7))), 3) for x_ in v]
    except Exception:  # noqa: BLE001
        return [0.3] * bars


# ----------------------------------------------------------------------------------------------- main planner
class Planner:
    def __init__(self, a):
        self.a = a
        self.work = Path(a.work)
        self.media = json.loads((self.work / "media.json").read_text())
        self.tr = json.loads(Path(a.transcript or self.work / "transcript.json").read_text())
        self.creative = json.loads(Path(a.creative).read_text()) if a.creative else {}
        C = self.creative
        self.lint = []
        # brand kit: a creator's saved identity (colours, fonts, own copy, default settings), reused on every video
        self.brand = {}
        bk = C.get("brand_kit")
        if bk:
            bp = Path(str(bk))
            if not bp.exists():
                sys.exit(f"brand_kit not found: {bp}")
            self.brand = json.loads(bp.read_text(encoding="utf-8"))
        B = self.brand
        style_name = (a.style or C.get("style") or B.get("defaults", {}).get("style") or "impact").lower()
        st = dict(load_style(style_name).get("defaults", {}))  # style defaults < brand kit < creative < CLI
        st.update({k: v for k, v in B.get("defaults", {}).items() if k not in ("style", "look")})
        st.update(C.get("settings", {}))
        if B:
            C["user_copy"] = list(C.get("user_copy", [])) + [str(x) for x in B.get("copy", [])]
            C["glossary"] = {**B.get("glossary", {}), **C.get("glossary", {})}
        self.settings = {
            "style": style_name,
            "editing_intensity": a.intensity or st.get("editing_intensity", "medium"),
            "aspect_ratio": a.aspect or st.get("aspect_ratio", "9:16"),
            "target_duration": a.target_duration or st.get("target_duration"),
            "caption_style": a.caption_style or st.get("caption_style", "kinetic"),
            "music": a.music or st.get("music", "enabled"),
            "sound_effects": a.sfx or st.get("sound_effects", "medium"),
            "visual_overlays": a.overlays or st.get("visual_overlays", "moderate"),
            "fps": 60,  # product rule: every style renders at 60 fps
            "remove_fillers": st.get("remove_fillers", True),
        }
        base = load_style(self.settings["style"])
        # look presets (e.g. MOGRAPH MIX "flat"): a named set of overrides shipped inside the style config
        bd = B.get("defaults", {})
        look = C.get("look") or C.get("settings", {}).get("look") or (bd.get("look") if bd.get("style", style_name) == style_name else None)
        if look and look != base.get("look"):
            if look not in base.get("looks", {}):
                sys.exit(f"style '{self.settings['style']}' has no look '{look}' (have: {', '.join([base.get('look', 'default')] + list(base.get('looks', {}))) })")
            base = deep_merge(base, base["looks"][look]); base["look"] = look
        base.pop("looks", None)
        self.settings["look"] = base.get("look")
        brand_over = {}
        if B:
            if B.get("colors"): brand_over["colors"] = B["colors"]
            if B.get("fonts"): brand_over["fonts"] = B["fonts"]
            brand_over = deep_merge(deep_merge(brand_over, B.get("style_overrides", {})), B.get("styles", {}).get(style_name, {}))
        self.style = deep_merge(deep_merge(base, brand_over), C.get("style_overrides", {}))
        fams = bundled_fonts()
        for k_, v_ in (self.style.get("fonts") or {}).items():
            if isinstance(v_, str) and fams and k_ in ("head", "body", "ui", "display", "serif") and v_ not in fams:
                self.lint.append(f"font '{v_}' ({k_}) is not bundled; use one of: {', '.join(fams)}")
        # creative mode: per-video components written for this video (copied into the render project by render-video.py)
        self.custom, self.custom_types = [], set()
        builtin = builtin_overlays()
        for k_, f_ in enumerate(C.get("custom_components", [])):
            fp = Path(str(f_)).resolve()
            if not fp.exists() or fp.suffix not in (".jsx", ".js"):
                self.lint.append(f"custom component not found or not .jsx: {fp}"); continue
            src = fp.read_text(encoding="utf-8")
            exports = re.findall(r"export\s+(?:const|function)\s+([A-Z]\w*)", src)
            if not exports:
                self.lint.append(f"{fp.name}: no exported component (export const MyScene = ({{E, t, o}}) => ...)"); continue
            for ex in exports:
                if ex in builtin: self.lint.append(f"{fp.name}: '{ex}' clashes with a built-in overlay; rename it")
            for pat, why in ((r"Math\.random\s*\(", "Math.random: frames render in parallel and would flicker; use rand(seed) from lib/kit.js"),
                             (r"Date\.now|new Date\(", "the clock: use t (seconds on the edited timeline) only"),
                             (r"\bfetch\s*\(|https?://", "network access/URLs: everything must be drawn locally"),
                             (r"useState|useEffect", "React state/effects: a component must be a pure function of t")):
                if re.search(pat, src): self.lint.append(f"{fp.name} uses {why}")
            self.custom.append({"file": str(fp), "name": f"u{k_}_" + re.sub(r"[^A-Za-z0-9_]", "_", fp.stem), "exports": exports,
                                "hash": hashlib.sha1(src.encode()).hexdigest()[:12]})
            self.custom_types.update(exports)
        global TEXT_OVERLAYS
        TEXT_OVERLAYS = tuple(dict.fromkeys(TEXT_OVERLAYS + tuple(sorted(self.custom_types))))
        self.builtin_types = builtin
        cs = self.settings["caption_style"]
        if cs == "clean": self.style["captions"]["variant"] = "clean"
        elif cs == "highlighted": self.style["captions"]["variant"] = "box"
        self.W, self.H = ASPECTS[self.settings["aspect_ratio"]]
        self.fps = self.settings["fps"]
        if self.fps != 60:
            raise ValueError(f"unsupported fps={self.fps}: this skill always renders at 60 fps")
        normalized_fps = self.media.get("normalized", {}).get("fps")
        if normalized_fps != self.fps:
            raise ValueError(f"media was normalized at {normalized_fps} fps but the edit requests {self.fps} fps. "
                             f"Re-run inspect-media.py INPUT --work WORK --normalize --fps {self.fps} "
                             "and create this edit again. Do not render a mismatched timeline.")
        self.slop = []
        self.suggestions = []
        self.extra_assets = []

    # -- time references ------------------------------------------------------------------------
    def T(self, ref):
        if isinstance(ref, (int, float)): return float(ref)
        m = TIMEREF.match(str(ref).strip())
        if not m: raise ValueError(f"bad time reference: {ref}")
        off = float(m.group(9)) if m.group(9) else 0.0
        if m.group(1):
            w = self.words_src[int(m.group(2))]
            base = src_to_edit(self.segs, w["end"] if m.group(3) else w["start"])
        elif m.group(4): base = src_to_edit(self.segs, float(m.group(5)))
        elif m.group(6): base = float(m.group(7))
        else: base = self.total
        return round(base + off, 3)

    def resolve(self, obj, key=None):
        if isinstance(obj, dict): return {k: self.resolve(v, k) for k, v in obj.items()}
        if isinstance(obj, list): return [self.resolve(v, key) for v in obj]
        if isinstance(obj, str) and (key in TIME_KEYS or (key or "").endswith("At")) and TIMEREF.match(obj.strip()): return self.T(obj)
        return obj

    # -- steps ----------------------------------------------------------------------------------
    def cut(self):
        cfg = dict(INTENSITY[self.settings["editing_intensity"]])
        self.words_src = self.tr["words"]
        C = self.creative
        rm = [list(map(int, r)) for r in C.get("remove_words", [])]
        keep_words = set(int(k) for k in C.get("keep_words", []))
        self.fillers = []
        if self.settings["remove_fillers"]:
            self.fillers = [i for i in find_fillers(self.words_src) if i not in keep_words]
            rm += [[i, i] for i in self.fillers]
        self.retakes = find_retakes(self.words_src)
        for r in self.retakes:
            applied = C.get("auto_retakes", False) or any(a <= r["remove"][0] and b >= r["remove"][1] for a, b in rm)
            if C.get("auto_retakes", False) and not any(a <= r["remove"][0] and b >= r["remove"][1] for a, b in rm):
                rm.append(r["remove"])
            msg = (f"RETAKE{'' if applied else '?'} words {r['remove'][0]}-{r['remove'][1]} \"{r['text']}\" is restarted at w:{r['repeat_at']}"
                   + ("" if applied else f" -> if it is a restart, add [{r['remove'][0]}, {r['remove'][1]}] to remove_words"))
            self.suggestions.append(msg)
        self.creative = C = {**C, "remove_words": rm}
        dur = self.media.get("normalized", {}).get("duration") or self.media["duration"]
        segs, removed = plan_cuts(self.words_src, dur, self.fps, cfg, self.creative)
        tgt = self.settings["target_duration"]
        hold = float(self.creative.get("end_hold", 0))
        while tgt and segs[-1]["edit"][1] + hold > float(tgt) and cfg["pause"] > 0.18:
            cfg["pause"] = max(0.18, cfg["pause"] - 0.05); cfg["pre"] = max(0.07, cfg["pre"] - 0.01); cfg["post"] = max(0.08, cfg["post"] - 0.01)
            segs, removed = plan_cuts(self.words_src, dur, self.fps, cfg, self.creative)
        self.cfg, self.segs, self.removed = cfg, segs, removed
        self.footage_dur = segs[-1]["edit"][1]
        self.total = round(self.footage_dur + hold, 3)
        if tgt and self.total > float(tgt) + 0.5:
            self.lint.append(f"target {tgt}s not reachable without cutting words (edit is {self.total:.2f}s); ask before removing sentences")

    def note_boards(self):
        """NoteBoard windows (edited seconds), read straight from creative.json: the footage is blurred behind the card, so
        automatic punch-ins, punch sounds and cut transitions skip those seconds."""
        if getattr(self, "_boards", None) is not None: return self._boards
        out, level = [], self.settings["visual_overlays"]
        for o in self.creative.get("overlays", []):
            if not isinstance(o, dict) or o.get("type") != "NoteBoard": continue
            if level == "minimal" and o.get("priority", 2) > 1: continue
            if level == "moderate" and o.get("priority", 2) > 2: continue
            try:
                a_ = self.T(o.get("start", o.get("at")))
                b_ = self.T(o["end"]) if o.get("end") is not None else self.total
            except Exception:  # noqa: BLE001
                continue
            out.append({"start": a_, "end": b_, "enter": o.get("enter", "cut"), "exit": o.get("exit", "cut")})
        self._boards = out
        return out

    def captions(self):
        C = self.creative
        S = self.style["captions"]
        toks = display_words(self.words_src, C.get("glossary", {}))
        emph = {}
        for e in C.get("emphasize", []):
            if isinstance(e, int): emph[e] = {}
            else: emph[int(e["w"])] = e
        auto = C.get("auto_emphasis", not C.get("emphasize"))
        for tk in toks:
            tk["i"] = tk["src"][0]
            tk["start"] = round(src_to_edit(self.segs, tk["start"]), 3)
            tk["end"] = round(src_to_edit(self.segs, tk["end"]), 3)
            hit = next((emph[s] for s in tk["src"] if s in emph), None)
            if hit is not None:
                tk["emph"] = True
                if hit.get("icon"): tk["icon"] = hit["icon"]
            elif auto and auto_emphasis(tk):
                tk["emph"] = True
        # drop tokens outside the kept footage (trimmed head/tail or removed retakes)
        keep = [tk for tk in toks if any(s["src"][0] - 0.02 <= self.words_src[tk["src"][0]]["start"] <= s["src"][1] for s in self.segs)]
        zones = []
        for z in self.resolve(C.get("captions", {}).get("zones", []), None):
            zz = {k: v for k, v in z.items()}
            zz["from"], zz["to"] = self.T(z["from"]), self.T(z["to"])
            zones.append(zz)
        self.tokens = keep
        sz = SAFE[self.settings["aspect_ratio"]]
        y_default = int(self.H * (1 - sz["bottom"]) - 4)  # caption bottom edge sits just inside the platform safe zone
        maxw = {"9:16": 920, "16:9": 1400, "1:1": 920, "4:5": 920}[self.settings["aspect_ratio"]]
        self.caps = {"pages": build_pages(keep, S, zones, self.scene_breaks()), "zones": zones,
                     "y": C.get("captions", {}).get("y", y_default), "maxWidth": C.get("captions", {}).get("maxWidth", maxw)}

    def scene_breaks(self):
        """Edited times where the layout changes (captions should not straddle a scene change)."""
        out = []
        for L in self.creative.get("layouts") or []:
            if L.get("transition") not in SHAPE_WIPES and (L.get("transition") == "cut" or L.get("preset") == "none"): continue
            try: out.append(self.T(L["at"]))
            except Exception: pass  # noqa: BLE001
        return out

    def camera(self):
        C = self.creative
        cam = self.style["camera"]
        smooth = bool(cam.get("smooth"))  # no snaps: eased punches, no zoom jump at cuts, pushes glide back
        faces, med = face_track(self.work, self.segs, self.fps, self.total)
        self.faces = faces
        # "talk" framing: lift the chin above the caption zone (clamped later so the frame stays covered)
        cap_top = self.caps["y"] - self.style["captions"]["size"] * 1.9
        s_talk = cam["talk"]
        chin_off = 0.55 * med["h"] * s_talk  # face-centre -> chin, as a fraction of canvas height
        fy_talk = max(0.28, min(0.5, (cap_top - 30) / self.H - chin_off))
        presets = {
            "wide": {"s": 1.0, "fx": None, "fy": None},
            "talk": {"s": s_talk, "fx": 0.5, "fy": round(fy_talk, 3)},
            "close": {"s": cam["close"], "fx": 0.5, "fy": round(max(0.3, fy_talk - 0.02), 3)},
        }
        self.presets = presets
        # where the face sits on the canvas in the default 'talk' framing (eyes -> mouth band), for the face-cover lint
        fw = (json.loads((self.work / "faces.json").read_text()).get("median", {}).get("w", 0.3) if (self.work / "faces.json").exists() else 0.3)
        # Mirror Footage.jsx ViewBox: cover-fit, anchor the face at (0.5, fy_talk), then clamp so no frame edge
        # shows. When the face sits high in the source (common), the clamp keeps it higher than fy_talk.
        vw, vh = self.media["video"]["display_width"], self.media["video"]["display_height"]
        c = max(self.W / vw, self.H / vh)
        VW, VH = vw * c * s_talk, vh * c * s_talk
        tx = min(0.0, max(self.W - VW, 0.5 * self.W - med["cx"] * VW))
        ty = min(0.0, max(self.H - VH, fy_talk * self.H - med["cy"] * VH))
        fcx, fcy = tx + med["cx"] * VW, ty + med["cy"] * VH
        fh_px, fw_px = med["h"] * VH, fw * VW
        self.face_box = (fcx - 0.32 * fw_px, fcy - 0.28 * fh_px, fcx + 0.32 * fw_px, fcy + 0.36 * fh_px)
        self.face_w, self.face_h_med = fw, med["h"]
        keys, last = [], dict(presets["talk"])
        ck = C.get("camera")
        if ck is None:
            # automatic camera: talk framing + punch-ins on emphasised words, spaced so they stay special
            ck = [{"at": "t:0", "preset": "talk"}]
            if self.cfg["punch"]:
                gap = 3.5 if self.settings["editing_intensity"] == "medium" else 2.4
                lastp = -9.0
                for tk in self.tokens:
                    if any(b["start"] - 0.1 <= tk["start"] <= b["end"] + 0.1 for b in self.note_boards()): continue  # footage hidden under a board
                    if tk.get("emph") and tk["start"] - lastp >= gap and tk["start"] > 0.8:
                        ck.append({"at": f"t:{tk['start']:.3f}", "preset": "punch", "hold": 0.45}); lastp = tk["start"]
        # resolve in time order: a punch pushes from (and relaxes back to) the framing active at its time,
        # whatever order the entries were written in
        ck = sorted(ck, key=lambda k_: self.T(k_["at"]))
        self.hits = []
        for k in ck:
            t = self.T(k["at"])
            if k.get("preset") in CAMERA_HITS:
                self.hits.append({"t": round(t, 3), "kind": k["preset"], "dur": k.get("dur", 0.3 if k["preset"] == "glitch" else 0.45), "amount": k.get("amount", 1.0)})
                continue
            if k.get("preset") == "punch":
                amt = k.get("amount", cam["punch"])
                # "smooth" camera styles (NOTED): the punch is a soft eased push and an eased release, never a snap
                pd = k.get("dur", cam.get("punchDur", 0.6) if smooth else 0.2)
                up = {"t": t, "dur": pd, "ease": k.get("ease", "camera" if smooth else "outExpo"), "s": round(last["s"] * (1 + amt), 4), "fx": last["fx"], "fy": last["fy"]}
                keys.append(up)
                hold = k.get("hold", 0.6) if not smooth else max(k.get("hold", 0.6), pd + 0.25)
                keys.append({"t": round(t + hold, 3), "dur": cam.get("relaxDur", 0.9) if smooth else 0.55, "ease": "camera" if smooth else "inOut",
                             "s": last["s"], "fx": last["fx"], "fy": last["fy"]})
                continue
            st = dict(presets.get(k.get("preset", "talk"), presets["talk"]))
            for f in ("s", "fx", "fy"):
                if f in k: st[f] = k[f]
            keys.append({"t": t, "dur": k.get("dur", 0.45 if keys else 0), "ease": k.get("ease", "camera"), **st})
            last = st
        keys.sort(key=lambda k: k["t"])
        if keys and keys[0]["t"] > 0:
            keys.insert(0, {"t": 0, "dur": 0, **presets["talk"]})
        # a key with fx None (wide/centre crop) is represented with explicit centre values for interpolation
        for k in keys:
            if k.get("fx") is None: k["fx"], k["fy"], k["center"] = 0.5, 0.5, True
        self.cam_keys = keys
        jz = []
        if self.cfg["jump"] and C.get("jump_zoom", not smooth):
            on = False
            for s in self.segs[1:]:
                on = not on
                jz.append([round(s["edit"][0], 4), cam["jumpZoom"] if on else 1.0])
        self.jump = jz

    def layouts(self):
        C = self.creative
        W, H = self.W, self.H
        presets = self.presets
        src_ar = self.media["video"]["display_width"] / self.media["video"]["display_height"]
        mismatch = (W / H) / src_ar > 1.6 or src_ar / (W / H) > 1.6  # e.g. 9:16 footage in a 16:9 canvas
        def view_full(grade=1):
            v = {"id": "main", "camera": True, "grade": grade}
            if mismatch: v["fit"] = "contain"
            return v
        def split(o):
            m, g = 52, 28
            w = (W - 2 * m - g) / 2; h = o.get("h", min(w * 16 / 9, H * 0.46))
            y = o.get("y", round(H * 0.155))
            left = {"id": o.get("left_id", "raw"), "rect": [m, y, round(w), round(h)], "radius": 34, "z": o.get("left_z", 1.0),
                    "fx": o.get("left_fx"), "fy": o.get("left_fy"), "grade": o.get("left_grade", 0), "shadow": 1,
                    "tag": o.get("left_tag", {"text": "MENTAH", "tone": "ink"}), "enter": {"dx": -120, "dy": 0, "s": 0.9, "o": 0}}
            right = {"id": o.get("right_id", "main"), "rect": [round(m + w + g), y, round(w), round(h)], "radius": 34, "z": o.get("z", 1.22), "fx": 0.5,
                     "fy": o.get("fy", 0.42), "grade": 1, "shadow": 1, "border": o.get("border", 6),
                     "tag": o.get("right_tag", {"text": "SETELAH", "tone": "accent", "icon": "sparkle"}), "enter": {"dx": 160, "dy": 0, "s": 0.9, "o": 0}}
            if o.get("only"):  # a single card is not a before/after pair: no default MENTAH/SETELAH tag
                if "right_tag" not in o: right["tag"] = None
                if "left_tag" not in o: left["tag"] = None
            if o.get("only") == "right": return [right]
            if o.get("only") == "left": return [left]
            return [left, right]
        PASS = ("rot", "halftone", "tape", "borderColor", "grade")
        def card(o):
            r = o.get("rect") or [90, round(H * 0.15), W - 180, round(H * 0.4)]
            v = {"id": "main", "rect": r, "radius": o.get("radius", 40), "z": o.get("z", 1.3), "fx": 0.5, "fy": o.get("fy", 0.45),
                 "grade": 1, "shadow": 1, "tag": o.get("tag"), "border": o.get("border", 0)}
            v.update({k: o[k] for k in PASS if k in o})
            return [v]
        def versus(o):
            # diagonal split screen: the same framing on both sides, raw grade left / edited grade right
            a, b = o.get("top", 0.6), o.get("bottom", 0.4)
            left = {"id": o.get("left_id", "raw"), "camera": True, "grade": o.get("left_grade", 0), "clip": f"0% 0%, {a * 100:.1f}% 0%, {b * 100:.1f}% 100%, 0% 100%"}
            right = {"id": o.get("right_id", "main"), "camera": True, "grade": 1, "clip": f"{a * 100:.1f}% 0%, 100% 0%, 100% 100%, {b * 100:.1f}% 100%"}
            return [left, right]
        def pip(o):
            d = o.get("size", 560)
            r = o.get("rect") or [(W - d) / 2, round(H * 0.16), d, d]
            return [{"id": "main", "rect": r, "radius": o.get("radius", d / 2), "z": o.get("z", 1.45), "fx": 0.5, "fy": o.get("fy", 0.5),
                     "grade": 1, "shadow": 1, "border": o.get("border", 8), "tag": o.get("tag")}]
        out = []
        self.shape_wipes = []
        for L in C.get("layouts") or []:
            t = self.T(L["at"])
            p = L.get("preset", "full")
            if p == "full": views = [view_full(L.get("grade", 1))]
            elif p == "raw-full": views = [{"id": "main", "camera": False, "z": 1.0, "fx": None, "grade": 0}]
            elif p == "split": views = split(L)
            elif p == "card": views = card(L)
            elif p == "pip": views = pip(L)
            elif p == "versus": views = versus(L)
            elif p == "none": views = []
            elif p == "custom": views = L["views"]
            else: raise ValueError(f"unknown layout preset {p}")
            for v in views:
                if v.get("tag"): v["tagAt"] = round(t + L.get("dur", 0.5) * 0.6, 3)
            # captions sit in a band above caps["y"]; a card whose bottom edge runs into that band collides with them
            cap_y = self.caps["y"]; settle = t + L.get("dur", 0.55)
            hidden = any(z.get("hidden") and z["from"] <= settle < z["to"] for z in self.caps["zones"])
            zone_y = next((z.get("y") for z in self.caps["zones"] if z.get("y") and z["from"] <= settle < z["to"]), None)
            if not hidden and zone_y is None:
                for v in views:
                    r = v.get("rect")
                    if r and r[1] + r[3] > cap_y - 120:
                        self.lint.append(f"layout '{p}' at {t:.2f}s: card bottom {r[1] + r[3]:.0f}px runs into the caption band "
                                         f"(keep it <= {cap_y - 120}px, e.g. rect y=300 h<={cap_y - 420}) or move captions with a zone 'y'")
                        break
            tr = L.get("transition", "morph")
            if tr in SHAPE_WIPES:
                # the switch happens under a full-frame cover (ShapeWipe overlay centred on t)
                self.shape_wipes.append({"at": t, "shape": tr, "sfx": L.get("sfx"), "color": L.get("color"), "color2": L.get("color2"), "hold": L.get("hold"),
                                         "x": L.get("x"), "y": L.get("y"), "dir": L.get("dir"), "bars": L.get("bars")})
                tr = "cut"
            key = {"t": t, "dur": 0 if tr == "cut" else L.get("dur", 0.55 if p != "none" else 0.4), "transition": tr,
                   "ease": L.get("ease", "inOutExpo"), "spring": L.get("spring", False), "views": views, "sfx": L.get("sfx")}
            if p == "versus" and L.get("divider", True) is not False:
                d = L.get("divider") if isinstance(L.get("divider"), dict) else {}
                key["divider"] = {"top": L.get("top", 0.6), "bottom": L.get("bottom", 0.4), "width": d.get("width", 14), "color": d.get("color", "#FFFFFF")}
            out.append(key)
        if not out or out[0]["t"] > 0:
            out.insert(0, {"t": 0, "dur": 0, "transition": "cut", "views": [view_full()]})
        out.sort(key=lambda x: x["t"])
        self.layout_keys = out

    def overlays(self):
        C = self.creative
        level = self.settings["visual_overlays"]
        ovs, thumbs = [], []
        voice = self.media.get("normalized", {}).get("voice48k")
        for i, o in enumerate(C.get("overlays", [])):
            if level == "minimal" and o.get("priority", 2) > 1: continue
            if level == "moderate" and o.get("priority", 2) > 2: continue
            r = self.resolve(o)
            if "start" not in r and "at" in r and not isinstance(r["at"], dict): r["start"] = r["at"]
            if r.get("type") == "Waveform":
                pf = o.get("peaks_from", {})
                a = float(pf.get("src_from", 0)); b = float(pf.get("src_to", a + 2))
                r["peaks"] = peaks(voice, a, b, pf.get("bars", 64)) if voice else [0.3] * 64
                if o.get("cuts") == "auto":
                    cuts, saved = [], 0.0
                    for rm in self.removed:
                        x0, x1 = rm["src"]
                        if x1 > a and x0 < b:
                            cuts.append([round(max(0, (x0 - a) / (b - a)), 4), round(min(1, (x1 - a) / (b - a)), 4)]); saved += rm["dur"]
                    r["cuts"] = cuts
                    r.setdefault("saved", f"{saved:.1f}s" if saved else "")
            if r.get("type") == "BRoll":
                src = Path(str(o.get("src", "")))
                if not src.exists():
                    self.lint.append(f"BRoll #{i}: file not found: {src} (use the user's uploaded file path)")
                    continue
                kind = "video" if src.suffix.lower() in (".mp4", ".mov", ".m4v", ".webm", ".mkv") else "image"
                stem = re.sub(r'[^A-Za-z0-9._-]', '_', src.stem)
                fn = f"broll/{i}_{stem}" + (".mp4" if kind == "video" else (src.suffix.lower() if src.suffix.lower() in (".jpg", ".jpeg", ".png", ".webp") else ".jpg"))
                self.extra_assets.append({"src": str(src.resolve()), "file": fn})
                r["file"] = fn
                r.setdefault("kind", kind)
            if r.get("type") == "FeedWall" and o.get("frames_src") == "auto":
                n_ = int(o.get("frames", 8)); dur_ = float(self.media.get("normalized", {}).get("duration") or self.media["duration"])
                o = {**o, "frames_src": [round(dur_ * (k + 0.5) / n_, 2) for k in range(n_)]}
            if r.get("type") == "SideCards":
                for k, it in enumerate(r.get("items", [])):
                    if not isinstance(it, dict): continue
                    if it.get("src"):
                        src = Path(str(it["src"]))
                        if not src.exists(): self.lint.append(f"SideCards #{i} item {k}: file not found: {src} (use the user's uploaded file path)"); continue
                        fn = f"cards/{i}_{k}_" + re.sub(r'[^A-Za-z0-9._-]', '_', src.name)
                        self.extra_assets.append({"src": str(src.resolve()), "file": fn}); it["file"] = fn
                    elif isinstance(it.get("frame"), (int, float)):
                        fn = f"thumbs/ov{i}_card{k}.jpg"; thumbs.append({"src_time": float(it["frame"]), "file": fn}); it["file"] = fn
            if r.get("type") == "NoteClips":
                for k, it in enumerate(r.get("items", [])):
                    if not isinstance(it, dict): continue
                    if it.get("src"):
                        src = Path(str(it["src"]))
                        if not src.exists(): self.lint.append(f"NoteClips #{i} item {k}: file not found: {src} (use the user's uploaded file path)"); continue
                        kind = "video" if src.suffix.lower() in VIDEO_EXT else "image"
                        ext = ".mp4" if kind == "video" else (src.suffix.lower() if src.suffix.lower() in (".jpg", ".jpeg", ".png", ".webp") else ".jpg")
                        fn = f"clips/{i}_{k}_" + re.sub(r'[^A-Za-z0-9._-]', '_', src.stem) + ext
                        self.extra_assets.append({"src": str(src.resolve()), "file": fn}); it["file"] = fn; it["kind"] = kind
                    elif isinstance(it.get("frame"), (int, float)):
                        fn = f"thumbs/ov{i}_clip{k}.jpg"; thumbs.append({"src_time": float(it["frame"]), "file": fn}); it["file"] = fn; it["kind"] = "image"
            if r.get("type") in ("FilmStrip", "FeedWall") and isinstance(o.get("frames_src"), list):
                files = []
                for k, ts in enumerate(o["frames_src"]):
                    fn = f"thumbs/ov{i}_{k}.jpg"; thumbs.append({"src_time": float(ts), "file": fn}); files.append(fn)
                r["thumbs"] = files
            ovs.append(r)
        # cover transitions vary per video (seeded by what was said): the orb opens from the face, a corner or the centre,
        # bars sweep up or down in 5-7 strips - two different videos never share the exact same transitions
        seed = int(hashlib.sha1(" ".join(w["word"] for w in self.words_src).encode()).hexdigest()[:8], 16)
        for k_, w_ in enumerate(getattr(self, "shape_wipes", [])):
            var = {}
            if w_["shape"] == "orb" and w_.get("x") is None and w_.get("y") is None:
                pick_ = (seed // 7 + k_) % 4
                if pick_ == 0:
                    fb = self.face_box_at(max(0.0, w_["at"] - 0.35))
                    var = {"x": round((fb[0] + fb[2]) / 2 / self.W, 3), "y": round((fb[1] + fb[3]) / 2 / self.H, 3)}
                else:
                    var = [{"x": 0.5, "y": 0.46}, {"x": 0.18, "y": 0.28}, {"x": 0.82, "y": 0.72}][pick_ - 1]
            elif w_["shape"] == "orb":
                var = {"x": w_.get("x", 0.5), "y": w_.get("y", 0.46)}
            if w_["shape"] == "bars":
                var = {"dir": w_.get("dir") or ("down" if (seed + k_) % 2 else "up"), "bars": w_.get("bars") or (5 + (seed // 3 + k_) % 3)}
            ovs.append({"type": "ShapeWipe", "start": round(w_["at"] - 0.3, 3), "end": round(w_["at"] + 0.32, 3), "at": w_["at"], "shape": w_["shape"],
                        "auto": True, **var, **({"sfx": w_["sfx"]} if w_.get("sfx") else {}), **({"color": w_["color"]} if w_.get("color") else {}),
                        **({"color2": w_["color2"]} if w_.get("color2") else {}), **({"hold": True, "end": self.total} if w_.get("hold") else {})})
        # HeadRing: one ring = two overlays, the back half behind the speaker (needs the matte), the front half in front
        split_ = []
        for o in ovs:
            if o.get("type") == "HeadRing" and not o.get("half"):
                split_.append({**o, "half": "back", "layer": "behind"}); split_.append({**o, "half": "front", "layer": o.get("layer") if o.get("layer") not in (None, "behind") else "below", "sfx": "none"})
            else: split_.append(o)
        ovs = split_
        dd = self.style.get("depth_defaults") or {}
        for o in ovs:
            if o.get("type") == "DepthText" and dd:
                for k_, v_ in dd.items():
                    if k_ not in o: o[k_] = v_   # o.look is the default for lines/rows without their own look
            if o.get("type") in PREMIUM and "layer" not in o: o["layer"] = "below"
            if o.get("type") in PREMIUM: self.check_premium(o)
            if o.get("type") in NOTES:
                if not isinstance(o.get("start"), (int, float)):
                    its_ = [it.get("at") for it in o.get("items", []) + [x for g in (o.get("groups") or []) if isinstance(g, dict) for x in g.get("items", [])]
                            if isinstance(it, dict) and isinstance(it.get("at"), (int, float))]
                    o["start"] = round(max(0.0, min(its_) - 0.1), 3) if its_ else 0.0
                if o["type"] == "NoteClips":  # items whose media is missing are reported (here or as file-not-found) and left out
                    for k_, it in enumerate(o.get("items", [])):
                        if isinstance(it, dict) and not it.get("file") and not it.get("src"):
                            self.lint.append(f"NoteClips@{o['start']:.1f}s: item {k_} has no media (src = the user's clip/photo path, or frame = source seconds)")
                    o["items"] = [it for it in o.get("items", []) if isinstance(it, dict) and it.get("file")]
                if "layer" not in o: o["layer"] = "above" if o["type"] == "HeadNote" else "below"
                self.check_notes(o)
            if o.get("type") in ("DepthText", "FeedWall") and "layer" not in o: o["layer"] = "behind"
            elif o.get("type") == "Doodles" and "layer" not in o: o["layer"] = "below"
            if o.get("type") in ("DepthText", "HeadRing", "Doodles", "FeedWall"): self.check_depth(o)
            if o.get("type") == "Cutout": o["layer"] = "cutout"
            elif o.get("type") == "ShapeWipe": o["layer"] = "top"
            elif (o.get("type") in SCENES + ("HeroTitle", "Accents", "MoodGrade", "SearchStack", "Floaters", "StoryTitle", "SceneBuild") or o.get("scene")) and "layer" not in o: o["layer"] = "below"
            if o.get("type") == "SceneBuild": self.check_build(o)
            if o.get("type") not in self.builtin_types and o.get("type") not in self.custom_types and self.builtin_types:
                self.lint.append(f"unknown overlay type '{o.get('type')}' (not built in, not exported by a custom_components file) - it would not render")
        # grades first, so they colour the footage (and text behind the speaker) but not the graphics drawn after them
        ovs.sort(key=lambda o_: 0 if o_.get("type") == "MoodGrade" else 1)
        self.ovs, self.thumbs = ovs, thumbs
        # background removal (person matte) only where it is used: text behind the speaker, Cutout beats
        rng = []
        for o in ovs:
            if o.get("layer") == "behind" or o.get("type") == "Cutout":
                st = o.get("start"); en = o.get("end") if isinstance(o.get("end"), (int, float)) else (st or 0) + 2
                if isinstance(st, (int, float)): rng.append([max(0.0, st - 0.1), min(self.footage_dur, en + 0.1)])
        rng.sort(); merged = []
        for a_, b_ in rng:
            if merged and a_ <= merged[-1][1] + 0.05: merged[-1][1] = max(merged[-1][1], b_)
            else: merged.append([a_, b_])
        mfps = int((C.get("matte") or {}).get("fps", 30))
        self.matte = {"fps": mfps, "ranges": [[round(a_, 3), round(b_, 3)] for a_, b_ in merged]} if merged else None
        if merged:
            secs = sum(b_ - a_ for a_, b_ in merged)
            self.suggestions.append(f"background removal on {secs:.1f}s of footage ({int(secs * mfps)} frames, adds ~{secs * mfps / 60:.1f} min to the first render/stills)")
        self.auto_hide_titles()
        self.clothes_contrast()
        # lint: text overlays inside platform safe zone
        sz = SAFE[self.settings["aspect_ratio"]]
        top, bot = sz["top"] * self.H, (1 - sz["bottom"]) * self.H
        for o in ovs:
            y = o.get("y")
            if o["type"] == "DepthText" and o.get("mode") == "wall": continue  # a repeating texture may run past the safe zone by design
            if isinstance(y, (int, float)) and o["type"] in TEXT_OVERLAYS and o["type"] not in ("Callout", "Tile"):
                if y < top or y > bot:
                    self.lint.append(f"{o['type']} at y={y} is outside the {self.settings['aspect_ratio']} safe zone ({top:.0f}-{bot:.0f})")
        cap_bottom = self.caps["y"]
        if cap_bottom > bot + 1:
            self.lint.append(f"caption baseline y={cap_bottom} is below the safe zone limit {bot:.0f}")
        for z in self.caps["zones"]:
            if z.get("y") and z["y"] > bot + 1:
                self.lint.append(f"caption zone at {z['from']:.2f}s uses y={z['y']} below safe limit {bot:.0f}")

    def check_premium(self, o):
        """PREMIUM PACK lint: object kinds, face slots, required text, gradients."""
        ty, st = o.get("type"), o.get("start") if isinstance(o.get("start"), (int, float)) else 0.0
        where = f"{ty}@{st:.1f}s"
        its = [it for it in o.get("items", []) if isinstance(it, dict)]
        for k_, it in enumerate(its + ([o] if ty == "GlowPill" else [])):
            if it.get("slot") and it["slot"] not in PREMIUM_SLOTS:
                self.lint.append(f"{where}: unknown slot '{it['slot']}' (have {', '.join(sorted(PREMIUM_SLOTS))})")
            if ty == "Glossy" and it.get("kind", "coin") not in GLOSSY_KINDS:
                self.lint.append(f"{where}: unknown glossy kind '{it.get('kind')}' (have {', '.join(sorted(GLOSSY_KINDS))})")
            if ty == "Glossy" and isinstance(it.get("symbol"), str) and len(it["symbol"]) > 3:
                self.lint.append(f"{where}: coin symbol '{it['symbol']}' is too long (1-3 characters: Rp, %, +)")
            g_ = it.get("grad")
            if isinstance(g_, str) and g_ not in GRAD_NAMES: self.lint.append(f"{where}: unknown gradient '{g_}' (have {', '.join(sorted(GRAD_NAMES))})")
        if ty == "GlowPill" and not o.get("text"): self.lint.append(f"{where}: GlowPill needs 'text' (spoken words)")
        if ty == "GlowPill" and len(str(o.get("text", ""))) > 26: self.slop.append(f"{where}: pill text '{o.get('text')}' is long - 1-4 words read in a pill")
        if ty == "Checklist" and not its: self.lint.append(f"{where}: Checklist needs items")
        if ty == "Checklist" and len(its) > 5: self.slop.append(f"{where}: {len(its)} checklist rows - 2-4 rows read on a phone")
        if ty == "SideCards" and len(its) > 4: self.slop.append(f"{where}: {len(its)} side cards at once - 1-2 per side")
        if ty == "BadgeOrbit" and len(its) > 8: self.slop.append(f"{where}: {len(its)} badges around the head - 4-7 read as a ring, more is noise")

    def check_notes(self, o):
        """NOTE PACK lint: surfaces, slots, short labels (a note is 1-4 spoken words), board size, clip cards."""
        ty, st = o.get("type"), o.get("start") if isinstance(o.get("start"), (int, float)) else 0.0
        where = f"{ty}@{st:.1f}s"
        its = [it for it in o.get("items", []) if isinstance(it, dict)]
        long_ = lambda x: len(str(x).split()) > 4 or len(str(x)) > 26
        if ty == "HeadNote":
            seq = its or [{"text": o.get("text", "")}]
            if not any(str(it.get("text", "")).strip() for it in seq): self.lint.append(f"{where}: HeadNote needs 'text' or 'items' (spoken words)")
            line, longest = "", 0
            for it in seq:
                line = str(it.get("text", "")) if (it.get("mode", "new") == "new" or not line) else line + "  ·  " + str(it.get("text", ""))
                longest = max(longest, len(line))
                if it.get("mode", "new") not in ("new", "add"): self.lint.append(f"{where}: item mode '{it.get('mode')}' (use new / add)")
            if longest > 36:
                self.slop.append(f"{where}: the label grows to {longest} characters - a head note is a few words (<= ~34 characters with separators); start a fresh line with mode 'new'")
            if o.get("anchor", "top") not in ("top", "face"): self.lint.append(f"{where}: anchor '{o.get('anchor')}' (use top / face)")
        elif ty == "NoteTags":
            if not its: self.lint.append(f"{where}: NoteTags needs items [{{text, at}}]")
            if o.get("slot") and o["slot"] not in NOTE_SLOTS: self.lint.append(f"{where}: unknown slot '{o['slot']}' (have {', '.join(sorted(NOTE_SLOTS))})")
            for sf in [o.get("surface")] + [it.get("tone") for it in its]:
                if sf and sf not in NOTE_SURFACES: self.lint.append(f"{where}: unknown surface/tone '{sf}' (have {', '.join(sorted(NOTE_SURFACES))})")
            if any(long_(it.get("text", "")) for it in its): self.slop.append(f"{where}: a tag holds 1-3 spoken words; split long phrases or shorten them")
            if len(its) > 6: self.slop.append(f"{where}: {len(its)} tags at once - 2-5 read above a head; use a NoteBoard for a long list")
            if o.get("label") and long_(o["label"]): self.slop.append(f"{where}: label '{o['label']}' is long - a mono label is 1-3 words")
        elif ty == "NoteBoard":
            gs = [g for g in (o.get("groups") or [{"items": o.get("items", [])}]) if isinstance(g, dict)]
            flat = [it for g in gs for it in g.get("items", []) if isinstance(it, dict)]
            if not flat: self.lint.append(f"{where}: NoteBoard needs groups[].items (or items)")
            if len(flat) > 9: self.slop.append(f"{where}: {len(flat)} tags on one board - keep it to ~8 (split into two boards or two beats)")
            if len(gs) > 3: self.slop.append(f"{where}: {len(gs)} groups on one board - 1-3 groups read on a phone")
            if any(len(str(it.get("text", "")).split()) > 5 or len(str(it.get("text", ""))) > 30 for it in flat):
                self.slop.append(f"{where}: a card line holds 1-5 spoken words (it is a note, not the sentence)")
            if not isinstance(o.get("end"), (int, float)): self.lint.append(f"{where}: NoteBoard needs an end (it covers the frame)")
        elif ty == "BigNote":
            if not str(o.get("text", "")).strip(): self.lint.append(f"{where}: BigNote needs 'text' (1-2 spoken words)")
            elif len(str(o["text"]).split()) > 2 or len(str(o["text"])) > 14: self.slop.append(f"{where}: '{o['text']}' - a big note is 1-2 short words")
            if o.get("kicker") and long_(o["kicker"]): self.slop.append(f"{where}: kicker '{o['kicker']}' is long - 1-4 words")
            if o.get("mark", "highlight") not in NOTE_MARKS: self.lint.append(f"{where}: unknown mark '{o.get('mark')}' (have {', '.join(sorted(NOTE_MARKS))})")
        elif ty == "NoteClips":
            if not its: self.lint.append(f"{where}: NoteClips has no usable items [{{src | frame, label, at}}]")
            if len(its) > 3: self.slop.append(f"{where}: {len(its)} clip cards - 1-3 fit around a head (left, above, right)")
            for k_, it in enumerate(its):
                if it.get("label") and long_(it["label"]): self.slop.append(f"{where}: card label '{it['label']}' is long - 1-3 words")

    def check_depth(self, o):
        """DEPTH PACK lint: modes, looks, doodle kinds, readable sizes, the face slot of type meant to sit behind the head."""
        ty, st = o.get("type"), o.get("start") if isinstance(o.get("start"), (int, float)) else 0.0
        where = f"{ty}@{st:.1f}s"
        if ty == "DepthText":
            if o.get("mode", "stack") not in DEPTH_MODES:
                self.lint.append(f"{where}: unknown mode '{o.get('mode')}' (have {', '.join(sorted(DEPTH_MODES))})")
            roles = [o.get("font")] + list(o.get("fonts") or []) + [l.get("font") for l in o.get("lines", []) if isinstance(l, dict)]
            for fr in roles:
                if fr and fr not in FONT_ROLES: self.lint.append(f"{where}: unknown font role '{fr}' (have {', '.join(sorted(FONT_ROLES))})")
            for g_ in [o.get("grad")] + [l.get("grad") for l in o.get("lines", []) if isinstance(l, dict)]:
                if isinstance(g_, str) and g_ not in GRAD_NAMES: self.lint.append(f"{where}: unknown gradient '{g_}' (have {', '.join(sorted(GRAD_NAMES))})")
            looks = [o.get("look")] + list(o.get("looks") or []) + [l.get("look") for l in o.get("lines", []) if isinstance(l, dict)]
            for lk in looks:
                if lk and lk not in TYPE_LOOKS: self.lint.append(f"{where}: unknown look '{lk}' (have {', '.join(sorted(TYPE_LOOKS))})")
            if not (o.get("text") or o.get("lines")): self.lint.append(f"{where}: no text / lines")
            if o.get("layer") == "behind" and isinstance(o.get("y"), (int, float)) and o.get("mode", "stack") != "wall":
                fb = self.face_box_at(st + 0.3)
                if o["y"] > fb[1] + 40:
                    self.suggestions.append(f"{where}: y={o['y']} starts below the eyes (~{fb[1]:.0f}px) - type behind the speaker reads best when the head covers only its lower part")
        elif ty == "Doodles":
            for it in o.get("items", []):
                if isinstance(it, dict) and it.get("kind") not in DOODLE_KINDS:
                    self.lint.append(f"{where}: unknown doodle '{it.get('kind')}' (have {', '.join(sorted(DOODLE_KINDS))})")
        elif ty == "HeadRing":
            if not o.get("text"): self.lint.append(f"{where}: HeadRing needs the spoken words to circle the head ('text')")
            elif len(str(o["text"])) > 28: self.slop.append(f"{where}: ring text '{o['text']}' is long - 1-4 spoken words read best around a head")
        elif ty == "FeedWall":
            if not o.get("thumbs") and not o.get("palette") and not o.get("frames_src"):
                self.suggestions.append(f"{where}: FeedWall without frames_src uses plain colour cards; \"frames_src\": \"auto\" fills them with the speaker's own frames")

    def check_build(self, o):
        """SceneBuild: default item times, validate kinds / poses / things / slots, keep text out of the caption band."""
        if not hasattr(self, "_things"): self._things = kit_things()
        face = o.get("anchor") == "face"
        st = o.get("start") if isinstance(o.get("start"), (int, float)) else 0.0
        for k_, it in enumerate(o.get("items", [])):
            if not isinstance(it, dict): continue
            where = f"SceneBuild@{st:.1f}s item {k_} ({it.get('kind')})"
            if it.get("kind") not in KIT_KINDS:
                self.lint.append(f"{where}: unknown kind (use {', '.join(sorted(KIT_KINDS))})"); continue
            if not isinstance(it.get("at"), (int, float)): it["at"] = round(st + 0.15 + 0.12 * k_, 3)
            if it.get("kind") == "thing" and self._things and it.get("name") not in self._things:
                self.lint.append(f"{where}: unknown thing '{it.get('name')}' (have: {', '.join(sorted(self._things))})")
            if it.get("kind") == "figure" and it.get("pose") not in KIT_POSES:
                self.lint.append(f"{where}: unknown pose '{it.get('pose')}' (stand, wave, point, cheer, shrug, hold)")
            if it.get("move") not in KIT_MOVES:
                self.lint.append(f"{where}: unknown move '{it.get('move')}'")
            if face:
                if it.get("slot", "above") not in KIT_SLOTS:
                    self.lint.append(f"{where}: face mode needs a slot ({', '.join(sorted(KIT_SLOTS))}) - items never sit on the face")
                if it.get("kind") in ("crowd", "arrow", "frame", "rect"):
                    self.lint.append(f"{where}: '{it.get('kind')}' is for full-screen scenes, not around the face")
            elif it.get("kind") in ("text", "chip", "bubble", "card", "tag", "bar", "counter", "steps", "meter") and isinstance(it.get("y"), (int, float)):
                cap_y = self.caps["y"]; band = cap_y - self.style["captions"]["size"] * 1.9 - 24
                a_ = it["at"]
                hidden = any(z.get("hidden") and z["from"] <= a_ + 0.3 < z["to"] for z in self.caps["zones"])
                if band - 30 < it["y"] < cap_y + 20 and not hidden and not it.get("hide_captions"):
                    self.lint.append(f"{where}: text at y={it['y']:.0f} sits in the caption band ({band:.0f}-{cap_y}); move it up")

    # -- text helpers -------------------------------------------------------------------------------
    @staticmethod
    def overlay_texts(o, fields=("text", "headline", "subhead", "detail", "title", "subtitle", "name", "role", "button", "word1", "word2", "sub", "dek",
                                 "pill", "tag", "prompt", "kicker", "text2")):
        out = [str(o[f]) for f in fields if isinstance(o.get(f), str)]
        if o.get("type") == "NoteTags" and isinstance(o.get("label"), str): out.append(o["label"])  # the mono group label
        for g in o.get("groups", []) if isinstance(o.get("groups"), list) else []:  # NoteBoard
            if not isinstance(g, dict): continue
            if isinstance(g.get("label"), str): out.append(g["label"])
            out += [str(it.get(k, "")) for it in g.get("items", []) if isinstance(it, dict) and it.get("copy") != "user" for k in ("text", "sub") if it.get(k)]
        out += [str(l.get("text", "")) for l in o.get("lines", []) if isinstance(l, dict)]
        out += [str(it.get("text") or it.get("title") or "") for it in o.get("items", []) if isinstance(it, dict) and it.get("copy") != "user"]
        out += [str(it.get("sub", "")) for it in o.get("items", []) if isinstance(it, dict) and it.get("sub") and it.get("copy") != "user"]
        for it in o.get("items", []):  # scene kit extras: labels, stamps, chart labels, things above heads, process steps
            if not isinstance(it, dict) or it.get("copy") == "user": continue
            out += [str(it[k]) for k in ("label", "stamp") if isinstance(it.get(k), str)]
            out += [str(x) for x in it.get("labels", []) if isinstance(x, str)]
            if isinstance(it.get("above"), dict) and isinstance(it["above"].get("text"), str): out.append(it["above"]["text"])
            out += [str(s_.get("text", "")) for s_ in it.get("items", []) if isinstance(s_, dict)]
        out += [str(m.get("text", "")) for m in o.get("messages", []) + o.get("steps", []) if isinstance(m, dict)]
        out += [str(p_.get("label", "")) for p_ in o.get("phones", []) if isinstance(p_, dict)]
        out += [str(w_ if isinstance(w_, str) else w_.get("text", "")) for w_ in o.get("words", []) if isinstance(w_, (str, dict))]
        return [x for x in out if x.strip()]

    def spoken_between(self, a, b):
        return [norm_tok(w["text"]) for w in self.tokens if a - 0.25 <= w["start"] <= b]

    def auto_hide_titles(self):
        """A KineticTitle that shows the words being spoken right now duplicates the captions (same words twice on
        screen = amateur). Hide the captions for the title's window instead - what a human editor does."""
        if self.creative.get("auto_hide_captions", True) is False: return
        # any scene that types a spoken quote ("prompt" + "typeAt", e.g. a custom component): same rule as the search bars
        for o in self.ovs:
            if o.get("keep_captions") or not isinstance(o.get("prompt"), str) or not isinstance(o.get("typeAt"), (int, float)): continue
            toks = [x for x in (norm_tok(y) for y in o["prompt"].split()) if x]
            if not toks: continue
            a_ = o["typeAt"]; b_ = min(a_ + float(o.get("typing", 1.0)) + 0.6, o["end"] if isinstance(o.get("end"), (int, float)) else 1e9)
            if sum(1 for x in toks if x in set(self.spoken_between(a_ - 0.6, b_))) / len(toks) >= 0.6:
                z = {"from": round(a_ - 0.05, 3), "to": round(b_, 3), "hidden": True, "auto": "typed"}
                self.caps["zones"].append(z)
                self.suggestions.append(f"captions hidden {z['from']:.2f}-{z['to']:.2f}s while {o['type']} types '{o['prompt']}'")
        # scene kit items: a big word / tag / bubble showing what is being said, or a bar typing a spoken quote
        for o in self.ovs:
            if o.get("type") != "SceneBuild" or o.get("keep_captions"): continue
            oend = o["end"] if isinstance(o.get("end"), (int, float)) else self.total
            for it in o.get("items", []):
                if not isinstance(it, dict) or it.get("keep_captions") or not isinstance(it.get("at"), (int, float)): continue
                kind = it.get("kind")
                if kind == "bar" and isinstance(it.get("typeAt"), (int, float)) and it.get("text"):
                    words_ = [it["text"]]; a_ = it["typeAt"]
                    b_ = min(a_ + float(it.get("typing", min(1.4, 0.05 * len(it["text"]) + 0.3))) + 0.6, oend)
                elif kind in ("text", "tag", "bubble", "chip", "card") or it.get("hide_captions"):
                    words_ = [str(it.get(k)) for k in ("text", "stamp", "sub") if it.get(k)]; a_ = it["at"]
                    b_ = min(it["until"] if isinstance(it.get("until"), (int, float)) else oend, oend)
                elif kind == "figure" and isinstance(it.get("above"), dict) and it["above"].get("text") and isinstance(it["above"].get("typeAt"), (int, float)):
                    ab = it["above"]; words_ = [ab["text"]]; a_ = ab["typeAt"]
                    b_ = min(a_ + float(ab.get("typing", min(1.4, 0.05 * len(ab["text"]) + 0.3))) + 0.6, oend)
                else:
                    continue
                toks = [x for x in (norm_tok(y) for w_ in words_ for y in w_.split()) if x]
                if not toks: continue
                spk = [w for w in self.tokens if a_ - 0.6 <= w["start"] <= b_ and norm_tok(w["text"]) in set(toks)]
                if len({norm_tok(w["text"]) for w in spk}) / len(set(toks)) < 0.6: continue
                hide_to = b_ if kind == "bar" or "typeAt" in (it.get("above") or {}) else min(b_, max(a_ + 0.8, max(w["end"] for w in spk) + 0.25))
                z = {"from": round(a_ - 0.05, 3), "to": round(hide_to, 3), "hidden": True, "auto": "kit"}
                self.caps["zones"].append(z)
                self.suggestions.append(f"captions hidden {z['from']:.2f}-{z['to']:.2f}s under SceneBuild {kind} '{' / '.join(words_)}'")
        # search bars typing a spoken quote: hide the captions only while that quote is being said
        for o in self.ovs:
            if o.get("type") != "SearchStack" or o.get("keep_captions"): continue
            its = [it for it in o.get("items", []) if isinstance(it, dict) and it.get("text") and isinstance(it.get("at"), (int, float))]
            for k_, it in enumerate(its):
                toks = [x for x in (norm_tok(y) for y in str(it["text"]).split()) if x]
                if not toks: continue
                typ = it.get("typing", min(1.4, 0.05 * len(str(it["text"])) + 0.3))
                end_ = min(its[k_ + 1]["at"] if k_ + 1 < len(its) else 1e9, o.get("end") if isinstance(o.get("end"), (int, float)) else 1e9, it["at"] + typ + 1.4)
                spoken = set(self.spoken_between(it["at"] - 0.6, end_))
                if sum(1 for x in toks if x in spoken) / len(toks) >= 0.6:
                    z = {"from": round(it["at"] - 0.05, 3), "to": round(end_, 3), "hidden": True, "auto": "search"}
                    self.caps["zones"].append(z)
                    self.suggestions.append(f"captions hidden {z['from']:.2f}-{z['to']:.2f}s while the search bar types '{it['text']}'")
        for o in self.ovs:
            if (o.get("type") not in ("KineticTitle", "EndCard", "TitleCard", "Headline", "HeroTitle", "SceneFeatures", "Shout", "StoryTitle", "DepthText", "GlowPill", "Checklist", "BigNote") and not o.get("hide_captions")) or o.get("keep_captions"): continue
            if o["type"] == "Counter":
                txts = [str(o.get("value", ""))]
            else:
                txts = self.overlay_texts(o, fields=("text", "word1", "word2", "title", "headline", "kicker", "tag"))
            toks = [norm_tok(x) for txt in txts for x in txt.split()]
            toks = [x for x in toks if x]
            if not toks: continue
            a0 = o["start"]
            if o["type"] == "EndCard" and isinstance(o.get("at"), dict) and isinstance(o["at"].get("wordmark"), (int, float)):
                a0 = o["at"]["wordmark"]
            end = o.get("end") if isinstance(o.get("end"), (int, float)) else self.total
            spoken = set(self.spoken_between(a0, end))
            if sum(1 for x in toks if x in spoken or x.rstrip("%") in spoken) / len(toks) >= 0.6:
                z = {"from": round(a0 - 0.05, 3), "to": round(end, 3), "hidden": True, "auto": "title"}
                self.caps["zones"].append(z)
                self.suggestions.append(f"captions hidden {z['from']:.2f}-{z['to']:.2f}s under {o['type']} '{' / '.join(txts)}' (same words already on screen)")

    @staticmethod
    def _track(keys, t, fields, dflt):
        """Python mirror of anim.js evalTrack (smoothstep instead of the exact ease; good enough for lint)."""
        if not keys: return dict(dflt)
        st = {f: keys[0].get(f, dflt.get(f)) for f in fields}
        for k in keys[1:]:
            if t < k["t"]: break
            dur = k.get("dur", 0.4)
            p = 1.0 if dur <= 0 or k.get("ease") == "cut" else min(1.0, max(0.0, (t - k["t"]) / dur))
            p = p * p * (3 - 2 * p)
            for f in fields:
                if k.get(f) is None: continue
                st[f] = k[f] if st.get(f) is None else st[f] + (k[f] - st[f]) * p
        return st

    def face_box_at(self, t):
        """Eyes->mouth band of the speaker on the canvas at edited time t, including punch-ins, close pushes,
        jump-cut zoom and rhythm pushes, with the same edge clamp the renderer applies."""
        cam = self._track(self.cam_keys, t, ("s", "fx", "fy"), {"s": 1.0, "fx": 0.5, "fy": 0.4})
        jk = 1.0
        for tt, kk in (self.jump or []):
            if t >= tt: jk = kk
            else: break
        push = self._track(self.pushes, t, ("k",), {"k": 1.0})["k"] if getattr(self, "pushes", None) else 1.0
        z = max(1.0, (cam["s"] or 1.0) * jk * push)
        fs = self.faces or [[0.0, 0.5, 0.42, 0.2]]
        f = min(fs, key=lambda r: abs(r[0] - t))
        cx, cy, h = f[1], f[2], f[3]
        vw, vh = self.media["video"]["display_width"], self.media["video"]["display_height"]
        c = max(self.W / vw, self.H / vh)
        VW, VH = vw * c * z, vh * c * z
        fx = 0.5 if cam.get("fx") is None else cam["fx"]
        fy = 0.4 if cam.get("fy") is None else cam["fy"]
        tx = min(0.0, max(self.W - VW, fx * self.W - cx * VW))
        ty = min(0.0, max(self.H - VH, fy * self.H - cy * VH))
        fcx, fcy = tx + cx * VW, ty + cy * VH
        fh_px = h * VH
        fw_px = self.face_w * (h / max(1e-6, self.face_h_med)) * VW
        return (fcx - 0.32 * fw_px, fcy - 0.28 * fh_px, fcx + 0.32 * fw_px, fcy + 0.36 * fh_px)

    def overlay_box(self, o):
        """Rough on-canvas bounding box [x0, y0, x1, y1] of a text overlay (for collision lint)."""
        W, H = self.W, self.H
        if isinstance(o.get("box"), list) and len(o["box"]) == 4: return tuple(float(v) for v in o["box"])  # custom components declare it
        ty, x, y = o.get("type"), o.get("x"), o.get("y")
        est = lambda txt, size: len(str(txt)) * size * 0.58
        if ty == "KineticTitle":
            lines = o.get("lines", [])
            if not lines or not isinstance(y, (int, float)): return None
            w = max(est(l.get("text", ""), l.get("size", 90)) for l in lines) + 40
            h = sum(l.get("size", 90) * 1.12 + 10 for l in lines)
            cx = (x if x is not None else W / 2) if o.get("align") != "left" else (x or 60) + w / 2
            return (cx - w / 2, y, cx + w / 2, y + h)
        if ty == "Badge" and isinstance(y, (int, float)):
            sz = o.get("size", 40); w = est(o.get("text", ""), sz) + sz * 2.4; cx = x if x is not None else W / 2
            return (cx - w / 2, y - sz * 0.9, cx + w / 2, y + sz * 0.9)
        if ty == "Counter":
            fs = o.get("fontSize", 170); cy = y if isinstance(y, (int, float)) else H * 0.24; cx = x if x is not None else W / 2
            w = est(str(o.get("value", "")) + str(o.get("suffix", "")) + str(o.get("prefix", "")), fs) + fs * 0.5
            return (cx - w / 2, cy - fs * 0.6, cx + w / 2, cy + fs * 0.6 + (50 if o.get("label") else 0))
        if ty == "Steps":
            fs = o.get("fontSize", 50); w = o.get("w", min(860, W - 140)); cx = x if x is not None else W / 2; y0 = y if isinstance(y, (int, float)) else H * 0.17
            return (cx - w / 2, y0, cx + w / 2, y0 + 60 + len(o.get("items", [])) * (fs * 1.25 + 54))
        if ty in ("InsightCard", "EvidenceCard", "ScrapNote", "Newsprint", "ChatPanel", "PromptBar", "FloatCard"):
            w = o.get("w", 840); cx = x if x is not None else W / 2; y0 = y if isinstance(y, (int, float)) else H * 0.17
            h = {"PromptBar": 110, "FloatCard": 200}.get(ty, 400)
            return (cx - w / 2, y0 - (h / 2 if ty in ("PromptBar", "FloatCard") else 0), cx + w / 2, y0 + (h / 2 if ty in ("PromptBar", "FloatCard") else h))
        if ty == "DepthText" and isinstance(y, (int, float)) and o.get("mode", "stack") in ("stack", "echo", "split"):
            if o.get("mode") == "echo": sz = o.get("size", 230); return (60, y - sz * 0.5, W - 60, y + sz * 0.6)
            if o.get("mode") == "split": sz = o.get("size", 200); return (40, y, W - 40, y + sz * 1.0)
            h_ = sum((l.get("size") or o.get("size") or 240) * 0.9 for l in o.get("lines", []) if isinstance(l, dict)) or (o.get("size") or 240) * 0.9
            return (60, y, W - 60, y + h_)
        if ty == "BigNote":
            sz = o.get("size", 220); w = min(W - 120, est(o.get("text", ""), sz) * 0.95); cx = x if x is not None else W / 2; cy = y if isinstance(y, (int, float)) else 470
            return (cx - w / 2, cy - sz * 0.58 - (sz * 0.25 if o.get("kicker") else 0), cx + w / 2, cy + sz * 0.45)
        if ty == "GlowPill" and isinstance(y, (int, float)):
            sz = o.get("size", 44); w = est(o.get("text", ""), sz) + sz * 3.2; cx = x if x is not None else W / 2
            return (cx - w / 2, y - sz * 1.1, cx + w / 2, y + sz * 1.1 + (sz * 0.7 if o.get("sub") else 0))
        if ty == "Checklist":
            sz = o.get("size", 40); y0 = o.get("y", 420); n_ = len(o.get("items", []))
            return (W / 2 - o.get("w", 780) / 2, y0 - (sz * 2.4 if o.get("title") else 0), W / 2 + o.get("w", 780) / 2, y0 + n_ * (sz * 2.2 + sz * 0.45))
        if ty == "LowerThird":
            x0 = x if x is not None else 70; y0 = y if isinstance(y, (int, float)) else H * 0.56
            return (x0, y0, x0 + 560, y0 + 160)
        if ty == "TitleCard":
            lines = o.get("lines") or [{"text": l} for l in str(o.get("title", "")).split("\n") if l]
            sz = o.get("size", 112); w = max([est(l.get("text", ""), l.get("size", sz)) * 0.85 for l in lines] + [300]) + 40
            h = sum(l.get("size", sz) * 1.05 + 20 for l in lines) + (60 if o.get("kicker") else 0) + (60 if o.get("sub") else 0) + 44
            cx = x if x is not None else W / 2; y0 = y if isinstance(y, (int, float)) else H * 0.3
            return (cx - w / 2, y0, cx + w / 2, y0 + h)
        if ty == "Headline":
            w = o.get("w", W - 90); cx = x if x is not None else W / 2; y0 = y if isinstance(y, (int, float)) else H * 0.15
            sz = o.get("size", 122); nl = max(1, math.ceil(est(o.get("headline", ""), sz) * 0.8 / max(1, w - 80)))
            h = 90 + nl * sz * 0.95 + 40 + (50 if o.get("dek") else 0) + (60 if o.get("columns", True) else 0)
            return (cx - w / 2, y0, cx + w / 2, y0 + h)
        if ty == "Shout" and isinstance(y, (int, float)):
            lines = o.get("lines") or [{"text": o.get("text", "")}]
            sz = o.get("size", 190); w = max(est(l.get("text", ""), l.get("size", sz)) * 1.15 for l in lines); cx = x if x is not None else W / 2
            return (cx - w / 2, y, cx + w / 2, y + sum(l.get("size", sz) * 0.98 for l in lines))
        if ty == "StoryTitle" and isinstance(y, (int, float)):
            sz = o.get("size", 200); w = est(o.get("text", ""), sz) * 1.2; cx = W / 2
            return (cx - w / 2, y - (sz * 0.62 if o.get("kicker") else 0), cx + w / 2, y + sz * 1.05)
        if ty == "HeroTitle" and isinstance(y, (int, float)):
            sz = o.get("size", 150); words_ = str(o.get("text", "")).split() or [""]
            sizes = [sz * ((o.get("sizes") or [])[i] if i < len(o.get("sizes") or []) else 1) for i in range(len(words_))]
            if o.get("stack"):
                w = max(est(wd, sz_) * 0.9 + sz_ * 0.3 for wd, sz_ in zip(words_, sizes)); hb = sum(sz_ * 1.04 for sz_ in sizes)
            else:
                w = min(W - 80, sum(est(wd, sz_) * 0.9 + sz_ * 0.3 for wd, sz_ in zip(words_, sizes))); hb = max(sizes) * max(1, math.ceil(w / (W - 120)))
            h = hb + (sz * 0.12 + 72 if o.get("kicker") else 0) + (sz * 0.14 + 74 if o.get("tag") else 0)
            lay = o.get("layout", "center")
            if lay == "left": x0 = x if x is not None else 80; return (x0, y - h / 2, x0 + w, y + h / 2)
            if lay == "right": x1 = x if x is not None else W - 80; return (x1 - w, y - h / 2, x1, y + h / 2)
            cx = x if x is not None else W / 2
            return (cx - w / 2, y - h / 2, cx + w / 2, y + h / 2)
        if ty == "Sticker" and isinstance(y, (int, float)):
            sz = o.get("size", 52); w = min(o.get("maxW") or 9e9, est(o.get("text", ""), sz) + sz * 1.4) + sz * 0.4
            cx = x if x is not None else W / 2
            return (cx - w / 2, y - sz * 0.9, cx + w / 2, y + sz * 0.9)
        return None

    # -- cut transitions -----------------------------------------------------------------------------
    def cut_fx(self):
        C = self.creative
        spec = self.style.get("cuts", {})
        req = C.get("cut_fx", "auto")
        out = []
        if isinstance(req, list):
            bounds = [s_["edit"][0] for s_ in self.segs[1:]]
            for c in req:
                t = self.T(c["at"])
                near = min(bounds, key=lambda b: abs(b - t)) if bounds else None
                if near is not None and abs(near - t) <= 0.35: t = near  # a transition belongs on the cut
                out.append({"t": t, "fx": c.get("fx", "zoom"), "dir": c.get("dir", 1), "dur": c.get("dur", spec.get("dur", 0.24)), "pre": c.get("pre", spec.get("pre", 0.1))})
        elif req == "auto" and self.settings["editing_intensity"] != "low" and spec.get("fx"):
            fxl = spec["fx"]; gap = spec.get("minGap", 3.0) * (0.8 if self.settings["editing_intensity"] == "high" else 1.0)
            lay = [L["t"] for L in self.layout_keys[1:]]
            last, k, d = -9.0, 0, 1
            cap = max(1, len(self.segs[1:]) // 2)  # most cuts stay clean jump cuts; transitions are punctuation
            if len(fxl) == 1: cap = min(cap, 2)  # one-transition styles: a third identical one reads as a preset (SLOP)
            for a, b in zip(self.segs, self.segs[1:]):
                if len(out) >= cap: break
                t = b["edit"][0]
                if t - last < gap or t < 0.8: continue
                if (a["edit"][1] - a["edit"][0]) < 0.6 or (b["edit"][1] - b["edit"][0]) < 0.6: continue
                if any(abs(t - x) < 0.6 for x in lay): continue
                if any(b["start"] - 0.3 <= t <= b["end"] + 0.3 for b in self.note_boards()): continue  # the frame is blurred under a card
                cur = max((L for L in self.layout_keys if L["t"] <= t + 1e-6), key=lambda L: L["t"])
                if not any(v.get("camera") for v in cur.get("views", [])): continue  # cards/split: the cut fx would not show
                fx = fxl[k % len(fxl)]; k += 1
                out.append({"t": round(t, 4), "fx": fx, "dir": d, "dur": spec.get("dur", 0.24), "pre": spec.get("pre", 0.1)}); d = -d
                last = t
        self.cutfx = out

    # -- rhythm -----------------------------------------------------------------------------------------
    def visual_events(self):
        ev = [s["edit"][0] for s in self.segs[1:]]
        ev += [k["t"] for k in self.cam_keys if k["t"] > 0.01]
        ev += [L["t"] for L in self.layout_keys[1:]]
        ev += [c["t"] for c in self.cutfx]
        ev += [h_["t"] for h_ in getattr(self, "hits", [])]
        ev += [p["t"] for p in self.pushes if p.get("dur", 0) > 0]
        for o in self.ovs:
            if o.get("type") in ("ProgressBar",): continue
            if isinstance(o.get("start"), (int, float)): ev.append(o["start"])
            for l in o.get("lines", []) + o.get("items", []) + [it for g in (o.get("groups") or []) if isinstance(g, dict) for it in g.get("items", [])]:
                if isinstance(l, dict) and isinstance(l.get("at"), (int, float)): ev.append(l["at"])
        return sorted(e for e in ev if 0 <= e <= self.footage_dur)

    def rhythm(self):
        R = self.style.get("rhythm", {})
        mx = R.get("maxStatic", 4.0) * {"low": 1.5, "medium": 1.0, "high": 0.8}[self.settings["editing_intensity"]]
        amt = self.style["camera"].get("push", 0.045)
        self.pushes = []
        auto = self.creative.get("auto_rhythm", True)
        cuts = [s["edit"][0] for s in self.segs[1:]]
        if auto and not any(e <= 1.6 for e in self.visual_events()):
            # hook: something must move in the first ~1.5 s. Quick push-in on the first content word after 0.4 s.
            first = next((w for w in self.tokens if w["start"] >= 0.4 and norm_tok(w["text"]) not in STOP and len(norm_tok(w["text"])) > 2), None)
            if first and first["start"] < 1.6:
                t = round(first["start"] - 0.03, 3)
                smooth = bool(self.style["camera"].get("smooth"))
                self.pushes.append({"t": t, "dur": 0.9 if smooth else 0.32, "ease": "glide" if smooth else "snappy", "k": round(1 + amt * 1.5, 4)})
                nxt = next((c for c in cuts if c > t + 0.5), None)
                self.pushes.append({"t": nxt, "dur": 0, "ease": "cut", "k": 1.0} if nxt and nxt < t + 3 and not smooth else {"t": round(t + 1.8, 3), "dur": 0.9 if not smooth else 1.3, "ease": "glide", "k": 1.0})
                self.suggestions.append(f"hook push-in added at {t:.2f}s on '{first['text']}' (nothing moved in the first 1.6 s)")
        for _ in range(40):
            ev = [0.0] + self.visual_events() + [self.footage_dur]
            gaps = [(a, b) for a, b in zip(ev, ev[1:]) if b - a > mx]
            if not gaps or not auto: break
            a, b = max(gaps, key=lambda g: g[1] - g[0])
            t = round(a + min(mx * 0.55, (b - a) * 0.4), 3)
            self.pushes.append({"t": t, "dur": 1.6, "ease": "glide", "k": round(1 + amt, 4)})
            nxt = next((c for c in cuts if c > t + 1.6), None)
            if nxt is not None and nxt <= b + 0.01 and not self.style["camera"].get("smooth"):
                self.pushes.append({"t": nxt, "dur": 0, "ease": "cut", "k": 1.0})
            else:
                self.pushes.append({"t": round(min(b, t + mx * 0.9), 3), "dur": 1.4, "ease": "glide", "k": 1.0})
            self.pushes.sort(key=lambda k: k["t"])
        ev = [0.0] + self.visual_events() + [self.footage_dur]
        self.longest_static = max((b - a for a, b in zip(ev, ev[1:])), default=0)
        if self.longest_static > mx + 0.01:
            a, b = max(zip(ev, ev[1:]), key=lambda g: g[1] - g[0])
            self.slop.append(f"static {a:.1f}-{b:.1f}s ({b - a:.1f}s > {mx:.1f}s for this style): add a punch-in, layout change or a graphic tied to the words there")
        if self.pushes:
            self.pushes.insert(0, {"t": 0, "dur": 0, "k": 1.0})

    # -- anti-slop lint --------------------------------------------------------------------------------
    def slop_lint(self):
        C, S = self.creative, self.slop
        R = self.style.get("rhythm", {})
        dur = max(1.0, self.footage_dur)
        words = self.tokens
        # 0 depth pack: camera hits are punctuation, not decoration; doodles need air between them
        hits = getattr(self, "hits", [])
        for kind_ in ("shake", "glitch"):
            hs = sorted(h_["t"] for h_ in hits if h_["kind"] == kind_)
            if len(hs) > max(2, round(2 * dur / 30)):
                S.append(f"{kind_} x{len(hs)} in {dur:.0f}s: a {kind_} is one hit on THE word (max 2 per 30 s); remove the rest")
            if any(b_ - a_ < 3.0 for a_, b_ in zip(hs, hs[1:])):
                S.append(f"two {kind_} hits less than 3 s apart: keep the stronger one")
        dts = sorted(it["at"] for o in self.ovs if o.get("type") == "Doodles" for it in o.get("items", []) if isinstance(it, dict) and isinstance(it.get("at"), (int, float)))
        if any(b_ - a_ < 1.5 for a_, b_ in zip(dts, dts[1:])):
            S.append("doodles less than 1.5 s apart: one doodle per thought, give each one air")
        if len(dts) > max(4, dur / 5):
            S.append(f"{len(dts)} doodles in {dur:.0f}s: doodles on every sentence stop meaning anything (about one per 5 s)")
        # 1 emphasis
        emph = [w for w in words if w.get("emph")]
        if words and len(emph) / len(words) > 0.2:
            S.append(f"over-emphasis: {len(emph)}/{len(words)} words are chips (>20%). Keep only the words that carry the message")
        if len(emph) > dur / 1.2:
            S.append(f"emphasis every {dur / max(1, len(emph)):.1f}s: chips stop meaning anything; aim for one per 2-3 s")
        icons = [w for w in emph if w.get("icon")]
        if len(emph) >= 4 and len(icons) / len(emph) > 0.5:
            S.append(f"icon spam: {len(icons)}/{len(emph)} chips have icons; use an icon only when it literally depicts the word")
        # 2 grounding: text must come from the transcript or from copy the user wrote
        vocab = {norm_tok(w["word"]) for w in self.words_src}
        for v in list(C.get("glossary", {}).values()) + list(C.get("user_copy", [])):
            vocab |= {norm_tok(x) for x in str(v).split()}
        stop = STOP | {"dan", "atau", "the", "and", "for", "with", "your", "you", "ini", "itu"}
        def grounded(tok):
            if not tok or tok.isdigit() or len(tok) < 3 or tok in stop: return True
            return tok in vocab or any(len(v) >= 4 and (v.startswith(tok[:5]) or tok.startswith(v[:5])) for v in vocab if v)
        for o in self.ovs:
            if o.get("copy") == "user" or o["type"] in ("Viewfinder", "Waveform", "FilmStrip", "ProgressBar", "LightLeak", "BRoll", "Burst", "Flash"): continue
            texts = self.overlay_texts(o)
            bad = sorted({x for txt in texts for x in (norm_tok(y) for y in txt.split()) if not grounded(x)})
            if bad:
                S.append(f"{o['type']}@{o.get('start', 0):.1f}s uses words nobody said: {bad[:6]} -> use the speaker's words, or mark \"copy\": \"user\" if the user wrote it")
            if any(EMOJI_RE.search(x) for x in texts):
                S.append(f"{o['type']}@{o.get('start', 0):.1f}s contains emoji: never decorate with emoji")
            # long text duplicated from the captions
            toks = [norm_tok(x) for txt in texts for x in txt.split() if norm_tok(x)]
            typed_quote = isinstance(o.get("prompt"), str) and isinstance(o.get("typeAt"), (int, float))
            if len(toks) > 6 and o.get("start") is not None and not typed_quote and o["type"] not in ("Steps", "StickerStack", "SceneStack", "SceneFeatures", "HeroTitle", "SearchStack", "SceneLens", "SceneBuild") + NOTES:
                spoken = set(self.spoken_between(o["start"], o.get("end", o["start"] + 2)))
                if sum(1 for x in toks if x in spoken) / len(toks) >= 0.8:
                    S.append(f"{o['type']}@{o['start']:.1f}s just repeats the sentence being captioned: condense to the 1-4 key words")
            for txt in texts:
                if txt.isupper() and len(txt.split()) > 6:
                    S.append(f"{o['type']}@{o.get('start', 0):.1f}s: {len(txt.split())} words in ALL CAPS is a wall; split it or cut words")
            # reading time
            if o["type"] == "HeroTitle" and isinstance(o.get("end"), (int, float)):
                # kicker, words and tag each appear on their own spoken word: time each piece from its own entrance
                for fld, af in (("text", "at"), ("tag", "tagAt"), ("kicker", "kickerAt")):
                    if not isinstance(o.get(fld), str): continue
                    a_ = o.get(af, o.get("at", o["start"]) + (0.3 if fld == "tag" else 0)) if fld != "kicker" else o.get("kickerAt", o["start"])
                    need = max(0.7, 0.45 + 0.25 * len(o[fld].split())) if fld != "text" else max(1.0, 0.6 + 0.3 * len(o[fld].split()))
                    if isinstance(a_, (int, float)) and o["end"] - a_ < need - 0.05:
                        S.append(f"HeroTitle@{o['start']:.1f}s: its {fld} '{o[fld]}' is readable for {o['end'] - a_:.2f}s, needs >= {need:.1f}s: hold the title longer or bring the {fld} in earlier")
            elif o["type"] == "SearchStack":
                its = [it for it in o.get("items", []) if isinstance(it, dict) and it.get("text") and isinstance(it.get("at"), (int, float))]
                stop_ = o.get("end") if isinstance(o.get("end"), (int, float)) else self.total
                for k_, it in enumerate(its):
                    nxt = its[k_ + 1]["at"] if (o.get("max", 3) == 1 and k_ + 1 < len(its)) else stop_
                    typ = it.get("typing", min(1.4, 0.05 * len(str(it["text"])) + 0.3))
                    need = max(1.0, typ + 0.6)  # read while it types, then a beat to take it in
                    if nxt - it["at"] < need - 0.05:
                        S.append(f"SearchStack@{it['at']:.1f}s: '{it['text']}' stays {nxt - it['at']:.2f}s but needs >= {need:.1f}s to read")
            elif o["type"] == "SceneLens":
                pass  # the word cloud is texture; the lens reads one word at a time
            elif o["type"] == "SceneBuild":
                oend = o["end"] if isinstance(o.get("end"), (int, float)) else self.total
                for it in o.get("items", []):
                    if not isinstance(it, dict) or not isinstance(it.get("at"), (int, float)): continue
                    txt = " ".join(str(it.get(k)) for k in ("text", "label", "stamp", "sub") if isinstance(it.get(k), str))
                    n_ = len(txt.split())
                    if not n_: continue
                    a_ = it.get("typeAt") if it.get("kind") == "bar" and isinstance(it.get("typeAt"), (int, float)) else it["at"]
                    stop_t = it["until"] if isinstance(it.get("until"), (int, float)) else oend
                    need = max(1.0, 0.6 + 0.3 * n_) if it.get("kind") != "bar" else float(it.get("typing", 1.0)) + 0.6
                    if stop_t - a_ < need - 0.05:
                        S.append(f"SceneBuild@{o['start']:.1f}s {it.get('kind')} '{txt[:40]}' is readable for {stop_t - a_:.2f}s, needs >= {need:.1f}s")
            elif o["type"] in NOTES:
                oend = o["end"] if isinstance(o.get("end"), (int, float)) else self.total
                its_ = [it for it in o.get("items", []) if isinstance(it, dict)] + [it for g in (o.get("groups") or []) if isinstance(g, dict) for it in g.get("items", []) if isinstance(it, dict)]
                if o["type"] in ("HeadNote", "BigNote") and not its_:
                    its_ = [{"text": " ".join(str(x) for x in (o.get("kicker"), o.get("text")) if x) if o["type"] == "BigNote" else o.get("text", ""),
                             "at": o.get("at", o.get("start"))}]
                news = sorted(it["at"] for it in its_ if isinstance(it.get("at"), (int, float)) and it.get("mode", "new") == "new") if o["type"] == "HeadNote" else []
                for it in its_:
                    a_ = it.get("at", o.get("start"))
                    txt = str(it.get("text") or it.get("label") or "")
                    if not isinstance(a_, (int, float)) or not txt: continue
                    stop_t = it["until"] if isinstance(it.get("until"), (int, float)) else oend
                    if news: stop_t = min([stop_t] + [x for x in news if x > a_ + 0.01])
                    if isinstance(it.get("strikeAt"), (int, float)): stop_t = min(stop_t, it["strikeAt"] + 0.6)
                    need = max(0.8, 0.5 + 0.3 * len(txt.split())) + (len(txt) / float(o.get("cps", 26)) if o["type"] == "HeadNote" else 0)
                    if stop_t - a_ < need - 0.05:
                        S.append(f"{o['type']}@{a_:.1f}s '{txt[:30]}' is readable for {stop_t - a_:.2f}s, needs >= {need:.1f}s")
            elif o["type"] == "Shout" and isinstance(o.get("end"), (int, float)):
                for k_, ln in enumerate(o.get("lines") or [{"text": o.get("text", "")}]):
                    a_ = ln.get("at", (o.get("at") or o["start"]) + k_ * 0.09)
                    need = max(1.0, 0.6 + 0.3 * len(str(ln.get("text", "")).split()))
                    if isinstance(a_, (int, float)) and o["end"] - a_ < need - 0.05:
                        S.append(f"Shout@{o['start']:.1f}s: line '{ln.get('text')}' is readable for {o['end'] - a_:.2f}s, needs >= {need:.1f}s")
            elif isinstance(o.get("end"), (int, float)) and isinstance(o.get("start"), (int, float)) and o["type"] in TEXT_OVERLAYS:
                n = max(1, sum(len(t_.split()) for t_ in texts))
                need = max(1.2, 0.6 + 0.3 * n) if o["type"] != "Callout" else 0.8
                if o["end"] - o["start"] < need - 0.05:
                    S.append(f"{o['type']}@{o['start']:.1f}s is on screen {o['end'] - o['start']:.2f}s but needs >= {need:.1f}s to read {n} words: lengthen it or drop it")
        # 2b face: text graphics must not sit on the eyes/mouth while the face is full-frame
        full_at = lambda t_: all(L.get("views") and any(v.get("camera") for v in L["views"]) for L in self.layout_keys if L["t"] <= t_ + 1e-6 and L is max((x for x in self.layout_keys if x["t"] <= t_ + 1e-6), key=lambda x: x["t"]))
        for o in self.ovs:
            box = self.overlay_box(o)
            if not box or not isinstance(o.get("start"), (int, float)) or o.get("layer") in ("back", "behind"): continue
            if o.get("type") == "Checklist" and o.get("backdrop", True) is not False: continue  # the face is blurred under it on purpose
            a_, b_ = o["start"], o["end"] if isinstance(o.get("end"), (int, float)) else o["start"] + 1.0
            for tt in sorted({a_ + 0.3, (a_ + b_) / 2, max(a_ + 0.3, b_ - 0.25)}):
                if tt > b_ or not full_at(tt): continue
                fx0, fy0, fx1, fy1 = self.face_box_at(tt)
                ix = max(0, min(box[2], fx1) - max(box[0], fx0)); iy = max(0, min(box[3], fy1) - max(box[1], fy0))
                if ix * iy > 0.18 * (fx1 - fx0) * (fy1 - fy0):
                    S.append(f"{o['type']}@{o['start']:.1f}s covers the speaker's eyes/mouth at {tt:.1f}s (face ~y {fy0:.0f}-{fy1:.0f}px, "
                             f"camera zoom included): move it above the head, below the chin, beside the face, or use a card/split layout")
                    break
        # 2b' text graphics must not run into the caption band (unless captions are hidden/moved there)
        cap_y = self.caps["y"]; band_top = cap_y - self.style["captions"]["size"] * 1.9 - 24
        for o in self.ovs:
            box = self.overlay_box(o)
            if not box or not isinstance(o.get("start"), (int, float)) or o["type"] in ("Counter",): continue
            mid = o["start"] + 0.3
            zone = [z for z in self.caps["zones"] if z["from"] <= mid < z["to"]]
            if any(z.get("hidden") or z.get("y") for z in zone): continue
            if box[3] > band_top and box[1] < cap_y:
                self.lint.append(f"{o['type']}@{o['start']:.1f}s reaches y={box[3]:.0f}px, into the caption band ({band_top:.0f}-{cap_y}px): move it up or move captions with a zone 'y'")
        # 2c a card/split/pip layout exists to make room for a graphic; without one the frame is half empty
        keys = self.layout_keys
        for k, L in enumerate(keys):
            if any(v.get("camera") for v in L.get("views", [])): continue
            a_, b_ = L["t"], (keys[k + 1]["t"] if k + 1 < len(keys) else self.total)
            busy = [o for o in self.ovs if o["type"] not in ("ProgressBar", "LightLeak", "Flash", "Burst") and isinstance(o.get("start"), (int, float))
                    and o["start"] < b_ and (o.get("end") if isinstance(o.get("end"), (int, float)) else self.total) > a_ + 0.3]
            if not busy and b_ - a_ > 0.8:
                S.append(f"layout change at {a_:.1f}s leaves empty space for {b_ - a_:.1f}s (no graphic in it - maybe filtered by visual_overlays='{self.settings['visual_overlays']}'): "
                         "add the graphic (priority 1) or remove the layout")
        # 3 overload / repetition
        per_min = len([o for o in self.ovs if o["type"] in TEXT_OVERLAYS]) / dur * 60
        lim = R.get("maxGraphicsPerMin", 14)
        if per_min > lim:
            S.append(f"graphics overload: {per_min:.0f} text graphics per minute (style limit {lim}); keep the ones that explain something")
        tl = sorted([o for o in self.ovs if o["type"] in TEXT_OVERLAYS and isinstance(o.get("start"), (int, float))], key=lambda o: o["start"])
        for o in tl:
            live = [p for p in tl if p is not o and p["start"] <= o["start"] < (p.get("end") or p["start"] + 2) and p.get("layer", "above") == o.get("layer", "above")]
            if len(live) >= 2:
                S.append(f"{len(live) + 1} text graphics on screen at {o['start']:.1f}s: one focal point at a time"); break
        # 3b monotony: the template feel comes from the same device and the same look over and over
        own = [o for o in tl if not o.get("auto")]
        cap = max(5, math.ceil(dur / 8))
        for ty_, n_ in Counter(o["type"] for o in own if o["type"] != "SceneBuild").most_common():
            if n_ > cap:
                S.append(f"monotony: {ty_} used {n_}x in {dur:.0f}s (max {cap}). Give some of those beats another device: text behind the speaker, "
                         "a Cutout, a scene, a different overlay type or a custom component"); break
        def look_of(o):
            y_ = o.get("y") if isinstance(o.get("y"), (int, float)) else -1
            return (o["type"], o.get("layout") or o.get("align") or "center", o.get("fx") or o.get("mode") or "default", bool(o.get("stack")),
                    o.get("layer") == "behind", round(y_ / 160), o.get("surface"), o.get("badge"), o.get("look"), o.get("slot"), o.get("dir"), o.get("anchor"))
        for lk, n_ in Counter(look_of(o) for o in own if o["type"] not in SCENES and o["type"] not in ("SearchStack", "Floaters", "Steps", "HeadNote")).most_common():
            if n_ >= 3:
                S.append(f"same look {n_}x: {lk[0]} with layout '{lk[1]}', fx '{lk[2]}' at the same height. Vary it (layout left/right, stack, "
                         "behind the head; fx slam/slide/type; another position) or use a different device"); break
        types = [o["type"] if o["type"] != "SceneBuild" else f"SceneBuild#{k_}" for k_, o in enumerate(tl)]  # each SceneBuild is its own composition
        for k in range(len(types) - 3):
            if len(set(types[k:k + 4])) == 1:
                S.append(f"four {types[k]} in a row: vary the device or merge them (e.g. one Steps list)"); break
        presets = [p for p in (self.creative.get("layouts") or [])]
        for k in range(len(presets) - 2):
            if len({p.get("preset", "full") for p in presets[k:k + 3]}) == 1 and presets[k].get("preset", "full") != "full":
                S.append(f"layout '{presets[k].get('preset')}' three times in a row: alternate full -> card/split -> full"); break
        wipes = sum(1 for L in self.layout_keys if L.get("transition") == "wipe")
        if wipes > R.get("maxWipes", 1):
            S.append(f"{wipes} wipes: the wipe is a signature move, use it at most {R.get('maxWipes', 1)}x")
        fx_types = [c["fx"] for c in self.cutfx]
        for k in range(len(fx_types) - 2):
            if len(set(fx_types[k:k + 3])) == 1:
                S.append(f"cut transition '{fx_types[k]}' three times in a row: alternate or leave plain jump cuts"); break
        bursts = sum(1 for o in self.ovs if o["type"] == "Burst")
        if bursts > 1: S.append(f"{bursts} Bursts: one at most (hook or CTA)")
        flashes = [o["start"] for o in self.ovs if o["type"] == "Flash"] + [c["t"] for c in self.cutfx if c["fx"] == "flash"]
        if len(flashes) > max(1, dur / 10 * 3):
            S.append(f"{len(flashes)} flashes: max 3 per 10 s (photosensitivity, cheapness)")
        # 4 sound
        cues = [c for c in self.cues if c["kind"] not in ("typing",)]
        if len(cues) / dur > R.get("maxSfxPerSec", 1.2):
            S.append(f"SFX spam: {len(cues) / dur:.1f} sounds per second; one sound per meaningful visual event (lower sound_effects or mute some with sfx:'none')")
        # 5 hook
        ev = self.visual_events()
        if not any(e <= 1.6 for e in ev):
            S.append("weak hook: nothing changes on screen in the first 1.6 s (add a punch-in on the hook word or a KineticTitle)")

    def direction_notes(self):
        """Not errors, but the difference between a template and an edit: a concept and one moment only this video has."""
        C = self.creative
        if not C.get("concept"):
            self.suggestions.append('CONCEPT? creative.json has no "concept" {idea, signature}: decide what this video is about and the one '
                                    "visual only it has before choosing elements (SKILL.md 'Direct it first')")
        sig = [o for o in self.ovs if o.get("signature") or o.get("layer") == "behind" or o.get("type") in ("Cutout", "BRoll")
               or o.get("type") in self.custom_types]
        if not sig:
            self.suggestions.append("SIGNATURE? nothing here is specific to this video (no custom component, no text behind the speaker, no Cutout, "
                                    "no b-roll, nothing marked \"signature\"). Another video with the same style would look the same")
        own = [o for o in self.ovs if not o.get("auto") and o.get("type") not in ("Accents", "MoodGrade")]
        kinds = {f"kit:{it.get('kind')}" for o in own if o.get("type") == "SceneBuild" for it in o.get("items", []) if isinstance(it, dict)}
        self.variety = {"devices": len({o["type"] for o in own if o.get("type") != "SceneBuild"} | kinds), "signature": len(sig), "custom": len(self.custom)}

    def sounds(self):
        C = self.creative
        lvl = SFX_LEVEL.get(self.settings["sound_effects"], 2)
        cues = []
        def add(t, kind, **kw):
            if t is None or t < 0 or t > self.total: return
            cues.append({"t": round(t, 3), "kind": kind, **kw})
        if lvl >= 1:
            for L in self.layout_keys[1:]:
                if L.get("sfx") == "none": continue
                if L["transition"] == "wipe": add(L["t"] + L["dur"] * 0.45, L.get("sfx") or "whoosh", gain_db=0, dur=0.55)
                elif L["transition"] == "morph": add(L["t"] + L["dur"] * 0.5, L.get("sfx") or "swoosh", gain_db=-3)
            for o in self.ovs:
                if o.get("sfx") == "none": continue
                ty, st = o["type"], o.get("start")
                if ty == "Badge": add(st, "pop", gain_db=-4, pitch=1.1)
                elif ty == "KineticTitle":
                    for ln in o.get("lines", []): add(ln.get("at", st), "pop", gain_db=-5)
                elif ty == "Waveform": add(st, "swoosh", gain_db=-6); add(o.get("cutAt"), "snap", gain_db=-2)
                elif ty == "FilmStrip": add(st + 0.1, "whoosh", gain_db=-4, dur=0.45)
                elif ty == "Newsprint": add(st + 0.08, "swoosh", gain_db=-9, dur=0.42)
                elif ty == "InsightCard": add(st + 0.05, "snap", gain_db=-9)
                elif ty == "EvidenceCard": add(st + 0.1, "swoosh", gain_db=-13, dur=0.55)
                elif ty == "ScrapNote": add(st + 0.07, "pop", gain_db=-11, pitch=0.86)
                elif ty == "FloatCard": add(o.get("at", st), "pop", gain_db=-4, pitch=0.9)
                elif ty == "Tile": add(o.get("at", st), "pop", gain_db=-5, pitch=0.95 + 0.05 * (len(cues) % 4))
                elif ty == "PromptBar":
                    at = o.get("at", st + 0.15); ty_ = o.get("typing", 0.9)
                    if lvl >= 2: add(at, "typing", until=at + ty_, gain_db=-6)
                    add(at + ty_, "click", gain_db=-2)
                elif ty == "ChatPanel":
                    add(st, "swoosh", gain_db=-6)
                    for m in o.get("messages", []):
                        if m["role"] == "user":
                            end = m["at"] + m.get("typing", 0.8)
                            if lvl >= 2: add(m["at"], "typing", until=end, gain_db=-6)
                            add(end, "click", gain_db=-2)
                        else:
                            add(m["at"], "pop", gain_db=-6, pitch=1.2)
                            if m.get("progress"):
                                pr = m["progress"]; add(pr["at"] + pr["dur"], "ding", gain_db=-3)
                elif ty == "EndCard":
                    at = o.get("at", {})
                    wm = at.get("wordmark", st + 0.3)
                    var = o.get("variant") or self.style.get("endcard", {}).get("variant", "block")
                    if var == "title":  # calm: no impacts
                        add(wm, "shimmer", gain_db=-8); add(at.get("button", st + 0.6), "click", gain_db=-6)
                    elif var == "newsprint":
                        add(at.get("kicker", st), "swoosh", gain_db=-7, dur=0.35); add(wm + 0.1, "snap", gain_db=-5)
                        add(at.get("button", st + 0.6), "click", gain_db=-4)
                    elif var == "hero":
                        add(wm - 0.03, "swoosh", gain_db=-5); add(wm + 0.5, "shimmer", gain_db=-10)
                        if o.get("button"): add(at.get("button", st + 0.6), "click", gain_db=-5)
                    elif var == "sticker":
                        add(wm, "pop", gain_db=-2); add(wm + 0.22, "pop", gain_db=-3, pitch=1.15); add(wm + 0.3, "shimmer", gain_db=-9)
                        add(at.get("button", st + 0.6), "pop", gain_db=-4, pitch=1.25)
                    else:
                        add(wm, "impact", gain_db=-3); add(wm + 0.05, "shimmer", gain_db=-8)
                        add(at.get("button", st + 0.6), "pop", gain_db=-4)
                elif ty == "Viewfinder": pass
                elif ty == "LowerThird": add(st + 0.05, "swoosh", gain_db=-9, dur=0.45)
                elif ty == "Steps":
                    for it in o.get("items", []): add(it.get("at"), "pop", gain_db=-7, pitch=0.95)
                elif ty == "StickerStack":
                    for k_, it in enumerate(o.get("items", [])): add(it.get("at"), "pop", gain_db=-6, pitch=0.9 + 0.06 * (k_ % 4))
                elif ty == "Sticker": add(o.get("at", st), "pop", gain_db=-4, pitch=1.05)
                elif ty == "TitleCard": add(st + 0.05, "swoosh", gain_db=-11, dur=0.6)
                elif ty == "Cutout": add(st, "whoosh", gain_db=-6, dur=0.4)
                elif ty == "ShapeWipe":
                    shp = o.get("shape", "orb")
                    if shp == "bloom": add(o["at"] - 0.25, "riser", gain_db=-12, dur=0.3); add(o["at"], "shimmer", gain_db=-6)
                    elif shp == "iris": add(o["at"] - 0.2, "swoosh", gain_db=-8, dur=0.6)
                    else:
                        add(o["at"] - 0.2, "whoosh", gain_db=-4, dur=0.55)
                        if shp == "orb": add(o["at"] + 0.04, "shimmer", gain_db=-13)
                elif ty == "HeroTitle":
                    add(o.get("at", st) - 0.03, "swoosh", gain_db=-6)
                    if o.get("tag"): add(o.get("tagAt", o.get("at", st) + 0.3), "click", gain_db=-8, pitch=1.2)
                elif ty == "SceneStack":
                    for k_, it in enumerate(o.get("items", [])): add(it.get("at"), "swoosh", gain_db=-9)
                elif ty == "SceneChat":
                    for m in o.get("messages", []):
                        if m.get("typed"):
                            ty_ = m.get("typing", 1.0)
                            if lvl >= 2: add(m["at"] - ty_, "typing", until=m["at"] - 0.1, gain_db=-7)
                            add(m["at"], "click", gain_db=-3)
                        else: add(m["at"], "pop", gain_db=-6, pitch=1.15 if m.get("side") == "right" else 0.95)
                        if m.get("progress"): add(m["progress"]["at"] + m["progress"].get("dur", 0.8), "ding", gain_db=-6)
                elif ty == "ScenePrompt":
                    if o.get("attach"): add(o.get("attachAt", o.get("at", st + 0.5) - 0.1) - 0.3, "swoosh", gain_db=-8)
                    at_ = o.get("at", st + 0.5); ty_ = o.get("typing", 1.0)
                    if lvl >= 2: add(at_, "typing", until=at_ + ty_, gain_db=-7)
                    add(at_ + ty_ + 0.08, "click", gain_db=-3)
                    for s_ in o.get("steps", []): add(s_.get("at", 0) + 0.3, "ding", gain_db=-10, pitch=1.1)
                elif ty == "SceneFeatures":
                    for k_, it in enumerate(o.get("items", [])):
                        add(it.get("at"), "pop", gain_db=-5, pitch=0.9 + 0.06 * (k_ % 4))
                        if it.get("art") == "cut": add((it.get("at") or 0) + 0.3, "snap", gain_db=-8)
                elif ty == "ScenePhones":
                    for p_ in o.get("phones", []): add(p_.get("at"), "swoosh", gain_db=-7)
                elif ty == "Accents": add(st + 0.05, "shimmer", gain_db=-15)
                elif ty == "MoodGrade":
                    if o.get("mood") == "shock": add(st, "impact", gain_db=-5); add(st - 0.6, "riser", gain_db=-16, dur=0.6)
                    elif o.get("mood") in ("dark", "serious"): add(st, "swoosh", gain_db=-14, dur=0.7)
                elif ty == "SearchStack":
                    for it in o.get("items", []):
                        a_ = it.get("at")
                        if not isinstance(a_, (int, float)): continue
                        add(a_, "pop", gain_db=-9, pitch=1.15)
                        if it.get("text") and lvl >= 2: add(a_ + 0.12, "typing", until=a_ + 0.12 + it.get("typing", min(1.4, 0.05 * len(it["text"]) + 0.3)), gain_db=-10)
                elif ty == "Floaters":
                    its = [it.get("at") for it in o.get("items", []) if isinstance(it.get("at"), (int, float))]
                    if its: add(min(its), "swoosh", gain_db=-13, dur=0.5)
                elif ty == "Shout":
                    a_ = o.get("at", st); add(a_ - 0.12, "whoosh", gain_db=-7, dur=0.35); add(a_, "impact", gain_db=-3)
                elif ty == "DepthText":
                    md, a_ = o.get("mode", "stack"), o.get("at", st)
                    if md == "echo": add(a_ - 0.1, "whoosh", gain_db=-8, dur=0.35); add(a_ + 0.02, "impact", gain_db=-5)
                    elif md == "wall": add(a_, "whoosh", gain_db=-7, dur=0.6); add(a_ + 0.05, "riser", gain_db=-17, dur=max(0.6, (o.get("end") or a_ + 2) - a_ - 0.2))
                    elif md == "split": add(a_ + 0.05, "whoosh", gain_db=-7, dur=0.5)
                    else:
                        for k_, ln in enumerate(o.get("lines") or [{"at": a_}]):
                            if isinstance(ln, dict): add(ln.get("at", a_ + 0.12 * k_), "swoosh", gain_db=-9 - 2 * k_, dur=0.35)
                elif ty == "Glossy":
                    its = [it for it in o.get("items", []) if isinstance(it, dict) and isinstance(it.get("at"), (int, float))]
                    for k_, it in enumerate(its): add(it["at"], "pop", gain_db=-8, pitch=1.0 + 0.08 * (k_ % 4))
                    if its: add(its[0]["at"] + 0.15, "shimmer", gain_db=-14)
                elif ty == "GlowPill":
                    a_ = o.get("at", st); add(a_, "swoosh", gain_db=-9, dur=0.4)
                    if o.get("badge", "check") != "none": add(o.get("badgeAt", a_ + 0.3), "ding", gain_db=-9, pitch=1.15)
                elif ty == "BadgeOrbit":
                    for k_, it in enumerate([it for it in o.get("items", []) if isinstance(it, dict)]):
                        a_ = it.get("at", st + 0.12 * k_)
                        if isinstance(a_, (int, float)): add(a_, "pop", gain_db=-10, pitch=0.95 + 0.06 * k_)
                elif ty == "SideCards":
                    for it in o.get("items", []):
                        if isinstance(it, dict) and isinstance(it.get("at"), (int, float)): add(it["at"] + 0.05, "whoosh", gain_db=-8, dur=0.45)
                elif ty == "Checklist":
                    add(st, "swoosh", gain_db=-9, dur=0.5)
                    for k_, it in enumerate(o.get("items", [])):
                        if isinstance(it, dict) and isinstance(it.get("at"), (int, float)): add(it["at"], "pop", gain_db=-8, pitch=1.0 + 0.07 * k_); add(it["at"] + 0.12, "tick", gain_db=-12)
                elif ty == "HeadRing":
                    a_ = o.get("at", st); add(a_, "swoosh", gain_db=-9, dur=0.5); add(a_ + 0.3, "shimmer", gain_db=-14)
                elif ty == "HeadNote":
                    seq = [it for it in (o.get("items") or [{"text": o.get("text"), "at": o.get("at", st)}]) if isinstance(it, dict) and it.get("text")]
                    for it in seq:
                        a_ = it.get("at", st)
                        if not isinstance(a_, (int, float)): continue
                        n_ = len(str(it["text"])) + (5 if it.get("mode") == "add" else 0)
                        if lvl >= 2: add(a_ + 0.03, "typing", until=a_ + min(1.3, n_ / float(o.get("cps", 26))), gain_db=-16)
                        else: add(a_, "tick", gain_db=-15)
                elif ty == "NoteTags":
                    if o.get("label") and lvl >= 2:
                        its_ = [it.get("at") for it in o.get("items", []) if isinstance(it, dict) and isinstance(it.get("at"), (int, float))]
                        la = o.get("labelAt", (min(its_) - 0.1) if its_ else st)
                        if isinstance(la, (int, float)): add(la, "typing", until=la + min(0.7, len(str(o["label"])) / 30), gain_db=-17)
                    for k_, it in enumerate([it for it in o.get("items", []) if isinstance(it, dict)]):
                        if isinstance(it.get("at"), (int, float)): add(it["at"] + 0.05, "paper", gain_db=-8, pitch=1.0 + 0.06 * (k_ % 3))
                        if isinstance(it.get("strikeAt"), (int, float)): add(it["strikeAt"], "scribble", gain_db=-12, dur=0.24)
                elif ty == "NoteBoard":
                    add(st + 0.08, "page", gain_db=-7)
                    if o.get("title") and lvl >= 2:
                        ta = o.get("titleAt", st + 0.05)
                        if isinstance(ta, (int, float)): add(ta, "typing", until=ta + min(0.9, len(str(o["title"])) / 30), gain_db=-15)
                    k_ = 0
                    for g in o.get("groups") or [{"items": o.get("items", [])}]:
                        for it in (g.get("items", []) if isinstance(g, dict) else []):
                            if isinstance(it, dict) and isinstance(it.get("at"), (int, float)):
                                if lvl >= 2: add(it["at"], "typing", until=it["at"] + min(0.9, len(str(it.get("text", ""))) / 30), gain_db=-13)
                                else: add(it["at"], "tick", gain_db=-12)
                                k_ += 1
                    if isinstance(o.get("strikeAt"), (int, float)): add(o["strikeAt"], "scribble", gain_db=-10, dur=min(0.7, 0.2 + 0.07 * k_))
                    if isinstance(o.get("end"), (int, float)): add(o["end"] - 0.25, "page", gain_db=-11)
                elif ty == "BigNote":
                    a_ = o.get("at", st)
                    if o.get("kicker") and lvl >= 2:
                        ka = o.get("kickerAt", st)
                        if isinstance(ka, (int, float)): add(ka, "typing", until=ka + min(0.9, len(str(o["kicker"])) / 28), gain_db=-15)
                    if isinstance(a_, (int, float)):
                        add(a_ - 0.02, "swoosh", gain_db=-10)
                        if o.get("mark", "highlight") != "none": add(o.get("markAt", a_ + 0.22), "scribble", gain_db=-11, dur=0.34 if o.get("mark", "highlight") != "circle" else 0.5)
                elif ty == "NoteClips":
                    for it in o.get("items", []):
                        if isinstance(it, dict) and isinstance(it.get("at"), (int, float)):
                            add(it["at"] + 0.04, "swoosh", gain_db=-10)
                            if it.get("label"): add(it["at"] + 0.32, "paper", gain_db=-12, pitch=1.1)
                elif ty == "Doodles":
                    for it in o.get("items", []):
                        if isinstance(it, dict) and isinstance(it.get("at"), (int, float)) and it.get("sfx") != "none":
                            add(it["at"], "scribble", gain_db=-11, dur=float(it.get("dur", 0.4)))
                elif ty == "FeedWall":
                    a_ = o.get("at", st); add(a_, "whoosh", gain_db=-6, dur=0.5)
                    for k_, sw in enumerate(o.get("swipes", [])):
                        if isinstance(sw, (int, float)): add(sw, "swoosh", gain_db=-9, pitch=1.0 + 0.04 * k_)
                elif ty == "StoryTitle":
                    add(o.get("at", st), "swoosh", gain_db=-8, dur=0.5); add(o.get("at", st) + 0.4, "shimmer", gain_db=-9)
                elif ty == "SceneLens":
                    add(st, "swoosh", gain_db=-9, dur=0.6)
                    for fo in o.get("focus", []):
                        if isinstance(fo.get("at"), (int, float)): add(fo["at"] - 0.2, "swoosh", gain_db=-15, dur=0.35)
                elif ty == "Headline": add(st + 0.02, "swoosh", gain_db=-7, dur=0.35); add(st + 0.2, "snap", gain_db=-6)
                elif ty == "Counter":
                    ca = o.get("countAt", st + 0.1); add(ca, "riser", gain_db=-14, dur=o.get("countDur", 0.9)); add(ca + o.get("countDur", 0.9), "ding", gain_db=-6)
                elif ty == "Callout": add(st + 0.05, "swoosh", gain_db=-12, dur=0.4)
                elif ty == "BRoll": add(st, "whoosh" if o.get("enter") == "whip" else "swoosh", gain_db=-6, dur=0.4)
                elif ty == "LightLeak": add(st + 0.1, "shimmer", gain_db=-14)
                elif ty == "SceneBuild":
                    # one sound per meaningful event: strong moves, typing, counting, stamps; quiet rises stay silent
                    for it in o.get("items", []):
                        if not isinstance(it, dict) or it.get("sfx") == "none" or not isinstance(it.get("at"), (int, float)): continue
                        a_, mv, kd = it["at"], it.get("move") or "rise", it.get("kind")
                        if mv == "drop": add(a_ + 0.1, "whoosh", gain_db=-7, dur=0.4); add(a_ + 0.5, "impact", gain_db=-11)
                        elif mv == "slam": add(a_ - 0.08, "whoosh", gain_db=-9, dur=0.3); add(a_ + 0.05, "impact", gain_db=-7)
                        elif mv == "slide": add(a_ + 0.1, "whoosh", gain_db=-8, dur=0.4)
                        elif mv in ("pop", "grow"): add(a_, "pop", gain_db=-8, pitch=1.0 + 0.05 * (len(cues) % 4))
                        if kd == "bar" and isinstance(it.get("typeAt"), (int, float)) and it.get("text"):
                            ty_ = float(it.get("typing", min(1.4, 0.05 * len(it["text"]) + 0.3)))
                            if lvl >= 2: add(it["typeAt"], "typing", until=it["typeAt"] + ty_, gain_db=-9)
                        elif kd == "counter":
                            ca = it.get("countAt", a_); cd = float(it.get("countDur", 1.0)); add(ca, "riser", gain_db=-14, dur=cd); add(ca + cd, "ding", gain_db=-7)
                        elif kd == "tag" and isinstance(it.get("stampAt"), (int, float)): add(it["stampAt"], "impact", gain_db=-6)
                        elif kd == "arrow": add(a_, "swoosh", gain_db=-12, dur=0.5)
                        elif kd == "steps":
                            for s_ in it.get("items", []):
                                if isinstance(s_.get("at"), (int, float)): add(s_["at"], "pop", gain_db=-8, pitch=1.1)
                        elif kd == "bars":
                            for b_ in (it.get("ats") or [])[:6]:
                                if isinstance(b_, (int, float)): add(b_, "pop", gain_db=-10, pitch=0.95)
                        elif kd == "crowd" and it.get("bars"): add(it.get("barsAt", a_ + 0.3), "pop", gain_db=-9, pitch=1.15)
                        elif kd == "figure" and isinstance(it.get("above"), dict):
                            ab = it["above"]
                            if isinstance(ab.get("at"), (int, float)): add(ab["at"], "pop", gain_db=-9, pitch=1.15)
                            if ab.get("text") and isinstance(ab.get("typeAt"), (int, float)) and lvl >= 2:
                                add(ab["typeAt"], "typing", until=ab["typeAt"] + float(ab.get("typing", 1.0)), gain_db=-9)
                elif ty in self.custom_types and not o.get("cues"): add(st + 0.03, o.get("enter_sfx", "swoosh"), gain_db=-8, dur=0.45)
                for c_ in o.get("cues", []) if isinstance(o.get("cues"), list) else []:
                    if isinstance(c_, dict) and isinstance(c_.get("at"), (int, float)):
                        add(c_["at"], c_.get("kind", "pop"), **{k: v for k, v in c_.items() if k not in ("at", "kind")})
            for c in self.cutfx:
                if c["fx"] in ("whip", "push"): add(c["t"], "whoosh", gain_db=-4 if c["fx"] == "whip" else -8, dur=0.35)
                elif c["fx"] == "zoom": add(c["t"], "swoosh", gain_db=-6)
                elif c["fx"] == "flash": add(c["t"], "impact", gain_db=-9)
                elif c["fx"] == "leak": add(c["t"], "shimmer", gain_db=-13)
            if lvl >= 2:
                for k in self.cam_keys:
                    if k.get("ease") == "outExpo" and not any(b["start"] <= k["t"] <= b["end"] for b in self.note_boards()):
                        add(k["t"], self.style["sound"].get("punch_sfx", "impact"), gain_db=-10)
            for h_ in getattr(self, "hits", []):
                if h_["kind"] == "shake": add(h_["t"], "impact", gain_db=-6)
                elif h_["kind"] == "glitch": add(h_["t"], "glitch", gain_db=-8, dur=h_["dur"])
            if lvl >= 2 and self.style["sound"].get("emph_pop", True):
                for pg in self.caps["pages"]:
                    for w in pg["words"]:
                        if w.get("emph"): add(w["start"], "pop", gain_db=-9 if lvl < 3 else -6)
        for c in C.get("sfx", []):
            r = self.resolve(c)
            t = self.T(r["at"]) if "at" in r else r.get("t")
            add(t, r["kind"], **{k: v for k, v in r.items() if k not in ("at", "t", "kind")})
        cues.sort(key=lambda c: (c["t"], -c.get("gain_db", 0)))
        dedup = []
        for c in cues:  # one cue per kind per 80 ms (overlays and caption chips often fire on the same word)
            if any(d["kind"] == c["kind"] and abs(c["t"] - d["t"]) < 0.08 for d in dedup[-8:]): continue
            dedup.append(c)
        self.cues = dedup
        mu = C.get("music", {})
        mfile = mu.get("file")
        if mfile and not Path(mfile).exists():
            self.lint.append(f"music.file not found: {mfile} -> falling back to the procedural bed"); mfile = None
        self.music = {"enabled": self.settings["music"] == "enabled", "style": self.style["sound"]["bed"], "file": str(Path(mfile).resolve()) if mfile else None,
                      "db": mu.get("db", self.style["sound"]["music_db"]), "sfx_db": self.style["sound"]["sfx_db"],
                      "hit_at": self.T(mu["hit_at"]) if mu.get("hit_at") else None,
                      "break_at": self.T(mu["break_at"]) if mu.get("break_at") else None}

    def plate_captions(self):
        """Clean (cardless) captions are white text: over a light backdrop (card/split/pip/none layouts) they
        disappear. Give those pages a dark translucent plate so they stay readable."""
        if self.style["captions"].get("variant") != "clean": return
        timeline = []
        for L in self.creative.get("layouts") or []:
            try: timeline.append((self.T(L["at"]), L.get("preset", "full")))
            except Exception: pass  # noqa: BLE001
        timeline.sort(key=lambda x: x[0])
        def preset_at(t):
            cur = "full"
            for at, pr in timeline:
                if at <= t + 1e-6: cur = pr
            return cur
        n = 0
        for pg in self.caps["pages"]:
            mid = (pg["start"] + pg["end"]) / 2
            if preset_at(pg["start"]) not in ("full", "raw-full") or preset_at(mid) not in ("full", "raw-full"):
                pg["plate"] = True; n += 1
        if n: print(f"[plan] {n} caption pages over the backdrop get a readability plate")

    def clothes_contrast(self):
        """Cutout colour fields vs the speaker's clothes: sample the torso under the face on a middle frame; when a planned
        field colour is close to it, the body melts into the background (red shirt on a red poster). Suggest a contrast."""
        cols = []
        for o in self.ovs:
            if o.get("type") == "Cutout" and o.get("bg", "color") in ("color", "poster"):
                c_ = o.get("color") or "accent"
                c_ = self.style["colors"].get(c_, c_)
                if isinstance(c_, str) and re.match(r"^#[0-9a-fA-F]{6}$", c_): cols.append(c_)
        if not cols: return
        try:
            import subprocess, numpy as np
            src = self.media.get("normalized", {}).get("file") or self.media["file"]
            dur_ = float(self.media.get("normalized", {}).get("duration") or self.media["duration"])
            raw = subprocess.run(["ffmpeg", "-v", "error", "-ss", f"{dur_ * 0.5:.2f}", "-i", src, "-frames:v", "1", "-vf", "scale=90:160,format=rgb24",
                                  "-f", "rawvideo", "-"], capture_output=True, timeout=30).stdout
            if len(raw) < 90 * 160 * 3: return
            a = np.frombuffer(raw[:90 * 160 * 3], np.uint8).reshape(160, 90, 3).astype(float)
            fm = (json.loads((self.work / "faces.json").read_text()).get("median", {}) if (self.work / "faces.json").exists() else {}) or {}
            cx, cy, fh = fm.get("cx", 0.5), fm.get("cy", 0.42), fm.get("h", 0.2)
            y0 = int(min(150, (cy + fh * 0.9) * 160)); y1 = int(min(159, y0 + 0.2 * 160)); x0 = int(max(0, (cx - 0.14) * 90)); x1 = int(min(89, (cx + 0.14) * 90))
            if y1 - y0 < 4 or x1 - x0 < 4: return
            shirt = a[y0:y1, x0:x1].reshape(-1, 3).mean(0)
            for c_ in sorted(set(cols)):
                rgb = np.array([int(c_[1:3], 16), int(c_[3:5], 16), int(c_[5:7], 16)], float)
                if np.linalg.norm(shirt - rgb) < 110:
                    hx = "#%02X%02X%02X" % tuple(int(v) for v in shirt)
                    self.suggestions.append(f"CONTRAST? the speaker's clothes ({hx}) are close to the Cutout/poster colour {c_}: the body melts into the field - "
                                            "pick a contrasting colour (complementary hue, or ink/paper) for color/color2")
        except Exception:  # noqa: BLE001
            pass

    def wall_lum(self):
        """Mean brightness (0-1) of the wall above the speaker's head, sampled from three frames. Lets 'auto' tone pick
        ink type on light walls and paper type on dark ones (type behind the speaker, doodles, head rings)."""
        if hasattr(self, "_wall_lum"): return self._wall_lum
        self._wall_lum = None
        try:
            import subprocess, numpy as np
            src = self.media.get("normalized", {}).get("file") or self.media["file"]
            dur_ = float(self.media.get("normalized", {}).get("duration") or self.media["duration"])
            vals = []
            for k in (0.2, 0.5, 0.8):
                raw = subprocess.run(["ffmpeg", "-v", "error", "-ss", f"{dur_ * k:.2f}", "-i", src, "-frames:v", "1", "-vf", "scale=64:114,format=gray",
                                      "-f", "rawvideo", "-"], capture_output=True, timeout=30).stdout
                if len(raw) >= 64 * 114:
                    a = np.frombuffer(raw[:64 * 114], np.uint8).reshape(114, 64) / 255.0
                    vals.append(float(np.concatenate([a[4:30, 2:20].ravel(), a[4:30, 44:62].ravel()]).mean()))
            if vals: self._wall_lum = round(sum(vals) / len(vals), 3)
        except Exception:  # noqa: BLE001
            pass
        return self._wall_lum

    def write(self, out):
        self.plate_captions()
        norm = self.media.get("normalized", {})
        E = {
            "version": 1, "fps": self.fps, "width": self.W, "height": self.H,
            "durationInFrames": int(round(self.total * self.fps)), "duration": self.total,
            "settings": self.settings, "style": self.style,
            "footage": {"src": "cut.mp4", "duration": round(self.footage_dur, 4), "width": norm.get("width", self.media["video"]["display_width"]),
                        "height": norm.get("height", self.media["video"]["display_height"])},
            "source": {"video": norm.get("file"), "voice": norm.get("voice48k"), "original": self.media["file"]},
            "segments": self.segs, "removed": self.removed,
            "faces": self.faces, "camera": self.cam_keys, "jumpZoom": self.jump, "layouts": self.layout_keys,
            "words": [{k: w[k] for k in ("i", "text", "start", "end", "emph", "icon") if k in w} for w in self.tokens],
            "captions": self.caps, "overlays": self.ovs, "thumbs": self.thumbs, "sfx": self.cues, "music": self.music,
            "cutFx": self.cutfx, "camPush": self.pushes, "hits": getattr(self, "hits", []), "wallLum": self.wall_lum(), "assets": self.extra_assets, "matte": getattr(self, "matte", None),
            "custom": self.custom, "brand": self.brand.get("name") if self.brand else None,
            "lint": self.lint, "slop": self.slop, "suggestions": self.suggestions,
            "editor": {"fillers_removed": self.fillers, "longest_static": round(self.longest_static, 2)},
        }
        Path(out).write_text(json.dumps(E, ensure_ascii=False, indent=1))
        src_len = self.media.get("normalized", {}).get("duration") or self.media["duration"]
        print(f"edit: {self.total:.2f}s ({E['durationInFrames']} frames) from {src_len:.2f}s source | {len(self.segs)} segments, "
              f"{len(self.removed)} pauses/retakes removed ({sum(r['dur'] for r in self.removed):.2f}s) | {len(self.caps['pages'])} caption pages | "
              f"{len(self.ovs)} overlays | {len(self.cues)} sfx | style={self.settings['style']} {self.W}x{self.H}")
        print(f"editor: {len(self.fillers)} filler words cut | {len(self.cutfx)} cut transitions ({', '.join(c['fx'] for c in self.cutfx) or '-'}) | "
              f"{sum(1 for p in self.pushes if p.get('k', 1) != 1)} rhythm pushes | longest static {self.longest_static:.1f}s")
        v_ = getattr(self, "variety", {})
        if v_:
            print(f"variety: {v_['devices']} different devices | {v_['signature']} signature moment(s) | {v_['custom']} custom component file(s)"
                  + (f" | brand kit '{self.brand.get('name', 'unnamed')}'" if self.brand else ""))
        for l in self.suggestions: print("  NOTE:", l)
        for l in self.lint: print("  LINT:", l)
        for l in self.slop: print("  SLOP:", l)
        if not self.lint and not self.slop: print("  CLEAN: no LINT, no SLOP")
        print("->", out)


def main():
    ap = argparse.ArgumentParser(description=__doc__, formatter_class=argparse.RawDescriptionHelpFormatter)
    ap.add_argument("--work", default="work")
    ap.add_argument("--transcript"); ap.add_argument("--creative"); ap.add_argument("--out")
    ap.add_argument("--style"); ap.add_argument("--intensity", choices=list(INTENSITY))
    ap.add_argument("--aspect", choices=list(ASPECTS)); ap.add_argument("--target-duration", type=float)
    ap.add_argument("--caption-style", choices=["clean", "kinetic", "highlighted"])
    ap.add_argument("--music", choices=["enabled", "disabled"]); ap.add_argument("--sfx", choices=list(SFX_LEVEL))
    ap.add_argument("--overlays", choices=["minimal", "moderate", "extensive"])
    ap.add_argument("--fps", type=int, choices=[60], help="always 60 (kept for compatibility)")
    a = ap.parse_args()
    P = Planner(a)
    P.cut(); P.captions(); P.camera(); P.layouts(); P.overlays(); P.cut_fx(); P.pushes = []; P.rhythm(); P.sounds(); P.slop_lint(); P.direction_notes()
    P.write(a.out or Path(a.work) / "edit.json")


if __name__ == "__main__":
    main()
