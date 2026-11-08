# Changelog

## v3.10.0
**New style 10: NOTED - simple and clear, like a page of notes stuck around your head.** A quiet, informative talking-head
look: the topic types itself above the head, the things you name are taped there, real lists go on a ruled index card,
and one highlighter stroke marks the word that matters.
- **NOTED (style 10)**: natural warm grade, Geist subtitles with their punctuation and a yellow highlighter stroke on the
  key word, Geist Mono labels and margin notes, Inter Tight on tape and cards, a new `note` music bed (86 bpm notebook
  groove).
- **Note pack** (every style):
  - `HeadNote`: a mono label that types itself above the head with a block caret - the topic of each section; `mode:
    "new"` erases and retypes it, `mode: "add"` grows a spoken list ("A · B · C"); a glass pill over b-roll; `anchor` top
    or face.
  - `NoteTags`: strips of masking tape (cream, yellow, black) with torn ends laid on the frame above or beside the head on
    each word, or outlined labels; a mono group label, a hairline `pointer` to the head, and `strikeAt` - a marker-red line
    crossing a strip out when the speaker drops an idea ("dulu… tapi sekarang").
  - `NoteBoard`: a ruled index card taped over the blurred, dimmed frame for a real list - a highlighted mono heading,
    numbered lines typed on their words, a strike pass; punch-ins and cut transitions skip its seconds.
  - `BigNote`: the hook word huge with a highlighter stroke (or a marker circle / double underline) and a mono kicker
    above it or a margin note with a drawn arrow beside it, auto-fitted to the frame.
  - `NoteClips`: the user's own clips/photos taped as cards left of, above and right of the head with tape labels; each
    clip plays from its own start when its card lands.
- **Captions `note`**: words fade in out of a soft blur, punctuation kept, highlighter emphasis. A caption zone with
  `tone: "ink"` now turns every boxless caption look ink for light surfaces.
- **MoodGrade `mono`**: black & white that blooms into colour (flashbacks, "before" moments).
- **Sounds**: `paper` (a strip laid) and `page` (a sheet over the frame) join the procedural palette.
- **BRoll**: clips now play from their `from` at the overlay's start (a clip used to be read at the composition time);
  `enter: "cut"` / `exit: "cut"` for back-to-back b-roll cut on the words.
- **Smooth camera** (`camera.smooth`, NOTED default, any style via `style_overrides`): no zoom jump at jump cuts, no cut
  transitions, punch-ins become a soft eased push and release, the hook push and rhythm pushes glide instead of snapping
  or resetting on a cut; note elements follow a smoothed face position so they glide after a cut.
- **Planner**: note lint (surfaces, slots, marks, label length, strip count, card size, reading time per strip/line/label),
  grounding for card groups and tape labels, `same look` ignores the running HeadNote, NoteClips media copied into the
  render, no automatic punch-ins or cut transitions under an index card.
- Docs: NOTE PACK in `references/creative-plan.md`, note patterns in `references/visual-ideas.md`, motion specs, caption
  and audio rows, anti-slop rows, a note-pack line in every style, worked plan `assets/examples/noted.creative.json`.

## v3.9.0
**Two new styles - LUMEN and POSTER - and the Premium Pack.** The polished pieces of high-end After-Effects Reels,
usable in every style.
- **LUMEN (style 8)**: cinematic and luminous. A warm night grade that turns bright rooms into a moody set, big words
  in a three-stop gradient with bloom, halo and a light sweep, Ms Madi handwritten script for the human word,
  letter-spaced kickers, glossy 3D coins and checks, glass pills with badges, `lumen` captions (emphasis glows), a new
  warm `lumen` music bed.
- **POSTER (style 9)**: every key beat is a designed poster. A lit colour field opens as a circle from the speaker's
  head (`Cutout bg: "poster"`), filled two-tone Archivo Black titles behind the speaker with outlined ghost echoes
  stepping down, icon badges in an arc around the head, cards flying in beside the head, checklists over the blurred
  frame, `poster` captions (emphasis on an accent label).
- **Premium Pack** (every style): `Glossy` (coin with a symbol, check, cross, star, heart, bolt, up, down, plus - lit 3D
  discs that spin with a rim and a specular band), `GlowPill` (glass / paper / ink / accent pill + badge with a glint
  ring), `BadgeOrbit` (icons or kit objects on an arc over the head), `SideCards` (the user's photos, the speaker's own
  frames or kit objects, flying in with motion blur), `Checklist` (header pill + rows with check badges over a
  blurred, tinted frame).
- **Type engine**: looks `lumen` (gradient + bloom) and `neon` (glowing tube) with gradient presets (fire, gold, ice,
  mint, rose, violet, silver), `sweep: true` light sweep, `font: "script"`, per-line `track`, `grad`, `echo` ghosts,
  `align` left/right stacks, style-level `depth_defaults`; glow is never clipped by the reveal mask.
- **Planner**: `CONTRAST?` warning when a colour field is close to the speaker's clothes (red shirt on a red poster),
  premium pack lint (kinds, slots, gradients, readable lengths), assets for side cards (photos / own frames), sounds for
  every new element, caption auto-hide under pills and checklists, face lint for type placed in front of the speaker.
- Fonts bundled (SIL OFL, licences included): Outfit, Archivo, Ms Madi.
- Docs: PREMIUM PACK in `references/creative-plan.md`, premium patterns in `references/visual-ideas.md`, motion specs,
  anti-slop rules, caption rows, a premium note in every style, worked plans `lumen.creative.json` and
  `poster.creative.json`.

## v3.8.0
**New style 7: DEPTH - the premium After-Effects Reels look.** Built on the depth pack: big, tight grotesk type lives
on the wall behind the speaker, accent words switch to a fine italic serif, words orbit the head, marker doodles draw
on around it; the footage stays bright and clean and the subtitles stay quiet.
- **Type system**: Inter Tight Black (uppercase, tight) for the huge words + Instrument Serif Italic for accent words
  (both SIL OFL, bundled). One electric-blue accent, ink type on light walls and white on dark ones.
- **New caption look `reel`**: no box, Inter Tight SemiBold in sentence case with a soft shadow; each word arrives out
  of a blur with a short rise; emphasis words switch to the italic serif in the pale accent.
- **Font roles in the depth pack**: `font: "accent"` (the style's italic serif, as spoken), `display`, `serif`,
  `condensed`, `ui` - per line of a stack, per row of a wall (`fonts: ["display", "accent"]` alternates grotesk and
  italic rows) or per word of a split.
- **New music bed `depth`**: 100 bpm minimal groove, minor 9ths, half-time kick with claps, soft plucks.
- Clean bright grade, light vignette, almost no grain; cuts zoom-through / push about one per 3.6 s.
- Style picker, aliases ("after effects", "AE premium", "aesthetic", "teks di belakang badan", "elemen di kepala" →
  DEPTH), caption guide, README and a worked plan (`assets/examples/depth.creative.json`) updated.
- Planner: DepthText modes count as different looks in the `same look` check; font roles are validated.

## v3.7.0
**Depth pack: the premium After-Effects Reels tricks, on the speaker's head.** Type that lives behind the person,
words that orbit their head, doodles drawn around it, and a feed that never ends behind them.
- **DepthText** (type behind the speaker) in four modes: `stack` (lines rise out of masks, letter spacing tightens,
  slow creep), `echo` (the shock word slams in, outlined echoes fan out above and below), `wall` (rows of a spoken
  phrase sliding in alternating directions, optional endless upward drift and a speed ramp), `split` (two words part
  from the centre to the edges). Six looks per line/row: fill, outline, chrome, gradient, extrude, glow. Type
  auto-fits the frame with the bundled fonts' real metrics and drifts a little against the face (parallax).
- **HeadRing**: a ring of spoken words orbiting the head in 3D. The back half passes behind the head (mirrored,
  dimmer), the front half in front of it, letters turn edge-on at the sides; a contrasting edge keeps the words
  readable over hair and wall.
- **Doodles**: 14 hand-drawn marker doodles that draw on around the head and boil at 12 fps like real hand
  animation: sparks, swirl, question, exclaim, halo, circle, shock, crown, bulb, stars, arrow (with a spoken label),
  scribble, drops, heart. They follow the face and stay inside the safe area.
- **FeedWall**: an endless tilted wall of vertical video cards scrolling up behind the speaker, filled with the
  speaker's own frames (`frames_src: "auto"`) or plain colour cards; each spoken cue swipes one card up; the speed ramps.
- **Camera hits**: `shake` (decaying handheld shake on the impact word) and `glitch` (RGB split + jitter), with
  face-tracked graphics and the person cut-out moving with the footage.
- **Sounds**: `scribble` (marker on paper) and `glitch` (digital stutter) join the procedural SFX palette; every new
  element has its own cues (echo whoosh + impact, wall whoosh + riser, ring shimmer, feed swipes).
- **Auto tone**: the planner samples how bright the wall behind the speaker is (`wallLum`), so `tone: "auto"` picks
  ink type on light walls and paper type on dark ones.
- **Planner**: checks modes, looks and doodle kinds, splits a HeadRing into its two halves, hides captions while
  type behind the speaker shows the spoken words, fills feed cards with the speaker's frames, and flags camera hits
  used as decoration (max 2 of each per 30 s, 3 s apart) and doodles on every sentence.
- **Scene kit**: items placed around the head now keep their label chips inside the frame.
- **Docs**: DEPTH PACK in `references/creative-plan.md`, a "Depth pack" pattern table in `references/visual-ideas.md`,
  motion specs in `references/motion-design.md`, new anti-slop rules, a "Depth pack in this style" note in every
  style, and a worked plan (`assets/examples/depth-pack.creative.json`). Custom components can reuse
  `measureText`, `typeLook`, `toneColor` and the four new elements from `lib/kit.js`.

## v3.6.0
**A scene kit: pictures for any script.** Elements now follow the script, whatever it is about.
- **SceneBuild**: compose a scene from parts in creative.json, no code: people (stand, wave, point, cheer, shrug,
  hold), crowds with search bars or labels above every head, 48 flat objects (house, store, building, money, coin,
  wallet, clock, hourglass, calendar, phone, laptop, chart, scale, lock, key, bag, box, cart, truck, car, chat, doc,
  bulb, target, trophy, heart, star, magnifier, gear, globe, pin, camera, mic, play, cup, rocket, eye, bell, warning,
  check, cross, question, book, door, plant, fire, battery, gift), chips, display text, speech/thought bubbles,
  cards, hanging price tags with a stamp, typed search/prompt bars, counters (Indonesian number format), bar charts,
  progress meters, drawn arrows, numbered steps, the speaker's footage in a phone frame, panels and rings.
  Each item enters on its word (rise, pop, drop, slide, grow, slam, fade), can float, swing, pulse or spin, and the
  scene camera can push into a point. Things can also float around the speaker's head (above, beside, on the chest),
  never on the face.
- **Visual-ideas playbook** (`references/visual-ideas.md`): how to find the picture in a script, 26 script patterns
  with their picture and kit recipe, motif and composition rules, three worked scripts.
- Everything in the kit wears the current style: solid ink lines on flat and light styles, white lines on dark ones.
- Planner: checks kit items (kinds, poses, object names, face slots, reading time, caption band), hides captions while
  a kit item shows or types the words being said, sounds per move (drop, slam, slide, pop, typing, counting, stamps).
- Custom components can draw kit items too (`renderItem`, `THINGS` in `lib/kit.js`).
- Fix: steps draw their connecting lines under the numbers.

## v3.5.0
**Every video designed from its own story.** Two videos in the same style now share the look (fonts, colours,
captions, motion) but not the composition.
- **Direct it first**: before choosing any element, Claude writes the video's concept (the picture the script is
  about) and its signature moment (something only this video has). The plan records it (`concept`).
- **Creative mode**: 1–2 custom scenes written for the script itself (e.g. a crowd of silhouettes with search bars
  above their heads, a price tag that drops and swings). Worked examples and a skeleton in `assets/custom-examples/`,
  rules in `references/custom-components.md`. Custom scenes use the style's colours and fonts and are checked like
  every other element (spoken words only, timing, safe zones, no randomness or network).
- **Title variants** for `HeroTitle`: `layout` center/left/right, `stack` (poster), per-word `sizes`, entrances
  `rise` / `slam` / `slide` / `type`, and huge words behind the speaker's head.
- **One library for all styles**: every overlay renders in every style in that style's own colours (solid surfaces on
  light styles and the flat look, glass on dark ones). `SearchStack` and `SceneLens` gain solid versions.
- **Brand kit**: a creator's colours, fonts, own copy, glossary and default settings in one JSON file
  (`assets/examples/brand-kit.example.json`), applied with `"brand_kit"`.
- **Transitions vary per video**: orb origin (face, corner, centre) and bars (up/down, 5–7 strips) are chosen from
  what was said, so no two videos share the same covers.
- Planner: `variety:` summary line; new checks `monotony` (one element type used too often) and `same look` (three
  identical titles); `CONCEPT?` / `SIGNATURE?` notes; captions hide automatically while any element types a spoken
  quote; sound `cues` per element; unknown overlay types are reported.
- Fix: face-tracked elements (search bars above the head, accents) stay in place during the final hold instead of
  disappearing when the footage ends.
- Fix: an accent word written with punctuation (`"berani?"`) is highlighted.
- MOGRAPH MIX: the 30-second recipe is now a rhythm; titles on face beats are optional and varied.

## v3.4.0
**New style: STORYTELLER** (`storyteller`) - cinematic storytelling where the picture follows the emotion of every
line. For "bayangin kalau…" stories, what-ifs, opinions, surprising facts and dramatic narration.
- **Mood grading** (`MoodGrade`): warm, dream, amused, excited, shock (white flash + red punch), whisper (rose,
  desaturated, heavy vignette), serious (cold blue) and dark, eased in and out per beat; script markers like
  [shocked] / [whispers] decide them.
- **SearchStack**: generic search bars floating above the speaker's head (face-tracked), queries typed with a caret,
  newer bars pushing older ones up; shimmer placeholders for the premise.
- **Floaters**: extruded 3D glyphs and icons (?, !, eye, search, heart, lock) drifting around the head with depth of field.
- **Shout**: Unbounded Black word with a coloured 3D extrusion that slams in with motion blur, works behind the speaker.
- **StoryTitle**: thesis title rising out of a mask with a drawn light arc and flare; tracked gold kicker.
- **SceneLens**: full-screen dark word cloud read by a gliding magnifier, word by word on its cue.
- **Cover transitions** `bloom` (white light swell) and `iris` (black circle; `"hold": true` ends the video in black).
- `type` captions (letters typed in as each word is spoken, gold caret), a slow minor `cinematic` music bed, new icons
  (eye, lock, heart, search).
- Fonts (SIL OFL): **Unbounded** (display) + **Plus Jakarta Sans** (captions/UI).
- Planner: reading-time checks per search bar and per Shout line, captions hidden while a spoken quote is typed,
  automatic sounds for the new elements (impact, riser, typing, shimmer, swoosh).
- Fix: cutting long edits with many pauses removed no longer runs out of memory (the video is cut in one pass).
- Fix: a camera `punch` now pushes from the framing active at its time, even when it is written out of order in
  creative.json (it used to inherit the last listed preset and could leave the shot zoomed in).
- verify: a planned `iris` + `hold` ending is no longer reported as black frames.

## v3.3.0
**MOGRAPH MIX `flat` look** - the same scenes and eased motion without any gradients, glows or glass, for a clean,
printed feel: warm paper backdrop with a drifting drafting grid and crop marks; solid ink black, signal orange and cobalt;
HeroTitle words on solid blocks that wipe in; solid cards, chips and chat bubbles; a solid ink caption pill with orange
key words; a solid orange orb with an ink ring and ink/orange/cobalt bars for the covers. Pick it with `"look": "flat"`
in creative.json ("tanpa gradasi", "flat", "clean"). `aurora` stays the default and renders exactly as before.
- Style configs can now ship named **look presets** (`looks`), merged by the planner when creative.json sets `look`.


## v3.2.0
**New style: MOGRAPH MIX** (`mograph-mix`) - the talking head and full-screen motion graphics take turns, cut like a
premium After Effects Reel, so the face is not on screen the whole time. For sellers, product explainers, workflows,
app demos and online classes.
- **Aurora backdrop**: deep midnight indigo with slowly drifting violet / coral / amber light and a faint perspective
  floor grid. **Glass UI** everywhere (translucent panels, top sheen, light rim), coral → amber gradient highlights.
- **Scenes** (layout `none` + one scene overlay): `ScenePhones` (phones turning in 3D, playing the footage raw vs
  edited, light arc), `ScenePrompt` (generic AI window: clip arcs in, prompt typed, checklist), `SceneFeatures` (chips +
  big gradient word rolling item to item + an animated illustration per spoken item: cut, caption, motion, zoom,
  b-roll, sound), `SceneStack` (glass cards flipping up) and `SceneChat` (video bubble, typed message, progress + check).
- **Eased motion language**: easeOutQuint entrances with blur-to-sharp, easeInQuint exits, masked word reveals with a
  light sweep on the gradient word, expo clip-wipes, 3D depth (rotateX/rotateY), slow camera pushes on every scene.
- **Cover transitions**: layout `transition: "orb"` (gradient orb swells over the frame, a circular hole reveals the new
  shot) and `"bars"` (staggered rounded gradient bars sweep up); scene-to-scene eased pushes with motion blur.
- `HeroTitle` for face beats (kicker chip, kinetic words, gradient tag; each piece timed from its own entrance),
  `Accents` (lens glints, focus brackets, orbit ring) that follow the speaker's head (new `faceOnCanvas` helper),
  `glass` karaoke captions, `hero` end card, a bright `playful` music bed.
- Fonts (SIL OFL): **Bricolage Grotesque** (display) + **Manrope** (captions/UI).
- Planner: SceneFeatures/HeroTitle hide duplicate captions, scene texts are grounding-checked, style option
  `sound.emph_pop`.

## v3.0.0
**Four focused styles.** IMPACT, SYSTEM, EDITORIAL and CREATOR POP. NEWSROOM, BUSINESS BREAKDOWN, INVESTIGATIVE STORY and
SCRAPBOOK CREATOR are retired (their use cases map to the four via aliases; an old style name is answered in one line).

**Reels typography** (all SIL OFL, safe to bundle in a commercial product)
- IMPACT: Anton (hook slams, end-card wordmark) + Montserrat ExtraBold (captions, UI) - the common Reels ad pairing.
- SYSTEM: Geist + Geist Mono (terminal captions and typed titles).
- EDITORIAL: Playfair Display (titles, italic key words) + Poppins (captions, kickers).
- CREATOR POP: TikTok Sans Black/ExtraBold (captions and stickers).
- New font roles `display`/`displayWeight` and `serifWeight` in the style config.

**Background removal, locally (no API)**
- `render-video.py` computes a person matte (U2-Net "silueta" via onnxruntime, downloaded once) only for the seconds
  that use it, cleans it against stray background shapes using the tracked face, smooths it over time and caches it.
- `layer: "behind"` puts any overlay between the background and the speaker - the "text behind subject" look.
- New `Cutout` overlay: background swapped (colour + stripe pattern, blur or dim) while the speaker pops forward with
  a white sticker outline and a soft shadow.
- The planner derives the matte ranges, prints the compute cost, and skips the face-cover lint for `behind` text.
- If the model can't be downloaded the render continues (text in front, no pop-out) and says so.

**Fixes**
- verify-output's asset check follows the fonts actually bundled with the skill.
- FFmpeg fallback maps the new web fonts to bundled TTFs.


## v2.1.0
**Three styles rebuilt with their own signature elements** (they used to share IMPACT's components with a colour swap)
- **EDITORIAL** - cinematic letterbox that slides in over the first second (`frame.letterbox`), a real filmic grade
  (warm, lower saturation, deeper contrast, view vignette) with a flat grey "before", feathered cinema-scrim captions,
  serif emphasis in warm white, new `TitleCard` (kicker, hairline rules, rack-focus serif lines) over a new `Scrim`
  (dim + blur), and an end card in film-title form (`title` variant). Graphic limit raised to 10/min so calm edits
  are not empty.
- **NEWSROOM** - captions printed on torn newsprint strips (`strip` variant), new `Headline` (front-page band,
  printed landing, red block on one word or red circle/underline), cards become taped halftone photos automatically
  (`photo`), torn-paper wipe (`wipe.kind: "tear"`), printed column rules on the backdrop, front-page end card
  (`newsprint` variant).
- **CREATOR POP** - die-cut `Sticker` and `StickerStack` (a spoken list slapped around the face), punchline words as
  mint sticker boxes with hard-shadow outlined captions, `versus` layout (diagonal split-face before/after),
  sticker end card (`sticker` variant), mint/coral palette.

**Engine**
- `EndCard` look follows `style.endcard.variant` (`block` default for IMPACT/SYSTEM is unchanged), with variant-aware SFX
  (no impacts on calm styles).
- Cards accept `rot`, `borderColor`, `halftone`, `tape`; views accept `clip`; layouts can draw a diagonal divider.
- Captions: `clean` scrim + `underline` colour, `strip` variant, `stroke` hard shadow / sticker emphasis / page tilt,
  word gap now accounts for the outline width (long words no longer touch).
- Planner: new elements in grounding, face-cover, reading-time and SFX checks; Headline/TitleCard auto-hide captions
  that show the same words; `keep_captions` to opt out.
- IMPACT and SYSTEM plans and renders are unchanged.

## v2.0.1
**Fixes**
- Face-cover check now uses the real on-screen face position. When the face sits high in the frame, the
  renderer clamps the framing so no edge shows; the planner ignored that clamp, so it could pass text that sat on
  the eyes and flag text that was actually clear of the face. It now also follows the camera while the graphic
  is on screen (punch-ins, close pushes, jump-cut zoom, rhythm pushes), not just the default framing.
- Script alignment (`--script`) on long audio no longer runs out of memory: the aligner processes 20 s chunks
  (a 2-minute clip needed more than 5 GB before).
- Transcription is resumable: it stops at `--budget` seconds with `NOT FINISHED` and continues on the next run,
  so footage longer than ~3 minutes works within claude.ai's command time limit.
- Styles with a single cut transition (EDITORIAL) no longer trip their own "same transition three times in a
  row" SLOP check on an automatic edit: automatic transitions are capped at two for those styles.
- KineticTitle: wider word gap for slam/track/focus lines ("EDIT MANUAL" no longer reads as one word at large sizes).
- Cleaner `inspect-media.py` face output (plain numbers).

## v2.0
**Motion engine (After-Effects grade)**
- Directional motion blur from real velocity (180° shutter) on captions, titles, lists, card morphs, b-roll, cuts.
- Bézier easing library (snappy, smooth, easyEase, glide, heavy, exitFast, anticipate).
- Text presets: rise, pop, slam, track, focus, type, cascade, slide; chip plates open like mask wipes.
- Cut transitions at jump cuts: zoom-through, whip, push, flash, light leak (per style, spaced and varied).
- Finish layer per style: film grain, vignette, colour tint; operator drift for calm styles.
- End-card hold now freezes the last frame instead of dropping the footage.

**Editor behaviour**
- Filler words ("eh", "emm", "anu", "um", "uh") cut automatically; `keep_words` to protect one.
- Retake detection (`RETAKE?` suggestions, `auto_retakes`).
- Rhythm engine: slow pushes fill static stretches; automatic hook push in the first 1.5 s.
- Captions auto-hidden under titles/end cards that show the same words.
- Per-style default settings (calm styles default to subtle SFX and minimal graphics).

**Anti-slop**
- New `SLOP:` lint: double text, ungrounded words, text over eyes/mouth, over-emphasis, icon spam, emoji,
  graphics overload, repetition, empty layouts, unreadable durations, SFX spam, weak hook, flash/burst limits.
- verify-output reports anti-slop and edit rhythm.
- `references/anti-slop.md` and `references/motion-design.md`.

**New elements**
- LowerThird, Steps, Counter, Callout (write-on circle/underline/arrow), BRoll (user photos/clips with Ken Burns),
  ProgressBar, LightLeak; KineticTitle `fx` per line and `dim`.
- User music files (`music.file`), looped, faded, LUFS-levelled and ducked.

**Styles**
- CREATOR POP: new outlined `stroke` captions, yellow/violet palette.
- BUSINESS BREAKDOWN: dark navy caption card, own backdrop.
- Every style: own cut transitions, text preset, finish and rhythm limits.

**Fixes**
- HDR (HLG/PQ, e.g. iPhone) footage was ~35 % too dark after tone-mapping → mobius tone-map matches SDR.
- KineticTitle `x` is now the centre like the other overlays (documented anchor rules).
- Dark caption cards: active word no longer half-invisible while its marker sweeps in.
- End-card wordmark kept spaces ("TRY IT", not "TRYIT"); word spacing in animated titles.
- Cut transitions no longer planned inside card/split layouts where they can't show.
- FFmpeg fallback engine: outlined captions and rhythm pushes.
- Removed leftover third-party branding from the worked example; env vars renamed `VY_*`.

## v1.0
First release: 8 styles, word-synced captions, face-anchored zooms, motion graphics, procedural music/SFX, 60 FPS.
