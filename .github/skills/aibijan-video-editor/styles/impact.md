# Style: IMPACT · `impact`

> Bold, bright, energetic. Stops the scroll in the first second and sells the point.

**Use for** ads, promos, product launches, hooks, "lihat ini!" content, Reels/TikTok hard-selling.
**Not for** grief, serious issues, long calm tutorials (→ EDITORIAL).
**Recommended settings** editing_intensity medium–high · sound_effects medium · visual_overlays extensive.

## Look
- Surfaces white/warm-neutral, type near-black, **electric lime** as the only loud accent, vivid blue (`accent2`)
  only for anything "AI/system/tech".
- **Anton** (condensed, uppercase) for hook titles, slams and the end-card wordmark; **Montserrat ExtraBold**
  UPPERCASE for captions, badges, tiles and buttons - the classic Reels ad-caption pairing.
- Lime is a marker or sits on ink: lime-on-ink or ink-on-lime, never lime text on white.

## Captions
- White card (`box`), 1–3 words per page, page ≤ 1.6 s. Words spring in with a short motion blur.
- The active word slides onto a lime marker. Emphasis word = dark chip with lime text (+ icon only if the icon
  literally depicts the word).
- Hook zone: `{"from":"t:0","to":"w:<end of first sentence>e","variant":"hook"}` → 1–2 big words per page.
- Max one chip per page and about one per 2–3 s. Never emphasise filler (yang, di, aja).

## Camera & cutting
- `talk` framing (1.16) keeps the face above the caption card. Jump cuts alternate zoom by ~6.5 %.
- Punch-in on the strongest word of each beat: `{"preset":"punch","amount":0.08–0.12,"hold":0.35–0.5}`,
  about one per 4–5 s. One `close` push (dur 1.0–1.4 s) for the payoff line.
- Pause threshold medium (0.38 s) or high (0.26 s) for ads. Never cut inside a word.

## Motion at 60 FPS
- Spring entrances with a little overshoot (engine damping 11–14); entrances 0.25–0.4 s, exits ~0.2 s.
- KineticTitle lines stagger 0.1–0.12 s. Overlays enter 0.2–0.3 s after the layout starts moving.
- The accent **wipe** transition is the signature move: use it **exactly once**, for the biggest transformation
  (before → after, problem → solution). Every other layout change is a 0.35–0.45 s `morph`.

## Signature graphics (plan them in creative.json)
| Moment | Overlay | Rule |
|---|---|---|
| Hook (0.4–2.5 s) | the hook keyword as one huge Anton word **behind the speaker** (`KineticTitle`, `layer: "behind"`, size 260–320, `tone: "ink"` on light walls), then a front `KineticTitle` slam (1–2 lines, one `accentChip`) on the punchline | words = the spoken hook keyword(s) |
| Feature / benefit | `Badge` with icon, or `FloatCard` | ≤ 2 on screen, enter on the word they name |
| List of 2–4 things | `Tile` row, one tile per spoken item | each tile enters on its own word |
| Transformation | `split` layout raw ↔ edited, then the wipe | only if the content is about change |
| CTA | `EndCard` kicker → wordmark → button | button text = the spoken/written CTA |
| Hit | `Burst` | max 1 (hook or CTA), never decorative |

## 30-second beat recipe
> A rhythm, not a template: keep the beat timing, but fill each beat from this video's concept (SKILL.md
> "Direct it first"). Swap any element for a custom scene or one borrowed from another style when the script has
> its own image, and never let two titles look alike.
1. **0–0.4 s** face full-frame, captions already running (no title card before the face).
2. **0.4–2.5 s HOOK** KineticTitle on the keyword + punch-in + `impact` SFX. Screen changes ≥ 2× in 3 s.
3. **3–8 s CLAIM** morph to card/split, one Badge stating the claim in spoken words.
4. **8–18 s PROOF** features/list as Tiles or FloatCards timed to each word; back to full frame between items.
5. **18–25 s PAYOFF** full frame, slow `close` push, one emphasis chip.
6. **25 s–end CTA** pip/end card, EndCard, music `hit_at` on the last word, `end_hold` 0.8–1.2 s.

## Sound
`impact` bed 118 BPM at −12 LU, ducked under the voice. Impact SFX only on the hook slam and the EndCard
wordmark; pops on chips/badges; whoosh on the single wipe. One sound per visual event, never two within 0.25 s.

## Never
Several accents fighting in one frame · more than one chip per caption page · lime text on white · shake ·
more than one wipe · Burst on every beat · emoji spam · a hook graphic with words the speaker didn't say.

## Depth pack in this style (v3.7)
- `DepthText` with `font: "condensed"` (Anton) reads like a billboard behind the head: `stack` for the offer number
  ("50%", "3 hari"), `echo` (`tone: "accent"` on dark walls, `ink` on light) for the hook word, plus one camera `shake`.
- `DepthText` `wall` of the product's spoken name or the pain ("ribet • ribet •") under the problem beat, `ramp` 2.
- `Doodles`: `arrow` with a spoken `label` pointing at the speaker on the CTA, `circle` around the head on "aku".
- Keep it to 2–3 depth moments per ad; the offer stays the hero.
- Premium pack (v3.9): `Glossy` coins on the price/profit word, `GlowPill` (`surface: "accent"`) for the offer result, `SideCards` with the user's product photos.
- Note pack (v3.10): `NoteTags` `surface: "accent"` for the offer items and a `HeadNote` on the product name; keep boards for real price lists.

## Pre-render checklist
- [ ] At least two visible changes in the first 3 s, and the face is on screen by 0.4 s.
- [ ] Hook title readable on a 360-px-wide still.
- [ ] Captions stay above y = 1244 and never cover the mouth.
- [ ] Exactly one wipe; every other transition is a morph.
- [ ] CTA text matches what was said or written, word for word.

## Motion & finish (v2 engine, automatic from the config below)
Text `pop`/`slam` (hook titles slam, chips pop). Cut transitions: `zoom` and `whip`, at most one per ~4 s, alternating. Finish: light grain 0.045, vignette 0.12. Rhythm: something changes on screen at least every 2.6 s.
All of it can be overridden in creative.json (`cut_fx`, line `fx`, `style_overrides`).

```json style-config
{
  "id": "impact",
  "colors": {
    "bg": "#F2F2EC",
    "paper": "#FFFFFF",
    "ink": "#0B0B0C",
    "muted": "#74746C",
    "soft": "#F0F1EA",
    "line": "#E4E5DD",
    "accent": "#C8FF2E",
    "accent2": "#2B59FF",
    "danger": "#FF4545",
    "grid": "rgba(11,11,12,0.08)",
    "glow1": "rgba(200,255,46,0.35)",
    "glow2": "rgba(43,89,255,0.16)"
  },
  "fonts": {
      "head": "Montserrat",
      "body": "Montserrat",
      "ui": "Geist",
      "display": "Anton",
      "displayWeight": 400,
      "serif": "PlayfairDisplay",
      "serifWeight": 500
  },
  "captions": {
    "variant": "box",
    "case": "upper",
    "size": 66,
    "hookSize": 84,
    "weight": 800,
    "radius": 26,
    "maxWords": 3,
    "maxChars": 17,
    "hookMaxWords": 2,
    "hookMaxChars": 13,
    "maxPageDur": 1.6,
    "box": "#FFFFFF",
    "text": "#0B0B0C",
    "active": "#C8FF2E"
  },
  "grade": {
    "sat": 1.12,
    "con": 1.07,
    "bri": 1.04,
    "rawSat": 0.42,
    "rawCon": 0.86,
    "rawBri": 0.98
  },
  "camera": {
    "talk": 1.16,
    "close": 1.3,
    "jumpZoom": 1.065,
    "punch": 0.1,
    "breathe": 0.012,
    "drift": 0,
    "push": 0.05
  },
  "wipe": {
    "band": 150,
    "skew": 160
  },
  "sound": {
    "bed": "impact",
    "music_db": -12,
    "sfx_db": -7,
    "punch_sfx": "impact"
  },
  "cuts": {
    "fx": [
      "zoom",
      "whip"
    ],
    "minGap": 4.0,
    "dur": 0.24,
    "pre": 0.1
  },
  "rhythm": {
    "maxStatic": 2.6,
    "maxGraphicsPerMin": 16,
    "maxSfxPerSec": 1.3,
    "maxWipes": 1
  },
  "finish": {
    "grain": 0.045,
    "vignette": 0.12
  },
  "textFx": "pop",
  "defaults": {
    "visual_overlays": "extensive"
  }
}
```
