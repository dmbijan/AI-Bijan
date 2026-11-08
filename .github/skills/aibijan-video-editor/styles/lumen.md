# Style: LUMEN · `lumen`

> Cinematic and luminous: a warm, filmic grade and type that glows. Key words land as big gradient letters with a soft
> bloom and a light sweep, a handwritten script word adds a human touch, small letter-spaced kickers frame the idea,
> glossy 3D objects (coins, checks, stars) float with depth of field, and glass pills confirm the payoff with a badge.
> The premium "AE creator" look for business, money, productivity and personal-brand talking heads.

**Use for** business / UMKM / money / productivity / tools talks, personal brand, course and product creators, any
talking head that should feel expensive and warm. "Bikin kayak editan AE yang glowing", "premium", "cinematic".
**Not for** loud sticker comedy (→ CREATOR POP), bright minimal (→ DEPTH), dark dramatic narration (→ STORYTELLER),
hard-sell offers with prices everywhere (→ IMPACT), UI tutorials (→ SYSTEM).
**Recommended settings** editing_intensity medium · sound_effects medium · visual_overlays moderate.
**Best footage** any indoor footage; LUMEN's warm grade and vignette make ordinary rooms look like a set. Dark or busy
backgrounds are fine: the type glows over them.

## Look
- **Warm cinematic night grade**: exposure pulled down ~20 %, strong contrast, warm mids, a deep vignette, light grain,
  warm light leaks on cuts - bright rooms turn into a moody set so the glowing type has something to glow against.
- **Type system**:
  - **Outfit ExtraBold** for the big words, filled with a vertical three-stop gradient (`look: "lumen"`: fire, gold, ice,
    mint, rose, violet or silver) with a tight bloom and a wide halo; a light sweep crosses each word once after it lands.
  - **Ms Madi** handwritten script (`font: "script"`) for one human word per beat ("jalan terus", "gratis").
  - **Outfit SemiBold, uppercase, letter-spaced** (`font: "ui"`, `track: 0.3`) for kickers ("CATAT TRANSAKSI").
  - One gradient per beat; fire (orange) is the default, ice (cyan) or mint for a contrasting beat.
- Big words sit in front at chest height (`layer: "below"`, y ≈ 840–900: the block must end above the caption band) or behind the head at the top
  (`layer: "behind"`, y ≈ 280–420). Never on the eyes or mouth.

## Captions
- `lumen`: no box, Outfit Bold 54 px (hook 62), white with a soft shadow, words rise out of a blur as they are spoken;
  emphasis words become the fire gradient with a glow. Hidden automatically under glowing type that shows the same words.

## Signature graphics (plan them in creative.json)
| Moment | Element | Rule |
|---|---|---|
| The hook | `DepthText` `stack`: script line (`font: "script"`) + one huge gradient word (`look: "lumen"`, `sweep: true`), behind the head at the top | first 2 s |
| The problem / the claim | `DepthText` `stack` in front at chest height (`layer: "below"`, y ≈ 870; below the mouth, above the captions): small kicker (`font: "ui"`, `track: 0.3`) + big gradient word | 1–3 words |
| Money, profit, numbers | `Glossy` coins (`symbol: "Rp"` or `%`) floating beside the head with depth of field, 2–4 | on the money word |
| "Langsung jadi", a result, a yes | `GlowPill` with a check badge (glass surface) | once or twice |
| A product / tool / feature name | `DepthText` stack: white kicker + gradient name + script word ("gratis") | the payoff |
| Contrast beat | the same stack in `grad: "ice"` (cyan) or `look: "neon"` | once |
| Things the person has / gets | `SideCards` with the user's photos or kit objects on gradients | 1–2 per side |
| Ending / CTA | `GlowPill` (spoken CTA) + slow push | — |

## 30–45 s beat recipe
> A rhythm, not a template: fill each beat from this video's concept (SKILL.md "Direct it first").
1. **HOOK** script + gradient word behind the head, warm push-in.
2. **PROBLEM** kicker + big word in front at the chest, glowing.
3. **NUMBERS / MONEY** glossy coins or a counter, a punch-in.
4. **TURN** cut to a closer framing, light leak, the solution word with a sweep.
5. **PROOF** glass pill with a check badge, side cards if the user has photos.
6. **PAYOFF / CTA** gradient name + script word, pill with the spoken CTA.

## Motion
Everything eased (quint), small overshoot only on badges and coins (back-out 1.45). Words rise out of masks and track
in, a light sweep crosses them 0.3 s after landing, coins spin 0.5 turns/s and float, pills slide up out of a blur and
the badge pops with a glint ring. Cuts: zoom-through and warm light leaks, about one per 3.6 s.

## Sound
`lumen` bed (96 bpm warm groove, major 7ths, soft plucks) at −15 LU under the voice. Swoosh under rising type, a short
riser into the payoff, pops for coins, a ding on pill badges, shimmer on sweeps.

## Never
More than one gradient colour in the same frame · glow on every word · script on long phrases (1–2 words) · coins with
invented amounts (symbols only, numbers only when spoken) · fake app screenshots · type on the eyes or mouth · emoji.

## Depth pack in this style
`DepthText` defaults to `look: "lumen"` with a light sweep here. Use `layer: "below"` for words in front of the chest,
`behind` for words over the head. `HeadRing` with `glow: true, tone: "accent"` for a loop; `Doodles` sparingly in
`color: "accent"` with `glow: true`.
- Note pack (v3.10): `HeadNote` `pill: true` (glass) as a kicker over the warm grade; no index cards or tape.

## Pre-render checklist
- [ ] At least one script + gradient combination and one glass pill; one gradient per frame.
- [ ] Glowing type never sits on the eyes/mouth; chest type stays above the caption band.
- [ ] Coins and badges float beside the head, not in front of the face.
- [ ] The warm grade does not orange the skin (check a still).

## Motion & finish (automatic from the config below)
Text `rise` + sweep. Cuts: `zoom` / `leak`, ~one per 3.6 s. Finish: grain 0.03, vignette 0.42. Rhythm ≤ 3.0 s.

```json style-config
{
  "id": "lumen",
  "colors": {
    "bg": "#0F0B08",
    "paper": "#FFFFFF",
    "ink": "#120E0A",
    "muted": "#A8988A",
    "soft": "#1E1712",
    "line": "#3A2E24",
    "accent": "#FF9A2E",
    "accent2": "#6FF2FF",
    "danger": "#FF5A5A",
    "grid": "rgba(255,255,255,0.03)",
    "glow1": "rgba(255,154,46,0.12)",
    "glow2": "rgba(111,242,255,0.07)"
  },
  "fonts": {
    "head": "Outfit",
    "body": "Outfit",
    "ui": "Outfit",
    "display": "Outfit",
    "displayWeight": 800,
    "serif": "PlayfairDisplay",
    "serifWeight": 500,
    "script": "MsMadi"
  },
  "captions": {
    "variant": "lumen",
    "size": 54,
    "hookSize": 62,
    "weight": 700,
    "radius": 12,
    "maxWords": 4,
    "maxChars": 24,
    "hookMaxWords": 3,
    "hookMaxChars": 18,
    "maxPageDur": 2.0,
    "text": "#FFFFFF",
    "grad": ["#FFE7A3", "#FF9A2E", "#E8501C"]
  },
  "grade": {
    "sat": 1.0,
    "con": 1.2,
    "bri": 0.8,
    "warm": 0.12,
    "rawSat": 0.5,
    "rawCon": 0.92,
    "rawBri": 1.0
  },
  "camera": {
    "talk": 1.1,
    "close": 1.28,
    "jumpZoom": 1.05,
    "punch": 0.07,
    "breathe": 0.014,
    "drift": 2.0,
    "push": 0.05
  },
  "wipe": {
    "band": 130,
    "skew": 140
  },
  "sound": {
    "bed": "lumen",
    "music_db": -15,
    "sfx_db": -7,
    "punch_sfx": "swoosh",
    "emph_pop": false
  },
  "cuts": {
    "fx": [
      "zoom",
      "leak"
    ],
    "minGap": 3.6,
    "dur": 0.26,
    "pre": 0.1
  },
  "rhythm": {
    "maxStatic": 3.0,
    "maxGraphicsPerMin": 22,
    "maxSfxPerSec": 1.4,
    "maxWipes": 1
  },
  "finish": {
    "grain": 0.03,
    "vignette": 0.42,
    "leak": ["#FFB45A", "#FF6A3D"]
  },
  "depth": {
    "grad": "fire"
  },
  "depth_defaults": {
    "look": "lumen",
    "sweep": true
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
