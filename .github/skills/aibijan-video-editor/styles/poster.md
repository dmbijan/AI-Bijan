# Style: POSTER · `poster`

> Every key beat looks like a designed thumbnail. On the big lines the room disappears: a lit colour field opens from
> behind the speaker's head, a heavy two-tone title sits behind them with outlined ghost echoes stepping down, round
> icon badges pop in an arc around the head, photo cards fly in beside them, and lists build as white rows with check
> badges over a blurred, tinted frame. Between those beats the real room comes back, so the colour stays special.

**Use for** educational and storytelling talking heads with strong topics (food, culture, careers, tips, reviews),
podcasts and interviews, creators who want a "designed" channel look, "kayak thumbnail", "background warna", "bold".
**Not for** cinematic warm business (→ LUMEN), minimal type on the wall (→ DEPTH), dark narration (→ STORYTELLER),
UI tutorials (→ SYSTEM), calm documentary (→ EDITORIAL).
**Recommended settings** editing_intensity medium · sound_effects medium · visual_overlays moderate.
**Best footage** a speaker who stays roughly centred; any background (it is replaced on poster beats).

## Look
- **Two worlds**: the real footage (bright, punchy grade) and the **poster field** (`Cutout` with `bg: "poster"`): a lit
  radial gradient in the brand colour (poster red by default) with a light spot, two faint rings and a soft floor
  shade, revealed by a circle opening from the head; the speaker pops forward with a soft shadow, no outline.
  Poster beats are 35–55 % of the runtime, 1.5–5 s each.
- **Type system**: **Archivo Black** uppercase titles behind the head: line 1 white, line 2 in the accent colour (or
  yellow on red), line 2 with 2–3 outlined ghost echoes stepping down (`lines[].echo`). Captions in Archivo ExtraBold.
- One brand colour per video (`style_overrides.colors.accent` + the poster `color`/`color2`), white, and ink. **The field
  must contrast with the speaker's clothes** (red shirt → cobalt or ink field; the planner prints `CONTRAST?` when they
  are close): e.g. `"color": "#2346F0", "color2": "#0B1660"` with yellow (`accent2`) title lines.

## Captions
- `poster`: no box, Archivo ExtraBold 56 px (hook 64) white with a soft shadow, words pop up as they are spoken;
  emphasis words sit on a solid accent label with a slight tilt. Up to 3 words per page.

## Signature graphics (plan them in creative.json)
| Moment | Element | Rule |
|---|---|---|
| The hook | `Cutout` `bg: "poster"` + `DepthText` `stack` behind: white line + accent line with `echo: 3` | first 2 s |
| A category, a theme, "dunia X" | `BadgeOrbit` - 5–7 icon badges (Icons or kit objects) on an arc over the head | on the theme word |
| Examples, "kayak X, Y" | `SideCards` - the user's photos (`src`) or kit objects / the speaker's own frames, one per side | on each example |
| A list / "ada A, B, C" | `Checklist` with a header pill (spoken topic) and one row per spoken item | 2–4 rows |
| A claim / reveal | poster beat + a new two-tone title | 1 per 6–8 s at most |
| Payoff / CTA | poster beat + `GlowPill` (`surface: "paper"`) with the spoken CTA | — |

## 30–45 s beat recipe
> A rhythm, not a template: fill each beat from this video's concept (SKILL.md "Direct it first").
1. **HOOK** poster field opens + two-tone title with echoes behind the head.
2. **CONTEXT** real room, captions, a punch-in.
3. **THEME** poster field + badge arc around the head.
4. **EXAMPLES** real room or poster with side cards flying in on each example.
5. **LIST** checklist over the blurred frame.
6. **PAYOFF** poster field + the last title, pill or CTA.

## Motion
Eased (quint); badges and check marks pop with a small back-out. The colour field opens as a circle from the head in
0.42 s and closes in 0.3 s; titles rise out of masks while ghost echoes step down 0.07 s apart; cards fly in with a
turn and motion blur and settle at ±8°; checklist rows slide in from the left out of a blur. Cuts: push / zoom-through.

## Sound
`playful` bed (124 bpm, bright plucks) at −15 LU. Whoosh as the field opens, pops for badges (rising pitch), whooshes
for cards, a pop + tick per checklist row, swoosh under titles.

## Never
The colour field all video long (it stops being special) · more than one brand colour · emoji · photos the user did not
give (use kit objects or their own frames) · logos or real brand marks on cards · titles over the eyes or mouth.

## Depth pack in this style
`DepthText` `stack` with `lines[].echo` is the POSTER title; `split` for "bukan X, tapi Y" on the field. `HeadRing`
works on poster beats (`tone: "paper"`). `Doodles` in `color: "paper"` on the field.
- Note pack (v3.10): `NoteTags` `surface: "ink"` beside the head on poster beats; a `NoteBoard` can replace a Checklist for longer lists.

## Pre-render checklist
- [ ] Poster beats 35–55 % of the runtime and never longer than ~5 s each.
- [ ] Titles behind the head read on the stills (head covers only the lower part of line 1).
- [ ] Badges and cards sit beside/above the head, never on the face; checklist rows are spoken words.
- [ ] One brand colour; captions readable on both the room and the field.

## Motion & finish (automatic from the config below)
Text `rise`. Cuts: `push` / `zoom`, ~one per 3.4 s. Finish: grain 0.02, vignette 0.1. Rhythm ≤ 2.8 s.

```json style-config
{
  "id": "poster",
  "colors": {
    "bg": "#B5161E",
    "paper": "#FFFFFF",
    "ink": "#141414",
    "muted": "#8C8C8C",
    "soft": "#F4F1EC",
    "line": "#E2DDD5",
    "accent": "#E8262E",
    "accent2": "#FFD23F",
    "danger": "#E8262E",
    "deep": "#6E0A10",
    "grid": "rgba(255,255,255,0.05)",
    "glow1": "rgba(232,38,46,0.14)",
    "glow2": "rgba(255,210,63,0.08)"
  },
  "fonts": {
    "head": "Archivo",
    "body": "Archivo",
    "ui": "Archivo",
    "display": "Archivo",
    "displayWeight": 900,
    "serif": "PlayfairDisplay",
    "serifWeight": 600
  },
  "captions": {
    "variant": "poster",
    "size": 56,
    "hookSize": 64,
    "weight": 800,
    "radius": 10,
    "maxWords": 3,
    "maxChars": 20,
    "hookMaxWords": 2,
    "hookMaxChars": 14,
    "maxPageDur": 1.8,
    "text": "#FFFFFF",
    "label": "#E8262E",
    "labelText": "#FFFFFF"
  },
  "grade": {
    "sat": 1.1,
    "con": 1.08,
    "bri": 1.04,
    "rawSat": 0.45,
    "rawCon": 0.9,
    "rawBri": 1.0
  },
  "camera": {
    "talk": 1.12,
    "close": 1.3,
    "jumpZoom": 1.06,
    "punch": 0.08,
    "breathe": 0.012,
    "drift": 1.0,
    "push": 0.05
  },
  "wipe": {
    "band": 140,
    "skew": 150
  },
  "sound": {
    "bed": "playful",
    "music_db": -15,
    "sfx_db": -7,
    "punch_sfx": "swoosh",
    "emph_pop": false
  },
  "cuts": {
    "fx": [
      "push",
      "zoom"
    ],
    "minGap": 3.4,
    "dur": 0.24,
    "pre": 0.1
  },
  "rhythm": {
    "maxStatic": 2.8,
    "maxGraphicsPerMin": 24,
    "maxSfxPerSec": 1.5,
    "maxWipes": 1
  },
  "finish": {
    "grain": 0.02,
    "vignette": 0.1
  },
  "depth_defaults": {
    "look": "fill",
    "creep": 0.03
  },
  "endcard": {
    "variant": "block"
  },
  "textFx": "rise",
  "defaults": {
    "sound_effects": "medium",
    "editing_intensity": "medium",
    "visual_overlays": "moderate"
  }
}
```
