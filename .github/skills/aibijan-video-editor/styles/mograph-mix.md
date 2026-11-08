# Style: MOGRAPH MIX · `mograph-mix`

> Talking head and full-screen motion graphics take turns, cut like a premium After Effects Reel. The speaker hooks
> and lands each point with a kinetic title; in between, the words become glass scenes floating in moving aurora
> light: 2.5D phone mockups playing the footage, an AI prompt window, a feature checklist with a big gradient word,
> stacking cards, a chat. Every move is eased (quint/expo in-out, blur-to-sharp, masked reveals) - silky, never bouncy.

**Use for** product / service explainers, "how I do X" workflows, seller & UMKM promos, app or tool demos, courses,
tips with steps - anything where showing beats telling. Best when the script names concrete things (orders, chats,
features, steps, before/after).
**Not for** serious/documentary topics (→ EDITORIAL), pure personality clips with nothing to illustrate (→ CREATOR POP),
a monochrome terminal look (→ SYSTEM).
**Recommended settings** editing_intensity high · sound_effects medium · visual_overlays moderate–extensive.

## Look
- Backdrop **aurora**: deep midnight indigo with slowly drifting light (violet, coral, amber, a touch of cyan and pink)
  and a faint perspective floor grid for depth; vignette and fine grain.
- **Glass** UI: translucent dark panels with a top sheen, 2 px light border, soft long shadows. Highlights use the
  **coral → amber gradient** (`accent` → `amber`); checks are **mint**; icons sit on violet/pink or cyan/violet gradients.
- **Bricolage Grotesque** ExtraBold for display words (mixed case, tight tracking, the key word in gradient with a light
  sweep) and **Manrope** ExtraBold for captions, chips, cards and UI.
- Face shots carry subtle face-tracked **accents**: lens glints, and focus brackets on the hook.

## Two looks (same motion, different surface)
- **`aurora`** (default) - everything above: midnight + drifting light, glass, coral → amber gradients.
- **`flat`** - for a clean, printed, non-"AI" feel: warm paper backdrop with a fine drafting grid and crop marks, solid
  **ink** black, **signal orange** and **cobalt** only. No gradients, no glows, no glass: HeroTitle words sit on solid
  blocks that wipe in (accent word on orange), chips/cards/bubbles are solid, captions are a solid ink pill with orange
  key words, the orb is solid orange with an ink ring, the bars are ink/orange/cobalt. Choose it with `"look": "flat"` at
  the top of creative.json (or when the user asks for "flat", "tanpa gradasi", "clean", "minimalis", "nggak kayak AI").

## Captions
- `glass` (karaoke): the page sits in a frosted dark pill (54 px, Manrope ExtraBold, white); upcoming words wait at
  ~40 % and light up smoothly as they are spoken; emphasis words light up in the coral → amber gradient.
  1–3 words per page. They stay on during scenes.
- Hidden automatically under a `HeroTitle` or a `SceneFeatures` whose words are being said (no double text).

## Motion language (what makes it feel premium)
- **Eased, not springy.** Entrances easeOutQuint (0.6–0.8 s) with blur-to-sharp and a short rise; exits easeInQuint
  (0.25–0.35 s), always faster than entrances. Only tiny badges (checks) get a slight overshoot.
- **Text rises out of masks** word by word (75 ms stagger) and leaves the same way; the gradient word gets one light
  sweep after it lands. Tags reveal with an expo clip-wipe; chips slide up out of blur.
- **Depth.** Phones turn in 3D (rotateY easing to ±10°), cards flip up from the floor (rotateX), every scene has a slow
  3.5 % camera push, the aurora keeps drifting behind.
- **Covers.** Into a scene: `orb` (a gradient circle swells over the frame, a circular hole opens on the new shot, light
  ring on the edges). Back to the face: `bars` (six rounded gradient bars sweep up in a stagger). Scene → scene:
  push with motion blur (`exit`/`enter: "slide"`).

## Structure: face ↔ scene alternation (the point of this style)
- Beats alternate: face (hook) → scene → scene → face → scene → face … → end card. Aim for 40–60 % of the runtime in
  scenes, no face beat longer than ~5 s, no scene longer than ~5 s.
- A scene = layout `"preset": "none"` with `"transition": "orb"` + one scene overlay for exactly that window (a library
  `Scene*`, `SceneLens`, or a custom scene drawn from this script's concept); the face returns with
  `"preset": "full", "transition": "bars"`.
- **Scenes come from the concept, not from the list.** The library scenes (phones, prompt, features, stack, chat) fit
  product/workflow scripts. Any other script gets its own picture composed with the scene kit (`SceneBuild`, see
  `references/visual-ideas.md`), or borrowed from other styles (`SceneLens`, `SearchStack`, `Shout`, `Cutout`).
- Face beats land their point with a kinetic title **when the words deserve it** - not automatically. No two titles
  alike: mix `layout` (center / left / right), `stack`, `layer: "behind"` (huge word behind the head), `fx`
  (rise / slam / slide / type), `sizes` and height. Some face beats need only captions and a punch-in.
- One `Accents` overlay over the whole edit (`items: ["glints"]`), plus `["brackets"]` on one face beat if you like;
  both hide during scenes.

## Signature graphics (plan them in creative.json)
| Moment | Element | Rule |
|---|---|---|
| Face beat (when it needs words) | `HeroTitle` `{kicker, text, accent, tag}` 1–2 words; vary `layout`/`stack`/`fx`/`layer: "behind"` | each piece ≥ its reading time, spoken words, no two alike |
| The script's own image (signature) | a `SceneBuild` composition (people, crowd, objects, tag, bubbles, bars, counter, chart, steps…), a borrowed element (`SceneLens`, `SearchStack`, `Cutout`, title behind the head), or a custom component | drawn from the concept, appears on its words |
| Before / after, "this video" | `ScenePhones` 1–2 phones in 3D playing the footage (`grade 0` raw / `1` edited), labels, light arc | labels spoken |
| "I asked AI / I typed" | `ScenePrompt` clip arcs into the input (`attach`), `prompt` typed, optional `steps` | prompt = spoken or user copy |
| Any spoken list / features | `SceneFeatures` chips row + big gradient word + art per item (`cut`, `caption`, `motion`, `zoom`, `broll`, `sound`, or an icon) | one item per spoken word |
| Orders / messages / what you get | `SceneStack` glass cards flipping up (`repeat` for "so many") | titles spoken |
| Conversation / revision | `SceneChat` glass + gradient bubbles, `typed` input, `media: "clip"` video bubble, `progress` | text spoken |
| Every face↔scene switch | layout `transition` `orb` (into a scene) / `bars` (back to the face) | automatic cover + whoosh |
| CTA | face + `HeroTitle` (brand + tag "… di bawah" `tagIcon: "arrowDown"`), then `EndCard` (automatic `hero` variant) | spoken CTA |

## 30-second rhythm (a shape, not a recipe)
The rhythm is fixed; what fills each beat comes from the concept.
1. **0–4 s HOOK (face)** the hook words big - behind the head, sliding in, or slammed - plus one move (punch, pull-back).
2. **4–11 s SCENES** orb → one or two scenes that *show* the premise / problem / how it works (push between them).
3. **11–14 s FACE** bars → the point, landed by a title or simply by a punch-in.
4. **14–20 s SCENE** orb → the example, the proof, the conversation, the reveal (the signature scene often lives here).
5. **20–24 s FACE** bars → the turn or the offer; a Cutout works well on a reveal.
6. **24 s–end** the payoff / CTA: face + title, or orb → hero EndCard; music hit on the last word.
Product example (phones → prompt → features → chat → cards → CTA) in `assets/examples/mograph-mix*.creative.json`;
a what-if story would instead compose a crowd with search bars, a magnifier over hidden words and a price-tag reveal.

## Sound
`playful` bed at −14 LU. Whoosh on every cover (+ a soft shimmer on orbs), swooshes on titles and cards, typing + click
on prompts and typed chat, dings on checks. Never cover a spoken word with a loud hit.

## Never
Emoji · scene text nobody said · imitating a real app's UI or logo (the chat/prompt windows are generic) · springy
rubber-band motion on text · scenes without a cover or push transition · more than ~5 s on one scene · titles over the
eyes/mouth · more than two fonts.

## Depth pack in this style (v3.7)
- On face beats between scenes: `DepthText` `stack` (display font, `look: "gradient"` on aurora, `fill` on flat)
  for the key word of the next scene, so the face beat hands over to the scene; `split` for "sebelum / sesudah".
- `FeedWall` is a ready-made "so much content / so many orders" beat behind the face (`frames_src: "auto"`, swipes on
  the spoken items), a strong alternative to a full-screen scene.
- `Doodles` stay rare here (the scenes already carry the drawing); camera `glitch` only into a tech scene.
- Premium pack (v3.9): `Checklist` and `SideCards` work as quick in-face scenes between full scenes; `Glossy` objects inside SceneBuild beats via custom components.
- Note pack (v3.10): a `NoteBoard` is a calm scene between motion scenes; `HeadNote` labels the scene topic on face beats.

## Pre-render checklist
- [ ] The face is off screen for 40–60 % of the runtime, in 3+ separate scenes.
- [ ] Every scene element appears on the word it illustrates; every scene text is spoken (or user copy).
- [ ] The plan has a `concept` and a signature moment only this video has; no two titles look alike.
- [ ] Titles sit off the eyes/mouth on the stills (below the chin, beside, or behind the head); accents never touch the eyes/mouth.
- [ ] Captions are visible on scenes and hidden only under titles/feature words.

## Motion & finish (v2 engine, automatic from the config below)
Cuts inside face beats: `zoom` / `whip` rotating, ~one per 3.5 s; operator drift 2 px. Finish: grain 0.03, vignette.
Rhythm ≤ 2.4 s. End card: `hero`. Defaults: intensity high.
All of it can be overridden in creative.json (`cut_fx`, `style_overrides`).

```json style-config
{
  "id": "mograph-mix",
  "look": "aurora",
  "colors": {
    "bg": "#0E0B26",
    "paper": "#FFFFFF",
    "ink": "#170F30",
    "muted": "#B7B0D6",
    "soft": "#221C4A",
    "line": "#3A3266",
    "accent": "#FF6A3D",
    "amber": "#FFB547",
    "accent2": "#8B6CFF",
    "pink": "#FF5EA8",
    "coral": "#FF5E5B",
    "cyan": "#38D6FF",
    "mint": "#4FF0B4",
    "danger": "#FF4D6A",
    "grid": "rgba(255,255,255,0.06)",
    "glow1": "rgba(139,108,255,0.3)",
    "glow2": "rgba(255,106,61,0.22)"
  },
  "fonts": {
    "head": "Manrope",
    "body": "Manrope",
    "ui": "Manrope",
    "display": "BricolageGrotesque",
    "displayWeight": 800,
    "serif": "PlayfairDisplay",
    "serifWeight": 500
  },
  "backdrop": {
    "variant": "aurora",
    "grid": 0.18
  },
  "captions": {
    "variant": "glass",
    "size": 54,
    "hookSize": 60,
    "weight": 800,
    "radius": 54,
    "maxWords": 3,
    "maxChars": 18,
    "hookMaxWords": 2,
    "hookMaxChars": 14,
    "maxPageDur": 1.5,
    "text": "#FFFFFF",
    "dim": 0.38
  },
  "grade": {
    "sat": 1.1,
    "con": 1.08,
    "bri": 1.03,
    "warm": 0.04,
    "rawSat": 0.45,
    "rawCon": 0.9,
    "rawBri": 1.0
  },
  "camera": {
    "talk": 1.08,
    "close": 1.22,
    "jumpZoom": 1.05,
    "punch": 0.06,
    "breathe": 0.012,
    "drift": 2,
    "push": 0.04
  },
  "wipe": {
    "band": 125,
    "skew": 130
  },
  "sound": {
    "bed": "playful",
    "music_db": -14,
    "sfx_db": -8,
    "punch_sfx": "swoosh",
    "emph_pop": false
  },
  "cuts": {
    "fx": [
      "zoom",
      "whip"
    ],
    "minGap": 3.5,
    "dur": 0.24,
    "pre": 0.1
  },
  "rhythm": {
    "maxStatic": 2.4,
    "maxGraphicsPerMin": 26,
    "maxSfxPerSec": 1.7,
    "maxWipes": 1
  },
  "finish": {
    "grain": 0.03,
    "vignette": 0.12
  },
  "endcard": {
    "variant": "hero"
  },
  "textFx": "rise",
  "defaults": {
    "sound_effects": "medium",
    "editing_intensity": "high"
  },
  "looks": {
    "flat": {
      "colors": {
        "bg": "#F1EEE7",
        "paper": "#FFFFFF",
        "ink": "#121212",
        "muted": "#6E6A62",
        "soft": "#E6E2D9",
        "line": "#D8D3C7",
        "accent": "#FF4D17",
        "amber": "#FF4D17",
        "accent2": "#2340FF",
        "pink": "#2340FF",
        "coral": "#FF4D17",
        "cyan": "#2340FF",
        "mint": "#121212",
        "danger": "#E5322D",
        "grid": "rgba(18,18,18,0.06)",
        "glow1": "rgba(0,0,0,0)",
        "glow2": "rgba(0,0,0,0)"
      },
      "backdrop": {
        "variant": "paper",
        "cell": 90
      },
      "captions": {
        "box": "#121212",
        "text": "#F4F1EA",
        "emph": "#FF4D17",
        "dim": 0.42
      },
      "grade": {
        "sat": 1.04,
        "con": 1.06,
        "bri": 1.03,
        "warm": 0.0
      },
      "finish": {
        "grain": 0.035,
        "vignette": 0.0
      }
    }
  }
}
```
