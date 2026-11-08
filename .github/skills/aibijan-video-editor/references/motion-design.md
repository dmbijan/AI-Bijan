# Motion design language (After-Effects grade, rendered in code)

The engine reproduces what a motion designer does by hand in After Effects: eased keyframes from a speed
graph, real motion blur, masked text reveals, trim-path write-ons, camera moves with weight, and a finishing
adjustment layer. Everything is computed per frame at 60 FPS, so motion is smooth and deterministic.

## 1. Easing (the speed graph)
Named curves are cubic-Béziers, exactly like AE's influence handles:

| Name | Bézier | Use |
|---|---|---|
| `snappy` | 0.16, 1, 0.3, 1 | entrances, text reveals, UI (fast out, long settle) - the default "pro" feel |
| `smooth` | 0.45, 0, 0.15, 1 | layout morphs, rack focus |
| `easyEase` | 0.33, 0, 0.67, 1 | neutral moves (AE F9) |
| `glide` | 0.37, 0, 0.18, 1 | camera pushes and drifts |
| `heavy` | 0.7, 0, 0.84, 0 | slams (accelerates into the landing) |
| `exitFast` | 0.55, 0, 1, 0.45 | exits - always faster than entrances |
| `anticipate` | 0.36, -0.35, 0.3, 1 | small wind-up before a move |
Springs (overshoot) for pops; `inOutExpo` remains for aggressive layout morphs. Linear only for progress bars.

## 2. Motion blur
Real shutter maths: blur length = distance travelled in 1/120 s (180° shutter at 60 fps), applied **along the
direction of travel** (SVG directional Gaussian, quantised so the renderer stays fast). It appears on:
caption words entering, text reveals, list items sliding, card morphs, b-roll entrances, whip/push cuts, and
(as a small isotropic blur) fast punch-ins and zoom-through cuts. Static frames are always sharp.
Don't add extra blur filters by hand.

## 3. Text animation presets (`fx` on KineticTitle lines, default from the style's `textFx`)
| fx | What it looks like | Best for |
|---|---|---|
| `rise` | each word slides up out of an invisible mask, vertical motion blur | clean, precise, business, news |
| `pop` | words scale in with a spring overshoot | energetic, creator, IMPACT chips |
| `slam` | whole line drops from 1.9× and lands hard (pair with an `impact` SFX) | the ONE hook moment |
| `track` | letter-spacing collapses from wide to tight while fading in | documentary, premium |
| `focus` | rack-focus: sharpens from blur while settling | editorial, calm storytelling |
| `type` | typewriter with caret | tech/SYSTEM, prompts |
| `cascade` | characters rise one after another | short words (≤ 8 letters) |
| `slide` | words slide in from the left with horizontal blur | lists, labels |
Chip lines (`accentChip`, `chip`) open their plate like a rectangle mask wipe, then the text animates inside.
Rule: one preset family per video (+ `slam` once). Mixing four presets is slop.

## 4. Cut transitions at jump cuts (`cutFx`, automatic per style)
| fx | Mechanics | Feel |
|---|---|---|
| `zoom` | outgoing pushes in 0.1 s, incoming lands from 1.2× with zoom blur | energy, continuity |
| `whip` | outgoing whips sideways, incoming arrives from the other side, heavy horizontal blur | fast, playful |
| `push` | a smaller, calmer whip | business, tech, documentary |
| `flash` | 2–8 frame white flash + small scale settle | news, camera-shutter |
| `leak` | warm light leak drifts across (screen blend) | editorial, documentary |
Planner rules: only on full-frame shots, ≥ the style's `minGap` apart, on at most half of the cuts, never within 0.6 s of a layout change,
alternating; low intensity = none. Override with `"cut_fx": "none"` or a list `[{"at": "w:31", "fx": "whip", "dir": -1}]`.

## 5. Camera
- Face-anchored framing (`talk` lifts the chin above the captions), punch-ins on emphasis words, jump-zoom
  alternation at cuts, 9-second "breathing" push.
- **Rhythm pushes**: when nothing has changed for longer than the style allows, the planner adds a slow
  `glide` dolly-in (+3–5 %) that resets invisibly at the next cut.
- **Operator drift** (`camera.drift`, px): fractal-noise float for calm styles - feels hand-held, never shaky.

Smooth camera (`camera.smooth: true` in the style config, NOTED): no zoom jump at jump cuts, no cut transitions, punch-ins
become a soft eased push (`punchDur` 0.65 s, `relaxDur` 1 s, ease `camera`), the hook push glides in over 0.9 s and
rhythm pushes glide back instead of resetting on a cut. Note-pack elements stuck to the head follow a smoothed face
position (~0.3 s), so they glide to the new place after a jump cut instead of jumping with it.

## 6. Finish (adjustment layer)
`finish.grain` (0–0.1, boils every frame), `finish.vignette`, `finish.tint` + `tintOpacity` (soft-light colour
wash on footage only), `finish.leak` colours. Subtle by design: felt, not seen.

## 7. Layout morphs
Cards morph between states with `inOutExpo` (default) or springs, and get motion blur from the speed of the
card's centre. The accent `wipe` is a signature move - once per video at most.

## 8. Timing rules (60 FPS)
Entrances 0.25–0.6 s, exits 0.18–0.35 s, morphs 0.3–0.6 s, camera pushes ≥ 0.25 s (rhythm pushes 1.6 s).
Stagger lines 0.06–0.12 s, components 0.2–0.3 s. One hero motion at a time: never start a title, a camera punch
and a layout morph on the same frame.

## 9. Depth pack motion (v3.7)
- **Type behind the speaker** (`DepthText`): stack lines rise out of a mask over 0.62 s (easeOutQuint) with a vertical
  smear from the real velocity, letter spacing tightens 0.06 → −0.02 em over 1.1 s, then the block creeps 4 % until
  it leaves; exits lift out in 0.3 s (easeInQuint), last line first. Echo: the word scales 1.45 → 1 with expo-out in
  0.42 s (motion blur from the scale velocity), echoes fan out 0.06 s apart and breathe ±3 px. Wall: rows slide in from
  alternate sides 0.06 s apart, marquee speed integrates a linear ramp (no jumps), horizontal blur from the true speed
  (180° shutter), optional upward drift with soft top/bottom fades. Split: both words leave the centre together over
  0.8 s and fade in on the way. A 10 % parallax against the face makes the type feel like it is on the wall.
- **HeadRing**: glyphs are placed by their real advance on a circle, then projected (ry = 0.2 rx): width ∝ sin θ, so
  letters turn edge-on at the sides and mirror on the back half, 16 % perspective scale, back half dimmed and 0.8 px
  soft. 0.8–1.1 rad/s reads; faster blurs into a stripe. Enters over 0.8 s (radius grows 82 → 100 %), leaves in 0.4 s.
- **Doodles**: trim-path draw-on (strokes drawn one after another, total 0.25–0.75 s by length), round caps, then
  boil: every point re-jitters 0.7 % of the face height at 12 fps (hand-drawn animation runs "on twos"). Idle: swirls
  turn 40°/s, sparks and bulbs pulse at 9 rad/s, others sway 2°. Exit: fade 0.28 s.
- **FeedWall**: 3 columns at 1 / 1.3 / 0.85 × speed (parallax), a swipe = one card pitch with easeInOutQuint over
  0.45 s (a thumb flick), vertical motion blur per column, 1.2 px depth-of-field blur, tilted 16° / −10° in a 1800 px
  perspective; enters scaling 1.12 → 1 over 0.7 s.
- **Camera hits**: shake = fbm noise ±26 px x / ±20 px y with (1 − p)^2.2 decay over 0.45 s plus a 2.5 % zoom kick;
  glitch = 30 Hz steps of RGB split (8–24 px) and horizontal jitter, on 75 % of the steps, over 0.3 s.

## 10. Premium pack motion (v3.9)
- **Glossy objects**: enter with a back-out pop (1.45 overshoot) over 0.55 s while dropping 50 px and unrotating 25°;
  spin 0.55 turns/s around the vertical axis (width = |cos|, rim offset = sin × 7 % of the size, a specular band slides
  with the angle), bob ±10 px at 1.4 rad/s, sway ±6 px; depth > 0.72 or < 0.12 gets depth-of-field blur; exit 0.28 s.
- **Glow type**: luminous gradient (three stops, top highlight) + a tight bloom (4.5 % of the size) + a wide halo (20 %);
  the reveal mask is padded by 32 % of the size so the glow is not clipped; a light sweep (a 24 %-wide white band at
  100°, screen blend) crosses the word over 0.85 s starting 0.32 s after it lands.
- **GlowPill**: slides up 46 px out of a 10 px blur (easeOutQuint 0.55 s), the badge pops 0.3 s later (back-out 0.42 s)
  with an expanding glint ring; exit drops 24 px and fades in 0.26 s.
- **BadgeOrbit**: badges pop in 0.12 s apart on the arc (back-out), the arc sways ±4° at 0.6 rad/s, each badge bobs ±5 px.
- **SideCards**: fly in 520 px from their side with a 24° turn over 0.62 s (motion blur from the true speed), settle at
  ±8°, float ±7 px; exit flies 600 px out in 0.32 s.
- **Checklist**: the backdrop blurs to 22 px and tints in 0.35 s (easeInOutQuint); the header pill drops and widens
  (scaleX 0.6 → 1); rows slide 90 px in from the left out of an 8 px blur, 0.25 s before their word; the check pops on
  the word.
- **Poster field**: a circle opens from the head to the far corner in 0.42 s (snappy), the light spot drifts slowly; it
  closes back in 0.3 s. The speaker pops 3 % forward with a soft drop shadow.

## 11. Note pack motion (v3.10)
- **HeadNote**: fades in 10 px over 0.22 s; types at 26 characters/s (Geist Mono 560, tracking 0.2 em) with a block caret
  that stays solid while typing and blinks out 1.2–1.6 s after the last character (and blinks while waiting for its first
  word); a `new` line first erases the old one with a backspace run (≤ 0.26 s); an `add` item types `  ·  ` + the item.
  Exit: 0.25 s up and out.
- **NoteTags (tape)**: each strip is laid left → right in 0.32 s (easeOutQuint clip reveal) while it turns 1.6° into its
  resting angle (±0.7–2°, alternating), then pressed (a 2.5 % squash over 0.22 s); torn ends are different on every strip.
  Outlined labels instead drop ~0.95 × their size, turn 6° and scale 1.08 → 1 in 0.36 s with motion blur. Strike: a
  0.12 × size marker line draws left → right in 0.24 s at −3°, the strip dims to 62 %. The pointer hairline draws on with
  the first strip, then a dot lands with a fading ring. Exit: 0.22 s lift + fade, staggered.
- **NoteBoard (index card)**: the frame behind blurs to 26 px and dims over 0.35 s (easeInOutCubic); the card rises 170 px
  and settles from −4.6° to −1.1° in 0.5 s (quint out, vertical motion blur); heading, labels and lines type at 30
  characters/s with a caret; the heading's highlighter scales in after it is typed; strikes stagger 0.08 s per line;
  a 1.5 % slow push; exit 0.3 s down and out while the blur clears.
- **BigNote**: the word comes into focus (blur 14 → 0, scale 1.06 → 1) in 0.42 s and creeps 2.5 % over its life; the
  highlighter is drawn 0.22 s later in 0.34 s (a marker circle in 0.5 s, the double underline in two strokes); a margin
  note's arrow draws on in 0.35 s while the note types; exit 0.28 s up and out.
- **NoteClips**: cards drop 140 px with a 12° turn, scale 0.9 → 1, opacity in the first 40 %, over 0.5 s (motion blur), then
  float ±4 px; the tape label is laid 0.26 s after its card; exit 0.3 s up and out.
- **Captions `note`**: each word fades in out of a 7 px blur in 0.22 s (no rise); the highlighter stroke scales in left →
  right over 0.3 s from the word's start, rotated −1.2°.

## What the engine does NOT do (say so if asked)
3D camera tracking, rotoscoping/masking the person, object removal, particle simulations, AI frame
interpolation. 2-CPU rendering keeps effects to what reads on a phone.
