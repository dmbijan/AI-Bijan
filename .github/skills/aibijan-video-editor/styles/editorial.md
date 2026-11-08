# Style: EDITORIAL · `editorial`

> Cinematic magazine. Feels like a short film: letterbox, warm filmic grade, serif title cards, quiet confidence.
> Calm does not mean empty - every beat still has a deliberate composition.

**Use for** storytelling, thought leadership, calm education, testimonials, premium brands,
"clean", "minimalis", "sinematik", "jangan terlalu banyak efek".
**Not for** hard-selling ads or hype content (→ IMPACT / CREATOR POP).
**Recommended settings** editing_intensity medium · sound_effects subtle · visual_overlays moderate.

## Look
- **Letterbox bars** (automatic, `frame.letterbox`) slide in over the first second: the video "becomes a film".
- Warm filmic grade (lower saturation, deeper contrast, warm tint wash, soft vignette, grain). The "before" grade is
  flat and grey, so a raw → edited moment reads instantly.
- Warm off-white paper, ink type, **warm sand** accent (`#F2DDB4` on footage, `accentInk` brown on paper). No lime.
- **Poppins** (SemiBold) for captions and kickers, **Playfair Display** (italic for the key word) for every title.

## Captions
- `clean` captions with a feathered **cinema scrim** (dark pool behind the words, not a box) so white type reads on
  bright walls. Sentence case, 3–5 words per page, page ≤ 2.4 s, 62 px.
- Active word: thin sand underline. Emphasis word: serif italic (warm white, larger), ≤ 1 per page, about one per 3–4 s.

## Camera & cutting
- `talk` framing 1.12, subtle jump-zoom (1.035), operator drift. Slow `close` pushes (`"s": 1.17`, 1.8–2.4 s)
  on the hook line, the thesis and the conclusion. Punch-ins rare (≤ 1 per 10 s, `amount` ≤ 0.05).
- "Before" moments: layout `full` with `"grade": 0` + camera `wide` (cut), then morph back to `full` (0.6 s).

## Motion at 60 FPS
- Long eases, no bounce. Entrances 0.45–0.6 s, exits 0.3–0.35 s. Text rack-focuses (`focus`) or tracks in.
- Morphs only. No wipe, no flash, no Burst, no stickers.
- One graphic at a time: the previous one has fully left before the next begins.

## Signature graphics (plan them in creative.json)
| Moment | Element | Rule |
|---|---|---|
| Opening | magazine-cover title **behind the speaker**: `KineticTitle` `layer: "behind"`, a small sans line + one huge serif word (size 230–260, `tone: "ink"`) | the first spoken keyword, 1.5–2.5 s |
| Thesis / key line | `Scrim` (`dim` 0.5–0.6, `blur` 8–12, layer `below`) + `TitleCard` (`tone: light`, 1–2 serif `lines`, key line `italic`, optional `kicker`, `quote: true`) | exact spoken words, hold ≥ 2 s; captions auto-hide |
| Reflection / explanation | `card` layout with paper margins + a dark `TitleCard` (`tone: dark`) above it | 3–5 s, then back to full |
| Named offer / items | `TitleCard` (`tone: light`) with `kicker` + 1–2 short serif lines, over a `Scrim` | spoken words only |
| Ending | `Scrim` over the last shot + `EndCard` (automatic `title` variant, `tone: "light"`) | the CTA actually said |
Total: 3–4 graphics per 30 s. Never two at once.

## 30-second beat recipe
> A rhythm, not a template: keep the beat timing, but fill each beat from this video's concept (SKILL.md
> "Direct it first"). Swap any element for a custom scene or one borrowed from another style when the script has
> its own image, and never let two titles look alike.
1. **0–3 s** letterbox slides in, face full-frame, hook captions, slow push from the first content word.
2. **Before/after** (if spoken): grey `full` grade 0 + `wide` → slow morph to the graded frame.
3. **Thesis** Scrim + light TitleCard for 2–2.6 s (the line the video is about).
4. **Middle** card layout on paper + dark TitleCard, or full frame with a slow push - alternate.
5. **Offer / conclusion** light TitleCard over a Scrim, or a slow `close` push.
6. **End** Scrim + EndCard `title`, `end_hold` 1.0–1.2 s.

## Sound
`editorial` bed 92 BPM soft keys at −14 LU. Soft swooshes under title cards, a shimmer on the wordmark, clicks.
No impacts, no risers, no pops.

## Never
UPPERCASE captions · chips · stickers · bursts · flashes · wipes · lime · two graphics at once · fast jump-zoom ·
entrances faster than 0.4 s · a title card without a Scrim over footage.

## Depth pack in this style (v3.7)
- Calm only: `DepthText` `stack` with `font: "serif"` (Playfair), `looks: ["outline", "fill"]`, `tone: "paper"` on
  graded footage - one word or a year behind the head on the thesis line. `creep: 0.02`.
- `DepthText` `split` for a "bukan… tapi…" contrast, serif, slow (`dur` 1.1). No shake, no glitch, no doodles, no
  rings: EDITORIAL is the style that says no.
- Premium pack (v3.9): at most one `GlowPill` (`surface: "paper"`) and `SideCards` with the user's own photos; no glossy objects, no colour fields.
- Note pack (v3.10): one quiet `HeadNote` per chapter (`dot: false`) fits the calm look; no strikes, no accent tags.

## Pre-render checklist
- [ ] Letterbox visible from ~1 s; captions sit above the bottom bar (y ≤ 1244).
- [ ] Every serif title is readable on a 360-px still (Scrim on, white or ink type, never sand on white).
- [ ] At least 3 distinct compositions in 30 s (full / Scrim+title / card / end title).
- [ ] Raw vs edited grade difference is obvious if a before/after is spoken.
- [ ] The speaker's breathing pauses still sound natural.

## Motion & finish (v2 engine, automatic from the config below)
Text `focus` (rack-focus). Cuts: soft warm `leak` at most every 7 s. Finish: grain 0.07, vignette 0.24, warm tint wash,
view vignette; operator drift 4 px; slow pushes. Letterbox 128 px. Rhythm ≤ 4.2 s. End card: `title`.
All of it can be overridden in creative.json (`cut_fx`, line `fx`, `style_overrides`, e.g. `{"frame": {"letterbox": null}}`).

```json style-config
{
  "id": "editorial",
  "colors": {
    "bg": "#F1ECE2",
    "paper": "#FFFDF8",
    "ink": "#16130F",
    "muted": "#7A7366",
    "soft": "#ECE6DA",
    "line": "#DED7C9",
    "accent": "#E9CFA3",
    "accent2": "#2F5BE8",
    "accentInk": "#8A5A2B",
    "danger": "#E5533D",
    "grid": "rgba(22,19,15,0.05)",
    "glow1": "rgba(233,207,163,0.24)",
    "glow2": "rgba(138,90,43,0.08)"
  },
  "fonts": {
      "head": "Poppins",
      "body": "Poppins",
      "ui": "Poppins",
      "serif": "PlayfairDisplay",
      "serifWeight": 500
  },
  "captions": {
    "variant": "clean",
    "case": "none",
    "size": 62,
    "hookSize": 76,
    "weight": 600,
    "radius": 18,
    "maxWords": 5,
    "maxChars": 30,
    "hookMaxWords": 3,
    "hookMaxChars": 20,
    "maxPageDur": 2.4,
    "text": "#FFFFFF",
    "emph": "#FFF4DE",
    "underline": "#F2DDB4",
    "scrim": 0.5
  },
  "grade": {
    "sat": 0.9,
    "con": 1.13,
    "bri": 1.02,
    "warm": 0.07,
    "rawSat": 0.5,
    "rawCon": 0.9,
    "rawBri": 1.0
  },
  "camera": {
    "talk": 1.12,
    "close": 1.22,
    "jumpZoom": 1.035,
    "punch": 0.05,
    "breathe": 0.016,
    "drift": 4,
    "push": 0.035
  },
  "frame": {
    "letterbox": {"h": 128, "at": 0, "dur": 1.2, "line": "rgba(242,221,180,0.22)"}
  },
  "wipe": {
    "band": 90,
    "skew": 80
  },
  "sound": {
    "bed": "editorial",
    "music_db": -14,
    "sfx_db": -11,
    "punch_sfx": "swoosh"
  },
  "cuts": {
    "fx": [
      "leak"
    ],
    "minGap": 7.0,
    "dur": 0.3,
    "pre": 0.0
  },
  "rhythm": {
    "maxStatic": 4.2,
    "maxGraphicsPerMin": 12,
    "maxSfxPerSec": 0.7,
    "maxWipes": 0
  },
  "finish": {
    "grain": 0.07,
    "vignette": 0.24,
    "viewVignette": 0.2,
    "tint": "#FFB27A",
    "tintOpacity": 0.2,
    "leak": [
      "rgba(255,186,120,1)",
      "rgba(255,120,80,1)"
    ]
  },
  "endcard": {
    "variant": "title"
  },
  "textFx": "focus",
  "defaults": {
    "sound_effects": "subtle",
    "visual_overlays": "moderate"
  }
}
```
