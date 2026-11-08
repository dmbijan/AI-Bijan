# Style: SYSTEM · `system`

> Tech/UI-driven. Makes a workflow feel like software doing the work.

**Use for** AI, SaaS, coding, app tutorials, workflow/tool explainers, productivity tips.
**Not for** emotional stories, lifestyle or food content (→ EDITORIAL / CREATOR POP).
**Recommended settings** editing_intensity medium · sound_effects medium · visual_overlays moderate–extensive.

## Look
- Light neutral canvas with a dot grid, dark UI cards. **Geist** for UI and headings, **Geist Mono** for the
  terminal captions and typed titles.
- Vivid blue (`accent2`) is the primary accent; lime only for "done/active" states.
- Interface metaphors: prompt bars, chat panels, progress bars, status chips. Always generic, never a real app.

## Captions
- Dark UI card with a small `CC` tag (`terminal`), Sentence case, 2–4 words per page, page ≤ 1.9 s.
- Words type in with a caret; the active word turns lime.
- Emphasis = blue chip with an icon that literally matches (cursor, chat, send, file, check, wand, layers,
  refresh, play, sound, zoom, scissors, cc).

## Camera & cutting
- `talk` framing 1.14, jump-zoom 1.05. Crisp punch-ins `amount` 0.06–0.08, `hold` 0.3–0.4 s.
- Camera moves are short and decisive (0.25–0.35 s), one per 4–6 s.

## Motion at 60 FPS
- Expo-out feel (fast start, long settle), no rubbery overshoot on panels. UI slides along a grid.
- Typing speed believable: `typing` ≈ characters ÷ 16 seconds (never faster than 30 chars/s).
- Progress bars 0.8–1.6 s; typed text finishes ≥ 0.6 s before its panel exits.
- Only one element types at a time.

## Signature graphics (plan them in creative.json)
| Moment | Overlay | Rule |
|---|---|---|
| "tinggal ketik / minta / prompt" | `PromptBar` typing then sending | text = spoken words or user-written prompt |
| Ask → result workflow | `ChatPanel` user message → thinking → reply with `progress` | 1 per 30 s, never covers the mouth |
| Named feature | `FloatCard` (icon + title + subtitle) or `Tile` | ≤ 2 on screen, enters on its word |
| Task done | `Badge` with `check` icon + `ding` | only when the speaker states completion |
| Talking about audio/pauses | `Waveform` of the real audio | `cuts: "auto"` to show removed pauses |
| Product reveal (optional) | `Cutout` with `bg: "dim"` or `"blur"` (no colour) - the speaker lifts off a darkened UI-like frame | ≤ 1 per video |

## 30-second beat recipe
> A rhythm, not a template: keep the beat timing, but fill each beat from this video's concept (SKILL.md
> "Direct it first"). Swap any element for a custom scene or one borrowed from another style when the script has
> its own image, and never let two titles look alike.
1. **0–2.5 s HOOK** face + PromptBar typing the hook question (if spoken) or a KineticTitle.
2. **3–10 s HOW** `card` layout (face in the upper card) + ChatPanel below the face.
3. **10–20 s FEATURES** FloatCards/Tiles, one per named feature, on its word; return to full frame between.
4. **20–26 s RESULT** full frame, Badge "SELESAI"-type status (only if said) + ding.
5. **26 s–end CTA** EndCard with the spoken CTA.

## Sound
`system` bed 110 BPM pluck/arp at −13 LU. Typing ticks during typing, click on send, ding on completion,
soft swoosh on layout moves, `snap` on punch-ins. No impacts.

## Never
Serif type · lime text on white · glitch/RGB split · fake screenshots or real product names/logos ·
typing faster than readable · two panels typing at once · bursts on every beat.

## Depth pack in this style (v3.7)
- `DepthText` `wall` with `font: "ui"` (Geist) and `looks: ["fill", "outline"]` reads like a terminal marquee behind
  the speaker: the tool's spoken name, "prompt • prompt •". Camera `glitch` on the reveal of the tool / the AI word.
- `HeadRing` with the spoken command ("auto • edit •") on a "sekali klik" moment, `tone: "accent"`, `glow: true`.
- `Doodles` `bulb` on "caranya", `sparks` on "ternyata". No echo slams here: SYSTEM stays precise.
- Premium pack (v3.9): `GlowPill` (`surface: "ink"`, `badge: "check"`) for "berhasil / jadi", `Checklist` for setup steps over the blurred frame, `BadgeOrbit` of tool icons on the tool's name.
- Note pack (v3.10): `HeadNote` is native here (Geist Mono): the tool / step name typed above the head; `NoteTags` `surface: "ink"` for settings and options.

## Pre-render checklist
- [ ] Every UI string is spoken or user-provided; nothing implies a real product.
- [ ] UI text ≥ 28 px; captions don't collide with panels.
- [ ] Typed text completes and stays readable ≥ 0.6 s.
- [ ] The face stays visible while panels are on screen.

## Motion & finish (v2 engine, automatic from the config below)
Text `type` (typewriter + caret) for titles; UI elements `rise`. Cuts: `push` / `zoom` ~one per 4.5 s. Finish: grain 0.03. Rhythm ≤ 3.2 s.
All of it can be overridden in creative.json (`cut_fx`, line `fx`, `style_overrides`).

```json style-config
{
  "id": "system",
  "colors": {
    "bg": "#EEF0F3",
    "paper": "#FFFFFF",
    "ink": "#0D1117",
    "muted": "#6B7280",
    "soft": "#F1F3F6",
    "line": "#E1E5EA",
    "accent": "#C8FF2E",
    "accent2": "#2563FF",
    "danger": "#FF4D4F",
    "grid": "rgba(13,17,23,0.09)",
    "glow1": "rgba(37,99,255,0.16)",
    "glow2": "rgba(200,255,46,0.16)"
  },
  "fonts": {
      "head": "Geist",
      "body": "Geist",
      "ui": "GeistMono",
      "display": "GeistMono",
      "displayWeight": 700,
      "serif": "PlayfairDisplay",
      "serifWeight": 500
  },
  "captions": {
    "variant": "terminal",
    "case": "none",
    "size": 58,
    "hookSize": 72,
    "weight": 700,
    "radius": 20,
    "maxWords": 4,
    "maxChars": 22,
    "hookMaxWords": 2,
    "hookMaxChars": 14,
    "maxPageDur": 1.9,
    "box": "rgba(13,17,23,0.9)",
    "text": "#FFFFFF"
  },
  "grade": {
    "sat": 1.06,
    "con": 1.06,
    "bri": 1.03,
    "rawSat": 0.45,
    "rawCon": 0.88,
    "rawBri": 0.98
  },
  "camera": {
    "talk": 1.14,
    "close": 1.26,
    "jumpZoom": 1.05,
    "punch": 0.07,
    "breathe": 0.01,
    "drift": 0,
    "push": 0.04
  },
  "wipe": {
    "band": 110,
    "skew": 120
  },
  "sound": {
    "bed": "system",
    "music_db": -13,
    "sfx_db": -8,
    "punch_sfx": "snap"
  },
  "cuts": {
    "fx": [
      "push",
      "zoom"
    ],
    "minGap": 4.5,
    "dur": 0.24,
    "pre": 0.1
  },
  "rhythm": {
    "maxStatic": 3.2,
    "maxGraphicsPerMin": 12,
    "maxSfxPerSec": 1.2,
    "maxWipes": 1
  },
  "finish": {
    "grain": 0.03,
    "vignette": 0.12
  },
  "textFx": "type"
}
```
