# Limits — be honest with the user

## Works (tested)
- **Portrait 9:16 1080×1920 60 fps** films (default) and landscape 16:9 1920×1080 60 fps from one VO (+ script),
  25 s – a few minutes, each directed for its own frame; burned-in captions in the safe area (portrait;
  landscape: `.srt` only unless asked); optional user branding.
- Built-in worlds `coast`, `kos` + the `mind` studio; the 5 ready-made worlds `rumah`, `sekolah`, `kantor`,
  `minimarket`, `perpustakaan` (each checked with one test scene per place).
- **Dynamic worlds from data** (world.json): interiors (rooms, corridors, doors, windows, signs, wall
  bands, handrails), open halls (markets), exteriors (terrain, paths, buildings, scattered forests,
  lamp posts, campsites), day / dusk / dawn / night, fog. Tested: night hospital ward, traditional market,
  night forest, library in a park, night office with a meeting room (buyer test "ABSEN PUKUL DUA"); buyer tests
  of v1.4.0: a minimarket "what if", a kos at night, two home horror films (a house with a back corridor,
  a terrace + CCTV).
- **Objects from primitives** (object.json) with anchors, states, glow; built-in trees/bushes/rocks.
- Object interaction: push, pick up, put down, give/receive, open/close doors, kneel at a point, write at a
  spot, aim a flashlight, objects moving by themselves.
- Automatic cameras that avoid walls, furniture, foliage, other people and the subject's own head.
- Geometry preflight + automated video QA + a written visual review gate.
- **VO timing without whisper.cpp:** one global alignment of the script to the VO's speech segments.
  Measured on 4 ElevenLabs VOs (67–145 s): every sentence start lands on its own pause, worst sentence
  ≤ 1.4 s off a whisper reference (median ≈ 0), no drift towards the end; on VOs with known sentence
  times: 0 of 195 sentences > 0.5 s off. Inside a phrase words are spread by syllables (≈ ±0.3 s).
- **Story analyzer in context** (Indonesian): roots of words (me-/ber-/di-/-kan/-nya…), quotes, negation,
  conditionals, screens/photos/mirrors, pronouns and "-nya", second-person stories, a double of the
  protagonist, the story's own clock ("pukul dua lewat tiga belas" → 02.13 → night), places and their
  areas, objects implied by the place, unlisted nouns and unknown verbs reported for a decision.

## Limited
- Stylised procedural people (no cloth/hair simulation, simple hands); no phoneme lip-sync. A film has up to 3
  people; scenes are directed for 1–2.
- Walking follows a navigation grid on flat floors and terrain in dynamic worlds (built-in worlds: straight lines
  between marks); nobody walks from one place to another (cut to the next place); no stairs, ladders or climbing;
  no leg IK on slopes (feet follow the ground height, legs do not bend to the slope).
- Objects moved during the film do not block later walking paths (keep paths clear by staging).
- Tree crowns are simple low-poly blobs/cones; palm fronds look rough.
- Insert shots of hands are readable but simple (no finger contact detail). One hand carries: no two-hand
  carrying, throwing, opening drawers / lids by hand, or handing things through a window.
- Primitives build furniture, containers, tools, signs well; stringed instruments, statues, creatures and other
  organic silhouettes only as simple stand-ins (e.g. a ghost figure is a smooth wrapped shape); building
  exteriors are simple boxes with windows, doors and roofs.
- The analyzer is rule-based, not a language model: on scripts it had never seen it got time of day and
  place right almost always, the cast and the key actions about half the time before its rules were
  extended. Read its REQUIREMENTS.md against the script; it lists what it could not map instead of guessing.
  Indonesian only.
- Synthesised sound is clean and in sync but less organic than recorded foley.
- Word timing without whisper.cpp is approximate (≈0.3 s). Tested on macOS Apple Silicon only.

## Not available (offer the closest thing, never fake it)
- Animals that move, crowds, vehicles driving, detailed vehicles (car, motorbike, bicycle; the built-in `kos`
  yard has one simple parked scooter as set dressing) — primitives cannot build them convincingly: report, keep
  off screen / by sound, or use a stylised stand-in (an animal only sitting still) and say so in one line.
- Fights, sports, dancing, swimming, climbing, hugging, eating/drinking, falling.
- Rain/snow particles, explosions, big fires (fire = campfire/lantern flames only: a forest or a building on fire
  cannot be shown — a row of campfires reads as dark frames with orange blurs). Tell the story of a big fire with its
  effects: an orange / grey sky, smoke (`fog`), a glow behind silhouettes (`dusk` sky + warm `spot` / lamps), people
  reacting, a tag ("Ratusan km"); keep `fire` fixtures for campfires, stoves, candles seen up close.
- Real people's likeness, logos/brands, copyrighted characters.
## Known gaps (handled in data)
- No fixed / security-camera shot type: a static CCTV angle is a place of its own (`kantor` has `cctv`) or a
  `shot.subject` that is not in the scene (the camera then holds the place's `establish` view). Used in buyer tests;
  still check it on the stills.
- Screens cannot show a live camera picture (no render-to-texture): a monitor glows with text; show the
  footage as a cut to a full-frame `mood: "cctv"` view.
- `move` is [x, z] only (things drop to the floor): paper sliding out of a printer = stepped object states;
  a laptop lid closing = an instant state swap.
- A carried object is turned 90° from the body: lay hand-held paper text along its local −x.
- Each place lights itself with the 5 fixtures nearest to its centre: split a large room into places with
  an `area`. A `screen` fixture is a fixed 0.5×0.32 m box without yaw: hide it inside the device.
- No printer SFX (build it from `machine` + `roll` + `click`); fluorescent hum only in the `hospital` ambience.
- No collared shirt top (`jacket` instead); a bowed reading head hides the face in medium / close-up.

## Portrait — known limits
- Tight spots: a person handling something in a corner can only be filmed from behind (the camera search avoids
  walls and furniture) — give handling marks room.
- Over-the-shoulder shots keep the shoulder as an edge of about a third of the frame width.
- Two people side by side are shot staggered in depth (past one shoulder), never as a wide side-by-side two-shot.
- Wide geography reads less than in 16:9: establishing shots show depth (rows, corridors), not breadth.
- Captions are short word-timed lines from the VO words; their accuracy is the word timing's (±0.3 s inside a phrase
  without whisper.cpp).
- Signs near the frame edge can be cut by the narrow frame.
- Wide shots in portrait keep the whole figure, so in a big place a person can be small; cards on a medium can touch
  the hairline.
- Stylized people (no realistic faces), no lip-sync, feet read bare in some shoes; tested on macOS Apple Silicon.
