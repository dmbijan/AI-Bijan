# Style: DEPTH · `depth`

> The premium After-Effects Reels look, built on layers: big, tight grotesk type lives on the wall BEHIND the speaker,
> accent words switch to an italic serif, words orbit the head, marker doodles draw on around it. The footage stays
> bright and clean; the subtitles stay quiet so the type behind the person is the voice of the edit.

**Use for** personal brand, creator talking heads, "AE premium Reels" requests, tips and opinions with one strong idea
per line, IG Reels that should look designed rather than templated, "teks di belakang badan", "elemen di kepala".
**Not for** hard-sell ads with offers and CTAs everywhere (→ IMPACT), UI tutorials (→ SYSTEM / MOGRAPH MIX), dark
dramatic narration with mood changes (→ STORYTELLER), loud sticker comedy (→ CREATOR POP), calm documentary (→ EDITORIAL).
**Recommended settings** editing_intensity medium · sound_effects medium · visual_overlays moderate.
**Best footage** a plain wall behind the speaker (light or dark) and the head below the top quarter of the frame - the
type needs wall above and beside the head. On a busy background use `FeedWall` or a `Cutout` for the depth beats.

## Look
- **Clean bright grade**: true skin, a touch of contrast and saturation, no mood grades, almost no grain, a light
  vignette. The picture looks shot well, not "filtered".
- **Type system**: **Inter Tight Black** for the huge words (uppercase, tracking −0.04 em: dense and expensive), and
  **Instrument Serif Italic** for accent words (lowercase, as spoken) - the mix of a heavy grotesk and a fine italic
  serif is the signature of the style. One accent colour (electric blue) used sparingly; otherwise ink on light walls,
  white on dark ones (`tone: "auto"`).
- Type is never on a box or a card. It sits on the wall, behind the head.

## Captions
- `reel`: no box, Inter Tight SemiBold 54 px (hook 62) in sentence case, a soft shadow, each word arrives out of a blur
  with a short rise as it is spoken; emphasis words switch to Instrument Serif Italic in the accent colour.
- Up to 4 words per page, kept low so the type behind the head owns the top half. Hidden automatically while type
  behind the speaker shows the same words.

## Signature graphics (plan them in creative.json)
| Moment | Element | Rule |
|---|---|---|
| The hook | `DepthText` `stack`: a small accent-serif lead word + a huge grotesk word behind the head | first 2 s; `lines[0].font: "accent"` |
| A number, a claim | `DepthText` `stack` (`look: "fill"` + `"outline"`) | 1–3 words |
| The shock word | `DepthText` `echo` (`tone: "accent"`, `echoTone: "auto"`) + camera `shake` | once per video |
| A loop, a habit, "lagi dan lagi" | `HeadRing` (`tone: "paper"`, Inter Tight) or `DepthText` `wall` with `fonts: ["display", "accent"]` (grotesk and italic rows alternating) | the motif; usually the signature |
| "Bukan X, tapi Y", before/after | `DepthText` `split` with `fonts: ["accent", "display"]` | once |
| Inner states (ternyata, pusing, ide) | `Doodles` in `color: "paper"` on dark walls / `"auto"` on light ones, thin marker (`width` 7) | ≥ 3 s apart |
| Endless content, "so many" | `FeedWall` behind the speaker (`frames_src: "auto"`) | 1 per video |
| The payoff | `Cutout` on the accent colour + one accent-serif word beside the head | 1.5–3 s |
| Ending | slow push + `DepthText` `stack` of the last key word, fading with the last syllable | no end card unless the user asks |

## 30–45 s beat recipe
> A rhythm, not a template: fill each beat from this video's concept (SKILL.md "Direct it first").
1. **HOOK** stack behind the head on the hook word (accent-serif lead + grotesk word), hook push.
2. **SETUP** clean face beat, one doodle or a punch-in - let the face breathe.
3. **SHOCK** echo + shake on the surprising word.
4. **LOOP / MOTIF** ring or wall (the signature), the camera holds still so the type does the moving.
5. **TURN** split for the contrast, or a FeedWall if the script is about content/quantity.
6. **PAYOFF** Cutout or a last stack, then the ending push.

## Motion
Eased everything (quint/expo). Type rises out of masks and tracks in; echoes fan out; walls slide with a speed ramp;
rings turn 0.8–1.1 rad/s; doodles draw on and boil at 12 fps. Cuts inside face beats: zoom-through or push, about one
per 3.6 s. At most one camera `shake` and two `glitch` hits per 30 s.

## Sound
`depth` bed (100 bpm minimal groove, minor 9ths, half-time kick with claps, soft plucks) at −15 LU, ducked under the
voice. Whoosh + impact under echoes, a whoosh and a quiet riser under walls, shimmer on rings, marker scribbles on
doodles, swooshes on feed swipes.

## Never
Type on boxes or cards · more than one accent colour · emoji · a depth trick on every line (one per ~5 s at most) ·
ring or wall words nobody said · text across the eyes or mouth · mood grades (that is STORYTELLER) · fake app UI.

## Depth pack in this style
The depth pack IS this style's language - see the table above. Prefer type behind the head over titles in front of
the face; use `font: "accent"` for the one word per beat that should feel human (an adjective, a feeling, "kamu").
- Premium pack (v3.9): `GlowPill` (`surface: "paper"`) and `Glossy` checks in the accent colour; keep the type behind the head as the hero.
- Note pack (v3.10): a `HeadNote` above the type behind the head reads as a kicker; skip tags when a ring or wall is on screen.

## Pre-render checklist
- [ ] Every depth moment carries the concept; at least one beat is just the face with captions.
- [ ] Type behind the head reads on the stills (head covers only its lower part; ink on light walls, white on dark).
- [ ] Grotesk + italic serif mix appears at least twice, the accent colour at most once per beat.
- [ ] Captions stay low and quiet; nothing covers the eyes or mouth.

## Motion & finish (automatic from the config below)
Text `rise`. Cuts: `zoom` / `push`, ~one per 3.6 s. Finish: grain 0.015, vignette 0.12. Rhythm ≤ 3.0 s.

```json style-config
{
  "id": "depth",
  "colors": {
    "bg": "#0E0E10",
    "paper": "#FFFFFF",
    "ink": "#0E0E10",
    "muted": "#8E8E93",
    "soft": "#1C1C1E",
    "line": "#2C2C2E",
    "accent": "#2F5BFF",
    "accent2": "#A9B8FF",
    "danger": "#FF3B30",
    "grid": "rgba(255,255,255,0.035)",
    "glow1": "rgba(47,91,255,0.10)",
    "glow2": "rgba(169,184,255,0.06)"
  },
  "fonts": {
    "head": "InterTight",
    "body": "InterTight",
    "ui": "InterTight",
    "display": "InterTight",
    "displayWeight": 900,
    "serif": "InstrumentSerif",
    "serifWeight": 400,
    "accent": "InstrumentSerif"
  },
  "captions": {
    "variant": "reel",
    "size": 54,
    "hookSize": 62,
    "weight": 600,
    "radius": 12,
    "maxWords": 4,
    "maxChars": 26,
    "hookMaxWords": 3,
    "hookMaxChars": 18,
    "maxPageDur": 2.0,
    "text": "#FFFFFF",
    "emph": "#A9B8FF"
  },
  "grade": {
    "sat": 1.06,
    "con": 1.06,
    "bri": 1.03,
    "warm": 0.02,
    "rawSat": 0.5,
    "rawCon": 0.92,
    "rawBri": 1.0
  },
  "camera": {
    "talk": 1.08,
    "close": 1.24,
    "jumpZoom": 1.05,
    "punch": 0.07,
    "breathe": 0.012,
    "drift": 1.5,
    "push": 0.045
  },
  "wipe": {
    "band": 130,
    "skew": 140
  },
  "sound": {
    "bed": "depth",
    "music_db": -15,
    "sfx_db": -7,
    "punch_sfx": "swoosh",
    "emph_pop": false
  },
  "cuts": {
    "fx": [
      "zoom",
      "push"
    ],
    "minGap": 3.6,
    "dur": 0.24,
    "pre": 0.1
  },
  "rhythm": {
    "maxStatic": 3.0,
    "maxGraphicsPerMin": 20,
    "maxSfxPerSec": 1.4,
    "maxWipes": 1
  },
  "finish": {
    "grain": 0.015,
    "vignette": 0.12
  },
  "endcard": {
    "variant": "title"
  },
  "textFx": "rise",
  "defaults": {
    "sound_effects": "medium",
    "editing_intensity": "medium",
    "visual_overlays": "moderate"
  }
}
```
