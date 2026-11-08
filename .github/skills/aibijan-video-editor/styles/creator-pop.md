# Style: CREATOR POP · `creator-pop`

> Sticker energy. Big outlined captions with sticker-boxed punchlines, die-cut stickers slapped around the
> speaker, a split-face before/after, snap zooms. The presenter stays the star - stickers live around the face,
> never on it. Generic creator language, not any one creator's template.

**Use for** personal brands, quick tips, explainers with humour, product hooks, personality-led Reels/TikTok.
**Not for** serious/documentary topics (→ EDITORIAL) or calm premium content (→ EDITORIAL).
**Recommended settings** editing_intensity medium–high · sound_effects medium · visual_overlays moderate.

## Look
- Bright, saturated grade. Sticker palette: yellow `accent`, violet `accent2`, mint, coral, white, ink.
- Every graphic is a **die-cut sticker**: thick white outline, hard drop shadow, slight rotation, idle wobble.
- **TikTok Sans** (Black/ExtraBold) everywhere - the platform's own open-source typeface, so it feels native.
  No cards with UI chrome, no lists with numbers, no lime.

## Captions
- `stroke`: heavy UPPERCASE white text, thick black outline + hard offset shadow, 84 px (hook 100), pages tilt ±1.5°.
  The spoken word turns yellow and lifts. The punchline word becomes a **mint sticker box** (emphasis).
- 1–3 words per page, ≤ 1.3 s. One emphasis per beat, on the punchline.

## Camera & cutting
- `talk` framing 1.17, jump-zoom 1.062. Punch-ins (`amount` 0.08–0.13) only on punchlines, about one per 3–4 s;
  the biggest one on the payoff. Cut transitions zoom / whip / flash rotate automatically.
- Before/after: layout **`versus`** - a diagonal split-face (raw grade left, edited right, same framing) with a
  white divider and a "VS"-style label sticker pair. Enter it with the wipe (yellow band).

## Motion at 60 FPS
- Stickers peel on with a spin and overshoot (0.25–0.35 s), wobble while they stay, flip away on exit (0.2 s).
- Never more than one new popping element per 0.5 s.

## Signature graphics (plan them in creative.json)
| Moment | Element | Rule |
|---|---|---|
| Hook | `KineticTitle` 1–2 lines, `accentChip` / `chip` tones with `rot` ±4 (sticker chips) + punch | spoken hook words |
| Label / reaction | `Sticker` (1–3 words, optional literal icon, `tone`, `rot`) beside or above the head | on the word it labels, ≤ 2 on screen |
| Before → after | layout `versus` + two `Sticker`s "MENTAH" / "SETELAH" on each side | only if spoken |
| Any spoken list | `StickerStack` (one sticker per item on its word, default slots ring the face) | 2–6 items, `maxW` ~270 so long items wrap |
| Typing / asking | `PromptBar` (rare) | spoken or user-written text |
| Payoff / reveal | `Cutout` (`bg: "color"`, `color: "accent2"` or `accent`, white `outline` 12, `pop` 1.04) + stickers around the head, biggest punch-in + `Burst` | 1.5–3 s, max 1 Burst |
| CTA | `EndCard` (automatic `sticker` variant: kicker sticker, two-sticker wordmark, mint CTA sticker, chevrons) | spoken CTA |

## 30-second beat recipe
> A rhythm, not a template: keep the beat timing, but fill each beat from this video's concept (SKILL.md
> "Direct it first"). Swap any element for a custom scene or one borrowed from another style when the script has
> its own image, and never let two titles look alike.
1. **0–2 s HOOK** face + sticker-chip KineticTitle + punch on the hook word.
2. **2–8 s** before/after `versus` split-face if spoken, or full frame + one Sticker on the key word.
3. **8–18 s** StickerStack for any list (face stays full frame and uncovered); punch on each punchline; full → card → full.
4. **18–26 s PAYOFF** Sticker or PromptBar on the key action, biggest punch + Burst.
5. **26 s–end CTA** card + sticker EndCard, music hit on the last word.

## Sound
`impact` bed at −13 LU. Pops on stickers (pitch varied), whoosh on the wipe and whips, one soft impact on the hook only.
Never a hit on every syllable; never cover spoken words.

## Never
Emoji · stickers on the eyes or mouth · numbered lists / UI cards (that's IMPACT/SYSTEM) · 20 jump cuts in 10 s ·
strobes · camera shake · three fonts · copying a recognisable creator template · made-up words in captions ·
punch-ins without a punchline.

## Depth pack in this style (v3.7)
- Doodles are native here: `question`, `exclaim`, `heart`, `crown`, `stars`, `drops`, `scribble` around the head in
  `color: "paper"` with the sticker outline look; one per thought, on the word.
- `DepthText` `echo` with `font: "condensed"` or TikTok Sans for the punchline, `HeadRing` for the catchphrase or a
  repeated habit ("tiap hari • tiap hari •"), camera `shake` on the funniest word.
- Combine with `Cutout`: a ring or doodles around the cut-out speaker on a solid colour read beautifully.
- Premium pack (v3.9): `BadgeOrbit` and `Glossy` hearts/stars fit the playful look; `Cutout bg: "poster"` on the punchline.
- Note pack (v3.10): `NoteTags` `surface: "accent"` for the punchline word, `strikeAt` for the joke's "bukan…"; `NoteClips` with the creator's own photos.

## Pre-render checklist
- [ ] At least three distinct compositions in every 30 s (full, versus, card/end).
- [ ] Stickers never touch the eyes/mouth on the stills; long sticker text wraps (maxW).
- [ ] Each punch-in lands on a punchline/joke word.
- [ ] No more than one popping element per half second.

## Motion & finish (v2 engine, automatic from the config below)
Text `pop`, hook may `slam`. Cuts: `zoom` / `whip` / `flash` rotating, ~one per 3.5 s; operator drift 3 px. Finish: grain 0.03.
Rhythm ≤ 2.4 s. End card: `sticker`. Defaults: intensity high.
All of it can be overridden in creative.json (`cut_fx`, line `fx`, `style_overrides`).

```json style-config
{
  "id": "creator-pop",
  "colors": {
    "bg": "#F6F4FF",
    "paper": "#FFFFFF",
    "ink": "#14131F",
    "muted": "#706C80",
    "soft": "#EFECFA",
    "line": "#DDD8EF",
    "accent": "#FFD60A",
    "accent2": "#7B61FF",
    "mint": "#7CF29A",
    "coral": "#FF7A59",
    "danger": "#FF5263",
    "grid": "rgba(20,19,31,0.05)",
    "glow1": "rgba(93,91,254,0.16)",
    "glow2": "rgba(255,214,10,0.18)"
  },
  "fonts": {
      "head": "TikTokSans",
      "body": "TikTokSans",
      "ui": "TikTokSans",
      "serif": "PlayfairDisplay",
      "serifWeight": 500
  },
  "captions": {
    "variant": "stroke",
    "case": "upper",
    "size": 84,
    "hookSize": 100,
    "weight": 900,
    "radius": 18,
    "maxWords": 3,
    "maxChars": 16,
    "hookMaxWords": 2,
    "hookMaxChars": 11,
    "maxPageDur": 1.3,
    "text": "#FFFFFF",
    "stroke": "#0A0A0A",
    "strokeRatio": 0.17,
    "hardShadow": true,
    "active": "#FFD60A",
    "emph": "#7CF29A",
    "emphBox": "#7CF29A",
    "emphText": "#0A0A0A",
    "tilt": 1.5
  },
  "grade": {
    "sat": 1.14,
    "con": 1.08,
    "bri": 1.05,
    "rawSat": 0.45,
    "rawCon": 0.9,
    "rawBri": 1.0
  },
  "camera": {
    "talk": 1.17,
    "close": 1.32,
    "jumpZoom": 1.062,
    "punch": 0.095,
    "breathe": 0.012,
    "drift": 3,
    "push": 0.05
  },
  "wipe": {
    "band": 125,
    "skew": 130
  },
  "sound": {
    "bed": "impact",
    "music_db": -13,
    "sfx_db": -8,
    "punch_sfx": "pop"
  },
  "cuts": {
    "fx": [
      "zoom",
      "whip",
      "flash"
    ],
    "minGap": 3.5,
    "dur": 0.22,
    "pre": 0.09
  },
  "rhythm": {
    "maxStatic": 2.4,
    "maxGraphicsPerMin": 18,
    "maxSfxPerSec": 1.4,
    "maxWipes": 1
  },
  "finish": {
    "grain": 0.03,
    "vignette": 0.1
  },
  "endcard": {
    "variant": "sticker"
  },
  "textFx": "pop",
  "defaults": {
    "sound_effects": "medium",
    "editing_intensity": "high"
  }
}
```
