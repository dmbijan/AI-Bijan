# creative.json — the footage-specific scene plan

`plan-edit.py` does the reusable work automatically (pause cuts, captions, framing, SFX, music). `creative.json`
is where Claude writes the choices that depend on *this* footage: which words get emphasis, where the camera
punches in, when the layout changes, which graphics appear. Everything is optional; an empty `{}` gives a clean
captions + zooms + sound edit.

Times are **time references**, so the plan survives re-cutting (changing pause threshold or trimming):

| Ref | Meaning |
|---|---|
| `"w:12"` / `"w:12e"` | start / end of transcript word 12 (index in `transcript.json` → `words[].i`) |
| `"w:12+0.2"`, `"w:12-0.1"` | with an offset in seconds |
| `"s:4.5"` | source-time seconds (mapped through the cuts) |
| `"t:3.2"` | edited-timeline seconds (what the user sees as "detik 3") |
| `"end"` | end of the edit |

Any key named `start`, `end`, `at`, `from`, `to`, `cutAt`, `subAt`, `kicker`, `wordmark`, `button`, `hit_at`,
`break_at`, `until` or ending in `At` is resolved. Other strings are left alone.

## Top-level fields

```jsonc
{
  "concept": {"idea": "the picture this video is about", "signature": "the one moment only this video has"},  // see SKILL.md 'Direct it first'
  "brand_kit": "/mnt/user-data/uploads/brand-kit-toko.json",   // optional: a creator's saved colours/fonts/copy/defaults
  "custom_components": ["/home/claude/work/mograph-mix/components/SceneCrowd.jsx"],  // optional: creative mode
  "style": "impact",                       // impact | system | editorial | creator-pop | mograph-mix | storyteller | depth | lumen | poster | noted
  "style_overrides": {"colors": {"accent": "#00E5A0"}},   // optional brand tweaks (deep-merged)
  "settings": {                             // same names as the user-facing options
    "fps": 60,                              // always 60 in this skill (the planner enforces it)
    "editing_intensity": "medium",          // low | medium | high   -> pause threshold + padding, punch-ins
    "aspect_ratio": "9:16",                 // 9:16 | 16:9 | 1:1 | 4:5
    "target_duration": null,                // seconds; tightens pauses only, never deletes words
    "caption_style": "kinetic",             // kinetic (style default) | clean | highlighted
    "music": "enabled",                     // enabled | disabled
    "sound_effects": "medium",              // off | subtle | medium | energetic
    "visual_overlays": "moderate",          // minimal (priority 1 only) | moderate (<=2) | extensive (all)
    "remove_fillers": true                  // cut "eh/emm/anu/um/uh" automatically (default true)
  },                                        // unset settings come from the style's "defaults", then the global defaults
  "trim": {"start_at_word": 0, "end_after_word": 92, "head": 0.1, "tail": 0.15},
  "remove_words": [[40, 47]],               // retakes / flubs to cut out entirely (inclusive word ranges)
  "keep_words": [12],                       // never auto-cut these words (e.g. an "eh" that is part of a joke)
  "auto_retakes": false,                    // true = apply every RETAKE? suggestion without review
  "cut_fx": "auto",                         // "auto" (style) | "none" | [{"at": "w:31", "fx": "zoom|whip|push|flash|leak", "dir": 1|-1}]
  "auto_rhythm": true,                      // insert slow pushes where nothing changes for longer than the style allows
  "auto_hide_captions": true,               // hide captions under a KineticTitle/EndCard that shows the same words
  "user_copy": ["Kelas Editing AI", "Diskon 50%"],   // exact text the USER wrote (allowed in graphics besides transcript words)
  "end_hold": 1.0,                          // seconds of graphics after the footage ends (end card)
  "jump_zoom": true,                        // alternate zoom at every jump cut (set false when layouts already change at cuts)
  "glossary": {"Klod": "Claude"},     // display spelling for brands/terms; multi-word keys merge words into one token
  "emphasize": [13, {"w": 27, "icon": "scissors"}],   // emphasis chips (max one per caption page); icons below
  "auto_emphasis": true,                    // default true only when "emphasize" is empty (ALL-CAPS, numbers, glossary)
  "captions": {
    "y": 1244,                              // caption bottom edge (px). Default per aspect keeps it in the safe zone
    "maxWidth": 920,
    "zones": [                              // per time-range overrides
      {"from": "t:0", "to": "t:0.4", "variant": "raw"},        // plain "unedited" subtitle look
      {"from": "t:0.4", "to": "w:14e", "variant": "hook"},    // bigger 1-2 word pages
      {"from": "w:81", "to": "end", "y": 858, "size": 54},    // move captions for an end card
      {"from": "w:84", "to": "w:86", "hidden": true}          // hide while a title shows the same word
    ]
  },
  "camera":  [ ... ],
  "layouts": [ ... ],
  "overlays": [ ... ],
  "sfx": [ ... ],
  "music": {"hit_at": "w:92", "break_at": "w:81", "db": -12, "file": "/mnt/user-data/uploads/lagu.mp3"}  // file = the user's own track (their licence)
}
```

## camera

Applies to footage views with `camera: true` (the full-frame view). Framing is anchored on the tracked face.

```jsonc
{"at": "t:0",  "preset": "talk"}                      // wide (s=1, no reframing) | talk (lifts face above captions) | close
{"at": "w:31", "preset": "punch", "amount": 0.12, "hold": 0.45}   // quick push-in on a word, then relax
                                                       // (styles with camera.smooth - NOTED - make it a soft eased push)
{"at": "w:36", "preset": "close", "dur": 1.4}         // slow push over 1.4 s
{"at": "w:57", "s": 1.2, "fx": 0.5, "fy": 0.42, "dur": 0.5, "ease": "camera"}   // explicit
{"at": "w:20", "preset": "shake", "amount": 1.0, "dur": 0.45}  // impact shake (v3.7), decays; face-tracked graphics follow
{"at": "w:40", "preset": "glitch", "dur": 0.3}                 // RGB split + jitter hit (v3.7)
```
`fx/fy` = where the face should land on the canvas (fractions). The frame is always clamped so no edge shows.

## layouts

Each entry changes how the footage is shown, with a transition. Views are matched by `id` so a view can morph
from full-frame into a card.

| preset | What it is | Useful params |
|---|---|---|
| `full` | full-frame, camera-driven, graded | `grade` (0 raw .. 1 edited) |
| `raw-full` | full-frame, ungraded, no reframing ("before") | — |
| `split` | two cards side by side: `raw` (left, ungraded) + `main` (right, edited) | `y`, `h`, `z`, `fy`, `left_tag`, `right_tag`, `border`, `only: "left"/"right"` (single card: no default MENTAH/SETELAH tag) |
| `card` | one rounded card | `rect [x,y,w,h]`, `z`, `fy`, `radius`, `tag`, `border` |
| `pip` | round/rounded picture-in-picture (end cards) | `rect`, `size`, `z`, `fy`, `border` |
| `versus` | diagonal split-face: the same framing on both sides, raw grade left / edited right, white divider (CREATOR POP before/after) | `top`, `bottom` (divider x as fractions, default 0.6 / 0.4), `divider: false` or `{width, color}` |
| `none` | no footage: the style backdrop shows (end cards, and MOGRAPH MIX scenes) | — |
| `custom` | your own `views: [{id, rect, radius, z, fx, fy, grade, shadow, border, tag, enter, camera, clip}]` | — |

`card` also accepts `rot` (tilt in degrees), `borderColor`, `halftone` (0–0.4 dot screen) and `tape: true`.

Cover transitions vary per video automatically (orb origin: the face, a corner or the centre; bars up or down, 5–7
strips). Pin them with `x`/`y` (orb origin, fractions of the frame), `dir` (`"up"`/`"down"`) and `bars` (count).

Transition fields: `transition` = `morph` (default, eased), `wipe` (accent band reveals the new state left→right;
use once for a transformation moment), `cut`, `orb` / `bars` (cover transitions: the switch happens at `at` under a
full-frame gradient orb that opens a circular reveal, or staggered gradient bars sweeping up; a `ShapeWipe` overlay is
generated with a whoosh; MOGRAPH MIX uses `orb` into a scene and `bars` back to the face, optional `color`/`color2`),
`bloom` / `iris` (STORYTELLER covers: `bloom` swells white light to full white at `at` and fades out on the new state;
`iris` closes a black circle onto the face and reopens it - with `"hold": true` it stays black to the end, the
ending); `dur` seconds; `ease`;
`spring: true`; `sfx` (`"none"` to mute).
Tags: `{"text": "MENTAH", "tone": "ink|accent|light", "icon": "sparkle"}` appear inside the card.

## overlays

Common fields: `type`, `start`, `end`, `layer` (`back` | `behind` the speaker | `below` captions | `above`, default above; DepthText and FeedWall default to `behind`), `priority`
(1 essential … 3 decorative; filtered by `visual_overlays`), `sfx: "none"` to mute its default sound,
`copy: "user"` when the text was written by the user (otherwise every word must come from the transcript),
`signature: true` (this element is the video's signature moment), `cues: [{at, kind, gain_db, until}]` (explicit
sounds tied to the element), `hide_captions: true` (hide captions while it shows the spoken words), `scene: true`
(custom full-screen scene: drawn under the captions on the backdrop), `box: [x0, y0, x1, y1]` (custom element's
bounding box so the planner can check face and caption collisions).

**Every overlay renders in every style.** The library is shared: a STORYTELLER `SceneLens` or `SearchStack`, a MOGRAPH
MIX `HeroTitle` or `SceneFeatures` can be used in any style and wears that style's colours and fonts (solid surfaces on
light styles and the MOGRAPH MIX flat look, glass on dark ones). Pick by what explains the beat, not by the style's
own list.
Positions are canvas pixels (1080×1920 for 9:16). **`x` is always the horizontal centre** of the element
(default = canvas centre); the only exceptions are `align: "left"` on KineticTitle/Badge (then `x` is the left
edge) and LowerThird (left edge). `y` is the top edge for blocks (KineticTitle, cards, Steps, LowerThird) and the
centre for single-line pills/numbers (Badge, PromptBar, Counter, FloatCard, Tile). Keep text inside the safe zone
and **off the face** - plan-edit flags both.

| type | Purpose | Main props |
|---|---|---|
| `Viewfinder` | camera UI (REC, RAW tag, brackets, timecode) for "raw" moments | `label`, `tcOffset`, `rect` |
| `Badge` | pill with icon + short text | `text`, `icon`, `tone` ink/accent/accent2/paper, `x`, `y`, `size`, `align` |
| `KineticTitle` | stacked headline lines, each animated with a text preset | `x` (centre), `y` (top), `width`, `align`, `fx`, `dim` (0–0.7 darkens the frame behind it), `lines: [{text, at, size, tone, font: "serif", shadow, rot, fx}]`; `fx` = `rise · pop · slam · track · focus · type · cascade · slide` (default from the style). tone `ink` (default) · `paper` · `accent` · `chip` · `accentChip`. **Over footage use `paper` + `shadow: true` or a chip tone**; plain `ink` only on paper/backdrop areas. Captions under it are auto-hidden when it shows the spoken words |
| `Waveform` | the REAL waveform of a source range; removed pauses flash red and collapse | `x`, `y`, `w`, `h`, `cutAt`, `peaks_from: {src_from, src_to, bars}`, `cuts: "auto"`, `label` |
| `Tile` | square icon card (feature lists); can fly into a target on exit | `x`, `y`, `w`, `icon`, `label`, `from` left/right, `exitTo [x,y]`, `iconBg` |
| `FloatCard` | card with icon, title, subtitle; floats | `x`, `y`, `w`, `icon`, `title`, `subtitle`, `subAt`, `tone`, `tilt`, `from` |
| `PromptBar` | stylised prompt input that types and "sends" | `text`, `at`, `typing`, `x`, `y`, `w`, `fontSize` |
| `ChatPanel` | stylised chat: user types in the input, AI replies, progress bars | `x` (centre), `y` (top), `w`, `threadH`, `anchor: "top"`, `messages: [{role, text, at, typing, thinking, progress: {at, dur, label, doneText}}]` |
| `FilmStrip` | thumbnails of the user's own footage sliding in (b-roll / versions) | `y`, `x0`, `thumbW`, `frames_src: [source seconds]` |
| `EndCard` | kicker → two-part wordmark → CTA button. Look follows the style (`style.endcard.variant`): `block` (IMPACT/SYSTEM pill + accent block, display font), `title` (EDITORIAL serif title card, `tone: "light"` over a Scrim), `sticker` (CREATOR POP stickers), `hero` (MOGRAPH MIX kinetic gradient wordmark) | `kicker`, `word1`, `word2`, `button`, `y`, `size`, `chevrons`, `at: {kicker, wordmark, button}`, `shiftAt`, `y2`, `variant`, `tone` |
| `LowerThird` | speaker name + role strip (user-provided, mark `copy: "user"`) | `name`, `role`, `x` (left), `y` (top), `tone` ink/paper, `size` |
| `Steps` | numbered list that builds on each spoken item; current item highlighted | `title`, `items: [{text, at}]`, `x`, `y`, `w`, `fontSize` |
| `Counter` | count-up number (ONLY numbers that were said/written) | `value`, `from`, `prefix`, `suffix`, `label`, `countAt`, `countDur`, `decimals`, `x`, `y`, `fontSize`, `plate` (default dark plate) |
| `Callout` | hand-drawn write-on circle / underline / arrow + optional label | `shape` circle/underline/arrow, `x`, `y`, `w`, `h`, `to: [x, y]` (arrow), `label`, `color`, `stroke`, `drawDur` |
| `BRoll` | the user's OWN extra photo/clip as a cut-away (a clip plays from `from` at the overlay's start) | `src` (uploaded file path), `mode` full/card, `rect`, `enter` zoom/whip/rise/cut, `exit: "cut"` (no exit move: back-to-back clips cut on the words), `kenburns` in/out/left/right, `from` (clip start s), `label`. Full-frame b-roll under the captions: `layer: "below"` |
| `ProgressBar` | thin watch-progress bar at the top (opt-in) | `color`, `h`, `y` |
| `LightLeak` | warm light leak sweep (calm styles, transitions) | `start`, `end`, `dir`, `strength` |
| `Burst` | accent line burst on a hit | `x`, `y`, `at`, `radius`, `count` |
| `Flash` | brief white flash (hard cuts) | `at`, `dur`, `amount` |
| `Scrim` | darkens (and blurs) the footage behind a title; put it on `layer: "below"` | `dim` 0–0.8, `blur` px, `gradient` full/center/bottom/top, `rect` |
| `TitleCard` | EDITORIAL film/magazine title: letter-spaced kicker, hairline rules, serif lines that rack-focus in, optional sub line | `tone` light (over a Scrim) / dark (on paper), `kicker`, `lines: [{text, at, size, italic, accent, font: "sans"}]` or `title`, `sub`, `quote`, `rules`, `x`, `y` (top), `w`, `size` |
| `Sticker` | CREATOR POP die-cut sticker: white outline, hard shadow, peels on with a spin, wobbles | `text` (1–3 words), `icon`, `tone` accent/accent2/mint/coral/paper/ink, `x`, `y` (centre), `rot`, `size`, `maxW` (wrap), `shape` pill/rect |
| `StickerStack` | a spoken list as stickers slapped around the face, one per item on its word (counts as one graphic) | `items: [{text, at, x?, y?, rot?, tone?, icon?}]`, `slots: [[x, y, rot], ...]`, `size`, `maxW`, `tones` |

Icons: `scissors cc sparkle zoom film sound chat play file check send arrowDown arrowRight wand bolt layers wave rec cursor refresh book`.

### MOGRAPH MIX: scenes, hero titles, accents

Looks: top-level `"look": "aurora"` (default: dark aurora light, glass, gradients) or `"look": "flat"` (warm paper grid,
solid ink / orange / cobalt, word blocks, no gradients or glows). Same elements and motion; the look only changes surfaces.

A **scene** is a full-screen motion-graphic beat while the face is off screen: a layout `{"preset": "none",
"transition": "orb"}` at the scene start, one `Scene*` overlay for exactly that window, and a layout
`{"preset": "full", "transition": "bars"}` when the face returns. Scene → scene without the face: no layout change;
`"exit": "slide"` on the outgoing scene and `"enter": "slide"` on the incoming one (eased push with motion blur).
Scenes default to `layer: "below"` (captions stay on top), float on the aurora backdrop, and get a slow 3.5 % camera
push (`push` to change). Content lives between y ≈ 330 and 1150 so the caption pill (bottom 1244) stays free. Every text
in a scene must be spoken (or user copy) and appear on its word. All motion is eased (quint in/out, blur-to-sharp).

| type | What it shows | Key params |
|---|---|---|
| `ScenePhones` | 1–2 phone mockups turning in 3D, playing the live footage (raw grade 0 / edited grade 1), label chips, a light arc between them | `phones: [{label, at, grade, tone, icon, zoom}]`, `y` (centre, 760), `w`, `arrow` |
| `ScenePrompt` | generic AI window (glass, gradient rim): the clip arcs in and lands in the input (`attach`), prompt typed with caret, send button pulses, optional checklist | `prompt`, `at` (typing start), `typing` s, `attach`, `attachAt`, `clipLabel`, `steps: [{text, at}]`, `label`, `y`, `w` |
| `SceneFeatures` | a spoken list: chips row (current = gradient, done = ✓) + big gradient word rolling item to item + an animated illustration per item | `items: [{text, word?, at, art, caption?, chip?, accent?}]` with `art` = `cut` `caption` `motion` `zoom` `broll` `sound` or an icon name; `size`, `chipSize`, `artScale`, `y` |
| `SceneStack` | glass cards flipping up from the floor and stacking (orders, messages, what you get); `repeat` for "so many" | `items: [{title, sub?, icon, tone, badge?, at}]` (`tone` accent / accent2 / cool / mint / amber), `repeat`, `stagger`, `y`, `w`, `cardH` |
| `SceneChat` | glass (left) and gradient (right) bubbles: `media: "clip"` video bubble, `typed` message typed in an input bar then sent, typing dots, `progress` bar + check | `messages: [{text | media, side: left/right, at, typed, typing, progress: {at, dur}, grade}]`, `y`, `w`, `fontSize` |

All scenes accept `tag` (glass chip top-left, `tagTone`, `tagX`, `tagY`), `enter` `pop`/`slide`/`cut`, `exit`
`fade`/`slide`/`cut`, `push`.

| type | Use | Key params |
|---|---|---|
| `HeroTitle` | kinetic display title (any style): words enter one by one, the `accent` word highlighted (gradient on aurora, solid accent block on flat/light styles), `kicker` chip above, `tag` pill below (expo clip-wipe). Exits are faster than entrances. **Variants - never use the same combination twice in a video:** `layout` `center`/`left`/`right` (`x` = centre / left edge / right edge), `stack: true` (one word per line, poster), `sizes: [0.6, 1]` (per-word scale: small lead word + huge key word), `fx` `rise` (masked rise / block wipe) · `slam` (scales in from 1.6× with motion blur) · `slide` (pushes in sideways with directional blur, `from` left/right) · `type` (letters typed with a caret, `cps`), `layer: "behind"` (huge word behind the head, size 200–260, `y` just above the hair so the head covers its lower part). Off the eyes/mouth (lint checks the face) | `text` (1–2 words), `accent`, `at`, `kicker`, `kickerAt`, `tag`, `tagAt`, `tagIcon`, `x`, `y` (centre of the block), `size` (120–260), `layout`, `stack`, `sizes`, `fx`, `from`, `cps`, `kickerX`, `tagX` |
| `Accents` | subtle face-tracked accents, hidden automatically while the face is off screen: `glints` (lens glints), `brackets` (focus corners around the face, great on the hook), `orbit` (tilted ring with a travelling light) | `items: [...]` |
| `ShapeWipe` | the cover transition itself (normally generated from layout `transition: "orb"/"bars"/"bloom"/"iris"`) | `at`, `shape` orb/bars/bloom/iris, `hold` (iris), `x`/`y` (orb origin, fractions), `color`, `color2`, `color3`, `inDur`, `outDur`, `bars` |

`EndCard` variant `hero` (MOGRAPH MIX default): optional glass kicker, `word1` white + `word2` gradient display words rising
out of masks with a light sweep, optional gradient `button`, chevrons, on the aurora backdrop (layout `none`).

Honesty rules for overlays: chat panels and prompt bars are generic illustrations — no real product names, logos
or UI copies, never shown as "proof". Do not invent URLs, prices, numbers, testimonials or course features the
user did not provide. Brand text only as the user wrote it.


### STORYTELLER: moods, search bars, floaters, shouts, the lens

The picture follows the emotion of each line. Read the script's markers ([shocked], [whispers], [serious]…) or the
delivery, and give every beat one `MoodGrade`. Everything here is eased (quint/expo); nothing bounces.

| type | Use | Key params |
|---|---|---|
| `MoodGrade` | colour mood for one beat, layered over the footage (auto-sorted under the other graphics). `warm` setup / wonder · `dream` "bayangin…" · `amused` jokes · `excited` · `shock` (white flash + red punch) · `whisper` (rose, desaturated, heavy vignette) · `serious` (cold blue, desaturated) · `dark` (near-black drama) · `mono` (black & white; `start: "t:-0.05"`, `in: 0.01`, `out: 0.6-0.9` makes colour bloom in - a flashback or "before" moment). Back-to-back moods may overlap ~0.2 s; never stack two for longer | `mood`, `start`, `end`, `in` / `out` (ease seconds, default 0.6), `amount` (0–1), `tint` (override colour) |
| `SearchStack` | generic search bars floating above the head (face-tracked): each item rises out of blur, its query typed with a caret; newer bars push older ones up. Items without `text` are shimmer placeholders (the premise: "searches above everyone's head"). Captions hide while a bar types a spoken quote. Leave room above the head (camera `s` ≈ 1.0–1.12, or a pull-back on the reveal) | `items: [{text?, at, typing}]`, `max` (bars visible; `1` = each new bar replaces the last), `size` (46–50), `lift` (1.08 × face height above the face centre), `gap`, `dx`, `surface` `glass`/`ink`/`paper` (default follows the style); `lift` 1.15–1.25 for big hair |
| `Floaters` | 3D objects drifting around the head with depth of field: extruded glyphs (`?`, `!`) or icons (`eye`, `search`, `heart`, `lock`…), each pops in on its word, bobs and turns | `around: "head"`, `items: [{glyph | icon, at, slot 0–3 (around the head), size (90–150), depth 0–1 (blur/scale), accent}]` |
| `Shout` | the shock word: Unbounded Black with a coloured 3D extrusion, slams in (scale 1.55 → 1 with motion blur), creeps 4 %, fast exit. With `layer: "behind"` the speaker covers its lower part. 1–2 per video | `lines: [{text, size (110–150), at}]`, `at`, `tone` red / gold / ice / white, `y` (top, ≥ 275), `x`, `case`, `layer` |
| `StoryTitle` | the thesis: tracked gold kicker + a big display word rising out of a mask, a light arc drawn over it with a flare head | `text`, `at`, `kicker`, `kickerAt`, `kickerSize`, `y` (baseline area, 380–520), `size` (160–220), `color`, `arc: false` |
| `SceneLens` | full-screen scene (layout `none`, `bloom`/`orb` in and out): a cloud of spoken words, a magnifier glides to each `focus` word on its cue and reads it sharp in the accent colour. Dark on dark styles, paper + ink on solid ones | `words: [{text, x, y, size}]` (all spoken), `focus: [{word, at}]`, `radius` (190–240), `zoom` (1.3–1.75: the zoomed focus word must fit inside the lens), `bg`, `surface` `solid`/`dark` |

Captions look `type` (STORYTELLER default): no box, characters type in as each word is spoken, a gold caret on the last
word, emphasised words in the accent colour.

Honesty: search bars and floaters are generic shapes - never a real search engine's colours, logo or layout.
Queries shown are the spoken words (light spelling normalisation is fine, no new content).

## SceneBuild: the scene kit (compose any picture from JSON)

The default way to show the script's own images. One `SceneBuild` overlay = one composed scene (or a set of things
around the face). What to compose for which kind of line: `references/visual-ideas.md`.

```jsonc
{"type": "SceneBuild", "scene": true, "start": "w:15-0.05", "end": "w:27-0.08",   // full-screen: layout none + orb/bars
 "items": [ {item}, ... ],
 "camera": [{"at": "w:22", "zoom": 1.3, "x": 690, "y": 800, "dur": 0.8}]}         // optional push into a point
{"type": "SceneBuild", "anchor": "face", "start": "w:10", "end": "w:14e", "items": [{..., "slot": "above"}]}  // around the head
```

**Every item:** `kind`, `at` (the word that brings it; default: the scene start), `until` (leaves), `move`
(`rise` default · `pop` · `drop` · `slide` + `from` left/right · `grow` · `slam` · `fade` · `none`), `idle` (`float` ·
`swing` · `pulse` · `spin`), `x`, `y` (centre, canvas px) - or `slot` + `dx`/`dy` in face mode (`above`, `above-left`,
`above-right`, `left`, `right`, `chest`, `chest-left`, `chest-right`; never on the face), `size`, `tone` (`main` ·
`accent` · `accent2` · `soft` · `faint` · `paper` · `danger` · `#hex`), `rot`, `z`, `label` (chip under it, spoken
words), `sfx: "none"`, `copy: "user"`, `hide_captions` / `keep_captions`.

| kind | What it draws | Own params |
|---|---|---|
| `figure` | a person (flat silhouette); `y` = centre of the figure | `pose` stand/wave/point/cheer/shrug/hold, `holds` (a thing name, held at the chest), `flip`, `label` (on the chest), `above: {kind: bar/bubble/thing/text, text, typeAt, typing, name, at}` (something over the head: a typed search bar, a thought, a bulb) |
| `crowd` | rows of people drifting sideways, full width | `n` (7), `y` (front row feet), `size` (scale), `bars: true` + `barsAt` (blank search bars over every head), `highlight` (index in accent), `dimAt` (the crowd steps back, dims and blurs - when one person steps forward), `walk: false` |
| `thing` | one of 48 flat objects: house store building money coin wallet clock hourglass calendar phone laptop chart scale lock key bag box cart truck car chat doc bulb target trophy heart star magnifier gear globe pin camera mic play cup rocket eye bell warning check cross question book door plant fire battery gift | `name`, `size` (220), `tone` (main fill), `tone2` (plant leaves / cross), `trend: "down"` (chart), `tilt` (scale, degrees), `open` (lock, door), `level` (battery 0–1), `spin: false` (gear) |
| `icon` | a line icon (Icons list) | `name`, `size`, `stroke` |
| `chip` | pill label | `text`, `tone`, `icon` |
| `text` | display words | `text`, `size` (120), `accent` (word in the accent colour), `font: "ui"`, `fx: "type"`, `align`, `maxW` |
| `bubble` | speech / thought bubble | `text`, `style: "thought"`, `tail` left/right/down, `size`, `w` |
| `card` | panel with an object or icon + title + sub | `text`, `sub`, `thing` or `icon`, `w` |
| `tag` | price tag; can hang on a string and swing; a stamp slams on | `text`, `sub`, `hang`, `string` (px), `stamp`, `stampAt`, `stampIcon`, `w` (`y` = top of the tag when hanging) |
| `bar` | search / prompt bar; shimmer, then the spoken words typed | `text`, `typeAt`, `typing`, `icon` search/send/sparkle, `size`, `w` (captions hide while it types) |
| `counter` | a number counting up | `value`, `from`, `prefix`, `suffix`, `decimals`, `countAt`, `countDur`, `size` (Indonesian number format) |
| `bars` | bar chart growing bar by bar | `values`, `labels`, `ats` (one time ref per bar), `w`, `h`, `show` (numbers), `highlight`, `max` |
| `meter` | progress bar filling | `value` (0–1), `text`, `dur`, `w` |
| `arrow` | a curved arrow drawn on, full canvas | `from: [x, y]`, `to: [x, y]`, `bend`, `dur`, `width`, `dashed` |
| `steps` | numbered process, each step lights up on its word (block ~760 px wide when vertical) | `items: [{text, at}]`, `dir` down/right, `gap`, `size` |
| `frame` | the speaker's own footage in a frame or phone | `device: "phone"`, `w`, `h`, `grade` (0 raw, 1 edited), `zoom` |
| `rect` / `circle` | panels, spotlights, rings | `w`, `h`, `radius`, `stroke` / `r`, `ring`, `width` |

The planner checks kinds, poses, thing names, face slots, item reading time (labels need 1 s + 0.3 s/word), text in the
caption band, and grounding (all item text must be spoken or user copy). Sounds come from the moves (drop, slam, slide,
pop, typing, counting, stamps) - quiet rises stay silent. Captions hide automatically while a text, chip, card, tag or
bubble shows the words being said, and while a bar types a spoken quote.

## Custom components (creative mode)

For the video's signature image when no library element shows it: write a component in the work folder, list it in
`custom_components`, and use its export name as `type`. Rules, contract and examples: `references/custom-components.md`
(worked: `assets/custom-examples/SceneCrowd.jsx` - crowd or pair of silhouettes with search bars above their heads,
`ScenePriceTag.jsx` - a price tag that drops and swings with a stamp). Time refs in any `...At` key and in `items[].at`
are resolved; `prompt` + `typeAt` hides captions while the quote types.

```json
{"type": "SceneCrowd", "scene": true, "mode": "pair", "start": "w:40-0.05", "end": "w:58-0.1",
 "items": [{"role": "you", "text": "kamu", "at": "w:41"}, {"role": "other", "text": "orang yang kamu suka", "at": "w:43"}],
 "heartAt": "w:46", "barAt": "w:49", "prompt": "cara tahu dia suka kita balik", "typeAt": "w:52", "typing": 1.05,
 "cues": [{"at": "w:46", "kind": "pop"}, {"at": "w:52", "kind": "typing", "until": "w:52+1.05"}]}
```

## Brand kit

`"brand_kit": "<path>"` merges a creator's saved identity (schema `assets/examples/brand-kit.example.json`): `colors`
(all styles) and `styles.<id>.colors` (per style), `fonts` (bundled families only; the planner lists them when a name is
wrong), `copy` (their own words, allowed in graphics), `glossary`, `defaults` (style, look, sound_effects,
visual_overlays, …). Precedence: style defaults < brand kit < creative.json < what the user asks now.

## Background removal: text behind the speaker, Cutout

The engine cuts the speaker out of the frame with a local person matte (no API), only for the seconds that need it,
and draws the person again **above** two extra layers:

- `"layer": "behind"` on any overlay (usually a `KineticTitle`) → the text sits on the wall and the speaker's head
  and shoulders pass in front of it - the Reels "text behind subject" look. Make the word **wider than the head**
  (one word, 250–320 px, IMPACT `Anton`/EDITORIAL serif) and place it so the head covers only the lower third of
  the letters (y ≈ 280–340 in `talk` framing). Ink text on light walls, paper text on dark ones. The face-cover lint
  skips this layer on purpose - check readability on the stills.
- `Cutout` overlay → for a beat the background is replaced and the speaker pops forward with a white sticker outline:
  `{"type": "Cutout", "start": "w:74", "end": "w:81-0.15", "bg": "color" | "blur" | "dim", "color": "accent2",
  "color2": "#4A2FD8", "pattern": true, "outline": 12, "outlineColor": "#FFFFFF", "pop": 1.04}`. Use it on a
  payoff/reveal (1.5–3 s), at most twice per video; stickers/titles placed around the head read beautifully on it.

Cost: ~1 s of compute per footage frame (30 fps masks, cached, resumable; stills compute only what they show).
Keep the total under ~6 s of footage. Works on full-frame shots only (cards/split are skipped). Optional
`"matte": {"fps": 24}` lowers the cost. If the model can't be downloaded the render continues without the cut-out.

## DEPTH PACK: type behind the speaker, rings and doodles around the head, the endless feed

The premium "After Effects Reels" layer tricks. They use the person matte (layer `behind`: the speaker passes in front)
and the face track (things stay on the head while the camera moves). Every overlay here renders in every style.
Text is always spoken words (or user copy); `tone: "auto"` (default) picks ink type on light walls and paper type on
dark ones - the planner samples the wall brightness from the footage (`wallLum` in edit.json).

| type | What it is | Key params |
|---|---|---|
| `DepthText` | big type BEHIND the speaker (default layer `behind`), four modes. **`stack`** lines rise out of masks one by one (letter spacing tightens, slow 4 % creep) - a number, a short claim. **`echo`** one word slams in and outlined copies fan out above/below it - the shock word. **`wall`** rows of a repeated phrase sliding in alternating directions (marquee), optional endless upward drift (`scrollY`) and a speed `ramp` - loops, "terus-terusan", infinite, addiction. **`split`** two words appear from the centre and part to the left/right edges above the head - a two-part reveal | `mode`, `text` / `lines: [{text, at, size, look, tone}]`, `text2` (split), `at`, `y` (top of the block, 270–520: the head should cover only its lower part), `size` (stack 200–260, echo 200–240, wall 130–170, split 170–210; type auto-fits the frame with the font's real metrics), `look`/`looks` (`fill` `outline` `chrome` `gradient` `extrude` `glow`, alternate per line/row), `tone`/`tones` (`auto` `ink` `paper` `accent` `accent2` `danger` `#hex`), `echoes` (3), `echoTone`, `rows` (5), `speed` (px/s), `ramp` (end speed ×), `scrollY` (px/s), `sep` (`"  •  "`), `parallax` (0.1: the type drifts against the face = depth), `creep`, `case` (`upper` default, `none` as spoken, `lower`), `font` role (`display` default · `accent` = the style's italic accent serif, as spoken · `serif` · `condensed` (Anton) · `ui`) on the overlay, per line (`lines[].font`), per wall row (`fonts: ["display", "accent"]` alternates) or per split word (`fonts: ["accent", "display"]`) |
| `HeadRing` | a ring of spoken words orbiting the head in 3D: the back half passes BEHIND the head (mirrored, dimmer), the front half in front of it. One HeadRing = two halves (the planner splits it) | `text` (1–4 spoken words, repeated around the ring), `at`, `size` (50–64), `tone` (`paper` reads best over hair; a contrasting edge is drawn automatically), `radius` (0.92 × face height), `tilt` (ry/rx 0.2), `lift` (0.6–0.66 × face height above the face centre: crosses the hair, not the eyes), `roll` (deg), `speed` (rad/s, 0.8–1.1), `dir: "reverse"`, `line: false` (no orbit line), `glow` |
| `Doodles` | hand-drawn marker doodles around the head that draw on stroke by stroke and "boil" at 12 fps like real hand animation; they follow the face and stay inside the safe area | `items: [{kind, at, until?, side: "right"/"left", scale, dx, dy, color, width, dur, label?}]`, kinds: `sparks` (realisation, "ternyata"), `swirl` (hooked, dizzy, "susah berhenti"), `question` (curious), `exclaim` (alarm), `halo` (innocent, "niatnya"), `circle` (this person / me), `shock` (radiating lines on a shock word), `crown` (winner), `bulb` (idea), `stars` (dizzy, overwhelmed), `arrow` (pointing at the head, optional spoken `label`), `scribble` (stress, chaos), `drops` (nervous), `heart` (love); `color` (`auto`, `accent`, `paper`…), `boil: false`, `glow` |
| `FeedWall` | an endless tilted wall of vertical video cards scrolling up behind the speaker (background replaced): the speaker's own frames (`frames_src: "auto"`) or plain colour cards with a generic skeleton (no real app UI); each `swipes` cue snaps one card up like a thumb flick; the speed ramps | `frames_src` (`"auto"` or source seconds), `frames` (8), `cols` (3), `cardW` (300–340), `gap`, `speed` (px/s), `ramp`, `swipes: [time refs]`, `tilt: [rotX, rotZ]` (16, −10), `scale` (1.25–1.3), `dim`, `blur` (1.2 px depth of field), `bg` (backing colour, `"none"` keeps the wall), `palette` |

Camera hits (in `camera`): `{"at": "w:20", "preset": "shake", "amount": 1, "dur": 0.45}` (decaying handheld shake on an
impact word - everything face-tracked shakes with the footage) and `{"at": "w:40", "preset": "glitch", "dur": 0.3}`
(RGB split + horizontal jitter at 30 Hz, for digital/tech words or a hard turn). Sounds: `impact` / `glitch`.

Cost: everything on layer `behind` (DepthText, the back half of a HeadRing, FeedWall) needs the person matte for its
seconds (~1 s compute per footage frame at 30 fps, cached). A 35 s video with 5–6 depth moments is ~12–14 s of matte.

```jsonc
// schema only - the words are placeholders; every real plan uses its own transcript
{"type": "DepthText", "mode": "stack", "start": "w:12-0.05", "end": "w:17", "y": 300,
 "lines": [{"text": "cuma", "at": "w:12", "size": 130, "look": "outline"}, {"text": "3 hari", "at": "w:13", "size": 250}]}
{"type": "DepthText", "mode": "echo", "text": "gratis?", "start": "w:20-0.02", "end": "w:21e+0.25", "y": 430, "tone": "danger", "echoTone": "ink"}
{"type": "DepthText", "mode": "wall", "text": "ulang lagi", "start": "w:40-0.12", "end": "w:44", "y": 220, "rows": 5,
 "size": 150, "speed": 240, "ramp": 2, "scrollY": 50, "looks": ["fill", "outline"], "signature": true}
{"type": "DepthText", "mode": "split", "text": "sebelum", "text2": "sesudah", "start": "w:51-0.08", "end": "w:54", "y": 280, "tones": ["accent", "ink"], "fonts": ["accent", "display"]}
{"type": "HeadRing", "text": "terus dan terus", "start": "w:60-0.1", "end": "w:64", "size": 60, "tone": "paper", "lift": 0.64}
{"type": "Doodles", "start": "w:66", "end": "w:75", "items": [{"kind": "sparks", "at": "w:66", "until": "w:69"}, {"kind": "swirl", "at": "w:73", "color": "accent"}]}
{"type": "FeedWall", "start": "w:80-0.1", "end": "w:92", "frames_src": "auto", "swipes": ["w:85", "w:87", "w:89"], "ramp": 1.8}
```

## PREMIUM PACK: glossy objects, glass pills, badge arcs, side cards, checklists, colour fields

The polished pieces of high-end AE Reels (v3.9). They render in every style and are the identity of LUMEN and POSTER.
All default to layer `below` (captions stay on top). Positions: `slot` around the face (`above`, `above-left`,
`above-right`, `left`, `right`, `chest`, `chest-left`, `chest-right`, `shoulder-left`, `shoulder-right`, plus `dx`/`dy`)
or absolute `x`/`y` (centre). Text is spoken words or user copy.

| type | What it is | Key params |
|---|---|---|
| `Glossy` | glossy 3D objects that spin around their vertical axis (width follows the angle, the rim shows the thickness, a specular band slides across) and float with depth of field and a soft glow | `items: [{kind, at, until?, slot / x,y, size (110-150), depth 0-1, spin (turns/s, 0.55), symbol (coin: 1-3 chars, "Rp", "%"), dx, dy}]`; kinds `coin` `check` `cross` `star` `heart` `bolt` `up` `down` `plus`; `glow: false` |
| `GlowPill` | a pill with spoken words and a round badge that pops in with a glint ring | `text` (1-4 words), `sub`, `subAt`, `at`, `badge` (`check` `cross` `star` `bolt` `up` `down` `plus` `none`), `badgeAt`, `side` (`right`/`left` badge), `surface` (`glass` default / `paper` / `ink` / `accent`), `size` (44-52), `slot` / `x`,`y` |
| `BadgeOrbit` | round badges with icons or kit objects on an arc around the head, popping in one by one, slowly swaying | `items: [{icon | thing, at, tone}]` (4-7), `radius` (0.98 × face height), `from`/`to` (degrees, 195 → 345 = over the head), `lift`, `size` (84-100), `surface` (white / `glass` / `ink`) |
| `SideCards` | tilted rounded cards flying in beside the head (motion blur, a turn, settling at ±8°, floating) with a label chip | `items: [{src (the user's photo/clip path) | frame (source seconds: the speaker's own frame) | thing (kit object on a gradient), label, side, at, until, tilt, dy, grad}]`, `w` (290), `h` (360), `spread`, `frame: false` (no white border) |
| `Checklist` | a list over a blurred, tinted copy of the frame: header pill, rows sliding in from the left, each check badge pops on its word | `title`, `titleAt`, `items: [{text, at}]` (2-4), `y` (420-520), `w` (780), `size` (40-46), `tint`, `blur` (22), `dim`, `badge`, `backdrop: false` (no blur) |
| `Cutout` `bg: "poster"` | the POSTER colour field: a lit radial gradient (light spot, two faint rings, floor shade) opening as a circle from the head; the speaker pops forward with a soft shadow | `color`, `color2` (deep tone), `outline: 0`, `pop` (1.03), `pattern` (dot screen) |

Type looks added for every style: `look: "lumen"` (vertical three-stop gradient + bloom + halo, `grad`: `fire` `gold`
`ice` `mint` `rose` `violet` `silver` or three hex colours) and `look: "neon"` (thin glowing tube); `sweep: true` (a
light band crosses each word once after it lands); `font: "script"` (the style's handwritten script, LUMEN: Ms Madi);
per-line `track` (letter spacing, 0.3 for kickers), `grad`, `echo` (outlined ghosts stepping down under that line,
POSTER titles); `align: "left"/"right"` (x = edge, default 70 px in). Styles can set `depth_defaults` (LUMEN: lumen look
+ sweep; POSTER: fill) - a line's own `look` always wins.

Captions: `lumen` (white words rising out of a blur, emphasis = luminous gradient) and `poster` (heavy white words
popping up, emphasis on a solid accent label).

```jsonc
// schema only - placeholder words; every real plan uses its own transcript
{"type": "DepthText", "mode": "stack", "start": "w:3", "end": "w:9", "y": 280,
 "lines": [{"text": "jalan terus", "at": "w:3", "size": 120, "font": "script"}, {"text": "omzet", "at": "w:4", "size": 250, "grad": "fire"}]}
{"type": "DepthText", "mode": "stack", "layer": "below", "start": "w:12", "end": "w:16", "y": 870,
 "lines": [{"text": "kamu cuma", "at": "w:12", "size": 44, "font": "ui", "track": 0.3, "look": "fill", "tone": "paper"}, {"text": "angka", "at": "w:13", "size": 200, "grad": "gold"}]}
{"type": "Glossy", "start": "w:13", "end": "w:17", "items": [{"kind": "coin", "symbol": "Rp", "at": "w:13", "slot": "left", "size": 130}, {"kind": "coin", "at": "w:14", "slot": "above-right", "depth": 0.8}]}
{"type": "GlowPill", "start": "w:20", "end": "w:24", "text": "langsung jadi", "badge": "check", "slot": "chest", "dy": -30}
{"type": "Cutout", "start": "w:30", "end": "w:36", "bg": "poster", "color": "#2346F0", "color2": "#0B1660", "outline": 0}
{"type": "DepthText", "mode": "stack", "start": "w:30", "end": "w:36", "y": 285, "lines": [{"text": "siapa", "size": 200, "tone": "paper"}, {"text": "sangka?", "size": 230, "tone": "accent2", "echo": 3}]}
{"type": "BadgeOrbit", "start": "w:40", "end": "w:45", "items": [{"icon": "heart", "at": "w:40"}, {"thing": "cup", "at": "w:40+0.12"}, {"icon": "star", "at": "w:40+0.24"}]}
{"type": "SideCards", "start": "w:48", "end": "w:54", "items": [{"src": "/mnt/user-data/uploads/foto1.jpg", "label": "nasi bakar", "side": "left", "at": "w:49"}, {"frame": 12.4, "side": "right", "at": "w:52"}]}
{"type": "Checklist", "start": "w:60", "end": "w:70", "title": "kuliner", "items": [{"text": "rempah", "at": "w:61"}, {"text": "teknik masak", "at": "w:64"}]}
```

## NOTE PACK: explanations above the head (NOTED, v3.10)

A page of notes stuck around the speaker: a mono label that types the topic, strips of masking tape laid on each spoken
word, a ruled index card taped over the blurred frame for a real list, the user's clips taped around the head, one huge
highlighted word. Every element renders in every style (fonts and colours fall back to the style's own). All text is
spoken words or user copy, 1–3 words per strip.

| type | What it is | Key params |
|---|---|---|
| `HeadNote` | a small Geist Mono label (uppercase, wide tracking) that TYPES itself above the head with a block caret: the topic of the section. `mode: "new"` (default) erases the line with a fast backspace and types the new text; `mode: "add"` types `  ·  ` + the new item, so a spoken list grows ("A · B · C"). Default layer `above`. Ink on light walls / paper on dark ones (`tone: "auto"`), white with a glass pill over full-frame `BRoll`, white over a `NoteBoard` | `items: [{text, at, mode}]` (or `text` + `at`), `anchor` `top` (fixed `y`, default 300 - keep ≥ 270) / `face` (`lift` 1.45 × face height above the face centre), `x`, `align` center/left, `size` (32), `weight` (560), `track` (0.2 em), `tone`, `cps` (26 characters/s), `caret` (true), `dot` (accent dot before the text), `pill` (true/false; auto over b-roll), `sep` |
| `NoteTags` | strips of masking tape with torn ends LAID on the frame left → right on their words (then pressed), following the face; `outline` labels drop and turn into place instead. `strikeAt` draws a marker-red line through a strip (the idea is dropped) and dims it. Optional mono `label` over the group and `pointer` (a hairline from the group to the hair - or to the chin from a `chest` group - with a dot: an annotation). Default layer `below` | `items: [{text, at, until?, tone?, rot?, strikeAt?}]` (2–5), `slot` `above` (default) / `above-left` / `above-right` / `left` / `right` / `chest` (or fixed `x`,`y` centre), `dx`, `dy`, `dir` row/column (column on left/right), `maxW` (820), `size` (46-50), `gap`, `surface` `paper` (cream tape, default) / `accent` (highlighter-yellow tape) / `ink` (black tape) / `outline` (dark glass + marker-red outline, white text: questions, steps), per-item `tone` (same names: one yellow strip for the answer), `label`, `labelAt`, `pointer`, `align` |
| `NoteBoard` | a ruled index card (blue rules, red margin, a strip of tape on top) rising over the frame while the frame behind it blurs and dims - the speaker keeps moving behind the blur. A mono heading types in and gets a highlighter stroke, group labels type with a red bullet, every line is numbered (01, 02…) and typed on its word with a caret, `strikeAt` crosses the lines out in a quick stagger. Captions stay white on the dimmed frame; automatic punch-ins and cut transitions skip its seconds. Default layer `below` | `title`, `titleAt`, `groups: [{label?, at?, items: [{text, at, sub?, strikeAt?}]}]` (or `items`; 2–8 lines, 1–5 words each, 1–3 groups; `sub` = a mono note after the line), `strikeAt`, `y` (330, top of the card), `size` (60), `w` (900), `x`, `numbers` (true), `backdrop` (true), `blur` (26), `dim` (0.46), `push` (0.015), `cps` (30) - keep the card's bottom above the captions (~1100) |
| `NoteClips` | the user's own clips or photos taped as cards around the head - 3 cards: left of the head, above it, right of it (2: left + right, 1: above; the middle card shrinks to fit the headroom), a thin white border, a strip of tape on top, a tape label under each, dropping in with motion blur and floating; each clip plays from its `from` when its card lands | `items: [{src (the user's clip/photo path) \| frame (source seconds of the speaker's own footage), from (s), label, at, until?, tilt?}]`, `w` (230), `h` (300), `size` (label, 34), `frame: false`, `tape: false` |
| `BigNote` | the hook word written like a note: one huge Inter Tight Black word comes into focus, then a highlighter stroke is drawn behind it (`mark: "highlight"`), or a marker-red circle around it (`"circle"`) or a double underline (`"underline"`); a mono kicker types above it, or beside it as a margin note with a drawn arrow (`kickerPos: "right"` / `"left"`, auto-sized to stay inside the frame). Captions hide while it shows the spoken words. Default layer `below`; `behind` lets the head cover its lower part | `text` (1–2 spoken words), `at`, `kicker`, `kickerAt`, `kickerPos` (above / right / left), `kickerSize` (30), `size` (220-300, auto-fits the width), `x`, `y` (centre of the word, ~440-500 above the head), `tone` (auto / ink / paper / accent2 / #hex), `mark`, `markAt`, `case` (none / upper / lower), `layer` |

Placement: notes live in the headroom (y ≈ 270 → the hair) and beside the head; never on the eyes or mouth. One focal note
at a time: a `HeadNote` plus one `NoteTags` group (or one `BigNote`) is the maximum together; a `NoteBoard` replaces
everything else.

```jsonc
// schema only - placeholder words; every real plan uses its own transcript
{"type": "HeadNote", "start": "w:3-0.1", "end": "w:20", "items": [{"text": "nama model", "at": "w:3"}, {"text": "kecil", "at": "w:9", "mode": "new"}, {"text": "besar", "at": "w:10", "mode": "add"}]}
{"type": "BigNote", "start": "w:5-0.2", "end": "w:9", "text": "gratis", "at": "w:5", "kicker": "semua fiturnya", "kickerAt": "w:7", "kickerPos": "right", "y": 450, "layer": "behind"}
{"type": "NoteTags", "start": "w:24", "end": "w:31", "label": "dulu", "pointer": true,
 "items": [{"text": "manual", "at": "w:25", "strikeAt": "w:28"}, {"text": "chat doang", "at": "w:29", "tone": "accent"}]}
{"type": "NoteTags", "start": "w:40", "end": "w:46", "slot": "right", "surface": "outline", "items": [{"text": "gimana caranya?", "at": "w:41"}, {"text": "tentukan tugas", "at": "w:44"}]}
{"type": "NoteBoard", "start": "w:50-0.05", "end": "w:66+0.3", "title": "per hari ini", "strikeAt": "w:65",
 "groups": [{"label": "kelas", "items": [{"text": "junior", "at": "w:52"}, {"text": "senior", "at": "w:54", "sub": "kelas besar"}]}]}
{"type": "NoteClips", "start": "w:70", "end": "w:80", "items": [{"src": "/mnt/user-data/uploads/klip1.mp4", "label": "horor", "at": "w:71"}, {"src": "/mnt/user-data/uploads/foto2.jpg", "label": "sejarah", "at": "w:73"}]}
```

Captions look `note` (NOTED default): Geist in sentence case with punctuation, words fade in out of a soft blur, emphasis =
a yellow highlighter stroke drawn behind the word (the word turns ink). A caption zone with `"tone": "ink"` renders the
boxless looks (`note`, `reel`, `poster`, `lumen`, `type`, `clean`) in ink without a shadow - for a light surface you put
behind the captions yourself.

## Planner output you must read
plan-edit prints, besides the summary:
- `editor:` fillers cut, cut transitions chosen, rhythm pushes added, longest static stretch.
- `NOTE:` automatic decisions (captions hidden under a title, hook push) and `RETAKE?` suggestions - read the
  quoted words; if it is really a restart add the range to `remove_words`, otherwise ignore it.
- `variety:` number of different devices, signature moments and custom component files.
- `NOTE: CONCEPT?` / `NOTE: SIGNATURE?` - no concept / nothing only this video has: the plan is a template.
- `NOTE: CONTRAST?` - a Cutout / poster colour is close to the speaker's clothes: pick a contrasting field colour.
- `LINT:` technical problems (safe zone, caption band, missing files, unknown overlay types, custom component misuse). Fix all.
- `SLOP:` taste problems (see anti-slop.md), including `monotony` (one type used too often) and `same look` (three
  identical titles). Fix all, then re-run until it prints `CLEAN`.

## sfx

Automatic cues come from layouts and overlays (see audio-guidelines.md). Add explicit ones for moments that need
weight: `{"at": "w:13", "kind": "impact", "gain_db": -2}`. Kinds: `whoosh swoosh pop click tick typing impact
riser ding shimmer snap scribble glitch paper page` (`typing` needs `until`; `scribble` = marker on paper, `glitch` = digital stutter; `paper` = a strip of tape laid, `page` = a sheet sliding over the frame; `whoosh`/`riser`/`page` are pre-rolled so their peak lands on `at`).

## Minimal examples

Auto edit, no graphics: `{}`

Captions + hook + one punch-in:
```json
{"style": "impact",
 "captions": {"zones": [{"from": "t:0", "to": "w:6e", "variant": "hook"}]},
 "emphasize": [{"w": 4, "icon": "bolt"}],
 "camera": [{"at": "t:0", "preset": "talk"}, {"at": "w:4", "preset": "punch"}]}
```
A full worked plan (before/after split, feature tiles, chat revision, end card) is in
`assets/examples/talking-head-ad.creative.json` with notes on why each beat was chosen.
